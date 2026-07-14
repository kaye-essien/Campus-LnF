<?php
session_start();
require '../includes/db.php';
$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    if (empty($name) || empty($email) || empty($password)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address.";
    } elseif (!str_ends_with($email, '@st.umat.edu.gh')) {
        $error = "Please use your UMaT student email (@st.umat.edu.gh).";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, verified) VALUES (?, ?, ?, 1)");
        $stmt->bind_param("sss", $name, $email, $hash);
        try {
            $stmt->execute();
            $success = "Account created! You can now <a href='login.php'>login here</a>.";
        } catch (Exception $e) {
            $error = "Email already registered.";
        }
        $stmt->close();
    }
}
?>
<?php include '../includes/header.php'; ?>
<div class="auth-page">
    <div class="auth-box" style="max-width:440px">
        <div style="text-align:center;margin-bottom:2rem">
            <div style="font-size:2.5rem">🔍</div>
            <h1 style="font-size:1.5rem;margin-top:0.5rem">Campus<span style="color:var(--accent2)">L&F</span></h1>
            <p style="color:var(--muted);font-size:0.9rem">UMaT Lost & Found Platform</p>
        </div>
        <h2 style="font-size:1.2rem;margin-bottom:1.5rem">Create your account</h2>
        <?php if ($error): ?><p class="error"><?= $error ?></p><?php endif; ?>
        <?php if ($success): ?><p class="success"><?= $success ?></p><?php endif; ?>
        <?php if (!$success): ?>
        <form method="POST">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name" placeholder="e.g. Kwame Mensah" required>
            </div>
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" placeholder="student@st.umat.edu.gh" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" id="reg-pw" placeholder="Min 6 characters" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;padding:0.75rem;font-size:1rem">
                Create Account
            </button>
        </form>
        <?php endif; ?>
        <div style="text-align:center;margin-top:1.5rem;padding-top:1.5rem;border-top:1px solid var(--border)">
            <p style="color:var(--muted);font-size:0.9rem">Already have an account? <a href="login.php">Sign in</a></p>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
