<?php
session_start();
require '../includes/db.php';
require '../includes/auth.php';
requireLogin();

$pageTitle = 'Dashboard';
$uid = $_SESSION['user_id'];

// handle mark as found
if (isset($_GET['resolve'])) {
    $rid = intval($_GET['resolve']);
    $conn->query("UPDATE items SET status='resolved' WHERE id=$rid AND user_id=$uid");
    header("Location: /pages/dashboard.php");
    exit();
}

$items = $conn->prepare("SELECT * FROM items WHERE user_id = ? ORDER BY created_at DESC");
$items->bind_param("i", $uid);
$items->execute();
$my_items = $items->get_result()->fetch_all(MYSQLI_ASSOC);
$items->close();

$cr = $conn->prepare("
    SELECT c.*, i.title AS item_title, i.type AS item_type, i.id AS item_id
    FROM claims c JOIN items i ON c.item_id = i.id
    WHERE c.user_id = ? ORDER BY c.created_at DESC
");
$cr->bind_param("i", $uid);
$cr->execute();
$my_claims = $cr->get_result()->fetch_all(MYSQLI_ASSOC);
$cr->close();

// count pending claims on my items
$notif = $conn->prepare("SELECT COUNT(*) AS c FROM claims c JOIN items i ON c.item_id = i.id WHERE i.user_id = ? AND c.status = 'pending'");
$notif->bind_param("i", $uid);
$notif->execute();
$pending_claims = $notif->get_result()->fetch_assoc()['c'];
$notif->close();
?>
<?php include '../includes/header.php'; ?>
<div class="container">
    <div class="page-header">
        <h1>Welcome, <?= htmlspecialchars($_SESSION['name']) ?> 👋</h1>
        <p>Manage your lost & found reports and claim requests.</p>
    </div>

    <?php if ($pending_claims > 0): ?>
    <div style="background:#1b2d1b;border:1px solid var(--accent);border-radius:var(--radius);padding:1rem;margin-bottom:1.5rem;color:#7ee787">
        🔔 You have <strong><?= $pending_claims ?></strong> pending claim(s) on your items. Review them below.
    </div>
    <?php endif; ?>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:1rem;margin-bottom:2.5rem">
        <?php
        $open     = count(array_filter($my_items, fn($i) => $i['status']==='open'));
        $claimed  = count(array_filter($my_items, fn($i) => $i['status']==='claimed'));
        $resolved = count(array_filter($my_items, fn($i) => $i['status']==='resolved'));
        foreach ([
            ['📋','Total Reports', count($my_items), '#1f6feb'],
            ['🟢','Open',          $open,            '#238636'],
            ['🟡','Claimed',       $claimed,         '#9e6a03'],
            ['✅','Resolved',      $resolved,        '#8b949e'],
            ['📨','Claims Sent',   count($my_claims),'#6e40c9'],
        ] as [$icon,$label,$val,$color]):
        ?>
        <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:1.25rem;text-align:center">
            <div style="font-size:1.8rem"><?= $icon ?></div>
            <div style="font-size:1.6rem;font-weight:700;color:<?= $color ?>"><?= $val ?></div>
            <div style="font-size:0.8rem;color:var(--muted)"><?= $label ?></div>
        </div>
        <?php endforeach; ?>
    </div>

    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem">
        <h2>My Reports</h2>
        <a href="/pages/report.php" class="btn btn-primary">+ Report Item</a>
    </div>

    <?php if (empty($my_items)): ?>
        <p style="color:var(--muted);margin-bottom:2rem">You haven't reported any items yet.</p>
    <?php else: ?>
    <div style="overflow-x:auto;margin-bottom:2.5rem">
        <table>
            <thead>
                <tr><th>Title</th><th>Type</th><th>Category</th><th>Location</th><th>Status</th><th>Date</th><th>Actions</th></tr>
            </thead>
            <tbody>
            <?php foreach ($my_items as $item): ?>
            <tr>
                <td><a href="/pages/item.php?id=<?= $item['id'] ?>"><?= htmlspecialchars($item['title']) ?></a></td>
                <td><span class="badge badge-<?= $item['type'] ?>"><?= strtoupper($item['type']) ?></span></td>
                <td style="color:var(--muted);font-size:0.85rem"><?= htmlspecialchars($item['category'] ?? '—') ?></td>
                <td><?= htmlspecialchars($item['location'] ?? '—') ?></td>
                <td><span class="badge badge-<?= $item['status'] ?>"><?= strtoupper($item['status']) ?></span></td>
                <td><?= date('M j, Y', strtotime($item['created_at'])) ?></td>
                <td style="display:flex;gap:0.4rem;flex-wrap:wrap">
                    <a href="/pages/item.php?id=<?= $item['id'] ?>" class="btn btn-outline" style="font-size:0.78rem;padding:0.3rem 0.6rem">View</a>
                    <a href="/pages/edit.php?id=<?= $item['id'] ?>" class="btn btn-outline" style="font-size:0.78rem;padding:0.3rem 0.6rem">Edit</a>
                    <?php if ($item['status'] === 'open' || $item['status'] === 'claimed'): ?>
                    <a href="/pages/dashboard.php?resolve=<?= $item['id'] ?>" class="btn btn-success" style="font-size:0.78rem;padding:0.3rem 0.6rem" onclick="return confirm('Mark as found/resolved?')">✅ Found</a>
                    <?php endif; ?>
                    <a href="/pages/delete.php?id=<?= $item['id'] ?>" class="btn btn-danger" style="font-size:0.78rem;padding:0.3rem 0.6rem" onclick="return confirm('Delete this item?')">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <h2 style="margin-bottom:1rem">My Claim Requests</h2>
    <?php if (empty($my_claims)): ?>
        <p style="color:var(--muted)">You haven't submitted any claims yet.</p>
    <?php else: ?>
    <div style="overflow-x:auto">
        <table>
            <thead>
                <tr><th>Item</th><th>Type</th><th>Claim Status</th><th>Submitted</th><th></th></tr>
            </thead>
            <tbody>
            <?php foreach ($my_claims as $c): ?>
            <tr>
                <td><a href="/pages/item.php?id=<?= $c['item_id'] ?>"><?= htmlspecialchars($c['item_title']) ?></a></td>
                <td><span class="badge badge-<?= $c['item_type'] ?>"><?= strtoupper($c['item_type']) ?></span></td>
                <td><span class="badge badge-open"><?= strtoupper($c['status']) ?></span></td>
                <td><?= date('M j, Y', strtotime($c['created_at'])) ?></td>
                <td><a href="/pages/item.php?id=<?= $c['item_id'] ?>" class="btn btn-outline" style="font-size:0.8rem;padding:0.3rem 0.7rem">View</a></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
<?php include '../includes/footer.php'; ?>
