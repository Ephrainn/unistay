<?php
require_once '../config/db.php';
require_once 'auth.php';
requireAdmin();

$flash = '';

// Delete
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM hostels WHERE id=?")->execute([(int)$_GET['delete']]);
    header('Location: hostels.php?flash=deleted'); exit;
}

// Add / Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id          = (int)($_POST['id'] ?? 0);
    $name        = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $address     = trim($_POST['address'] ?? '');
    $distance    = trim($_POST['distance_from_campus'] ?? '');
    $rating      = (float)($_POST['rating'] ?? 0);
    $price_from  = (float)($_POST['price_from'] ?? 0);
    $amenities   = json_encode(array_filter(array_map('trim', explode(',', $_POST['amenities'] ?? ''))));

    if ($id) {
        $pdo->prepare("UPDATE hostels SET name=?,description=?,address=?,distance_from_campus=?,rating=?,price_from=?,amenities=? WHERE id=?")
            ->execute([$name,$description,$address,$distance,$rating,$price_from,$amenities,$id]);
    } else {
        $pdo->prepare("INSERT INTO hostels (name,description,address,distance_from_campus,rating,price_from,amenities) VALUES (?,?,?,?,?,?,?)")
            ->execute([$name,$description,$address,$distance,$rating,$price_from,$amenities]);
    }
    header('Location: hostels.php?flash=saved'); exit;
}

$edit = null;
if (isset($_GET['edit'])) {
    $s = $pdo->prepare("SELECT * FROM hostels WHERE id=?");
    $s->execute([(int)$_GET['edit']]);
    $edit = $s->fetch();
}

$hostels = $pdo->query("SELECT * FROM hostels ORDER BY id DESC")->fetchAll();
if (isset($_GET['flash'])) $flash = $_GET['flash'] === 'saved' ? 'Hostel saved.' : 'Hostel deleted.';

$pageTitle = 'Hostels'; $activePage = 'hostels';
include 'layout.php';
?>

<?php if ($flash): ?>
<div class="flash flash-success"><?= htmlspecialchars($flash) ?></div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 360px;gap:24px;align-items:start;">
    <div class="admin-table-wrap">
        <div class="admin-table-header">
            <h2>All Hostels</h2>
            <a href="hostels.php" class="btn btn-primary btn-auto" style="padding:8px 16px;font-size:0.82rem;border-radius:8px;">+ Add New</a>
        </div>
        <table>
            <thead><tr><th>Name</th><th>Distance</th><th>From</th><th>Rating</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($hostels as $h): ?>
            <tr>
                <td><div style="font-weight:500;"><?= htmlspecialchars($h['name']) ?></div><div style="font-size:0.72rem;color:var(--muted);"><?= htmlspecialchars($h['address']) ?></div></td>
                <td><?= htmlspecialchars($h['distance_from_campus']) ?></td>
                <td>$<?= number_format($h['price_from'], 0) ?>/mo</td>
                <td>⭐ <?= $h['rating'] ?></td>
                <td>
                    <div style="display:flex;gap:6px;">
                        <a href="rooms.php?hostel_id=<?= $h['id'] ?>" class="btn-icon" title="Manage Rooms"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg></a>
                        <a href="hostels.php?edit=<?= $h['id'] ?>" class="btn-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></a>
                        <a href="hostels.php?delete=<?= $h['id'] ?>" class="btn-icon danger" onclick="return confirm('Delete this hostel?')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/></svg></a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Add / Edit form -->
    <div class="admin-form-card">
        <h2><?= $edit ? 'Edit Hostel' : 'Add Hostel' ?></h2>
        <form method="POST">
            <?php if ($edit): ?><input type="hidden" name="id" value="<?= $edit['id'] ?>"><?php endif; ?>
            <div class="form-group"><label>Name</label><input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($edit['name'] ?? '') ?>"></div>
            <div class="form-group"><label>Address</label><input type="text" name="address" class="form-control" value="<?= htmlspecialchars($edit['address'] ?? '') ?>"></div>
            <div class="form-group"><label>Distance from Campus</label><input type="text" name="distance_from_campus" class="form-control" placeholder="e.g. 2 mins walk" value="<?= htmlspecialchars($edit['distance_from_campus'] ?? '') ?>"></div>
            <div class="form-row">
                <div class="form-group"><label>Price From ($/mo)</label><input type="number" name="price_from" class="form-control" step="0.01" value="<?= $edit['price_from'] ?? '' ?>"></div>
                <div class="form-group"><label>Rating</label><input type="number" name="rating" class="form-control" step="0.1" min="0" max="5" value="<?= $edit['rating'] ?? '' ?>"></div>
            </div>
            <div class="form-group"><label>Amenities <small style="color:var(--muted);">(comma separated)</small></label>
                <input type="text" name="amenities" class="form-control" placeholder="WiFi, Laundry, 24/7 Security" value="<?= htmlspecialchars(implode(', ', json_decode($edit['amenities'] ?? '[]', true))) ?>">
            </div>
            <div class="form-group"><label>Description</label><textarea name="description" class="form-control" rows="3" style="resize:vertical;"><?= htmlspecialchars($edit['description'] ?? '') ?></textarea></div>
            <button type="submit" class="btn btn-primary"><?= $edit ? 'Save Changes' : 'Add Hostel' ?></button>
            <?php if ($edit): ?><a href="hostels.php" class="btn btn-outline" style="margin-top:10px;">Cancel</a><?php endif; ?>
        </form>
    </div>
</div>

<?php include 'layout_end.php'; ?>
