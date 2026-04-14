<?php
require_once '../config/db.php';
require_once 'auth.php';
requireAdmin();

$flash = '';
$hostel_id = (int)($_GET['hostel_id'] ?? 0);

// Delete room
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM rooms WHERE id=?")->execute([(int)$_GET['delete']]);
    header("Location: rooms.php?hostel_id=$hostel_id&flash=deleted"); exit;
}

// Add / Edit room
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id      = (int)($_POST['id'] ?? 0);
    $hid     = (int)($_POST['hostel_id'] ?? 0);
    $type    = trim($_POST['type'] ?? '');
    $price   = (float)($_POST['price'] ?? 0);
    $desc    = trim($_POST['description'] ?? '');
    $avail   = (int)($_POST['available_count'] ?? 1);
    if ($id) {
        $pdo->prepare("UPDATE rooms SET type=?,price=?,description=?,available_count=? WHERE id=?")
            ->execute([$type,$price,$desc,$avail,$id]);
    } else {
        $pdo->prepare("INSERT INTO rooms (hostel_id,type,price,description,available_count) VALUES (?,?,?,?,?)")
            ->execute([$hid,$type,$price,$desc,$avail]);
        // Update hostel price_from
        $pdo->prepare("UPDATE hostels SET price_from=(SELECT MIN(price) FROM rooms WHERE hostel_id=?) WHERE id=?")->execute([$hid,$hid]);
    }
    header("Location: rooms.php?hostel_id=$hid&flash=saved"); exit;
}

$edit = null;
if (isset($_GET['edit'])) {
    $s = $pdo->prepare("SELECT * FROM rooms WHERE id=?");
    $s->execute([(int)$_GET['edit']]);
    $edit = $s->fetch();
    if ($edit) $hostel_id = $edit['hostel_id'];
}

$hostel = null;
if ($hostel_id) {
    $s = $pdo->prepare("SELECT * FROM hostels WHERE id=?");
    $s->execute([$hostel_id]);
    $hostel = $s->fetch();
}

$rooms = $hostel_id
    ? $pdo->prepare("SELECT * FROM rooms WHERE hostel_id=? ORDER BY price ASC")
    : $pdo->query("SELECT r.*, h.name as hostel_name FROM rooms r JOIN hostels h ON r.hostel_id=h.id ORDER BY h.name, r.price");
if ($hostel_id) $rooms->execute([$hostel_id]);
$rooms = $rooms->fetchAll();

if (isset($_GET['flash'])) $flash = $_GET['flash'] === 'saved' ? 'Room saved.' : 'Room deleted.';
$pageTitle = 'Rooms'; $activePage = 'hostels';
include 'layout.php';
?>

<?php if ($flash): ?><div class="flash flash-success"><?= htmlspecialchars($flash) ?></div><?php endif; ?>

<div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
    <a href="hostels.php" style="font-size:0.82rem;color:var(--muted);">← Hostels</a>
    <?php if ($hostel): ?>
    <span style="color:var(--dim);">/</span>
    <span style="font-size:0.875rem;font-weight:600;"><?= htmlspecialchars($hostel['name']) ?></span>
    <?php endif; ?>
</div>

<div style="display:grid;grid-template-columns:1fr 340px;gap:24px;align-items:start;">
    <div class="admin-table-wrap">
        <div class="admin-table-header">
            <h2>Rooms <?php if ($hostel): ?>— <?= htmlspecialchars($hostel['name']) ?><?php endif; ?></h2>
        </div>
        <table>
            <thead><tr><?php if (!$hostel_id): ?><th>Hostel</th><?php endif; ?><th>Type</th><th>Price/mo</th><th>Available</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($rooms as $r): ?>
            <tr>
                <?php if (!$hostel_id): ?><td><?= htmlspecialchars($r['hostel_name'] ?? '') ?></td><?php endif; ?>
                <td style="font-weight:500;"><?= htmlspecialchars($r['type']) ?></td>
                <td><?= CURRENCY ?><?= number_format($r['price'], 0) ?></td>
                <td>
                    <?php if ($r['available_count'] === 0): ?>
                    <span class="badge badge-cancelled">Waitlist</span>
                    <?php else: ?>
                    <span class="badge badge-confirmed"><?= $r['available_count'] ?> left</span>
                    <?php endif; ?>
                </td>
                <td>
                    <div style="display:flex;gap:6px;">
                        <a href="rooms.php?edit=<?= $r['id'] ?>&hostel_id=<?= $r['hostel_id'] ?>" class="btn-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></a>
                        <a href="rooms.php?delete=<?= $r['id'] ?>&hostel_id=<?= $r['hostel_id'] ?>" class="btn-icon danger" onclick="return confirm('Delete this room?')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/></svg></a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($rooms)): ?><tr><td colspan="5" style="text-align:center;color:var(--dim);padding:24px;">No rooms yet</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="admin-form-card">
        <h2><?= $edit ? 'Edit Room' : 'Add Room' ?></h2>
        <form method="POST">
            <?php if ($edit): ?><input type="hidden" name="id" value="<?= $edit['id'] ?>"><?php endif; ?>
            <div class="form-group">
                <label>Hostel</label>
                <select name="hostel_id" class="form-control" required>
                    <?php foreach ($pdo->query("SELECT id,name FROM hostels ORDER BY name") as $h): ?>
                    <option value="<?= $h['id'] ?>" <?= ($edit ? $edit['hostel_id'] : $hostel_id) == $h['id'] ? 'selected' : '' ?>><?= htmlspecialchars($h['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group"><label>Room Type</label><input type="text" name="type" class="form-control" placeholder="e.g. Premium Single" required value="<?= htmlspecialchars($edit['type'] ?? '') ?>"></div>
            <div class="form-row">
                <div class="form-group"><label>Price (<?= CURRENCY ?>/mo)</label><input type="number" name="price" class="form-control" step="0.01" required value="<?= $edit['price'] ?? '' ?>"></div>
                <div class="form-group"><label>Available Count</label><input type="number" name="available_count" class="form-control" min="0" required value="<?= $edit['available_count'] ?? 1 ?>"></div>
            </div>
            <div class="form-group"><label>Description</label><input type="text" name="description" class="form-control" placeholder="e.g. Private Kitchen · Utilities Incl." value="<?= htmlspecialchars($edit['description'] ?? '') ?>"></div>
            <button type="submit" class="btn btn-primary"><?= $edit ? 'Save Changes' : 'Add Room' ?></button>
            <?php if ($edit): ?><a href="rooms.php?hostel_id=<?= $edit['hostel_id'] ?>" class="btn btn-outline" style="margin-top:10px;">Cancel</a><?php endif; ?>
        </form>
    </div>
</div>

<?php include 'layout_end.php'; ?>
