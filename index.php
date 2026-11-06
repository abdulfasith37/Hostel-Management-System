<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hostel Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/app.js"></script>
</head>
<body>
    <header class="navbar">
        <h2>Hostel Accommodation Portal</h2>
        <button class="menu-toggle" onclick="toggleMenu()" aria-label="Toggle Navigation">&#9776;</button>
        <nav class="nav-links" id="navLinks">
            <a href="index.php">Home</a>
            <a href="student-search.php">Student Portal</a>
            <a href="submit-complaint.php">Report Issue</a>
            <a href="admin/login.php" class="btn-nav">Admin Login</a>
        </nav>
    </header>

    <main class="container" style="max-width: 980px; margin-top: 30px;">
        <div style="text-align: center; margin-bottom: 30px;">
            <h1 style="font-size: clamp(1.4rem, 4vw, 1.9rem); font-weight: 700; color: var(--text-main); margin-bottom: 8px;">
                University Accommodation Management
            </h1>
            <p style="color: var(--text-muted); font-size: 0.95rem;">
                Select your portal to access student accommodation allocations, submit maintenance requests, or access administrative control panels.
            </p>
        </div>

        <div class="grid-2">
            <div class="card interactive-card">
                <h3>Student Portal</h3>
                <p style="font-size: 0.85rem; color: var(--text-muted); margin: 12px 0 20px; line-height: 1.6;">
                    Lookup your allocated hostel, room number, assigned bed, and roommates using your registration ID.
                </p>
                <a href="student-search.php" class="btn" style="width: 100%;">Enter Student Portal</a>
            </div>

            <div class="card interactive-card">
                <h3>Administrator</h3>
                <p style="font-size: 0.85rem; color: var(--text-muted); margin: 12px 0 20px; line-height: 1.6;">
                    Authorized access for managing room inventory, student allocations, cohort check-outs, and maintenance tickets.
                </p>
                <a href="admin/login.php" class="btn" style="width: 100%;">Admin Login</a>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>