<?php
session_start();
require_once '../config/db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_logged'] = true;
        $_SESSION['admin_user'] = $admin['username'];
        $_SESSION['admin_id'] = $admin['id'];
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Invalid username or password!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Hostel Management</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="../assets/js/app.js"></script>
</head>
<body>
    <header class="navbar">
        <h2>Hostel Accommodation Portal</h2>
        <button class="menu-toggle" onclick="toggleMenu()" aria-label="Toggle Navigation">&#9776;</button>
        <nav class="nav-links" id="navLinks">
            <a href="../index.php">Home</a>
            <a href="../student-search.php">Student Portal</a>
            <a href="../submit-complaint.php">Report Issue</a>
            <a href="login.php" class="btn-nav">Admin Login</a>
        </nav>
    </header>

    <main class="container" style="max-width: 440px; margin: 40px auto 30px auto;">
        <div class="card">
            <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 6px;">Admin Authentication</h3>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 20px;">
                Enter administrator credentials to access the management back-end.
            </p>

            <?php if ($error): ?>
                <div style="padding: 10px 14px; background: #fee2e2; border-left: 4px solid var(--danger); border-radius: 4px; color: #991b1b; font-size: 0.85rem; margin-bottom: 16px;">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" class="form-control" placeholder="admin" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>
                <button type="submit" class="btn" style="width: 100%; margin-top: 6px;">Sign In to Dashboard</button>
            </form>
            <p style="font-size: 0.75rem; color: var(--text-muted); text-align: center; margin-top: 16px;">
                Testing Default: <code>admin</code> / <code>Admin@123</code>
            </p>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
