<?php
require_once 'includes/auth.php';
require_once 'config/db.php';
if (isLoggedIn()) { header('Location: home.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($email && $password) {
        // Check admins first
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = ?");
        $stmt->execute([$email]);
        $admin = $stmt->fetch();
        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id']   = $admin['id'];
            $_SESSION['admin_name'] = $admin['name'];
            header('Location: admin/index.php'); exit;
        }
        // Then check regular users
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            header('Location: home.php'); exit;
        }
        $error = 'Invalid email or password.';
    } else {
        $error = 'Please fill in all fields.';
    }
}
$pageTitle = 'Sign In – UniStay';
include 'includes/header.php';
?>

<div class="auth-wrap">
    <!-- Visual panel — desktop only -->
    <div class="auth-visual-panel">
        <div class="auth-visual-inner">
            <div class="auth-visual-logo">
                <svg width="48" height="48" viewBox="0 0 40 40" fill="none">
                    <rect width="40" height="40" rx="10" fill="rgba(255,255,255,0.15)"/>
                    <path d="M20 8L8 16v16h8v-8h8v8h8V16L20 8z" fill="#fff"/>
                </svg>
                <span>UniStay</span>
            </div>
            <h2>Find your perfect space near campus</h2>
            <p>The smartest way for students to discover, compare, and book verified hostels.</p>
        </div>
    </div>

    <!-- Form panel -->
    <div class="auth-form-panel">
        <div class="auth-page">
            <div class="auth-logo">
                <svg viewBox="0 0 40 40" fill="none"><rect width="40" height="40" rx="10" fill="var(--card)"/><path d="M20 8L8 16v16h8v-8h8v8h8V16L20 8z" fill="#fff"/></svg>
            </div>
            <h1 style="font-size:1.5rem;">Welcome to UniStay</h1>
            <p style="margin-top:6px;margin-bottom:22px;">Exclusive hostel bookings for verified students.</p>

            <div class="auth-steps">
                <div class="auth-step">
                    <div class="step-num">1</div>
                    <div class="step-info"><h4>University Email</h4><p>Enter your .edu or .ac address</p></div>
                </div>
                <div class="auth-step">
                    <div class="step-num inactive">2</div>
                    <div class="step-info"><h4>Verification</h4><p>Click the link sent to your inbox</p></div>
                </div>
            </div>

            <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>University Email</label>
                    <div class="input-wrap">
                        <svg class="input-icon-left" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                        <input type="email" name="email" class="form-control has-icon" placeholder="student@university.edu" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    </div>
                    <small style="color:var(--dim);font-size:0.72rem;margin-top:4px;display:block;">Must be a valid institutional email</small>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <div class="input-wrap">
                        <svg class="input-icon-left" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                        <input type="password" name="password" id="pwd" class="form-control has-icon" placeholder="Enter your password" required>
                        <button type="button" class="eye-btn" onclick="togglePwd()">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                    <div style="text-align:right;margin-top:6px;"><a href="forgot.php" style="font-size:0.78rem;color:var(--muted);">Forgot Password?</a></div>
                </div>
                <button type="submit" class="btn btn-primary">→ Verify & Continue</button>
            </form>

            <div class="divider">OR</div>
            <div class="social-btns">
                <button class="btn-social">
                    <svg width="17" height="17" viewBox="0 0 24 24"><path fill="#fff" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#fff" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#fff" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#fff" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
                    Google
                </button>
                <button class="btn-social">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="currentColor"><path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.8-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z"/></svg>
                    Apple
                </button>
            </div>

            <div class="why-verify">
                <p>Why verify your student status?</p>
                <div class="why-chips">
                    <span class="chip">✓ Verified Roommates</span>
                    <span class="chip">🎓 Student Discounts</span>
                    <span class="chip">🏠 Near Campus</span>
                    <span class="chip">🕐 24/7 Support</span>
                </div>
            </div>
            <p class="link-center" style="margin-top:20px;">Don't have an account? <a href="register.php">Sign Up</a></p>
            <p class="link-center" style="margin-top:10px;"><a href="index.php" style="color:var(--muted);display:inline-flex;align-items:center;gap:5px;"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>Back to Home</a></p>
        </div>
    </div>
</div>

<script>
function togglePwd() {
    var p = document.getElementById('pwd');
    p.type = p.type === 'password' ? 'text' : 'password';
}
</script>
<?php include 'includes/footer.php'; ?>
