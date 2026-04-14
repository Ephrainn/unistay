<?php
require_once 'includes/auth.php';
require_once 'config/db.php';
requireLogin();

$id   = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT b.*, h.name as hostel_name, h.address, h.distance_from_campus, r.type as room_type, r.price FROM bookings b JOIN hostels h ON b.hostel_id=h.id JOIN rooms r ON b.room_id=r.id WHERE b.id=? AND b.user_id=?");
$stmt->execute([$id, $_SESSION['user_id']]);
$booking = $stmt->fetch();
if (!$booking) { header('Location: my-bookings.php'); exit; }

$pageTitle = 'Booking Confirmed – UniStay';
$showNav = true; $activePage = 'bookings';
include 'includes/header.php';
?>
<div class="page-wrap" style="max-width:600px;margin:0 auto;">
    <div style="text-align:center;padding:40px 0 32px;">
        <div style="width:72px;height:72px;background:var(--green-bg);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">
            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        </div>
        <h1 style="font-size:1.6rem;">Booking Submitted!</h1>
        <p style="margin-top:8px;">Your booking request has been received and is pending confirmation.</p>
    </div>

    <div style="background:var(--card);border-radius:16px;padding:24px;border:1px solid var(--border);margin-bottom:20px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;padding-bottom:16px;border-bottom:1px solid var(--border);">
            <h3>Booking #<?= str_pad($booking['id'], 5, '0', STR_PAD_LEFT) ?></h3>
            <span class="badge badge-pending">Pending</span>
        </div>
        <div style="display:flex;flex-direction:column;gap:12px;">
            <div style="display:flex;justify-content:space-between;"><span style="color:var(--muted);">Hostel</span><strong><?= htmlspecialchars($booking['hostel_name']) ?></strong></div>
            <div style="display:flex;justify-content:space-between;"><span style="color:var(--muted);">Room Type</span><strong><?= htmlspecialchars($booking['room_type']) ?></strong></div>
            <div style="display:flex;justify-content:space-between;"><span style="color:var(--muted);">Move-in</span><strong><?= date('M d, Y', strtotime($booking['move_in'])) ?></strong></div>
            <div style="display:flex;justify-content:space-between;"><span style="color:var(--muted);">Move-out</span><strong><?= date('M d, Y', strtotime($booking['move_out'])) ?></strong></div>
            <div style="display:flex;justify-content:space-between;padding-top:12px;border-top:1px solid var(--border);">
                <span style="color:var(--muted);">Monthly Rent</span>
                <strong style="font-size:1.1rem;color:var(--accent);"><?= CURRENCY ?><?= number_format($booking['monthly_rent'], 2) ?></strong>
            </div>
        </div>
    </div>

    <div style="background:var(--accent-bg);border-radius:14px;padding:16px;margin-bottom:24px;display:flex;gap:12px;align-items:flex-start;">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="2" style="flex-shrink:0;margin-top:2px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <p style="font-size:0.85rem;color:var(--text);">The hostel manager will review your booking and student ID. You'll receive a notification once confirmed — usually within 24 hours.</p>
    </div>

    <div style="display:flex;gap:12px;flex-wrap:wrap;">
        <a href="my-bookings.php" class="btn btn-primary" style="flex:1;">View My Bookings</a>
        <a href="payments.php" class="btn btn-outline" style="flex:1;">Make a Payment</a>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
