<?php if (!isset($_SESSION)) session_start(); ?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'CampusL&F' ?> — UMaT Lost & Found</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🔍</text></svg>">
    <script>
        const saved = localStorage.getItem('clf-theme') || 'dark';
        document.documentElement.setAttribute('data-theme', saved);
    </script>
</head>
<body>

<!-- Mobile Overlay -->
<div id="nav-overlay" onclick="closeDrawer()" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:300"></div>

<!-- Mobile Drawer -->
<div id="nav-drawer" style="position:fixed;top:0;right:-280px;width:280px;height:100vh;background:var(--surface);border-left:1px solid var(--border);z-index:400;transition:right 0.3s ease;padding:1.5rem;overflow-y:auto">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:2rem">
        <span style="font-size:1.1rem;font-weight:700">Campus<span style="color:var(--accent2)">L&F</span></span>
        <button onclick="closeDrawer()" style="background:none;border:none;color:var(--text);font-size:1.5rem;cursor:pointer">✕</button>
    </div>
    <nav style="display:flex;flex-direction:column;gap:0.25rem">
        <a href="/" style="padding:0.75rem 0.5rem;color:var(--text);border-bottom:1px solid var(--border);text-decoration:none">🏠 Home</a>
        <a href="/pages/browse.php" style="padding:0.75rem 0.5rem;color:var(--text);border-bottom:1px solid var(--border);text-decoration:none">🔎 Browse</a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="/pages/report.php" style="padding:0.75rem 0.5rem;color:var(--text);border-bottom:1px solid var(--border);text-decoration:none">📋 Report Item</a>
            <a href="/pages/dashboard.php" style="padding:0.75rem 0.5rem;color:var(--text);border-bottom:1px solid var(--border);text-decoration:none">📊 Dashboard</a>
            <a href="/pages/account.php" style="padding:0.75rem 0.5rem;color:var(--text);border-bottom:1px solid var(--border);text-decoration:none">👤 My Account</a>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <a href="/admin/" style="padding:0.75rem 0.5rem;color:var(--text);border-bottom:1px solid var(--border);text-decoration:none">⚙️ Admin</a>
            <?php endif; ?>
            <a href="/pages/logout.php" style="padding:0.75rem 0.5rem;color:var(--danger);text-decoration:none">🚪 Logout</a>
        <?php else: ?>
            <a href="/pages/login.php" style="padding:0.75rem 0.5rem;color:var(--text);border-bottom:1px solid var(--border);text-decoration:none">🔑 Login</a>
            <a href="/pages/register.php" style="padding:0.75rem 0.5rem;color:var(--accent2);text-decoration:none">✨ Register</a>
        <?php endif; ?>
        <div style="margin-top:1.5rem">
            <p style="color:var(--muted);font-size:0.8rem;margin-bottom:0.5rem">Theme</p>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.5rem">
                <button onclick="setTheme('dark')"      class="theme-opt" style="border:1px solid var(--border);border-radius:var(--radius);padding:0.5rem;text-align:center">🌑 Dark</button>
                <button onclick="setTheme('light')"     class="theme-opt" style="border:1px solid var(--border);border-radius:var(--radius);padding:0.5rem;text-align:center">☀️ Light</button>
                <button onclick="setTheme('cyberpunk')" class="theme-opt" style="border:1px solid var(--border);border-radius:var(--radius);padding:0.5rem;text-align:center">⚡ Cyber</button>
                <button onclick="setTheme('ocean')"     class="theme-opt" style="border:1px solid var(--border);border-radius:var(--radius);padding:0.5rem;text-align:center">🌊 Ocean</button>
            </div>
        </div>
    </nav>
</div>

<nav>
    <a href="/" class="logo">Campus<span>L&F</span></a>

    <!-- Desktop links -->
    <ul id="nav-links">
        <li><a href="/">Home</a></li>
        <li><a href="/pages/browse.php">Browse</a></li>
	<li><a href="/pages/about.php">About</a></li>

        <?php if (isset($_SESSION['user_id'])): ?>
            <li><a href="/pages/report.php">Report Item</a></li>
            <li><a href="/pages/dashboard.php">Dashboard</a></li>
            <li><a href="/pages/account.php">My Account</a></li>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <li><a href="/admin/">Admin</a></li>
            <?php endif; ?>
        <?php else: ?>
            <li><a href="/pages/login.php">Login</a></li>
            <li><a href="/pages/register.php" class="btn btn-primary">Register</a></li>
        <?php endif; ?>
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

    <!-- Hamburger (mobile only) -->
    <button class="hamburger" id="hamburger" onclick="openDrawer()" aria-label="Open menu">
        <span></span><span></span><span></span>
    </button>
</nav>

<script>
function openDrawer() {
    document.getElementById('nav-drawer').style.right = '0';
    document.getElementById('nav-overlay').style.display = 'block';
    document.body.style.overflow = 'hidden';
}
function closeDrawer() {
    document.getElementById('nav-drawer').style.right = '-280px';
    document.getElementById('nav-overlay').style.display = 'none';
    document.body.style.overflow = '';
}
function toggleThemeMenu() {
    const m = document.getElementById('theme-menu');
    m.style.display = m.style.display === 'none' ? 'block' : 'none';
}
function setTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('clf-theme', theme);
    const m = document.getElementById('theme-menu');
    if (m) m.style.display = 'none';
    closeDrawer();
}
document.addEventListener('click', function(e) {
    const btn  = document.getElementById('theme-btn');
    const menu = document.getElementById('theme-menu');
    if (menu && btn && !btn.contains(e.target) && !menu.contains(e.target)) {
        menu.style.display = 'none';
    }
});
</script>
