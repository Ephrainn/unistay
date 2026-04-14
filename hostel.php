<?php
require_once 'includes/auth.php';
require_once 'config/db.php';
requireLogin();

$id     = (int)($_GET['id'] ?? 0);
$stmt   = $pdo->prepare("SELECT * FROM hostels WHERE id = ?");
$stmt->execute([$id]);
$hostel = $stmt->fetch();
if (!$hostel) { header('Location: home.php'); exit; }

$rStmt = $pdo->prepare("SELECT * FROM rooms WHERE hostel_id = ?");
$rStmt->execute([$id]);
$rooms = $rStmt->fetchAll();

$amenities = json_decode($hostel['amenities'] ?? '[]', true);
$pageTitle = $hostel['name'] . ' – UniStay';
$showNav   = true; $activePage = 'home';

// Check if wishlisted
$wStmt = $pdo->prepare("SELECT id FROM wishlists WHERE user_id=? AND hostel_id=?");
$wStmt->execute([$_SESSION['user_id'], $id]);
$isWishlisted = (bool)$wStmt->fetch();

// Fetch reviews
$revStmt = $pdo->prepare("SELECT r.*, u.name as user_name FROM reviews r JOIN users u ON r.user_id=u.id WHERE r.hostel_id=? ORDER BY r.created_at DESC");
$revStmt->execute([$id]);
$reviews = $revStmt->fetchAll();
$avgRating = count($reviews) ? round(array_sum(array_column($reviews, 'rating')) / count($reviews), 1) : $hostel['rating'];

// Check if user can review (has completed booking)
$canReview = false;
$existingReview = null;
$revBooking = $pdo->prepare("SELECT id FROM bookings WHERE user_id=? AND hostel_id=? AND status='completed' LIMIT 1");
$revBooking->execute([$_SESSION['user_id'], $id]);
$revBookingRow = $revBooking->fetch();
if ($revBookingRow) {
    $canReview = true;
    $erStmt = $pdo->prepare("SELECT * FROM reviews WHERE user_id=? AND hostel_id=?");
    $erStmt->execute([$_SESSION['user_id'], $id]);
    $existingReview = $erStmt->fetch();
}

// Submit review
$reviewMsg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review_rating'])) {
    $rating  = max(1, min(5, (int)$_POST['review_rating']));
    $comment = trim($_POST['review_comment'] ?? '');
    if ($existingReview) {
        $pdo->prepare("UPDATE reviews SET rating=?,comment=? WHERE id=?")->execute([$rating, $comment, $existingReview['id']]);
    } else {
        $pdo->prepare("INSERT INTO reviews (user_id,hostel_id,booking_id,rating,comment) VALUES (?,?,?,?,?)")
            ->execute([$_SESSION['user_id'], $id, $revBookingRow['id'], $rating, $comment]);
        // Update hostel rating
        $pdo->prepare("UPDATE hostels SET rating=(SELECT AVG(rating) FROM reviews WHERE hostel_id=?) WHERE id=?")->execute([$id, $id]);
    }
    header("Location: hostel.php?id=$id&reviewed=1"); exit;
}

include 'includes/header.php';
?>

<div class="page-wrap" style="padding-left:0;padding-right:0;padding-top:0;">

    <!-- Hero image -->
    <div class="hostel-hero">
        <img src="<?= htmlspecialchars($hostel['image']) ?>" alt="" onerror="this.style.display='none'">
        <a href="home.php" class="hero-back">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
        </a>
        <div class="hero-actions">
            <button onclick="toggleWishlist(<?= $id ?>)" id="wishBtn" style="background:rgba(0,0,0,.6);border:none;width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="<?= $isWishlisted ? 'var(--red)' : 'none' ?>" stroke="<?= $isWishlisted ? 'var(--red)' : '#fff' ?>" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg>
            </button>
            <button onclick="shareHostel('<?= htmlspecialchars(addslashes($hostel['name'])) ?>')" style="background:rgba(0,0,0,.6);border:none;width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px;height:18px;color:#fff;"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg></button>
        </div>
        <?php if ($hostel['is_verified']): ?><div class="hero-badge">✓ Verified Property</div><?php endif; ?>
        <div class="hero-count">1/8 Photos</div>
    </div>

    <!-- Content -->
    <div class="page-wrap">
        <div class="two-col wide-side">
            <!-- Left: details -->
            <div>
                <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:6px;">
                    <h1 style="font-size:1.4rem;"><?= htmlspecialchars($hostel['name']) ?></h1>
                    <span style="font-weight:600;white-space:nowrap;">⭐ <?= $hostel['rating'] ?></span>
                </div>
                <div class="hostel-meta">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    <?= htmlspecialchars($hostel['distance_from_campus']) ?>
                </div>

                <h3 style="margin-bottom:10px;">Amenities</h3>
                <div class="amenity-pills">
                    <?php foreach ($amenities as $a): ?>
                    <span class="amenity-pill">✓ <?= htmlspecialchars($a) ?></span>
                    <?php endforeach; ?>
                </div>

                <h3 style="margin-bottom:8px;">About this Hostel</h3>
                <p style="line-height:1.7;margin-bottom:20px;"><?= htmlspecialchars($hostel['description']) ?></p>

                <h3 style="margin-bottom:12px;">Available Rooms</h3>
                <?php foreach ($rooms as $room): ?>
                <div class="room-card">
                    <div class="room-thumb" style="background:var(--border2);"></div>
                    <div class="room-info">
                        <h4><?= htmlspecialchars($room['type']) ?></h4>
                        <?php if ($room['available_count'] === 0): ?>
                        <p style="color:var(--red);">Waitlist available</p>
                        <?php else: ?>
                        <p><?= $room['available_count'] ?> room<?= $room['available_count'] > 1 ? 's' : '' ?> left</p>
                        <?php endif; ?>
                    </div>
                    <div class="room-price">
                        <strong><?= CURRENCY ?><?= number_format($room['price'], 0) ?>/mo</strong>
                        <span><?= htmlspecialchars($room['description']) ?></span>
                    </div>
                </div>
                <?php endforeach; ?>

                <h3 style="margin:20px 0 10px;">Location</h3>
                <div class="map-box">🗺 <?= htmlspecialchars($hostel['address']) ?></div>

                <!-- Reviews -->
                <h3 style="margin:24px 0 14px;">Reviews <?php if (count($reviews)): ?><span style="font-size:0.85rem;color:var(--muted);font-weight:400;">(<?= count($reviews) ?>)</span><?php endif; ?></h3>

                <?php if (!empty($_GET['reviewed'])): ?>
                <div class="alert alert-success" style="margin-bottom:16px;">Review submitted, thank you!</div>
                <?php endif; ?>

                <?php if ($canReview): ?>
                <div style="background:var(--card);border-radius:14px;padding:18px;margin-bottom:20px;border:1px solid var(--border);">
                    <h4 style="margin-bottom:12px;"><?= $existingReview ? 'Edit Your Review' : 'Leave a Review' ?></h4>
                    <form method="POST">
                        <div style="display:flex;gap:6px;margin-bottom:12px;" id="starRow">
                            <?php for ($s=1;$s<=5;$s++): ?>
                            <button type="button" onclick="setRating(<?= $s ?>)" class="star-btn" data-val="<?= $s ?>"
                                style="background:none;border:none;cursor:pointer;font-size:1.4rem;color:<?= ($existingReview && $existingReview['rating'] >= $s) ? 'var(--yellow)' : 'var(--dim)' ?>;">★</button>
                            <?php endfor; ?>
                        </div>
                        <input type="hidden" name="review_rating" id="review_rating" value="<?= $existingReview['rating'] ?? 0 ?>">
                        <div class="form-group">
                            <textarea name="review_comment" class="form-control" rows="3" placeholder="Share your experience..."><?= htmlspecialchars($existingReview['comment'] ?? '') ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-auto" style="padding:10px 24px;">Submit Review</button>
                    </form>
                </div>
                <?php endif; ?>

                <?php foreach ($reviews as $rev): ?>
                <div style="background:var(--card);border-radius:12px;padding:16px;margin-bottom:10px;border:1px solid var(--border);">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:8px;">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div class="avatar" style="width:34px;height:34px;font-size:0.72rem;"><?= strtoupper(substr($rev['user_name'],0,1)) ?></div>
                            <div>
                                <div style="font-size:0.875rem;font-weight:600;color:var(--text);"><?= htmlspecialchars($rev['user_name']) ?></div>
                                <div style="font-size:0.7rem;color:var(--dim);"><?= date('M d, Y', strtotime($rev['created_at'])) ?></div>
                            </div>
                        </div>
                        <div style="color:var(--yellow);font-size:0.9rem;"><?= str_repeat('★', $rev['rating']) ?><span style="color:var(--dim);"><?= str_repeat('★', 5 - $rev['rating']) ?></span></div>
                    </div>
                    <?php if ($rev['comment']): ?><p style="font-size:0.82rem;line-height:1.6;"><?= htmlspecialchars($rev['comment']) ?></p><?php endif; ?>
                </div>
                <?php endforeach; ?>
                <?php if (empty($reviews)): ?><p style="color:var(--dim);font-size:0.85rem;">No reviews yet. Be the first!</p><?php endif; ?>
            </div>

            <!-- Right: sticky booking card (desktop) -->
            <div>
                <div class="hostel-sticky-card">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                        <div>
                            <strong style="font-size:1.3rem;color:var(--text);">From <?= CURRENCY ?><?= number_format($hostel['price_from'], 0) ?></strong>
                            <span style="display:block;font-size:0.72rem;color:var(--muted);">per month</span>
                        </div>
                        <div style="display:flex;gap:8px;">
                            <button style="width:34px;height:34px;background:var(--border2);border-radius:50%;border:none;display:flex;align-items:center;justify-content:center;cursor:pointer;"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg></button>
                        </div>
                    </div>
                    <h3>Available Rooms</h3>
                    <?php foreach ($rooms as $room): ?>
                    <div class="room-card" style="margin-bottom:8px;">
                        <div class="room-info">
                            <h4><?= htmlspecialchars($room['type']) ?></h4>
                            <?php if ($room['available_count'] === 0): ?>
                            <p style="color:var(--red);">Waitlist</p>
                            <?php else: ?><p><?= $room['available_count'] ?> left</p><?php endif; ?>
                        </div>
                        <div class="room-price">
                        <strong><?= CURRENCY ?><?= number_format($room['price'], 0) ?>/mo</strong>
                            <span><?= htmlspecialchars($room['description']) ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <a href="booking.php?hostel_id=<?= $hostel['id'] ?>" class="btn btn-primary" style="margin-top:16px;">Book Now</a>
                </div>
            </div>
        </div>
    </div><!-- .page-wrap -->

    <!-- Mobile sticky footer -->
    <div class="sticky-footer mobile-only">
        <div class="sticky-footer-price">
            <strong>From <?= CURRENCY ?><?= number_format($hostel['price_from'], 0) ?></strong>
            <span>per month</span>
        </div>
        <a href="booking.php?hostel_id=<?= $hostel['id'] ?>" class="btn btn-primary btn-auto">Book Now</a>
    </div>
</div>

<script>
function toggleWishlist(hostelId) {
    var btn = document.getElementById('wishBtn');
    var svg = btn.querySelector('svg');
    var fd = new FormData(); fd.append('hostel_id', hostelId); fd.append('ajax','1');
    fetch('wishlist.php', {method:'POST',body:fd}).then(r=>r.json()).then(d=>{
        var on = d.status === 'added';
        svg.setAttribute('fill', on ? 'var(--red)' : 'none');
        svg.setAttribute('stroke', on ? 'var(--red)' : '#fff');
    });
}
function setRating(val) {
    document.getElementById('review_rating').value = val;
    document.querySelectorAll('.star-btn').forEach(function(b){
        b.style.color = parseInt(b.dataset.val) <= val ? 'var(--yellow)' : 'var(--dim)';
    });
}
function shareHostel(name) {
    if (navigator.share) {
        navigator.share({ title: name, text: 'Check out ' + name + ' on UniStay', url: window.location.href });
    } else {
        navigator.clipboard.writeText(window.location.href).then(function(){
            alert('Link copied to clipboard!');
        });
    }
}
</script>
<?php include 'includes/footer.php'; ?>
