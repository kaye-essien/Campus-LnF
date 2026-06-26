<?php
session_start();
require '../includes/db.php';
$pageTitle = 'Browse Items';

$type   = $_GET['type']   ?? '';
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? 'open';

$where = ["1=1"];
$params = [];
$types  = '';

if ($type)   { $where[] = "i.type = ?";    $params[] = $type;   $types .= 's'; }
if ($search) { $where[] = "i.title LIKE ?"; $params[] = "%$search%"; $types .= 's'; }
if ($status) { $where[] = "i.status = ?";  $params[] = $status; $types .= 's'; }

$sql = "SELECT i.*, u.name AS reporter FROM items i JOIN users u ON i.user_id = u.id WHERE " . implode(' AND ', $where) . " ORDER BY i.created_at DESC";

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

    <!-- Filters -->
    <form method="GET" style="display:flex;gap:1rem;flex-wrap:wrap;margin-bottom:2rem">
        <input type="text" name="search" placeholder="Search by title..." value="<?= htmlspecialchars($search) ?>" style="background:var(--surface);border:1px solid var(--border);color:var(--text);padding:0.6rem 0.9rem;border-radius:var(--radius);flex:1;min-width:200px">
        <select name="type" style="background:var(--surface);border:1px solid var(--border);color:var(--text);padding:0.6rem 0.9rem;border-radius:var(--radius)">
            <option value="">All Types</option>
            <option value="lost"  <?= $type==='lost'  ? 'selected':'' ?>>Lost</option>
            <option value="found" <?= $type==='found' ? 'selected':'' ?>>Found</option>
        </select>
        <select name="status" style="background:var(--surface);border:1px solid var(--border);color:var(--text);padding:0.6rem 0.9rem;border-radius:var(--radius)">
            <option value="open"     <?= $status==='open'     ? 'selected':'' ?>>Open</option>
            <option value="claimed"  <?= $status==='claimed'  ? 'selected':'' ?>>Claimed</option>
            <option value="resolved" <?= $status==='resolved' ? 'selected':'' ?>>Resolved</option>
            <option value="">All Status</option>
        </select>
        <button type="submit" class="btn btn-primary">Search</button>
        <a href="browse.php" class="btn btn-outline">Reset</a>
    </form>

    <?php if ($result->num_rows === 0): ?>
        <p style="color:var(--muted)">No items found matching your criteria.</p>
    <?php else: ?>
    <p style="color:var(--muted);margin-bottom:1rem"><?= $result->num_rows ?> item(s) found</p>
    <div class="cards-grid">
        <?php while ($item = $result->fetch_assoc()): ?>
        <a href="/pages/item.php?id=<?= $item['id'] ?>" class="card" style="text-decoration:none;color:inherit">
            <?php if ($item['image_path'] && file_exists('../uploads/' . $item['image_path'])): ?>
                <img src="/uploads/<?= htmlspecialchars($item['image_path']) ?>" class="card-img" alt="">
            <?php else: ?>
                <div class="card-img-placeholder"><?= $item['type'] === 'lost' ? '🔍' : '📦' ?></div>
            <?php endif; ?>
            <div class="card-body">
                <span class="badge badge-<?= $item['type'] ?>"><?= strtoupper($item['type']) ?></span>
                <span class="badge badge-<?= $item['status'] ?>" style="margin-left:4px"><?= strtoupper($item['status']) ?></span>
                <h3 style="margin-top:0.5rem"><?= htmlspecialchars($item['title']) ?></h3>
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
