<?php
require_once 'includes/auth.php';
require_once 'config/db.php';
if (isLoggedIn()) { header('Location: home.php'); exit; }

$success = $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if (!$email) {
        $error = 'Please enter your email address.';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user) {
            $token   = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            $pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?,?,?)")
                ->execute([$email, $token, $expires]);
            // In production send email — for now show the reset link directly
            $resetLink = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/reset-password.php?token=' . $token;
            $success = 'Reset link generated. <a href="' . htmlspecialchars($resetLink) . '" style="color:var(--accent);">Click here to reset</a> (in production this would be emailed).';
        } else {
            // Don't reveal if email exists
            $success = 'If that email is registered, a reset link has been sent.';
        }
    }
}
$pageTitle = 'Forgot Password – UniStay';
include 'includes/header.php';
?>
<div class="auth-wrap">
    <div class="auth-form-panel">
        <div class="auth-page">
            <div class="auth-logo">
                <svg viewBox="0 0 40 40" fill="none"><rect width="40" height="40" rx="10" fill="var(--card)"/><path d="M20 8L8 16v16h8v-8h8v8h8V16L20 8z" fill="#fff"/></svg>
            </div>
            <h1 style="font-size:1.4rem;">Reset Password</h1>
            <p style="margin-top:6px;margin-bottom:22px;">Enter your university email and we'll send a reset link.</p>
            <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
            <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
            <?php if (!$success): ?>
            <form method="POST">
                <div class="form-group">
                    <label>University Email</label>
                    <div class="input-wrap">
                        <svg class="input-icon-left" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                        <input type="email" name="email" class="form-control has-icon" placeholder="student@university.edu" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Send Reset Link</button>
            </form>
            <?php endif; ?>
            <p class="link-center" style="margin-top:16px;"><a href="login.php" style="color:var(--muted);display:inline-flex;align-items:center;gap:5px;"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>Back to Sign In</a></p>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
