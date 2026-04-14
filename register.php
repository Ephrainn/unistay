<?php
require_once 'includes/auth.php';
require_once 'config/db.php';
if (isLoggedIn()) { header('Location: home.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name       = trim($_POST['name'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $password   = $_POST['password'] ?? '';
    $university = trim($_POST['university'] ?? '');
    if ($name && $email && $password && $university) {
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);
        if ($check->fetch()) {
            $error = 'Email already registered.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $pdo->prepare("INSERT INTO users (name, email, password, university) VALUES (?,?,?,?)")
                ->execute([$name, $email, $hash, $university]);
            $_SESSION['user_id'] = $pdo->lastInsertId();
            header('Location: home.php'); exit;
        }
    } else {
        $error = 'Please fill in all fields.';
    }
}
$pageTitle = 'Sign Up – UniStay';
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
            <h2>Join thousands of verified students</h2>
            <p>Discover, compare, and book verified hostels near your campus.</p>
        </div>
    </div>

    <!-- Form panel -->
    <div class="auth-form-panel">
        <div class="auth-page">
            <div class="auth-logo">
                <svg viewBox="0 0 40 40" fill="none"><rect width="40" height="40" rx="10" fill="var(--card)"/><path d="M20 8L8 16v16h8v-8h8v8h8V16L20 8z" fill="#fff"/></svg>
            </div>
            <h1 style="font-size:1.5rem;">Create Account</h1>
            <p style="margin-top:6px;margin-bottom:22px;">Join thousands of verified students.</p>

            <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" class="form-control" placeholder="Alex Rivera" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>University Email</label>
                    <div class="input-wrap">
                        <svg class="input-icon-left" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                        <input type="email" name="email" class="form-control has-icon" placeholder="student@university.edu" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label>University</label>
                    <input type="text" name="university" class="form-control" placeholder="Stanford University" required value="<?= htmlspecialchars($_POST['university'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <div class="input-wrap">
                        <svg class="input-icon-left" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                        <input type="password" name="password" id="pwd" class="form-control has-icon" placeholder="Create a secure password" required>
                        <button type="button" class="eye-btn" onclick="togglePwd()">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">→ Create Account</button>
            </form>
            <p class="link-center" style="margin-top:18px;">Already have an account? <a href="login.php">Sign In</a></p>
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
