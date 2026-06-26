<?php
session_start();
require '../includes/db.php';

$token = trim($_GET['token'] ?? '');
$message = '';
$success = false;

if (!$token) {
    $message = "Invalid verification link.";
} else {
    $stmt = $conn->prepare("SELECT id, is_verified FROM users WHERE verify_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$user) {
        $message = "Invalid or expired verification link.";
    } elseif ($user['is_verified']) {
        $message = "Your account is already verified. <a href='login.php'>Login here</a>.";
        $success = true;
    } else {
        $conn->query("UPDATE users SET is_verified=1, verify_token=NULL WHERE id={$user['id']}");
        $message = "✅ Email verified! Your account is now active.";
        $success = true;
    }
}
$pageTitle = 'Verify Email';
?>
<?php include '../includes/header.php'; ?>
<div class="auth-page">
    <div class="auth-box" style="max-width:440px;text-align:center">
        <div style="font-size:3rem;margin-bottom:1rem"><?= $success ? '✅' : '❌' ?></div>
        <h2 style="margin-bottom:1rem">Email Verification</h2>
        <p class="<?= $success ? 'success' : 'error' ?>"><?= $message ?></p>
        <?php if ($success): ?>
            <a href="login.php" class="btn btn-primary" style="margin-top:1rem;display:inline-block">Go to Login</a>
        <?php endif; ?>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
