<?php
require_once '../config/db.php';
require_once 'auth.php';
requireAdmin();

$totalRevenue  = $pdo->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE type='debit' AND status='completed'")->fetchColumn();
$totalRefunds  = $pdo->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE type='credit' AND status='completed'")->fetchColumn();
$pendingCount  = $pdo->query("SELECT COUNT(*) FROM payments WHERE status='pending'")->fetchColumn();
$thisMonth     = $pdo->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE type='debit' AND status='completed' AND MONTH(created_at)=MONTH(NOW()) AND YEAR(created_at)=YEAR(NOW())")->fetchColumn();

$filter = $_GET['type'] ?? 'all';
$sql    = "SELECT p.*, u.name as user_name FROM payments p JOIN users u ON p.user_id = u.id WHERE 1=1";
if ($filter !== 'all') $sql .= " AND p.type = " . $pdo->quote($filter);
$sql .= " ORDER BY p.created_at DESC LIMIT 100";
$transactions = $pdo->query($sql)->fetchAll();

$pageTitle = 'Payments'; $activePage = 'payments';
include 'layout.php';
?>

<div class="admin-stats">
    <div class="admin-stat-card">
        <div class="icon icon-green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg></div>
        <div class="label">Total Revenue</div>
        <div class="value">$<?= number_format($totalRevenue, 0) ?></div>
        <div class="sub">All completed payments</div>
    </div>
    <div class="admin-stat-card">
        <div class="icon icon-blue"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg></div>
        <div class="label">This Month</div>
        <div class="value">$<?= number_format($thisMonth, 0) ?></div>
        <div class="sub">Current month revenue</div>
    </div>
    <div class="admin-stat-card">
        <div class="icon icon-red"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="1 6 11.5 15.5 16.5 10.5 24 18"/></svg></div>
        <div class="label">Total Refunds</div>
        <div class="value">$<?= number_format($totalRefunds, 0) ?></div>
        <div class="sub">Credits issued</div>
    </div>
    <div class="admin-stat-card">
        <div class="icon icon-yellow"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg></div>
        <div class="label">Pending</div>
        <div class="value"><?= $pendingCount ?></div>
        <div class="sub">Awaiting processing</div>
    </div>
</div>

<div class="admin-toolbar">
    <div class="admin-search">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="text" id="searchInput" placeholder="Search student or description...">
    </div>
    <div style="display:flex;gap:8px;">
        <a href="payments.php?type=all"    class="chip <?= $filter==='all'   ?'active':'' ?>">All</a>
        <a href="payments.php?type=debit"  class="chip <?= $filter==='debit' ?'active':'' ?>">Payments</a>
        <a href="payments.php?type=credit" class="chip <?= $filter==='credit'?'active':'' ?>">Refunds</a>
    </div>
</div>

<div class="admin-table-wrap">
    <div class="admin-table-header">
        <h2>Transactions <span style="color:var(--muted);font-weight:400;font-size:0.82rem;">(<?= count($transactions) ?>)</span></h2>
    </div>
    <table id="txTable">
        <thead><tr><th>Student</th><th>Description</th><th>Amount</th><th>Type</th><th>Status</th><th>Date</th></tr></thead>
        <tbody>
        <?php foreach ($transactions as $tx): ?>
        <tr>
            <td><?= htmlspecialchars($tx['user_name']) ?></td>
            <td><?= htmlspecialchars($tx['description']) ?></td>
            <td style="font-weight:600;color:<?= $tx['type']==='debit'?'var(--red)':'var(--green)' ?>">
                <?= $tx['type']==='debit'?'-':'+' ?>$<?= number_format($tx['amount'],2) ?>
            </td>
            <td><?= ucfirst($tx['type']) ?></td>
            <td><span class="badge badge-<?= $tx['status']==='completed'?'confirmed':($tx['status']==='pending'?'pending':'cancelled') ?>"><?= ucfirst($tx['status']) ?></span></td>
            <td><?= date('M d, Y', strtotime($tx['created_at'])) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($transactions)): ?>
        <tr><td colspan="6" style="text-align:center;color:var(--dim);padding:28px;">No transactions found</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
document.getElementById('searchInput').addEventListener('input', function() {
    var q = this.value.toLowerCase();
    document.querySelectorAll('#txTable tbody tr').forEach(function(r) {
        r.style.display = r.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
});
</script>

<?php include 'layout_end.php'; ?>
