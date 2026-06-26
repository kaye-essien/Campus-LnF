<?php
session_start();
require '../includes/db.php';

$pageTitle = 'Forgot Password';
$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $stmt  = $conn->prepare("SELECT id, name FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($user) {
        $token = bin2hex(random_bytes(32));
        $upd   = $conn->prepare("UPDATE users SET verify_token = ? WHERE id = ?");
        $upd->bind_param("si", $token, $user['id']);
        $upd->execute();
        $upd->close();
        $link    = "https://eg361.ceiscy.com/pages/reset.php?token=$token";
        $message = "Reset link ready: <a href='$link' style='color:var(--accent2)'>Click here to reset your password</a>.";
        $success = true;
    } else {
        $message = "No account found with that email.";
    }
}
?>
<?php include '../includes/header.php'; ?>
<div class="auth-page">
    <div class="auth-box" style="max-width:440px">
        <div style="text-align:center;margin-bottom:2rem">
            <div style="font-size:2.5rem">🔑</div>
            <h2 style="margin-top:0.5rem">Reset Password</h2>
            <p style="color:var(--muted);font-size:0.9rem">Enter your UMaT email to get a reset link.</p>
        </div>
        <?php if ($message): ?>
            <p class="<?= $success ? 'success' : 'error' ?>"><?= $message ?></p>
        <?php endif; ?>
        <?php if (!$success): ?>
        <form method="POST">
            <div class="form-group">
                <label>Email address</label>
                <input type="email" name="email" placeholder="student@umat.edu.gh" required autofocus>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;padding:0.75rem">Get Reset Link</button>
        </form>
        <?php endif; ?>
        <p style="text-align:center;margin-top:1rem;font-size:0.9rem;color:var(--muted)">
            <a href="login.php">← Back to Login</a>
        </p>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
