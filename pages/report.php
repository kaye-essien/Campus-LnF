<?php
session_start();
require '../includes/db.php';
require '../includes/auth.php';
requireLogin();
$pageTitle = 'Report Item';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type        = $_POST['type'];
    $category    = $_POST['category'];
    $title       = trim($_POST['title']);
    $description = trim($_POST['description']);
    $location    = trim($_POST['location']);
    $whatsapp    = trim($_POST['whatsapp'] ?? '');

    if (empty($title) || empty($type) || empty($category)) {
        $error = "Type, category and title are required.";
    } else {
        $stmt = $conn->prepare("INSERT INTO items (user_id, type, category, title, description, location, whatsapp) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssss", $_SESSION['user_id'], $type, $category, $title, $description, $location, $whatsapp);
        if ($stmt->execute()) {
            $new_id = $stmt->insert_id;
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
                        $is->bind_param("is", $new_id, $filename);
                        $is->execute();
                        $is->close();
                    }
                }
            }
            header("Location: /pages/item.php?id=$new_id");
            exit();
        } else {
            $error = "Something went wrong. Try again.";
            $stmt->close();
        }
    }
}

$categories = ['Electronics','Clothing','Books','ID Card','Keys','Bag','Wallet','Jewellery','Sports','Other'];
?>
<?php include '../includes/header.php'; ?>
<div class="container" style="max-width:680px">
    <div class="page-header">
        <h1>Report an Item</h1>
        <p>Fill in the details below to post a lost or found item.</p>
    </div>
    <?php if ($error): ?><p class="error"><?= $error ?></p><?php endif; ?>
    <form method="POST" enctype="multipart/form-data" style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:2rem">
        <div class="form-group">
            <label>Item Type *</label>
            <select name="type" required>
                <option value="">-- Select --</option>
                <option value="lost">🔍 I Lost Something</option>
                <option value="found">📦 I Found Something</option>
            </select>
        </div>
        <div class="form-group">
            <label>Category *</label>
            <select name="category" required>
                <option value="">-- Select Category --</option>
                <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat ?>"><?= $cat ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Item Title *</label>
            <input type="text" name="title" placeholder="e.g. Blue Samsung Galaxy A14" required>
        </div>
        <div class="form-group">
            <label>Description</label>
            <textarea name="description" placeholder="Describe the item — color, brand, distinguishing features..."></textarea>
        </div>
        <div class="form-group">
            <label>Location</label>
            <input type="text" name="location" placeholder="e.g. Main Library, Block C Lecture Hall">
        </div>
        <div class="form-group">
            <label>WhatsApp Number <span style="color:var(--muted);font-weight:400">(optional)</span></label>
            <input type="text" name="whatsapp" placeholder="e.g. 0244123456">
            <small style="color:var(--muted)">So people can contact you directly on WhatsApp.</small>
        </div>
        <div class="form-group">
            <label>Photos (up to 4, max 5MB each)</label>
            <input type="file" name="images[]" accept="image/*" multiple style="color:var(--text)">
            <small style="color:var(--muted)">Hold Ctrl/Cmd to select multiple photos.</small>
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%;padding:0.8rem">Post Item</button>
    </form>
</div>
<?php include '../includes/footer.php'; ?>
