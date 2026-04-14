<?php
require_once 'includes/auth.php';
require_once 'config/db.php';
requireLogin();

$filter = $_GET['filter'] ?? 'all';
$sql    = "SELECT b.*, h.name as hostel_name, h.image as hostel_image, h.distance_from_campus
           FROM bookings b
           JOIN hostels h ON b.hostel_id = h.id
           WHERE b.user_id = ?";
$params = [$_SESSION['user_id']];
if ($filter === 'upcoming')  { $sql .= " AND b.status IN ('pending','confirmed')"; }
elseif ($filter === 'past')  { $sql .= " AND b.status = 'completed'"; }
elseif ($filter === 'cancelled') { $sql .= " AND b.status = 'cancelled'"; }
$sql .= " ORDER BY b.created_at DESC";
$stmt = $pdo->prepare($sql); $stmt->execute($params);
$bookings = $stmt->fetchAll();

$upcoming  = array_values(array_filter($bookings, fn($b) => in_array($b['status'], ['pending','confirmed'])));
$past      = array_values(array_filter($bookings, fn($b) => $b['status'] === 'completed'));
$cancelled = array_values(array_filter($bookings, fn($b) => $b['status'] === 'cancelled'));

$pageTitle = 'My Bookings – UniStay';
$showNav   = true; $activePage = 'bookings';
include 'includes/header.php';
?>

<div class="page-wrap">
    <div class="page-header">
        <h1>My Bookings</h1>
        <button style="background:none;border:none;cursor:pointer;padding:4px;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--text)" stroke-width="2"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg>
        </button>
    </div>

    <?php if (!empty($_GET['booked'])): ?>
    <div class="alert alert-success">Booking submitted successfully!</div>
    <?php endif; ?>

    <div class="chip-row" style="margin-bottom:24px;">
        <a href="my-bookings.php?filter=all"       class="chip <?= $filter==='all'       ? 'active':'' ?>">✓ All</a>
        <a href="my-bookings.php?filter=upcoming"  class="chip <?= $filter==='upcoming'  ? 'active':'' ?>">Upcoming</a>
        <a href="my-bookings.php?filter=past"      class="chip <?= $filter==='past'      ? 'active':'' ?>">Past</a>
        <a href="my-bookings.php?filter=cancelled" class="chip <?= $filter==='cancelled' ? 'active':'' ?>">Cancelled</a>
    </div>

    <div class="two-col">
        <!-- Main list -->
        <div>
            <?php
            $groups = [];
            if ($filter === 'all' || $filter === 'upcoming')  $groups[] = ['label'=>'Upcoming Stay',  'count'=>count($upcoming),  'items'=>$upcoming];
            if ($filter === 'all' || $filter === 'past')      $groups[] = ['label'=>'Past Stays',     'count'=>count($past),      'items'=>$past];
            if ($filter === 'all' || $filter === 'cancelled') $groups[] = ['label'=>'Cancelled',      'count'=>count($cancelled), 'items'=>$cancelled];
            foreach ($groups as $g):
                if (empty($g['items'])) continue; ?>
            <div class="section-header" style="margin-bottom:12px;">
                <h2><?= $g['label'] ?></h2>
                <span style="font-size:0.82rem;color:var(--muted);"><?= $g['count'] ?> booking<?= $g['count']!==1?'s':'' ?></span>
            </div>
            <?php foreach ($g['items'] as $b): ?>
            <div class="booking-card">
                <div class="booking-card-top">
                    <img class="booking-card-img" src="<?= htmlspecialchars($b['hostel_image']) ?>" alt="" onerror="this.style.display='none'">
                    <div class="booking-card-info">
                        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:8px;margin-bottom:4px;">
                            <span class="badge badge-<?= $b['status'] ?>"><?= ucfirst($b['status']) ?></span>
                            <span class="price"><?= CURRENCY ?><?= number_format($b['monthly_rent'], 0) ?>/mo</span>
                        </div>
                        <h4><?= htmlspecialchars($b['hostel_name']) ?></h4>
                        <p class="loc">📍 <?= htmlspecialchars($b['distance_from_campus']) ?></p>
                    </div>
                </div>
                <div class="booking-card-bottom">
                    <div>
                        <div class="checkin-label">Check-In</div>
                        <div class="checkin-date"><?= date('M d, Y', strtotime($b['move_in'])) ?></div>
                    </div>
                    <div class="card-actions">
                        <a href="hostel.php?id=<?= $b['hostel_id'] ?>" class="btn-sm btn-dark">Details</a>
                        <a href="https://www.google.com/maps/search/<?= urlencode($b['hostel_name'] . ' Accra Ghana') ?>" target="_blank" class="btn-sm btn-outline">🗺 Directions</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endforeach; ?>

            <?php if (empty($bookings)): ?>
            <div style="text-align:center;padding:60px 0;">
                <p style="color:var(--dim);">No bookings found.</p>
                <a href="home.php" class="btn btn-primary btn-auto" style="margin-top:16px;padding:14px 28px;">Find a Hostel</a>
            </div>
            <?php endif; ?>

            <div class="support-banner">
                <div class="icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--muted)" stroke-width="2"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg></div>
                <div><h4>Need help with a booking?</h4><p>Visit our support center for assistance</p></div>
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="var(--dim)" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            </div>
        </div>

        <!-- Right: summary panel -->
        <div>
            <div class="bookings-summary">
                <h3>Summary</h3>
                <div class="bs-row"><span>Total Bookings</span><strong><?= count($bookings) ?></strong></div>
                <div class="bs-row"><span>Upcoming</span><strong style="color:var(--green);"><?= count($upcoming) ?></strong></div>
                <div class="bs-row"><span>Past</span><strong style="color:var(--blue);"><?= count($past) ?></strong></div>
                <div class="bs-row"><span>Cancelled</span><strong style="color:var(--red);"><?= count($cancelled) ?></strong></div>
            </div>
            <a href="home.php" class="btn btn-primary" style="margin-top:16px;">+ New Booking</a>
        </div>
    </div>
</div>

<a href="home.php" class="fab mobile-only">+ New Booking</a>
<?php include 'includes/footer.php'; ?>
