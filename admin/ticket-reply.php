<?php
require_once '../config/db.php';
require_once 'auth.php';
requireAdmin();

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT t.*, u.name as user_name, u.email as user_email FROM support_tickets t JOIN users u ON t.user_id=u.id WHERE t.id=?");
$stmt->execute([$id]);
$ticket = $stmt->fetch();
if (!$ticket) { header('Location: users.php'); exit; }

$flash = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reply  = trim($_POST['reply'] ?? '');
    $status = $_POST['status'] ?? $ticket['status'];
    if ($reply) {
        // Send notification to user
        $pdo->prepare("INSERT INTO notifications (user_id,title,message,type,link) VALUES (?,?,?,'system','notifications.php')")
            ->execute([$ticket['user_id'], 'Support Reply: ' . $ticket['subject'], $reply]);
        $pdo->prepare("UPDATE support_tickets SET status=? WHERE id=?")->execute([$status, $id]);
        $flash = 'Reply sent and user notified.';
        $ticket['status'] = $status;
    }
}

$pageTitle = 'Ticket #' . $id; $activePage = 'users';
include 'layout.php';
?>
<?php if ($flash): ?><div class="flash flash-success"><?= htmlspecialchars($flash) ?></div><?php endif; ?>

<div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
    <a href="users.php" style="font-size:0.82rem;color:var(--muted);">← Users & Tickets</a>
</div>

<div style="display:grid;grid-template-columns:1fr 340px;gap:24px;align-items:start;">
    <div class="admin-form-card" style="max-width:none;">
        <div style="margin-bottom:20px;padding-bottom:16px;border-bottom:1px solid var(--border);">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                <div>
                    <h2 style="margin-bottom:4px;"><?= htmlspecialchars($ticket['subject']) ?></h2>
                    <p style="font-size:0.78rem;">From <strong style="color:var(--text);"><?= htmlspecialchars($ticket['user_name']) ?></strong> · <?= date('M d, Y g:i A', strtotime($ticket['created_at'])) ?></p>
                </div>
                <span class="badge badge-<?= $ticket['status']==='open'?'pending':($ticket['status']==='in_progress'?'confirmed':'completed') ?>"><?= ucfirst(str_replace('_',' ',$ticket['status'])) ?></span>
            </div>
        </div>
        <div style="background:var(--bg);border-radius:12px;padding:16px;margin-bottom:24px;">
            <p style="font-size:0.875rem;line-height:1.7;color:var(--text);"><?= nl2br(htmlspecialchars($ticket['message'])) ?></p>
        </div>
        <form method="POST">
            <div class="form-group">
                <label>Reply to Student</label>
                <textarea name="reply" class="form-control" rows="5" placeholder="Type your response..." required style="resize:vertical;"></textarea>
            </div>
            <div class="form-group">
                <label>Update Status</label>
                <select name="status" class="form-control">
                    <?php foreach (['open','in_progress','closed'] as $s): ?>
                    <option value="<?= $s ?>" <?= $ticket['status']===$s?'selected':'' ?>><?= ucfirst(str_replace('_',' ',$s)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Send Reply & Update</button>
        </form>
    </div>

    <div style="background:var(--card);border-radius:14px;padding:20px;border:1px solid var(--border);">
        <h3 style="font-size:0.9rem;margin-bottom:14px;">Student Info</h3>
        <div style="display:flex;flex-direction:column;gap:10px;font-size:0.82rem;">
            <div><span style="color:var(--muted);">Name</span><div style="font-weight:600;margin-top:2px;"><?= htmlspecialchars($ticket['user_name']) ?></div></div>
            <div><span style="color:var(--muted);">Email</span><div style="margin-top:2px;"><?= htmlspecialchars($ticket['user_email']) ?></div></div>
            <div><span style="color:var(--muted);">Ticket ID</span><div style="margin-top:2px;">#<?= str_pad($ticket['id'],4,'0',STR_PAD_LEFT) ?></div></div>
        </div>
    </div>
</div>
<?php include 'layout_end.php'; ?>
