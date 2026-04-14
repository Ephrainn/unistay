<?php
require_once '../config/db.php';
require_once 'auth.php';
requireAdmin();

// Update ticket status
if (isset($_POST['update_ticket'], $_POST['ticket_id'])) {
    $pdo->prepare("UPDATE support_tickets SET status=? WHERE id=?")
        ->execute([$_POST['ticket_status'], (int)$_POST['ticket_id']]);
    header('Location: users.php?flash=updated'); exit;
}

// Toggle verification
if (isset($_GET['verify'])) {
    $s = $pdo->prepare("SELECT is_verified FROM users WHERE id=?");
    $s->execute([(int)$_GET['verify']]);
    $cur = $s->fetchColumn();
    $pdo->prepare("UPDATE users SET is_verified=? WHERE id=?")->execute([$cur ? 0 : 1, (int)$_GET['verify']]);
    header('Location: users.php?flash=updated'); exit;
}

// Delete
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM users WHERE id=?")->execute([(int)$_GET['delete']]);
    header('Location: users.php?flash=deleted'); exit;
}

$search = trim($_GET['q'] ?? '');
$sql    = "SELECT u.*, (SELECT COUNT(*) FROM bookings WHERE user_id=u.id) as booking_count FROM users u WHERE 1=1";
$params = [];
if ($search) { $sql .= " AND (u.name LIKE ? OR u.email LIKE ? OR u.university LIKE ?)"; $params = ["%$search%","%$search%","%$search%"]; }
$sql .= " ORDER BY u.created_at DESC";
$stmt = $pdo->prepare($sql); $stmt->execute($params);
$users = $stmt->fetchAll();

$flash = isset($_GET['flash']) ? ($_GET['flash']==='deleted' ? 'User deleted.' : 'User updated.') : '';
$pageTitle = 'Users'; $activePage = 'users';
include 'layout.php';
?>

<?php if ($flash): ?><div class="flash flash-success"><?= htmlspecialchars($flash) ?></div><?php endif; ?>

<div class="admin-toolbar">
    <form method="GET" style="flex:1;display:flex;gap:12px;flex-wrap:wrap;">
        <div class="admin-search">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" name="q" placeholder="Search name, email or university..." value="<?= htmlspecialchars($search) ?>">
        </div>
        <button type="submit" class="btn btn-dark btn-auto" style="padding:9px 18px;border-radius:10px;font-size:0.875rem;">Search</button>
    </form>
</div>

<div class="admin-table-wrap">
    <div class="admin-table-header">
        <h2>All Students <span style="color:var(--muted);font-weight:400;font-size:0.82rem;">(<?= count($users) ?>)</span></h2>
    </div>
    <table>
        <thead><tr><th>Student</th><th>University</th><th>Bookings</th><th>Joined</th><th>Verified</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($users as $u): ?>
        <tr>
            <td>
                <div style="display:flex;align-items:center;gap:10px;">
                    <div class="avatar" style="width:32px;height:32px;font-size:0.72rem;flex-shrink:0;">
                        <?= strtoupper(substr($u['name'],0,1)) ?>
                    </div>
                    <div>
                        <div style="font-weight:500;"><?= htmlspecialchars($u['name']) ?></div>
                        <div style="font-size:0.72rem;color:var(--muted);"><?= htmlspecialchars($u['email']) ?></div>
                    </div>
                </div>
            </td>
            <td><?= htmlspecialchars($u['university'] ?? '—') ?></td>
            <td><?= $u['booking_count'] ?></td>
            <td><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
            <td><?= $u['is_verified'] ? '<span class="badge badge-confirmed">Verified</span>' : '<span class="badge badge-pending">Pending</span>' ?></td>
            <td>
                <div style="display:flex;gap:6px;">
                    <a href="users.php?verify=<?= $u['id'] ?>" class="btn-icon" title="<?= $u['is_verified']?'Unverify':'Verify' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    </a>
                    <a href="users.php?delete=<?= $u['id'] ?>" class="btn-icon danger" onclick="return confirm('Delete this user?')">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/></svg>
                    </a>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($users)): ?>
        <tr><td colspan="6" style="text-align:center;color:var(--dim);padding:28px;">No users found</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
// Support tickets section
$tickets = $pdo->query(
    "SELECT t.*, u.name as user_name, u.email as user_email
     FROM support_tickets t JOIN users u ON t.user_id=u.id
     ORDER BY t.created_at DESC LIMIT 20"
)->fetchAll();
?>
<div class="admin-table-wrap" style="margin-top:28px;">
    <div class="admin-table-header">
        <h2>Support Tickets</h2>
        <span style="font-size:0.82rem;color:var(--muted);"><?= count($tickets) ?> recent</span>
    </div>
    <table>
        <thead><tr><th>Student</th><th>Subject</th><th>Status</th><th>Date</th><th>Action</th></tr></thead>
        <tbody>
        <?php foreach ($tickets as $t): ?>
        <tr>
            <td>
                <div style="font-weight:500;"><?= htmlspecialchars($t['user_name']) ?></div>
                <div style="font-size:0.72rem;color:var(--muted);"><?= htmlspecialchars($t['user_email']) ?></div>
            </td>
            <td>
                <div><?= htmlspecialchars($t['subject']) ?></div>
                <div style="font-size:0.72rem;color:var(--muted);margin-top:2px;"><?= htmlspecialchars(substr($t['message'],0,60)) ?>...</div>
            </td>
            <td>
                <span class="badge badge-<?= $t['status']==='open'?'pending':($t['status']==='in_progress'?'confirmed':'completed') ?>">
                    <?= ucfirst(str_replace('_',' ',$t['status'])) ?>
                </span>
            </td>
            <td><?= date('M d, Y', strtotime($t['created_at'])) ?></td>
            <td>
                <form method="POST" style="display:flex;gap:6px;">
                    <input type="hidden" name="ticket_id" value="<?= $t['id'] ?>">
                    <select name="ticket_status" class="form-control" style="padding:5px 8px;font-size:0.78rem;width:auto;">
                        <?php foreach (['open','in_progress','closed'] as $s): ?>
                        <option value="<?= $s ?>" <?= $t['status']===$s?'selected':'' ?>><?= ucfirst(str_replace('_',' ',$s)) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" name="update_ticket" class="btn-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg></button>
                    <a href="ticket-reply.php?id=<?= $t['id'] ?>" class="btn-icon" title="Reply"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg></a>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($tickets)): ?>
        <tr><td colspan="5" style="text-align:center;color:var(--dim);padding:24px;">No support tickets yet</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php include 'layout_end.php'; ?>
