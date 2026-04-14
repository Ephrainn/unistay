<?php
require_once 'includes/auth.php';
require_once 'config/db.php';
requireLogin();

// Mark all as read
if (isset($_GET['mark_read'])) {
    $pdo->prepare("UPDATE notifications SET is_read=1 WHERE user_id=?")->execute([$_SESSION['user_id']]);
    header('Location: notifications.php'); exit;
}

// Mark single as read
if (isset($_GET['read'])) {
    $pdo->prepare("UPDATE notifications SET is_read=1 WHERE id=? AND user_id=?")->execute([(int)$_GET['read'], $_SESSION['user_id']]);
}

$stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$notifications = $stmt->fetchAll();
$unread = array_filter($notifications, fn($n) => !$n['is_read']);

$pageTitle = 'Notifications – UniStay';
$showNav = true; $activePage = 'notifications';
include 'includes/header.php';
?>
<div class="page-wrap">
    <div class="page-header">
        <div><h1>Notifications</h1><p><?= count($unread) ?> unread</p></div>
        <?php if ($unread): ?>
        <a href="notifications.php?mark_read=1" class="btn btn-outline btn-auto" style="padding:9px 16px;font-size:0.82rem;">Mark all read</a>
        <?php endif; ?>
    </div>

    <?php if (empty($notifications)): ?>
    <div style="text-align:center;padding:60px 0;">
        <div style="font-size:3rem;margin-bottom:16px;">🔔</div>
        <h3 style="color:var(--muted);font-weight:400;">No notifications yet</h3>
        <p style="margin-top:6px;">We'll notify you about bookings, payments, and updates.</p>
    </div>
    <?php else: ?>
    <div style="display:flex;flex-direction:column;gap:8px;">
        <?php foreach ($notifications as $n): ?>
        <a href="<?php
               $link = $n['link'] ?: '#';
               $sep  = ($link !== '#' && strpos($link, '?') !== false) ? '&' : '?';
               echo htmlspecialchars($link . $sep . 'read=' . $n['id']);
           ?>"
           style="display:flex;align-items:flex-start;gap:14px;background:var(--card);border-radius:14px;padding:16px;border:1px solid <?= $n['is_read'] ? 'var(--border)' : 'var(--accent)' ?>;opacity:<?= $n['is_read'] ? '.7' : '1' ?>;">
            <div style="width:40px;height:40px;border-radius:10px;background:var(--accent-bg);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <?php
                $icons = [
                    'booking' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="3" y1="10" x2="21" y2="10"/></svg>',
                    'payment' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>',
                    'review'  => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>',
                    'system'  => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>',
                ];
                echo $icons[$n['type']] ?? $icons['system'];
                ?>
            </div>
            <div style="flex:1;">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:8px;">
                    <h4 style="font-size:0.875rem;"><?= htmlspecialchars($n['title']) ?></h4>
                    <?php if (!$n['is_read']): ?><span style="width:8px;height:8px;background:var(--accent);border-radius:50%;flex-shrink:0;margin-top:4px;"></span><?php endif; ?>
                </div>
                <p style="font-size:0.8rem;margin-top:3px;"><?= htmlspecialchars($n['message']) ?></p>
                <p style="font-size:0.7rem;color:var(--dim);margin-top:6px;"><?= date('M d, Y · g:i A', strtotime($n['created_at'])) ?></p>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
<?php include 'includes/footer.php'; ?>
