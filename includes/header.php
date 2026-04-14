<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'UniStay' ?></title>
    <link rel="stylesheet" href="<?= $basePath ?? '' ?>assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="<?= (isset($showNav) && $showNav) ? '' : 'auth-body' ?>" id="appBody">
<script>
// Apply theme instantly before paint to avoid flash
(function(){
    var stored = localStorage.getItem('theme');
    var dbPref = '<?= isset($user) && isset($user['dark_mode']) ? ($user['dark_mode'] ? 'dark' : 'light') : 'light' ?>';
    var theme  = stored || dbPref;
    document.getElementById('appBody').classList.add(theme);
})();
</script>

<?php if (isset($showNav) && $showNav): ?>
<!-- Desktop Top Bar -->
<header class="desktop-topbar">
    <a href="home.php" class="logo">
        <svg width="28" height="28" viewBox="0 0 40 40" fill="none">
            <rect width="40" height="40" rx="8" fill="#fff"/>
            <path d="M20 8L8 16v16h8v-8h8v8h8V16L20 8z" fill="#000"/>
        </svg>
        UniStay
    </a>
    <div class="topbar-right">
        <a href="home.php">Find Hostels</a>
        <a href="my-bookings.php">My Bookings</a>
        <a href="payments.php">Payments</a>
        <?php
        // Unread notification count
        $unreadCount = 0;
        if (isset($_SESSION['user_id'])) {
            $nStmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0");
            $nStmt->execute([$_SESSION['user_id']]);
            $unreadCount = (int)$nStmt->fetchColumn();
        }
        ?>
        <a href="notifications.php" style="position:relative;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg>
            <?php if ($unreadCount > 0): ?>
            <span style="position:absolute;top:-4px;right:-4px;background:var(--red);color:#fff;font-size:0.6rem;font-weight:700;width:16px;height:16px;border-radius:50%;display:flex;align-items:center;justify-content:center;"><?= $unreadCount > 9 ? '9+' : $unreadCount ?></span>
            <?php endif; ?>
        </a>
        <a href="profile.php">
            <?php
            if (isset($user)) {
                $ini = strtoupper(implode('', array_map(fn($w) => $w[0], explode(' ', $user['name']))));
                echo '<div class="avatar" style="width:32px;height:32px;font-size:0.75rem;">' . htmlspecialchars($ini) . '</div>';
            }
            ?>
        </a>
    </div>
</header>

<!-- Desktop Sidebar -->
<aside class="desktop-sidebar">
    <nav class="sidebar-nav">
        <span class="sidebar-label">Menu</span>
        <a href="home.php" class="sidebar-item <?= ($activePage ?? '') === 'home' ? 'active' : '' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            Home
        </a>
        <a href="my-bookings.php" class="sidebar-item <?= ($activePage ?? '') === 'bookings' ? 'active' : '' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            My Bookings
        </a>
        <a href="payments.php" class="sidebar-item <?= ($activePage ?? '') === 'payments' ? 'active' : '' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
            Payments
        </a>
        <a href="notifications.php" class="sidebar-item <?= ($activePage ?? '') === 'notifications' ? 'active' : '' ?>" style="position:relative;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg>
            Notifications
            <?php if (!empty($unreadCount) && $unreadCount > 0): ?>
            <span style="margin-left:auto;background:var(--red);color:#fff;font-size:0.6rem;font-weight:700;padding:2px 6px;border-radius:50px;"><?= $unreadCount ?></span>
            <?php endif; ?>
        </a>
        <div class="sidebar-divider"></div>
        <span class="sidebar-label">Account</span>
        <a href="profile.php" class="sidebar-item <?= ($activePage ?? '') === 'profile' ? 'active' : '' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            Profile
        </a>
    </nav>
    <div class="sidebar-bottom">
        <div class="sidebar-divider"></div>
        <a href="logout.php" class="sidebar-item">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            Sign Out
        </a>
    </div>
</aside>
<?php endif; ?>

<div class="app-container <?= (isset($showNav) && $showNav) ? '' : 'no-nav' ?>">
