<?php
session_start();
require '../includes/db.php';
require '../includes/auth.php';

if (isLoggedIn()) {
    header("Location: /pages/dashboard.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, name, password_hash, role, is_verified FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if ($user && password_verify($password, $user['password_hash'])) {
    if (!$user['is_verified']) {
        $error = "Please verify your email before logging in. Check your inbox.";
    } else {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name']    = $user['name'];
        $_SESSION['role']    = $user['role'];
        header("Location: /pages/dashboard.php");
        exit();
    }
} else {
    $error = "Invalid email or password.";
}
}
?>
<?php include '../includes/header.php'; ?>

<div class="auth-page">
    <div class="auth-box" style="width:100%;max-width:440px">

        <!-- Logo / Brand -->
        <div style="text-align:center;margin-bottom:2rem">
            <div style="font-size:2.5rem">🔍</div>
            <h1 style="font-size:1.5rem;margin-top:0.5rem">Campus<span style="color:var(--accent2)">L&F</span></h1>
            <p style="color:var(--muted);font-size:0.9rem;margin-top:0.25rem">UMaT Lost & Found Platform</p>
        </div>

        <h2 style="font-size:1.2rem;margin-bottom:1.5rem">Sign in to your account</h2>

        <?php if ($error): ?>
        <p class="error"><?= $error ?></p>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Email address</label>
                <input type="email" name="email" placeholder="you@example.com" required autofocus>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" id="login-pw" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;padding:0.75rem;font-size:1rem;margin-top:0.5rem">
                Sign In
            </button>
        </form>

        <div style="text-align:center;margin-top:1.5rem;padding-top:1.5rem;border-top:1px solid var(--border)">
            <p style="color:var(--muted);font-size:0.9rem">
                Don't have an account? <a href="register.php">Create one</a>
            </p>
<p style="color:var(--muted);font-size:0.9rem;margin-bottom:0.5rem">
    <a href="forgot.php">Forgot your password?</a>
</p>
        </div>
    </div>
</div>
<script>
function togglePw(id, btn) {
    const f = document.getElementById(id);
    if (f.type === 'password') { f.type = 'text'; btn.textContent = '🙈'; }
    else { f.type = 'password'; btn.textContent = '👁️'; }
}
</script>
<?php include '../includes/footer.php'; ?>
