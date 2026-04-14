<?php
require_once 'includes/auth.php';
require_once 'config/db.php';
requireLogin();

// Toggle wishlist via AJAX or redirect
$hostel_id = (int)($_POST['hostel_id'] ?? $_GET['hostel_id'] ?? 0);
if ($hostel_id) {
    $check = $pdo->prepare("SELECT id FROM wishlists WHERE user_id=? AND hostel_id=?");
    $check->execute([$_SESSION['user_id'], $hostel_id]);
    if ($check->fetch()) {
        $pdo->prepare("DELETE FROM wishlists WHERE user_id=? AND hostel_id=?")->execute([$_SESSION['user_id'], $hostel_id]);
        $status = 'removed';
    } else {
        $pdo->prepare("INSERT INTO wishlists (user_id, hostel_id) VALUES (?,?)")->execute([$_SESSION['user_id'], $hostel_id]);
        $status = 'added';
    }
    if (!empty($_POST['ajax'])) {
        header('Content-Type: application/json');
        echo json_encode(['status' => $status]);
        exit;
    }
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'home.php'));
    exit;
}

// Show wishlist page
$stmt = $pdo->prepare(
    "SELECT h.* FROM wishlists w JOIN hostels h ON w.hostel_id=h.id WHERE w.user_id=? ORDER BY w.created_at DESC"
);
$stmt->execute([$_SESSION['user_id']]);
$hostels = $stmt->fetchAll();

// Get user's wishlisted IDs for heart state
$wishIds = array_column($hostels, 'id');

$pageTitle = 'Saved Hostels – UniStay';
$showNav = true; $activePage = 'home';
include 'includes/header.php';
?>
<div class="page-wrap">
    <div class="page-header">
        <div><h1>Saved Hostels</h1><p><?= count($hostels) ?> saved</p></div>
        <a href="home.php" class="btn btn-outline btn-auto" style="padding:9px 16px;font-size:0.82rem;">Browse More</a>
    </div>

    <?php if (empty($hostels)): ?>
    <div style="text-align:center;padding:60px 0;">
        <div style="font-size:3rem;margin-bottom:16px;">❤️</div>
        <h3 style="color:var(--muted);font-weight:400;">No saved hostels yet</h3>
        <p style="margin-top:6px;">Tap the heart on any hostel to save it here.</p>
        <a href="home.php" class="btn btn-primary btn-auto" style="margin-top:20px;padding:12px 28px;">Find Hostels</a>
    </div>
    <?php else: ?>
    <div class="hostel-grid">
        <?php foreach ($hostels as $h):
            $amenities = json_decode($h['amenities'] ?? '[]', true); ?>
        <div style="position:relative;">
            <a href="hostel.php?id=<?= $h['id'] ?>">
                <div class="card">
                    <div style="height:190px;overflow:hidden;background:var(--border2);">
                        <img class="card-img" src="<?= htmlspecialchars($h['image']) ?>" alt="" onerror="this.style.display='none'">
                    </div>
                    <div class="card-body">
                        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:8px;">
                            <span class="card-title"><?= htmlspecialchars($h['name']) ?></span>
                            <span class="card-price"><?= CURRENCY ?><?= number_format($h['price_from'], 0) ?>/mo</span>
                        </div>
                        <div class="card-meta">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                            <?= htmlspecialchars($h['distance_from_campus']) ?>
                        </div>
                    </div>
                </div>
            </a>
            <button onclick="toggleWishlist(this, <?= $h['id'] ?>)"
                style="position:absolute;top:12px;right:12px;width:34px;height:34px;background:rgba(0,0,0,.5);border:none;border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;color:var(--red);">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="var(--red)" stroke="var(--red)" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg>
            </button>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
<script>
function toggleWishlist(btn, hostelId) {
    var fd = new FormData();
    fd.append('hostel_id', hostelId);
    fd.append('ajax', '1');
    fetch('wishlist.php', { method:'POST', body:fd })
        .then(r => r.json())
        .then(d => {
            if (d.status === 'removed') btn.closest('[style*="position:relative"]').remove();
        });
}
</script>
<?php include 'includes/footer.php'; ?>
