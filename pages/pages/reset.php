<?php
session_start();
require '../includes/db.php';

$pageTitle = 'Reset Password';
$token  = trim($_GET['token'] ?? '');
$error  = '';
$success = '';

if (!$token) { header("Location: /pages/login.php"); exit(); }

$stmt = $conn->prepare("SELECT id FROM users WHERE verify_token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    $error = "Invalid or expired reset link.";
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pw  = $_POST['password'];
    $pw2 = $_POST['password2'];
    if (strlen($pw) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif ($pw !== $pw2) {
        $error = "Passwords don't match.";
    } else {
        $hash = password_hash($pw, PASSWORD_BCRYPT);
        $upd  = $conn->prepare("UPDATE users SET password_hash=?, verify_token=NULL, is_verified=1 WHERE id=?");
        $upd->bind_param("si", $hash, $user['id']);
        $upd->execute();
        $upd->close();
        $success = "Password reset! <a href='login.php'>Login here</a>.";
    }
}
?>
<?php include '../includes/header.php'; ?>
<div class="auth-page">
    <div class="auth-box" style="max-width:440px">
        <div style="text-align:center;margin-bottom:2rem">
            <div style="font-size:2.5rem">🔒</div>
            <h2 style="margin-top:0.5rem">New Password</h2>
        </div>
        <?php if ($error): ?><p class="error"><?= $error ?></p><?php endif; ?>
        <?php if ($success): ?><p class="success"><?= $success ?></p><?php endif; ?>
        <?php if (!$success && !$error): ?>
        <form method="POST">
            <div class="form-group">
                <label>New Password</label>
                <div class="pw-wrapper">
                    <input type="password" name="password" id="pw1" placeholder="Min 6 characters" required oninput="checkStrength(this.value)">
                    <button type="button" class="pw-toggle" onclick="togglePw('pw1',this)">👁️</button>
                </div>
                <div class="strength-bar"><div class="strength-fill" id="strength-fill"></div></div>
                <div class="strength-label" id="strength-label">Enter a password</div>
            </div>
            <div class="form-group">
                <label>Confirm Password</label>
                <div class="pw-wrapper">
                    <input type="password" name="password2" id="pw2" placeholder="Repeat password" required>
                    <button type="button" class="pw-toggle" onclick="togglePw('pw2',this)">👁️</button>
                </div>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;padding:0.75rem">Reset Password</button>
        </form>
        <?php endif; ?>
    </div>
</div>
<script>
function togglePw(id, btn) {
    const f = document.getElementById(id);
    if (f.type === 'password') { f.type = 'text'; btn.textContent = '🙈'; }
    else { f.type = 'password'; btn.textContent = '👁️'; }
}
function checkStrength(val) {
    const fill = document.getElementById('strength-fill');
    const label = document.getElementById('strength-label');
    let score = 0;
    if (val.length >= 6) score++;
    if (val.length >= 10) score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;
    const levels = [
        { pct:'0%',   color:'transparent', text:'Enter a password'},
        { pct:'25%',  color:'#da3633',     text:'😟 Too weak'},
        { pct:'50%',  color:'#e3b341',     text:'😐 Weak'},
        { pct:'70%',  color:'#f0883e',     text:'🙂 Fair'},
        { pct:'85%',  color:'#3fb950',     text:'😊 Strong'},
        { pct:'100%', color:'#238636',     text:'💪 Very strong'},
    ];
    const l = levels[score] || levels[0];
    fill.style.width = l.pct;
    fill.style.background = l.color;
    label.textContent = l.text;
    label.style.color = l.color;
}
</script>
<?php include '../includes/footer.php'; ?>
