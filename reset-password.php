<?php
require_once 'includes/auth.php';
require_once 'config/db.php';
if (isLoggedIn()) { header('Location: home.php'); exit; }

$token = trim($_GET['token'] ?? '');
$error = $success = '';
$valid = false;

if ($token) {
    $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token=? AND used=0 AND expires_at > NOW()");
    $stmt->execute([$token]);
    $reset = $stmt->fetch();
    $valid = (bool)$reset;
}

if (!$token || !$valid) {
    $error = 'This reset link is invalid or has expired.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid) {
    $password  = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';
    if (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $password2) {
        $error = 'Passwords do not match.';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $pdo->prepare("UPDATE users SET password=? WHERE email=?")->execute([$hash, $reset['email']]);
        $pdo->prepare("UPDATE password_resets SET used=1 WHERE token=?")->execute([$token]);
        $success = 'Password updated successfully.';
        $valid   = false;
    }
}
$pageTitle = 'Reset Password – UniStay';
include 'includes/header.php';
?>
<div class="auth-wrap">
    <div class="auth-form-panel">
        <div class="auth-page">
            <div class="auth-logo">
                <svg viewBox="0 0 40 40" fill="none"><rect width="40" height="40" rx="10" fill="var(--card)"/><path d="M20 8L8 16v16h8v-8h8v8h8V16L20 8z" fill="#fff"/></svg>
            </div>
            <h1 style="font-size:1.4rem;">New Password</h1>
            <p style="margin-top:6px;margin-bottom:22px;">Choose a strong password for your account.</p>
            <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <a href="login.php" class="btn btn-primary" style="margin-top:8px;">Sign In</a>
            <?php elseif ($valid): ?>
            <form method="POST">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Min. 6 characters" required>
                </div>
                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" name="password2" class="form-control" placeholder="Repeat password" required>
                </div>
                <button type="submit" class="btn btn-primary">Update Password</button>
            </form>
            <?php endif; ?>
            <p class="link-center" style="margin-top:16px;"><a href="login.php" style="color:var(--muted);display:inline-flex;align-items:center;gap:5px;"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>Back to Sign In</a></p>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
