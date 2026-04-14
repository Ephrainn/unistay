<?php
require_once '../config/db.php';
require_once 'auth.php';
requireAdmin();

// Update status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id'])) {
    $pdo->prepare("UPDATE bookings SET status=? WHERE id=?")
        ->execute([$_POST['status'], (int)$_POST['booking_id']]);
    header('Location: bookings.php?flash=updated'); exit;
}

$filter = $_GET['status'] ?? 'all';
$sql    = "SELECT b.*, u.name as user_name, u.email as user_email, h.name as hostel_name
           FROM bookings b
           JOIN users u ON b.user_id = u.id
           JOIN hostels h ON b.hostel_id = h.id";
if ($filter !== 'all') $sql .= " WHERE b.status = " . $pdo->quote($filter);
$sql .= " ORDER BY b.created_at DESC";
$bookings = $pdo->query($sql)->fetchAll();

$flash = isset($_GET['flash']) ? 'Booking updated.' : '';
$pageTitle = 'Bookings'; $activePage = 'bookings';
include 'layout.php';
?>

<?php if ($flash): ?><div class="flash flash-success"><?= $flash ?></div><?php endif; ?>

<div class="admin-toolbar">
    <div class="admin-search">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="text" id="searchInput" placeholder="Search student or hostel...">
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <?php foreach (['all','pending','confirmed','completed','cancelled'] as $s): ?>
        <a href="bookings.php?status=<?= $s ?>" class="chip <?= $filter===$s?'active':'' ?>"><?= ucfirst($s) ?></a>
        <?php endforeach; ?>
    </div>
</div>

<div class="admin-table-wrap">
    <div class="admin-table-header">
        <h2>All Bookings <span style="color:var(--muted);font-weight:400;font-size:0.82rem;">(<?= count($bookings) ?>)</span></h2>
    </div>
    <table id="bookingsTable">
        <thead><tr><th>Student</th><th>Hostel</th><th>Move-in</th><th>Move-out</th><th>Rent</th><th>Status</th><th>Action</th></tr></thead>
        <tbody>
        <?php foreach ($bookings as $b): ?>
        <tr>
            <td><div style="font-weight:500;"><?= htmlspecialchars($b['user_name']) ?></div><div style="font-size:0.72rem;color:var(--muted);"><?= htmlspecialchars($b['user_email']) ?></div></td>
            <td><?= htmlspecialchars($b['hostel_name']) ?></td>
            <td><?= date('M d, Y', strtotime($b['move_in'])) ?></td>
            <td><?= date('M d, Y', strtotime($b['move_out'])) ?></td>
            <td>$<?= number_format($b['monthly_rent'], 0) ?>/mo</td>
            <td><span class="badge badge-<?= $b['status'] ?>"><?= ucfirst($b['status']) ?></span></td>
            <td>
                <form method="POST" style="display:flex;gap:6px;align-items:center;">
                    <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                    <select name="status" class="form-control" style="padding:6px 10px;font-size:0.78rem;width:auto;">
                        <?php foreach (['pending','confirmed','completed','cancelled'] as $s): ?>
                        <option value="<?= $s ?>" <?= $b['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg></button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($bookings)): ?>
        <tr><td colspan="7" style="text-align:center;color:var(--dim);padding:28px;">No bookings found</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
document.getElementById('searchInput').addEventListener('input', function() {
    var q = this.value.toLowerCase();
    document.querySelectorAll('#bookingsTable tbody tr').forEach(function(row) {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
});
</script>

<?php include 'layout_end.php'; ?>
