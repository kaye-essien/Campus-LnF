<?php
session_start();
require '../includes/db.php';
$pageTitle = 'Browse Items';
$type     = $_GET['type']     ?? '';
$search   = $_GET['search']   ?? '';
$status   = $_GET['status']   ?? 'open';
$category = $_GET['category'] ?? '';
$location = $_GET['location'] ?? '';

$categories = ['Electronics','Clothing','Books','ID Card','Keys','Bag','Wallet','Jewellery','Sports','Other'];

$where = ["1=1"];
$params = [];
$types  = '';
if ($type)     { $where[] = "i.type = ?";          $params[] = $type;       $types .= 's'; }
if ($search)   { $where[] = "i.title LIKE ?";       $params[] = "%$search%"; $types .= 's'; }
if ($status)   { $where[] = "i.status = ?";         $params[] = $status;     $types .= 's'; }
if ($category) { $where[] = "i.category = ?";       $params[] = $category;   $types .= 's'; }
if ($location) { $where[] = "i.location LIKE ?";    $params[] = "%$location%";$types .= 's'; }

$sql = "SELECT i.*, u.name AS reporter,
        (SELECT image_path FROM item_images WHERE item_id = i.id ORDER BY id ASC LIMIT 1) AS thumb
        FROM items i JOIN users u ON i.user_id = u.id
        WHERE " . implode(' AND ', $where) . " ORDER BY i.created_at DESC";
$stmt = $conn->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>
<?php include '../includes/header.php'; ?>
<div class="container">
    <div class="page-header">
        <h1>Browse Items</h1>
        <p>Search through all lost & found reports on campus.</p>
    </div>
    <form method="GET" style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;margin-bottom:2rem">
        <input type="text" name="search" placeholder="Search by title..." value="<?= htmlspecialchars($search) ?>" style="background:var(--surface);border:1px solid var(--border);color:var(--text);padding:0.6rem 0.9rem;border-radius:var(--radius)">
        <input type="text" name="location" placeholder="Search by location..." value="<?= htmlspecialchars($location) ?>" style="background:var(--surface);border:1px solid var(--border);color:var(--text);padding:0.6rem 0.9rem;border-radius:var(--radius)">
        <select name="type" style="background:var(--surface);border:1px solid var(--border);color:var(--text);padding:0.6rem 0.9rem;border-radius:var(--radius)">
            <option value="">All Types</option>
            <option value="lost"  <?= $type==='lost'  ? 'selected':'' ?>>Lost</option>
            <option value="found" <?= $type==='found' ? 'selected':'' ?>>Found</option>
        </select>
        <select name="category" style="background:var(--surface);border:1px solid var(--border);color:var(--text);padding:0.6rem 0.9rem;border-radius:var(--radius)">
            <option value="">All Categories</option>
            <?php foreach ($categories as $cat): ?>
            <option value="<?= $cat ?>" <?= $category===$cat ? 'selected':'' ?>><?= $cat ?></option>
            <?php endforeach; ?>
        </select>
        <select name="status" style="background:var(--surface);border:1px solid var(--border);color:var(--text);padding:0.6rem 0.9rem;border-radius:var(--radius)">
            <option value="">All Status</option>
            <option value="open"     <?= $status==='open'     ? 'selected':'' ?>>Open</option>
            <option value="claimed"  <?= $status==='claimed'  ? 'selected':'' ?>>Claimed</option>
            <option value="resolved" <?= $status==='resolved' ? 'selected':'' ?>>Resolved</option>
        </select>
        <div style="display:flex;gap:0.5rem">
            <button type="submit" class="btn btn-primary" style="flex:1">Search</button>
            <a href="browse.php" class="btn btn-outline" style="flex:1;text-align:center;padding:0.6rem">Reset</a>
        </div>
    </form>
    <?php if ($result->num_rows === 0): ?>
        <p style="color:var(--muted)">No items found matching your criteria.</p>
    <?php else: ?>
    <p style="color:var(--muted);margin-bottom:1rem"><?= $result->num_rows ?> item(s) found</p>
    <div class="cards-grid">
        <?php while ($item = $result->fetch_assoc()): ?>
        <a href="/pages/item.php?id=<?= $item['id'] ?>" class="card" style="text-decoration:none;color:inherit">
            <?php if ($item['thumb']): ?>
                <img src="/uploads/<?= htmlspecialchars($item['thumb']) ?>" class="card-img" alt="">
            <?php else: ?>
                <div class="card-img-placeholder"><?= $item['type'] === 'lost' ? '🔍' : '📦' ?></div>
            <?php endif; ?>
            <div class="card-body">
                <div style="display:flex;gap:0.4rem;flex-wrap:wrap;margin-bottom:0.4rem">
                    <span class="badge badge-<?= $item['type'] ?>"><?= strtoupper($item['type']) ?></span>
                    <span class="badge badge-<?= $item['status'] ?>"><?= strtoupper($item['status']) ?></span>
                    <?php if ($item['category']): ?>
                    <span style="background:var(--bg);border:1px solid var(--border);color:var(--muted);padding:0.2rem 0.5rem;border-radius:20px;font-size:0.72rem"><?= htmlspecialchars($item['category']) ?></span>
                    <?php endif; ?>
                </div>
                <h3><?= htmlspecialchars($item['title']) ?></h3>
                <p>📍 <?= htmlspecialchars($item['location'] ?? 'Not specified') ?></p>
                <p>👤 <?= htmlspecialchars($item['reporter']) ?></p>
                <p style="font-size:0.8rem;color:var(--muted)"><?= date('M j, Y', strtotime($item['created_at'])) ?></p>
            </div>
        </a>
        <?php endwhile; ?>
    </div>
    <?php endif; ?>
</div>
<?php include '../includes/footer.php'; ?>
