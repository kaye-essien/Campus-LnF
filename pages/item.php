<?php
session_start();
require '../includes/db.php';
require '../includes/auth.php';

$id = intval($_GET['id'] ?? 0);
if (!$id) { header("Location: /pages/browse.php"); exit(); }

$stmt = $conn->prepare("SELECT i.*, u.name AS reporter, u.email AS reporter_email FROM items i JOIN users u ON i.user_id = u.id WHERE i.id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$item = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$item) { echo "Item not found."; exit(); }

$imgs = $conn->prepare("SELECT image_path FROM item_images WHERE item_id = ? ORDER BY id ASC");
$imgs->bind_param("i", $id);
$imgs->execute();
$images = $imgs->get_result()->fetch_all(MYSQLI_ASSOC);
$imgs->close();

$pageTitle = htmlspecialchars($item['title']);
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['claim'])) {
    requireLogin();
    if ($_SESSION['user_id'] == $item['user_id']) {
        $error = "You can't claim your own item.";
    } else {
        $chk = $conn->prepare("SELECT id FROM claims WHERE item_id = ? AND user_id = ?");
        $chk->bind_param("ii", $id, $_SESSION['user_id']);
        $chk->execute();
        if ($chk->get_result()->num_rows > 0) {
            $error = "You've already submitted a claim for this item.";
        } else {
            $msg = trim($_POST['message']);
            $ins = $conn->prepare("INSERT INTO claims (item_id, user_id, message) VALUES (?, ?, ?)");
            $ins->bind_param("iis", $id, $_SESSION['user_id'], $msg);
            $ins->execute();
            $ins->close();
            $conn->query("UPDATE items SET status='claimed' WHERE id=$id");
            $item['status'] = 'claimed';
            $success = "Claim submitted! The reporter will review it.";
        }
        $chk->close();
    }
}

$claims = [];
if (isLoggedIn() && ($_SESSION['user_id'] == $item['user_id'] || isAdmin())) {
    $cr = $conn->prepare("SELECT c.*, u.name AS claimant_name, u.email AS claimant_email FROM claims c JOIN users u ON c.user_id = u.id WHERE c.item_id = ? ORDER BY c.created_at DESC");
    $cr->bind_param("i", $id);
    $cr->execute();
    $claims = $cr->get_result()->fetch_all(MYSQLI_ASSOC);
    $cr->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['claim_action'])) {
    requireLogin();
    if ($_SESSION['user_id'] == $item['user_id'] || isAdmin()) {
        $claim_id = intval($_POST['claim_id']);
        $action   = $_POST['claim_action'] === 'approve' ? 'approved' : 'rejected';
        $conn->query("UPDATE claims SET status='$action' WHERE id=$claim_id");
        if ($action === 'approved') {
            $conn->query("UPDATE items SET status='resolved' WHERE id=$id");
            $item['status'] = 'resolved';
        }
        header("Location: /pages/item.php?id=$id");
        exit();
    }
}
?>
<?php include '../includes/header.php'; ?>
<div class="container" style="max-width:780px">
    <a href="/pages/browse.php" style="color:var(--muted);font-size:0.9rem">← Back to Browse</a>

    <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;margin-top:1.5rem">

        <?php if (!empty($images)): ?>
        <div style="display:flex;gap:0.5rem;overflow-x:auto;padding:0.5rem;background:var(--bg)">
            <?php foreach ($images as $img): ?>
                <img src="/uploads/<?= htmlspecialchars($img['image_path']) ?>"
                     style="height:260px;min-width:260px;object-fit:cover;border-radius:var(--radius);flex-shrink:0" alt="">
            <?php endforeach; ?>
        </div>
        <?php else: ?>
            <div style="width:100%;height:200px;background:var(--bg);display:flex;align-items:center;justify-content:center;font-size:4rem">
                <?= $item['type'] === 'lost' ? '🔍' : '📦' ?>
            </div>
        <?php endif; ?>

        <div style="padding:2rem">
            <div style="display:flex;gap:0.5rem;flex-wrap:wrap;margin-bottom:0.75rem">
                <span class="badge badge-<?= $item['type'] ?>"><?= strtoupper($item['type']) ?></span>
                <span class="badge badge-<?= $item['status'] ?>"><?= strtoupper($item['status']) ?></span>
                <?php if ($item['category']): ?>
                <span style="background:var(--bg);border:1px solid var(--border);color:var(--muted);padding:0.2rem 0.6rem;border-radius:20px;font-size:0.75rem"><?= htmlspecialchars($item['category']) ?></span>
                <?php endif; ?>
            </div>
            <h1 style="font-size:1.6rem;margin-bottom:1.25rem"><?= htmlspecialchars($item['title']) ?></h1>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;margin-bottom:1.5rem;font-size:0.9rem;color:var(--muted)">
                <div>📍 <strong style="color:var(--text)"><?= htmlspecialchars($item['location'] ?? 'Not specified') ?></strong></div>
                <div>🕐 Posted <strong style="color:var(--text)"><?= date('M j, Y', strtotime($item['created_at'])) ?></strong></div>
                <div>👤 Reported by <strong style="color:var(--text)"><?= htmlspecialchars($item['reporter']) ?></strong></div>
            </div>

            <?php if ($item['description']): ?>
            <div style="background:var(--bg);border:1px solid var(--border);border-radius:var(--radius);padding:1rem;margin-bottom:1.5rem;font-size:0.95rem;line-height:1.7">
                <?= nl2br(htmlspecialchars($item['description'])) ?>
            </div>
            <?php endif; ?>

            <?php if ($error): ?><p class="error"><?= $error ?></p><?php endif; ?>
            <?php if ($success): ?><p class="success"><?= $success ?></p><?php endif; ?>

            <!-- WhatsApp button -->
            <?php if (!empty($item['whatsapp']) && isLoggedIn() && $_SESSION['user_id'] != $item['user_id']): ?>
            <?php
                $wa_num = preg_replace('/[^0-9]/', '', $item['whatsapp']);
                if (strlen($wa_num) === 10 && $wa_num[0] === '0') $wa_num = '233' . substr($wa_num, 1);
                $wa_msg = urlencode("Hi, I saw your post on CampusL&F about \"{$item['title']}\". I'd like to help.");
            ?>
            <a href="https://wa.me/<?= $wa_num ?>?text=<?= $wa_msg ?>" target="_blank"
               style="display:inline-flex;align-items:center;gap:0.5rem;background:#25D366;color:#fff;padding:0.6rem 1.2rem;border-radius:var(--radius);text-decoration:none;font-weight:600;margin-bottom:1rem">
                💬 Contact on WhatsApp
            </a>
            <?php endif; ?>

            <?php if ($item['status'] === 'open' && isLoggedIn() && $_SESSION['user_id'] != $item['user_id']): ?>
            <details style="margin-top:0.5rem">
                <summary class="btn btn-success" style="cursor:pointer;list-style:none;display:inline-block">✋ This is Mine / I Found the Owner</summary>
                <form method="POST" style="margin-top:1rem;background:var(--bg);padding:1rem;border-radius:var(--radius);border:1px solid var(--border)">
                    <div class="form-group">
                        <label>Message to reporter</label>
                        <textarea name="message" placeholder="Describe how you know it's yours..." required></textarea>
                    </div>
                    <button type="submit" name="claim" class="btn btn-success">Submit Claim</button>
                </form>
            </details>
            <?php elseif (!isLoggedIn()): ?>
                <p style="margin-top:1rem;color:var(--muted)"><a href="/pages/login.php">Login</a> to submit a claim.</p>
            <?php elseif ($item['status'] !== 'open'): ?>
                <p style="margin-top:1rem" class="success">This item has been <?= $item['status'] ?>.</p>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($claims)): ?>
    <div style="margin-top:2rem">
        <h2 style="margin-bottom:1rem">Claims (<?= count($claims) ?>)</h2>
        <?php foreach ($claims as $claim): ?>
        <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:1.25rem;margin-bottom:1rem">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.5rem">
                <strong><?= htmlspecialchars($claim['claimant_name']) ?></strong>
                <span class="badge badge-open"><?= strtoupper($claim['status']) ?></span>
            </div>
            <p style="font-size:0.85rem;color:var(--muted);margin-bottom:0.75rem">📧 <?= htmlspecialchars($claim['claimant_email']) ?> · <?= date('M j, Y g:i A', strtotime($claim['created_at'])) ?></p>
            <?php if ($claim['message']): ?>
                <p style="font-size:0.9rem;background:var(--bg);padding:0.75rem;border-radius:var(--radius)"><?= nl2br(htmlspecialchars($claim['message'])) ?></p>
            <?php endif; ?>
            <?php if ($claim['status'] === 'pending' && $item['status'] !== 'resolved'): ?>
            <div style="display:flex;gap:0.75rem;margin-top:0.75rem">
                <form method="POST">
                    <input type="hidden" name="claim_id" value="<?= $claim['id'] ?>">
                    <button type="submit" name="claim_action" value="approve" class="btn btn-success">✅ Approve & Resolve</button>
                </form>
                <form method="POST">
                    <input type="hidden" name="claim_id" value="<?= $claim['id'] ?>">
                    <button type="submit" name="claim_action" value="reject" class="btn btn-danger">❌ Reject</button>
                </form>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
<?php include '../includes/footer.php'; ?>
