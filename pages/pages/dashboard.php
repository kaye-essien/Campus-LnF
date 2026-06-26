<?php
session_start();
require '../includes/db.php';
require '../includes/auth.php';
requireLogin();

$pageTitle = 'Dashboard';
$uid = $_SESSION['user_id'];

// user's reported items
$items = $conn->prepare("SELECT * FROM items WHERE user_id = ? ORDER BY created_at DESC");
$items->bind_param("i", $uid);
$items->execute();
$my_items = $items->get_result()->fetch_all(MYSQLI_ASSOC);
$items->close();

// claims the user has submitted
$cr = $conn->prepare("
    SELECT c.*, i.title AS item_title, i.type AS item_type, i.id AS item_id
    FROM claims c
    JOIN items i ON c.item_id = i.id
    WHERE c.claimant_id = ?
    ORDER BY c.created_at DESC
");
$cr->bind_param("i", $uid);
$cr->execute();
$my_claims = $cr->get_result()->fetch_all(MYSQLI_ASSOC);
$cr->close();
?>
<?php include '../includes/header.php'; ?>

<div class="container">
    <div class="page-header">
        <h1>Welcome, <?= htmlspecialchars($_SESSION['name']) ?> 👋</h1>
        <p>Manage your lost & found reports and claim requests.</p>
    </div>

    <!-- Stats bar -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:1rem;margin-bottom:2.5rem">
        <?php
        $open     = count(array_filter($my_items, fn($i) => $i['status']==='open'));
        $claimed  = count(array_filter($my_items, fn($i) => $i['status']==='claimed'));
        $resolved = count(array_filter($my_items, fn($i) => $i['status']==='resolved'));
        foreach ([
            ['📋', 'Total Reports',   count($my_items),  '#1f6feb'],
            ['🟢', 'Open',            $open,             '#238636'],
            ['🟡', 'Claimed',         $claimed,          '#9e6a03'],
            ['✅', 'Resolved',        $resolved,         '#8b949e'],
            ['📨', 'Claims Sent',     count($my_claims), '#6e40c9'],
        ] as [$icon, $label, $val, $color]):
        ?>
        <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:1.25rem;text-align:center">
            <div style="font-size:1.8rem"><?= $icon ?></div>
            <div style="font-size:1.6rem;font-weight:700;color:<?= $color ?>"><?= $val ?></div>
            <div style="font-size:0.8rem;color:var(--muted)"><?= $label ?></div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- My Reports -->
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
                <tr>
                    <th>Title</th><th>Type</th><th>Location</th><th>Status</th><th>Date</th><th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($my_items as $item): ?>
            <tr>
                <td><a href="/pages/item.php?id=<?= $item['id'] ?>"><?= htmlspecialchars($item['title']) ?></a></td>
                <td><span class="badge badge-<?= $item['type'] ?>"><?= strtoupper($item['type']) ?></span></td>
                <td><?= htmlspecialchars($item['location'] ?? '—') ?></td>
                <td><span class="badge badge-<?= $item['status'] ?>"><?= strtoupper($item['status']) ?></span></td>
                <td><?= date('M j, Y', strtotime($item['created_at'])) ?></td>
                <td>
                    <a href="/pages/item.php?id=<?= $item['id'] ?>" class="btn btn-outline" style="font-size:0.8rem;padding:0.3rem 0.7rem">View</a>
                    <a href="/pages/delete.php?id=<?= $item['id'] ?>" class="btn btn-danger" style="font-size:0.8rem;padding:0.3rem 0.7rem" onclick="return confirm('Delete this item?')">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <!-- My Claims -->
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
                <td><span class="badge badge-<?= $c['status']==='pending'?'open':($c['status']==='approved'?'found':'lost') ?>"><?= strtoupper($c['status']) ?></span></td>
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
