<?php
require_once 'includes/auth.php';
require_once 'config/db.php';
requireLogin();
$user = currentUser($pdo);
if (!$user) { session_destroy(); header('Location: login.php'); exit; }

$search  = trim($_GET['q'] ?? '');
$filter  = $_GET['filter'] ?? 'all';
$minPrice = (int)($_GET['min'] ?? 0);
$maxPrice = (int)($_GET['max'] ?? 0);

$sql    = "SELECT * FROM hostels WHERE 1=1";
$params = [];
if ($search) {
    $sql   .= " AND (name LIKE ? OR address LIKE ? OR distance_from_campus LIKE ?)";
    $params = array_merge($params, ["%$search%", "%$search%", "%$search%"]);
}
if ($minPrice > 0) { $sql .= " AND price_from >= ?"; $params[] = $minPrice; }
if ($maxPrice > 0) { $sql .= " AND price_from <= ?"; $params[] = $maxPrice; }
if ($filter === 'campus') { $sql .= " AND (distance_from_campus LIKE '%min%' OR distance_from_campus LIKE '%walk%')"; }
$sql .= " ORDER BY rating DESC";

$stmt    = $pdo->prepare($sql);
$stmt->execute($params);
$hostels = $stmt->fetchAll();

// Get user's wishlist IDs
$wStmt = $pdo->prepare("SELECT hostel_id FROM wishlists WHERE user_id=?");
$wStmt->execute([$_SESSION['user_id']]);
$wishIds = array_column($wStmt->fetchAll(), 'hostel_id');

$initials  = strtoupper(implode('', array_map(fn($w) => $w[0], explode(' ', $user['name']))));
$pageTitle = 'Home – UniStay';
$showNav   = true; $activePage = 'home';
include 'includes/header.php';
?>

<div class="page-wrap">
    <div class="page-header">
        <div><h1>Find your home</h1><p>Near your university</p></div>
        <a href="profile.php" class="mobile-only"><div class="avatar"><?= htmlspecialchars($initials) ?></div></a>
    </div>

    <form method="GET" action="home.php">
        <div class="search-bar">
            <div class="search-input">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="var(--dim)" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" name="q" placeholder="Search by university or area..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <button type="submit" class="search-submit-btn">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <span>Search</span>
            </button>
        </div>
        <!-- Price filter -->
        <div style="display:flex;gap:10px;margin-bottom:14px;flex-wrap:wrap;">
            <div style="display:flex;align-items:center;gap:6px;background:var(--card);border-radius:10px;padding:8px 12px;font-size:0.82rem;">
                <span style="color:var(--muted);">Min GH₵</span>
                <input type="number" name="min" value="<?= $minPrice ?: '' ?>" placeholder="0" style="width:60px;background:none;border:none;outline:none;color:var(--text);font-size:0.82rem;font-family:inherit;">
            </div>
            <div style="display:flex;align-items:center;gap:6px;background:var(--card);border-radius:10px;padding:8px 12px;font-size:0.82rem;">
                <span style="color:var(--muted);">Max GH₵</span>
                <input type="number" name="max" value="<?= $maxPrice ?: '' ?>" placeholder="Any" style="width:60px;background:none;border:none;outline:none;color:var(--text);font-size:0.82rem;font-family:inherit;">
            </div>
            <?php if ($search || $minPrice || $maxPrice || $filter !== 'all'): ?>
            <a href="home.php" style="display:flex;align-items:center;gap:4px;background:var(--red-bg);color:var(--red);border-radius:10px;padding:8px 12px;font-size:0.82rem;">✕ Clear</a>
            <?php endif; ?>
        </div>
    </form>

    <div class="chip-row">
        <a href="home.php?q=<?= urlencode($search) ?>" class="chip <?= $filter==='all'?'active':'' ?>">All</a>
        <a href="home.php?q=<?= urlencode($search) ?>&filter=campus" class="chip <?= $filter==='campus'?'active':'' ?>">📍 Near Campus</a>
        <a href="wishlist.php" class="chip">❤️ Saved</a>
    </div>

    <div class="section-header">
        <h2>Recommended for you</h2>
        <a href="home.php">See all</a>
    </div>

    <div class="hostel-grid">
        <?php foreach ($hostels as $h):
            $amenities = json_decode($h['amenities'] ?? '[]', true);
            $wished = in_array($h['id'], $wishIds); ?>
        <div style="position:relative;">
            <a href="hostel.php?id=<?= $h['id'] ?>">
                <div class="card">
                    <div style="height:190px;overflow:hidden;background:var(--border2);">
                        <img class="card-img" src="<?= htmlspecialchars($h['image']) ?>" alt="<?= htmlspecialchars($h['name']) ?>" onerror="this.style.display='none'">
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
                        <div class="card-tags">
                            <?php foreach (array_slice($amenities, 0, 3) as $a): ?>
                            <span class="card-tag">• <?= htmlspecialchars($a) ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </a>
            <button onclick="toggleWish(this,<?= $h['id'] ?>)"
                style="position:absolute;top:12px;right:12px;width:34px;height:34px;background:rgba(0,0,0,.5);border:none;border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="<?= $wished?'var(--red)':'none' ?>" stroke="<?= $wished?'var(--red)':'#fff' ?>" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg>
            </button>
        </div>
        <?php endforeach; ?>

        <!-- Map card -->
        <div class="card">
            <div style="height:190px;background:linear-gradient(135deg,#e9e4ff,#d8d0ff);display:flex;align-items:center;justify-content:center;font-size:2.5rem;">🗺</div>
            <div class="card-body">
                <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;">
                    <div>
                        <div class="card-title">Explore on Map</div>
                        <p style="margin-top:2px;"><?= count($hostels) ?> hostels available</p>
                    </div>
                    <a href="https://www.google.com/maps/search/student+hostels+near+GCTU+Tesano+Accra" target="_blank" class="btn btn-dark btn-auto" style="padding:10px 16px;font-size:0.8rem;border-radius:10px;">Open</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleWish(btn, hostelId) {
    var svg = btn.querySelector('svg');
    var fd = new FormData(); fd.append('hostel_id', hostelId); fd.append('ajax','1');
    fetch('wishlist.php', {method:'POST',body:fd}).then(r=>r.json()).then(d=>{
        var on = d.status === 'added';
        svg.setAttribute('fill', on ? 'var(--red)' : 'none');
        svg.setAttribute('stroke', on ? 'var(--red)' : '#fff');
    });
}
</script>
<?php include 'includes/footer.php'; ?>
