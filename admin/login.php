<?php
require_once '../config/db.php';
require_once 'auth.php';
if (adminLoggedIn()) { header('Location: index.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    // Simple hardcoded admin check — replace with DB lookup as needed
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = ?");
    $stmt->execute([$email]);
    $admin = $stmt->fetch();
    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_id']   = $admin['id'];
        $_SESSION['admin_name'] = $admin['name'];
        header('Location: index.php'); exit;
    }
    $error = 'Invalid credentials.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login – UniStay</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="dark auth-body">
<div class="app-container no-nav" style="display:flex;align-items:center;justify-content:center;min-height:100vh;">
    <div style="width:100%;max-width:400px;padding:32px 24px;">
        <div style="text-align:center;margin-bottom:32px;">
            <div style="display:inline-flex;align-items:center;justify-content:center;width:52px;height:52px;background:#fff;border-radius:12px;margin-bottom:16px;">
                <svg width="32" height="32" viewBox="0 0 40 40" fill="none"><path d="M20 8L8 16v16h8v-8h8v8h8V16L20 8z" fill="#000"/></svg>
            </div>
            <h1 style="font-size:1.4rem;">Admin Panel</h1>
            <p style="margin-top:4px;">UniStay Management</p>
        </div>
        <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control" placeholder="admin@unistay.com" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn btn-primary">Sign In</button>
        </form>
        <p style="text-align:center;margin-top:20px;font-size:0.78rem;color:var(--dim);">
            <a href="../index.php" style="color:var(--muted);">← Back to site</a>
        </p>
    </div>
</div>
</body>
</html>
