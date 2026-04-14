<?php
require_once '../config/db.php';
require_once 'auth.php';
requireAdmin();

$totalUsers    = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalHostels  = $pdo->query("SELECT COUNT(*) FROM hostels")->fetchColumn();
$totalBookings = $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
$totalRevenue  = $pdo->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE type='debit' AND status='completed'")->fetchColumn();

$recentBookings = $pdo->query(
    "SELECT b.*, u.name as user_name, h.name as hostel_name
     FROM bookings b
     JOIN users u ON b.user_id = u.id
     JOIN hostels h ON b.hostel_id = h.id
     ORDER BY b.created_at DESC LIMIT 8"
)->fetchAll();

$recentUsers = $pdo->query(
    "SELECT * FROM users ORDER BY created_at DESC LIMIT 6"
)->fetchAll();

$pageTitle = 'Dashboard'; $activePage = 'dashboard';
include 'layout.php';
?>

<div class="admin-stats">
    <div class="admin-stat-card">
        <div class="icon icon-blue"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg></div>
        <div class="label">Total Students</div>
        <div class="value"><?= number_format($totalUsers) ?></div>
        <div class="sub">Registered users</div>
    </div>
    <div class="admin-stat-card">
        <div class="icon icon-green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg></div>
        <div class="label">Total Hostels</div>
        <div class="value"><?= number_format($totalHostels) ?></div>
        <div class="sub">Listed properties</div>
    </div>
    <div class="admin-stat-card">
        <div class="icon icon-yellow"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="3" y1="10" x2="21" y2="10"/></svg></div>
        <div class="label">Total Bookings</div>
        <div class="value"><?= number_format($totalBookings) ?></div>
        <div class="sub">All time</div>
    </div>
    <div class="admin-stat-card">
        <div class="icon icon-green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg></div>
        <div class="label">Total Revenue</div>
        <div class="value">$<?= number_format($totalRevenue, 0) ?></div>
        <div class="sub">Payments collected</div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 340px;gap:20px;">
    <!-- Recent bookings -->
    <div class="admin-table-wrap">
        <div class="admin-table-header">
            <h2>Recent Bookings</h2>
            <a href="bookings.php" style="font-size:0.82rem;color:var(--muted);">View all</a>
        </div>
        <table>
            <thead><tr><th>Student</th><th>Hostel</th><th>Move-in</th><th>Rent</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach ($recentBookings as $b): ?>
            <tr>
                <td><?= htmlspecialchars($b['user_name']) ?></td>
                <td><?= htmlspecialchars($b['hostel_name']) ?></td>
                <td><?= date('M d, Y', strtotime($b['move_in'])) ?></td>
                <td>$<?= number_format($b['monthly_rent'], 0) ?>/mo</td>
                <td><span class="badge badge-<?= $b['status'] ?>"><?= ucfirst($b['status']) ?></span></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($recentBookings)): ?>
            <tr><td colspan="5" style="text-align:center;color:var(--dim);padding:24px;">No bookings yet</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Recent users -->
    <div class="admin-table-wrap">
        <div class="admin-table-header">
            <h2>New Students</h2>
            <a href="users.php" style="font-size:0.82rem;color:var(--muted);">View all</a>
        </div>
        <table>
            <thead><tr><th>Name</th><th>Verified</th></tr></thead>
            <tbody>
            <?php foreach ($recentUsers as $u): ?>
            <tr>
                <td>
                    <div style="font-weight:500;"><?= htmlspecialchars($u['name']) ?></div>
                    <div style="font-size:0.72rem;color:var(--muted);"><?= htmlspecialchars($u['email']) ?></div>
                </td>
                <td><?= $u['is_verified'] ? '<span class="badge badge-confirmed">Yes</span>' : '<span class="badge badge-pending">No</span>' ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'layout_end.php'; ?>
