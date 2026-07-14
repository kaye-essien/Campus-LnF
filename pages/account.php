<?php
session_start();
require '../includes/db.php';
require '../includes/auth.php';
requireLogin();

$pageTitle = 'My Account';
$uid = $_SESSION['user_id'];
$error = '';
$success = '';

$stmt = $conn->prepare("SELECT name, email FROM users WHERE id = ?");
$stmt->bind_param("i", $uid);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $current_pw = $_POST['current_password'];
    $new_pw = $_POST['new_password'];

    if (empty($name)) {
        $error = "Name cannot be empty.";
    } else {
        $s = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $s->bind_param("i", $uid);
        $s->execute();
        $row = $s->get_result()->fetch_assoc();
        $s->close();

        if (!empty($current_pw)) {
            if (!password_verify($current_pw, $row['password'])) {
                $error = "Current password is incorrect.";
            } elseif (strlen($new_pw) < 6) {
                $error = "New password must be at least 6 characters.";
            } else {
                $hash = password_hash($new_pw, PASSWORD_BCRYPT);
                $u = $conn->prepare("UPDATE users SET name=?, password=? WHERE id=?");
                $u->bind_param("ssi", $name, $hash, $uid);
                $u->execute();
                $u->close();
                $_SESSION['name'] = $name;
                $success = "Account updated successfully!";
            }
        } else {
            $u = $conn->prepare("UPDATE users SET name=? WHERE id=?");
            $u->bind_param("si", $name, $uid);
            $u->execute();
            $u->close();
            $_SESSION['name'] = $name;
            $success = "Name updated successfully!";
        }
        $user['name'] = $name;
    }
}
?>
<?php include '../includes/header.php'; ?>
<div class="container" style="max-width:520px">
    <div class="page-header">
        <h1>My Account</h1>
        <p>Update your name or change your password.</p>
    </div>
    <?php if ($error): ?><p class="error"><?= $error ?></p><?php endif; ?>
    <?php if ($success): ?><p class="success"><?= $success ?></p><?php endif; ?>
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:2rem">
        <form method="POST">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
            </div>
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" value="<?= htmlspecialchars($user['email']) ?>" disabled style="opacity:0.6;cursor:not-allowed">
                <small style="color:var(--muted)">Email cannot be changed.</small>
            </div>
            <hr style="border-color:var(--border);margin:1.5rem 0">
            <p style="color:var(--muted);font-size:0.9rem;margin-bottom:1rem">Leave password fields empty to keep current password.</p>
            <div class="form-group">
                <label>Current Password</label>
                <input type="password" name="current_password" placeholder="Enter current password">
            </div>
            <div class="form-group">
                <label>New Password</label>
                <input type="password" name="new_password" placeholder="Min 6 characters">
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;padding:0.75rem">
                Save Changes
            </button>
        </form>
    </div>
    <div style="margin-top:1rem;text-align:center">
        <a href="/pages/dashboard.php" style="color:var(--muted);font-size:0.9rem">← Back to Dashboard</a>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
