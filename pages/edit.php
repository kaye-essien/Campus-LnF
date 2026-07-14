<?php
session_start();
require '../includes/db.php';
require '../includes/auth.php';
requireLogin();

$id = intval($_GET['id'] ?? 0);
if (!$id) { header("Location: /pages/dashboard.php"); exit(); }

$stmt = $conn->prepare("SELECT * FROM items WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $id, $_SESSION['user_id']);
$stmt->execute();
$item = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$item) { header("Location: /pages/dashboard.php"); exit(); }

$pageTitle = 'Edit Item';
$error = '';
$success = '';
$categories = ['Electronics','Clothing','Books','ID Card','Keys','Bag','Wallet','Jewellery','Sports','Other'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type        = $_POST['type'];
    $category    = $_POST['category'];
    $title       = trim($_POST['title']);
    $description = trim($_POST['description']);
    $location    = trim($_POST['location']);

    if (empty($title) || empty($type)) {
        $error = "Type and title are required.";
    } else {
        $stmt = $conn->prepare("UPDATE items SET type=?, category=?, title=?, description=?, location=? WHERE id=?");
        $stmt->bind_param("sssssi", $type, $category, $title, $description, $location, $id);
        $stmt->execute();
        $stmt->close();

        if (!empty($_FILES['images']['name'][0])) {
            $allowed = ['jpg','jpeg','png','webp'];
            $count = min(count($_FILES['images']['name']), 4);
            for ($i = 0; $i < $count; $i++) {
                if ($_FILES['images']['error'][$i] !== 0) continue;
                $ext = strtolower(pathinfo($_FILES['images']['name'][$i], PATHINFO_EXTENSION));
                if (!in_array($ext, $allowed)) continue;
                if ($_FILES['images']['size'][$i] > 5 * 1024 * 1024) continue;
                $filename = uniqid('item_') . '.' . $ext;
                if (move_uploaded_file($_FILES['images']['tmp_name'][$i], '../uploads/' . $filename)) {
                    $is = $conn->prepare("INSERT INTO item_images (item_id, image_path) VALUES (?, ?)");
                    $is->bind_param("is", $id, $filename);
                    $is->execute();
                    $is->close();
                }
            }
        }
        $success = "Item updated successfully!";
        $item = array_merge($item, compact('type','category','title','description','location'));
    }
}

$imgs = $conn->prepare("SELECT * FROM item_images WHERE item_id = ?");
$imgs->bind_param("i", $id);
$imgs->execute();
$images = $imgs->get_result()->fetch_all(MYSQLI_ASSOC);
$imgs->close();
?>
<?php include '../includes/header.php'; ?>
<div class="container" style="max-width:680px">
    <div class="page-header">
        <h1>Edit Item</h1>
        <p>Update your lost or found report.</p>
    </div>
    <?php if ($error): ?><p class="error"><?= $error ?></p><?php endif; ?>
    <?php if ($success): ?><p class="success"><?= $success ?></p><?php endif; ?>
    <form method="POST" enctype="multipart/form-data" style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:2rem">
        <div class="form-group">
            <label>Item Type *</label>
            <select name="type" required>
                <option value="lost"  <?= $item['type']==='lost'  ? 'selected':'' ?>>🔍 Lost</option>
                <option value="found" <?= $item['type']==='found' ? 'selected':'' ?>>📦 Found</option>
            </select>
        </div>
        <div class="form-group">
            <label>Category</label>
            <select name="category">
                <option value="">-- Select --</option>
                <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat ?>" <?= $item['category']===$cat ? 'selected':'' ?>><?= $cat ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Item Title *</label>
            <input type="text" name="title" value="<?= htmlspecialchars($item['title']) ?>" required>
        </div>
        <div class="form-group">
            <label>Description</label>
            <textarea name="description"><?= htmlspecialchars($item['description'] ?? '') ?></textarea>
        </div>
        <div class="form-group">
            <label>Location</label>
            <input type="text" name="location" value="<?= htmlspecialchars($item['location'] ?? '') ?>">
        </div>
        <?php if (!empty($images)): ?>
        <div class="form-group">
            <label>Current Photos</label>
            <div style="display:flex;gap:0.5rem;flex-wrap:wrap">
                <?php foreach ($images as $img): ?>
                <div style="position:relative">
                    <img src="/uploads/<?= htmlspecialchars($img['image_path']) ?>" style="width:80px;height:80px;object-fit:cover;border-radius:var(--radius)">
                    <a href="/pages/delete_image.php?id=<?= $img['id'] ?>&item_id=<?= $id ?>" onclick="return confirm('Delete this photo?')" style="position:absolute;top:-6px;right:-6px;background:var(--danger);color:#fff;border-radius:50%;width:20px;height:20px;display:flex;align-items:center;justify-content:center;font-size:0.7rem;text-decoration:none">✕</a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        <div class="form-group">
            <label>Add More Photos (up to 4, max 5MB each)</label>
            <input type="file" name="images[]" accept="image/*" multiple style="color:var(--text)">
        </div>
        <div style="display:flex;gap:1rem">
            <button type="submit" class="btn btn-primary" style="flex:1;padding:0.8rem">Save Changes</button>
            <a href="/pages/item.php?id=<?= $id ?>" class="btn btn-outline" style="flex:1;padding:0.8rem;text-align:center">Cancel</a>
        </div>
    </form>
</div>
<?php include '../includes/footer.php'; ?>
