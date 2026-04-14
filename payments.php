<?php
require_once 'includes/auth.php';
require_once 'config/db.php';
requireLogin();
$user = currentUser($pdo);
if (!$user) { session_destroy(); header('Location: login.php'); exit; }

// Handle payment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'pay') {
    $amount = (float)($_POST['amount'] ?? 0);
    $desc   = trim($_POST['description'] ?? 'Payment');
    if ($amount > 0) {
        $pdo->prepare("INSERT INTO payments (user_id, description, amount, type, status) VALUES (?,?,?,'debit','completed')")
            ->execute([$_SESSION['user_id'], $desc, $amount]);
        // Create notification
        $pdo->prepare("INSERT INTO notifications (user_id, title, message, type, link) VALUES (?,?,?,'payment','payments.php')")
            ->execute([$_SESSION['user_id'], 'Payment Recorded', CURRENCY . number_format($amount,2) . ' for ' . $desc . ' was recorded.']);
    }
    header('Location: payments.php?paid=1'); exit;
}

$balStmt = $pdo->prepare("SELECT COALESCE(SUM(monthly_rent),0) FROM bookings WHERE user_id=? AND status IN ('pending','confirmed')");
$balStmt->execute([$_SESSION['user_id']]);
$balance = (float)$balStmt->fetchColumn();

$paidStmt = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM payments WHERE user_id=? AND type='debit' AND status='completed'");
$paidStmt->execute([$_SESSION['user_id']]);
$totalPaid = (float)$paidStmt->fetchColumn();

$secStmt = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM payments WHERE user_id=? AND description LIKE '%Security%'");
$secStmt->execute([$_SESSION['user_id']]);
$security = (float)$secStmt->fetchColumn();

$txStmt = $pdo->prepare("SELECT * FROM payments WHERE user_id=? ORDER BY created_at DESC LIMIT 10");
$txStmt->execute([$_SESSION['user_id']]);
$transactions = $txStmt->fetchAll();

$chartData = [];
for ($i = 5; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $s = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM payments WHERE user_id=? AND type='debit' AND DATE_FORMAT(created_at,'%Y-%m')=?");
    $s->execute([$_SESSION['user_id'], $month]);
    $chartData[] = ['label' => date('M', strtotime("-$i months")), 'value' => (float)$s->fetchColumn()];
}
$maxVal = max(array_column($chartData, 'value')) ?: 1;

$initials  = strtoupper(implode('', array_map(fn($w) => $w[0], explode(' ', $user['name']))));
$pageTitle = 'Payments – UniStay';
$showNav   = true; $activePage = 'payments';
include 'includes/header.php';
?>

<div class="page-wrap">
    <div class="page-header">
        <div><h1>Payments</h1><p>Manage your rent & deposits</p></div>
        <div class="avatar mobile-only"><?= htmlspecialchars($initials) ?></div>
    </div>

    <div class="two-col">
        <!-- Left column -->
        <div>
            <div class="balance-card">
                <p>Current Balance</p>
                <h2><?= CURRENCY ?><?= number_format($balance, 2) ?></h2>
            </div>

            <div class="stats-grid">
                <div class="stat-card"><p>Total Paid</p><h3><?= CURRENCY ?><?= number_format($totalPaid, 0) ?></h3></div>
                <div class="stat-card"><p>Security Deposit</p><h3><?= CURRENCY ?><?= number_format($security, 0) ?></h3></div>
            </div>

            <h3 style="margin-bottom:12px;">Quick Actions</h3>
            <div class="quick-actions">
                <button class="action-btn" onclick="document.querySelector('.tx-item') ? document.querySelector('.tx-item').scrollIntoView({behavior:'smooth'}) : null">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    <span>Invoices</span>
                </button>
                <button class="action-btn" onclick="document.querySelector('[name=description]')?.closest('form')?.scrollIntoView({behavior:'smooth'})">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                    <span>Methods</span>
                </button>
                <a href="profile.php" class="action-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <span>Support</span>
                </a>
            </div>

            <div class="section-header" style="margin-bottom:10px;">
                <h2>Recent Transactions</h2>
                <a href="#">See All</a>
            </div>
            <?php if (empty($transactions)): ?>
            <p style="color:var(--dim);padding:20px 0;">No transactions yet.</p>
            <?php endif; ?>
            <?php foreach ($transactions as $tx): ?>
            <div class="tx-item">
                <div class="tx-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/></svg>
                </div>
                <div class="tx-info">
                    <h4><?= htmlspecialchars($tx['description']) ?></h4>
                    <p><?= date('M d, Y', strtotime($tx['created_at'])) ?></p>
                </div>
                <div class="tx-amount">
                    <div class="val <?= $tx['type'] ?>"><?= $tx['type']==='debit'?'-':'+' ?><?= CURRENCY ?><?= number_format($tx['amount'],2) ?></div>
                    <div class="status"><?= ucfirst($tx['status']) ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Right column -->
        <div>
            <div class="chart-card">
                <h3>Payment History Trend</h3>
                <div class="chart-bars">
                    <?php foreach ($chartData as $d):
                        $h = max(8, ($d['value'] / $maxVal) * 82); ?>
                    <div class="chart-bar" style="height:<?= $h ?>px;" title="<?= $d['label'] ?>: <?= CURRENCY ?><?= number_format($d['value'],0) ?>"></div>
                    <?php endforeach; ?>
                </div>
                <div class="chart-labels">
                    <?php foreach ($chartData as $d): ?><span><?= $d['label'] ?></span><?php endforeach; ?>
                </div>
            </div>

            <div style="background:var(--card);border-radius:16px;padding:20px;">
                <h3 style="margin-bottom:16px;">Make a Payment</h3>
                <?php if (!empty($_GET['paid'])): ?><div class="alert alert-success">Payment recorded successfully.</div><?php endif; ?>
                <form method="POST" action="payments.php">
                    <input type="hidden" name="action" value="pay">
                    <div class="form-group">
                        <label>Amount (<?= CURRENCY ?>)</label>
                        <input type="number" name="amount" class="form-control" placeholder="0.00" step="0.01" min="1" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <select name="description" class="form-control">
                            <option>Monthly Rent</option>
                            <option>Security Deposit</option>
                            <option>Maintenance Fee</option>
                            <option>Electricity Bill</option>
                            <option>Other</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Pay Now</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
