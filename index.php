<?php
session_start();
require 'includes/db.php';
$pageTitle = 'Home';

// fetch 6 most recent open items
$result = $conn->query("
    SELECT i.*, u.name AS reporter
    FROM items i
    JOIN users u ON i.user_id = u.id
    WHERE i.status = 'open'
    ORDER BY i.created_at DESC
    LIMIT 6
");
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
            <?php if ($item['image_path'] && file_exists('uploads/' . $item['image_path'])): ?>
                <img src="/uploads/<?= htmlspecialchars($item['image_path']) ?>" class="card-img" alt="">
            <?php else: ?>
                <div class="card-img-placeholder">
                    <?= $item['type'] === 'lost' ? '🔍' : '📦' ?>
                </div>
            <?php endif; ?>
            <div class="card-body">
                <span class="badge badge-<?= $item['type'] ?>"><?= strtoupper($item['type']) ?></span>
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
