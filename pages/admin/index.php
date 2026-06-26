<?php
session_start();
require '../includes/db.php';
require '../includes/auth.php';
requireLogin();
requireAdmin();

$pageTitle = 'Admin Panel';

// stats
$stats = [];
foreach (['users','items','claims'] as $t) {
    $r = $conn->query("SELECT COUNT(*) AS c FROM $t");
    $stats[$t] = $r->fetch_assoc()['c'];
}
$stats['open']     = $conn->query("SELECT COUNT(*) AS c FROM items WHERE status='open'")->fetch_assoc()['c'];
$stats['resolved'] = $conn->query("SELECT COUNT(*) AS c FROM items WHERE status='resolved'")->fetch_assoc()['c'];

// all items
$all_items = $conn->query("
    SELECT i.*, u.name AS reporter
    FROM items i JOIN users u ON i.user_id = u.id
    ORDER BY i.created_at DESC
")->fetch_all(MYSQLI_ASSOC);

// handle delete from admin
if (isset($_GET['delete'])) {
    $did = intval($_GET['delete']);
    $di  = $conn->query("SELECT image_path FROM items WHERE id=$did")->fetch_assoc();
    if ($di && $di['image_path'] && file_exists('../uploads/'.$di['image_path']))
        unlink('../uploads/'.$di['image_path']);
    $conn->query("DELETE FROM items WHERE id=$did");
    header("Location: /admin/");
    exit();
}
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
            ['👥','Users',        $stats['users'],    '#1f6feb'],
            ['📋','Total Items',  $stats['items'],    '#6e40c9'],
            ['🟢','Open',         $stats['open'],     '#238636'],
            ['✅','Resolved',     $stats['resolved'], '#8b949e'],
            ['📨','Claims',       $stats['claims'],   '#9e6a03'],
        ] as [$icon,$label,$val,$color]): ?>
        <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:1.25rem;text-align:center">
            <div style="font-size:1.8rem"><?= $icon ?></div>
            <div style="font-size:1.6rem;font-weight:700;color:<?= $color ?>"><?= $val ?></div>
            <div style="font-size:0.8rem;color:var(--muted)"><?= $label ?></div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- All Items Table -->
    <h2 style="margin-bottom:1rem">All Items</h2>
    <div style="overflow-x:auto">
        <table>
            <thead>
                <tr><th>ID</th><th>Title</th><th>Type</th><th>Reporter</th><th>Location</th><th>Status</th><th>Date</th><th>Actions</th></tr>
            </thead>
            <tbody>
            <?php foreach ($all_items as $item): ?>
            <tr>
                <td style="color:var(--muted)">#<?= $item['id'] ?></td>
                <td><a href="/pages/item.php?id=<?= $item['id'] ?>"><?= htmlspecialchars($item['title']) ?></a></td>
                <td><span class="badge badge-<?= $item['type'] ?>"><?= strtoupper($item['type']) ?></span></td>
                <td><?= htmlspecialchars($item['reporter']) ?></td>
                <td><?= htmlspecialchars($item['location'] ?? '—') ?></td>
                <td><span class="badge badge-<?= $item['status'] ?>"><?= strtoupper($item['status']) ?></span></td>
                <td><?= date('M j, Y', strtotime($item['created_at'])) ?></td>
                <td style="display:flex;gap:0.5rem">
                    <a href="/pages/item.php?id=<?= $item['id'] ?>" class="btn btn-outline" style="font-size:0.78rem;padding:0.3rem 0.6rem">View</a>
                    <a href="/admin/?delete=<?= $item['id'] ?>" class="btn btn-danger" style="font-size:0.78rem;padding:0.3rem 0.6rem" onclick="return confirm('Delete this item?')">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
