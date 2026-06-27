<?php if (!isset($_SESSION)) session_start(); ?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'CampusL&F' ?> — UMaT Lost & Found</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <script>
        // apply saved theme immediately to avoid flash
        const saved = localStorage.getItem('clf-theme') || 'dark';
        document.documentElement.setAttribute('data-theme', saved);
    </script>
</head>
<body>
<nav>
    <a href="/" class="logo">Campus<span>L&F</span></a>
    <button type="button" id="nav-toggle" class="nav-toggle" onclick="toggleNav()" aria-expanded="false" aria-label="Toggle menu">☰</button>
    <ul id="nav-links">
        <li><a href="/">Home</a></li>
        <li><a href="/pages/browse.php">Browse</a></li>
        <?php if (isset($_SESSION['user_id'])): ?>
            <li><a href="/pages/report.php">Report Item</a></li>
            <li><a href="/pages/dashboard.php">Dashboard</a></li>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <li><a href="/admin/">Admin</a></li>
            <?php endif; ?>
        <?php else: ?>
            <li><a href="/pages/login.php">Login</a></li>
            <li><a href="/pages/register.php" class="btn btn-primary">Register</a></li>
        <?php endif; ?>

        <!-- Theme Switcher -->
        <li style="position:relative">
            <button type="button" onclick="toggleThemeMenu()" id="theme-btn" class="btn btn-outline" style="font-size:0.85rem;padding:0.4rem 0.8rem">
                🎨 Theme
            </button>
            <div id="theme-menu" style="display:none;position:absolute;right:0;top:110%;background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);min-width:160px;z-index:200;overflow:hidden">
                <button type="button" onclick="setTheme('dark')"      class="theme-opt">🌑 Dark</button>
                <button type="button" onclick="setTheme('light')"     class="theme-opt">☀️ Light</button>
                <button type="button" onclick="setTheme('cyberpunk')" class="theme-opt">⚡ Cyberpunk</button>
                <button type="button" onclick="setTheme('ocean')"     class="theme-opt">🌊 Ocean</button>
            </div>
        </li>

        <?php if (isset($_SESSION['user_id'])): ?>
            <li><a href="/pages/logout.php" class="btn btn-outline">Logout</a></li>
        <?php endif; ?>
    </ul>
</nav>

<script>
function toggleThemeMenu() {
    const m = document.getElementById('theme-menu');
    m.style.display = m.style.display === 'none' ? 'block' : 'none';
}

function setTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('clf-theme', theme);
    document.getElementById('theme-menu').style.display = 'none';
}

function toggleNav() {
    const navLinks = document.getElementById('nav-links');
    const toggle = document.getElementById('nav-toggle');
    const isOpen = navLinks.classList.toggle('open');
    toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
}

// close menu when clicking outside
document.addEventListener('click', function(e) {
    const btn  = document.getElementById('theme-btn');
    const menu = document.getElementById('theme-menu');
    const navToggle = document.getElementById('nav-toggle');
    const navLinks = document.getElementById('nav-links');

    if (menu && !btn.contains(e.target) && !menu.contains(e.target)) {
        menu.style.display = 'none';
    }

    if (navLinks && navToggle && !navToggle.contains(e.target) && !navLinks.contains(e.target)) {
        navLinks.classList.remove('open');
        navToggle.setAttribute('aria-expanded', 'false');
    }
});
</script>
