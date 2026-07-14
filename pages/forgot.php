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
        $token   = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        $upd     = $conn->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?");
        $upd->bind_param("ssi", $token, $expires, $user['id']);
        $upd->execute();
        $upd->close();
        $link    = "https://campus-lnf.page.gd/pages/reset.php?token=$token";
        $message = "Reset link generated! <a href='$link' style='color:var(--accent2)'>Click here to reset your password</a>.<br><small style='color:var(--muted)'>Link expires in 1 hour.</small>";
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
            <p style="color:var(--muted);font-size:0.9rem">Enter your UMaT student email to get a reset link.</p>
        </div>
        <?php if ($message): ?>
            <p class="<?= $success ? 'success' : 'error' ?>"><?= $message ?></p>
        <?php endif; ?>
        <?php if (!$success): ?>
        <form method="POST">
            <div class="form-group">
                <label>Email address</label>
                <input type="email" name="email" placeholder="student@st.umat.edu.gh" required autofocus>
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
