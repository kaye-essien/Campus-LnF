<?php
session_start();
require '../includes/db.php';
require '../includes/auth.php';
requireLogin();

$pageTitle = 'Report Item';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type        = $_POST['type'];
    $title       = trim($_POST['title']);
    $description = trim($_POST['description']);
    $location    = trim($_POST['location']);
    $date        = $_POST['date_reported'];
    $image_path  = null;

    if (empty($title) || empty($type)) {
        $error = "Type and title are required.";
    } else {
        // handle image upload
        if (!empty($_FILES['image']['name'])) {
            $allowed = ['jpg','jpeg','png','webp'];
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed)) {
                $error = "Only JPG, PNG, WEBP images allowed.";
            } elseif ($_FILES['image']['size'] > 20 * 1024 * 1024) {
                $error = "Image must be under 20MB.";
            } else {
                $filename = uniqid('item_') . '.' . $ext;
                move_uploaded_file($_FILES['image']['tmp_name'], '../uploads/' . $filename);
                $image_path = $filename;
            }
        }

        if (!$error) {
            $stmt = $conn->prepare("INSERT INTO items (user_id, type, title, description, location, date_reported, image_path) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("issssss", $_SESSION['user_id'], $type, $title, $description, $location, $date, $image_path);
            if ($stmt->execute()) {
                $new_id = $stmt->insert_id;
                header("Location: /pages/item.php?id=$new_id");
                exit();
            } else {
                $error = "Something went wrong. Try again.";
            }
            $stmt->close();
        }
    }
}
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
            <label>Date Lost/Found</label>
            <input type="date" name="date_reported" max="<?= date('Y-m-d') ?>">
        </div>

        <div class="form-group">
            <label>Photo (optional, max 2MB)</label>
            <input type="file" name="image" accept="image/*" style="color:var(--muted)">
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%;padding:0.8rem">Post Item</button>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
