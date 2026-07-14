<?php
session_start();
require 'includes/db.php';
$pageTitle = 'Home';

$result = $conn->query("
    SELECT i.*, u.name AS reporter,
    (SELECT image_path FROM item_images WHERE item_id = i.id ORDER BY id ASC LIMIT 1) AS thumb
    FROM items i JOIN users u ON i.user_id = u.id
    WHERE i.status = 'open'
    ORDER BY i.created_at DESC LIMIT 6
");

$stats = [
    'total'    => $conn->query("SELECT COUNT(*) AS c FROM items")->fetch_assoc()['c'],
    'open'     => $conn->query("SELECT COUNT(*) AS c FROM items WHERE status='open'")->fetch_assoc()['c'],
    'resolved' => $conn->query("SELECT COUNT(*) AS c FROM items WHERE status='resolved'")->fetch_assoc()['c'],
    'users'    => $conn->query("SELECT COUNT(*) AS c FROM users")->fetch_assoc()['c'],
];
?>
<?php include 'includes/header.php'; ?>
<div class="hero">
    <h1>Lost something on <span>campus?</span></h1>
    <p>UMaT's student-run lost & found platform. Report a missing item or help return what you've found.</p>
    <div class="hero-btns">
        <a href="pages/report.php" class="btn btn-primary">Report an Item</a>
        <a href="pages/browse.php" class="btn btn-outline">Browse All Items</a>
    </div>
</div>

<!-- Stats bar -->
<div style="background:var(--surface);border-bottom:1px solid var(--border);padding:1.5rem">
    <div style="max-width:1100px;margin:0 auto;display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:1rem;text-align:center">
        <?php foreach ([
            ['📋', $stats['total'],    'Total Reports'],
            ['🟢', $stats['open'],     'Active Listings'],
            ['✅', $stats['resolved'], 'Items Recovered'],
            ['👥', $stats['users'],    'Students Registered'],
        ] as [$icon,$val,$label]): ?>
        <div>
            <div style="font-size:1.5rem"><?= $icon ?></div>
            <div style="font-size:1.8rem;font-weight:700;color:var(--accent2)"><?= $val ?></div>
            <div style="font-size:0.8rem;color:var(--muted)"><?= $label ?></div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="container">
    <div class="page-header">
        <h1>Recent Listings</h1>
        <p>Latest lost & found reports from campus</p>
    </div>
    <?php if ($result->num_rows === 0): ?>
        <p style="color:var(--muted)">No items reported yet. Be the first!</p>
    <?php else: ?>
    <div class="cards-grid">
        <?php while ($item = $result->fetch_assoc()): ?>
        <a href="pages/item.php?id=<?= $item['id'] ?>" class="card" style="text-decoration:none;color:inherit">
            <?php if ($item['thumb']): ?>
                <img src="/uploads/<?= htmlspecialchars($item['thumb']) ?>" class="card-img" alt="">
            <?php else: ?>
                <div class="card-img-placeholder"><?= $item['type'] === 'lost' ? '🔍' : '📦' ?></div>
            <?php endif; ?>
            <div class="card-body">
                <div style="display:flex;gap:0.4rem;flex-wrap:wrap;margin-bottom:0.4rem">
                    <span class="badge badge-<?= $item['type'] ?>"><?= strtoupper($item['type']) ?></span>
                    <?php if ($item['category']): ?>
                    <span style="background:var(--bg);border:1px solid var(--border);color:var(--muted);padding:0.2rem 0.5rem;border-radius:20px;font-size:0.72rem"><?= htmlspecialchars($item['category']) ?></span>
                    <?php endif; ?>
                </div>
                <h3><?= htmlspecialchars($item['title']) ?></h3>
                <p>📍 <?= htmlspecialchars($item['location'] ?? 'Location not specified') ?></p>
                <p>👤 <?= htmlspecialchars($item['reporter']) ?></p>
                <p style="font-size:0.8rem;color:var(--muted)"><?= date('M j, Y', strtotime($item['created_at'])) ?></p>
            </div>
        </a>
        <?php endwhile; ?>
    </div>
    <?php endif; ?>
    <div style="text-align:center;margin-top:2rem">
        <a href="pages/browse.php" class="btn btn-outline">View All Listings →</a>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
