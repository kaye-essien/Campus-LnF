<?php
session_start();
require '../includes/db.php';
require '../includes/auth.php';
requireLogin();
requireAdmin();
$pageTitle = 'Admin Panel';

// handle actions
if (isset($_GET['delete_item'])) {
    $did = intval($_GET['delete_item']);
    $imgs = $conn->query("SELECT image_path FROM item_images WHERE item_id=$did")->fetch_all(MYSQLI_ASSOC);
    foreach ($imgs as $img) {
        $f = '../uploads/' . $img['image_path'];
        if (file_exists($f)) unlink($f);
    }
    $conn->query("DELETE FROM items WHERE id=$did");
    header("Location: /admin/"); exit();
}

if (isset($_GET['delete_user'])) {
    $duid = intval($_GET['delete_user']);
    if ($duid != $_SESSION['user_id']) {
        $conn->query("DELETE FROM users WHERE id=$duid");
    }
    header("Location: /admin/"); exit();
}

if (isset($_GET['toggle_admin'])) {
    $tuid = intval($_GET['toggle_admin']);
    $cur = $conn->query("SELECT role FROM users WHERE id=$tuid")->fetch_assoc();
    if ($cur) {
        $newrole = $cur['role'] === 'admin' ? 'user' : 'admin';
        $conn->query("UPDATE users SET role='$newrole' WHERE id=$tuid");
    }
    header("Location: /admin/#users"); exit();
}

if (isset($_GET['change_status'])) {
    $sid = intval($_GET['change_status']);
    $ns  = $_GET['status'] ?? 'open';
    if (in_array($ns, ['open','claimed','resolved'])) {
        $conn->query("UPDATE items SET status='$ns' WHERE id=$sid");
    }
    header("Location: /admin/"); exit();
}

// stats
$stats = [];
foreach (['users','items','claims'] as $t) {
    $stats[$t] = $conn->query("SELECT COUNT(*) AS c FROM $t")->fetch_assoc()['c'];
}
$stats['open']     = $conn->query("SELECT COUNT(*) AS c FROM items WHERE status='open'")->fetch_assoc()['c'];
$stats['resolved'] = $conn->query("SELECT COUNT(*) AS c FROM items WHERE status='resolved'")->fetch_assoc()['c'];

$all_items = $conn->query("
    SELECT i.*, u.name AS reporter
    FROM items i JOIN users u ON i.user_id = u.id
    ORDER BY i.created_at DESC
")->fetch_all(MYSQLI_ASSOC);

$all_users = $conn->query("
    SELECT u.*, COUNT(i.id) AS item_count
    FROM users u LEFT JOIN items i ON i.user_id = u.id
    GROUP BY u.id ORDER BY u.created_at DESC
")->fetch_all(MYSQLI_ASSOC);

$all_claims = $conn->query("
    SELECT c.*, i.title AS item_title, u.name AS claimant_name, u.email AS claimant_email
    FROM claims c
    JOIN items i ON c.item_id = i.id
    JOIN users u ON c.user_id = u.id
    ORDER BY c.created_at DESC
")->fetch_all(MYSQLI_ASSOC);
?>
<?php include '../includes/header.php'; ?>
<div class="container">
    <div class="page-header">
        <h1>⚙️ Admin Panel</h1>
        <p>Manage all platform content and users.</p>
    </div>

    <!-- Stats -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:1rem;margin-bottom:2.5rem">
        <?php foreach ([
            ['👥','Users',       $stats['users'],    '#1f6feb'],
            ['📋','Total Items', $stats['items'],    '#6e40c9'],
            ['🟢','Open',        $stats['open'],     '#238636'],
            ['✅','Resolved',    $stats['resolved'], '#8b949e'],
            ['📨','Claims',      $stats['claims'],   '#9e6a03'],
        ] as [$icon,$label,$val,$color]): ?>
        <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:1.25rem;text-align:center">
            <div style="font-size:1.8rem"><?= $icon ?></div>
            <div style="font-size:1.6rem;font-weight:700;color:<?= $color ?>"><?= $val ?></div>
            <div style="font-size:0.8rem;color:var(--muted)"><?= $label ?></div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Tab nav -->
    <div style="display:flex;gap:0.5rem;margin-bottom:1.5rem;border-bottom:1px solid var(--border);padding-bottom:0.5rem">
        <a href="#items"  onclick="showTab('items')"  id="tab-items"  class="btn btn-primary"  style="font-size:0.85rem">📋 Items</a>
        <a href="#users"  onclick="showTab('users')"  id="tab-users"  class="btn btn-outline" style="font-size:0.85rem">👥 Users</a>
        <a href="#claims" onclick="showTab('claims')" id="tab-claims" class="btn btn-outline" style="font-size:0.85rem">📨 Claims</a>
    </div>

    <!-- Items tab -->
    <div id="tab-items-content">
        <h2 style="margin-bottom:1rem">All Items</h2>
        <div style="overflow-x:auto">
            <table>
                <thead>
                    <tr><th>ID</th><th>Title</th><th>Type</th><th>Category</th><th>Reporter</th><th>Location</th><th>Status</th><th>Date</th><th>Actions</th></tr>
                </thead>
                <tbody>
                <?php foreach ($all_items as $item): ?>
                <tr>
                    <td style="color:var(--muted)">#<?= $item['id'] ?></td>
                    <td><a href="/pages/item.php?id=<?= $item['id'] ?>"><?= htmlspecialchars($item['title']) ?></a></td>
                    <td><span class="badge badge-<?= $item['type'] ?>"><?= strtoupper($item['type']) ?></span></td>
                    <td style="font-size:0.85rem;color:var(--muted)"><?= htmlspecialchars($item['category'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($item['reporter']) ?></td>
                    <td><?= htmlspecialchars($item['location'] ?? '—') ?></td>
                    <td>
                        <select onchange="location.href='/admin/?change_status=<?= $item['id'] ?>&status='+this.value" style="background:var(--bg);border:1px solid var(--border);color:var(--text);padding:0.2rem 0.4rem;border-radius:var(--radius);font-size:0.8rem">
                            <option value="open"     <?= $item['status']==='open'     ? 'selected':'' ?>>Open</option>
                            <option value="claimed"  <?= $item['status']==='claimed'  ? 'selected':'' ?>>Claimed</option>
                            <option value="resolved" <?= $item['status']==='resolved' ? 'selected':'' ?>>Resolved</option>
                        </select>
                    </td>
                    <td><?= date('M j, Y', strtotime($item['created_at'])) ?></td>
                    <td style="display:flex;gap:0.4rem;flex-wrap:wrap">
                        <a href="/pages/edit.php?id=<?= $item['id'] ?>" class="btn btn-outline" style="font-size:0.75rem;padding:0.25rem 0.5rem">Edit</a>
                        <a href="/admin/?delete_item=<?= $item['id'] ?>" class="btn btn-danger" style="font-size:0.75rem;padding:0.25rem 0.5rem" onclick="return confirm('Delete this item?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Users tab -->
    <div id="tab-users-content" style="display:none">
        <h2 style="margin-bottom:1rem">All Users</h2>
        <div style="overflow-x:auto">
            <table>
                <thead>
                    <tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Items</th><th>Joined</th><th>Actions</th></tr>
                </thead>
                <tbody>
                <?php foreach ($all_users as $u): ?>
                <tr>
                    <td style="color:var(--muted)">#<?= $u['id'] ?></td>
                    <td><?= htmlspecialchars($u['name']) ?></td>
                    <td style="font-size:0.85rem"><?= htmlspecialchars($u['email']) ?></td>
                    <td><span class="badge badge-<?= $u['role']==='admin' ? 'lost' : 'open' ?>"><?= strtoupper($u['role']) ?></span></td>
                    <td><?= $u['item_count'] ?></td>
                    <td><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
                    <td style="display:flex;gap:0.4rem;flex-wrap:wrap">
                        <?php if ($u['id'] != $_SESSION['user_id']): ?>
                        <a href="/admin/?toggle_admin=<?= $u['id'] ?>" class="btn btn-outline" style="font-size:0.75rem;padding:0.25rem 0.5rem" onclick="return confirm('Toggle admin role?')">
                            <?= $u['role']==='admin' ? 'Remove Admin' : 'Make Admin' ?>
                        </a>
                        <a href="/admin/?delete_user=<?= $u['id'] ?>" class="btn btn-danger" style="font-size:0.75rem;padding:0.25rem 0.5rem" onclick="return confirm('Delete this user and all their items?')">Delete</a>
                        <?php else: ?>
                        <span style="color:var(--muted);font-size:0.8rem">You</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Claims tab -->
    <div id="tab-claims-content" style="display:none">
        <h2 style="margin-bottom:1rem">All Claims</h2>
        <div style="overflow-x:auto">
            <table>
                <thead>
                    <tr><th>ID</th><th>Item</th><th>Claimant</th><th>Email</th><th>Status</th><th>Date</th><th>Action</th></tr>
                </thead>
                <tbody>
                <?php foreach ($all_claims as $c): ?>
                <tr>
                    <td style="color:var(--muted)">#<?= $c['id'] ?></td>
                    <td><a href="/pages/item.php?id=<?= $c['item_id'] ?>"><?= htmlspecialchars($c['item_title']) ?></a></td>
                    <td><?= htmlspecialchars($c['claimant_name']) ?></td>
                    <td style="font-size:0.82rem"><?= htmlspecialchars($c['claimant_email']) ?></td>
                    <td><span class="badge badge-open"><?= strtoupper($c['status']) ?></span></td>
                    <td><?= date('M j, Y', strtotime($c['created_at'])) ?></td>
                    <td><a href="/pages/item.php?id=<?= $c['item_id'] ?>" class="btn btn-outline" style="font-size:0.75rem;padding:0.25rem 0.5rem">View</a></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function showTab(tab) {
    ['items','users','claims'].forEach(t => {
        document.getElementById('tab-'+t+'-content').style.display = t===tab ? 'block' : 'none';
        document.getElementById('tab-'+t).className = t===tab ? 'btn btn-primary' : 'btn btn-outline';
    });
    return false;
}
// check URL hash on load
const hash = location.hash.replace('#','');
if (['items','users','claims'].includes(hash)) showTab(hash);
</script>

<?php include '../includes/footer.php'; ?>
