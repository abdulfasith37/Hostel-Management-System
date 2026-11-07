<?php
require_once 'config/db.php';

$msg = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reg = strtoupper(trim($_POST['reg_no'] ?? ''));
    $category = trim($_POST['category'] ?? '');
    $desc = trim($_POST['description'] ?? '');

    if (empty($reg) || empty($category) || empty($desc)) {
        $error = "Please fill in all required fields.";
    } else {
        // Verify that the student exists in the system
        $stmt = $pdo->prepare("SELECT id, first_name, last_name FROM students WHERE UPPER(reg_no) = ?");
        $stmt->execute([$reg]);
        $student = $stmt->fetch();

        if ($student) {
            $insertStmt = $pdo->prepare("INSERT INTO complaints (student_id, category, description, status) VALUES (?, ?, ?, 'Pending')");
            $insertStmt->execute([$student['id'], $category, $desc]);
            $msg = "Complaint ticket submitted successfully for " . htmlspecialchars($student['first_name']) . "! You can track its progress using your registration number in the Student Portal.";
        } else {
            $error = "Registration number '" . htmlspecialchars($reg) . "' not found in the student records. Please check and re-enter.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Report Hostel Issue - Hostel Management</title>
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

    <main class="container" style="max-width: 620px; margin-top: 30px;">
        <div class="card" style="box-sizing: border-box; width: 100%;">
            <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 6px;">Report a Maintenance Issue</h3>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 20px; line-height: 1.5;">
                Log broken electrical fittings, plumbing leaks, or room equipment that requires maintenance inspection.
            </p>

            <?php if ($msg): ?>
                <div style="padding: 12px 14px; background: #dcfce7; border-left: 4px solid var(--success); color: #166534; margin-bottom: 18px; border-radius: 4px; font-size: 0.875rem;">
                    <?= $msg ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div style="padding: 12px 14px; background: #fee2e2; border-left: 4px solid var(--danger); color: #991b1b; margin-bottom: 18px; border-radius: 4px; font-size: 0.875rem;">
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <form method="POST" style="width: 100%;">
                <div class="form-group">
                    <label>Student Registration Number</label>
                    <input type="text" name="reg_no" class="form-control" 
                           placeholder="e.g. 2023ICT001" 
                           value="<?= htmlspecialchars($_POST['reg_no'] ?? '') ?>"
                           style="text-transform: uppercase;" required>
                </div>

                <div class="form-group">
                    <label>Issue Category</label>
                    <select name="category" class="form-control" required>
                        <option value="">-- Choose Category --</option>
                        <option value="Plumbing" <?= (isset($_POST['category']) && $_POST['category'] === 'Plumbing') ? 'selected' : '' ?>>Plumbing (Taps, Drainage, Toilet)</option>
                        <option value="Electrical" <?= (isset($_POST['category']) && $_POST['category'] === 'Electrical') ? 'selected' : '' ?>>Electrical (Fan, Light, Switch, Socket)</option>
                        <option value="Furniture" <?= (isset($_POST['category']) && $_POST['category'] === 'Furniture') ? 'selected' : '' ?>>Furniture (Bed Frame, Chair, Study Table)</option>
                        <option value="Other" <?= (isset($_POST['category']) && $_POST['category'] === 'Other') ? 'selected' : '' ?>>Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Description of Damage / Request</label>
                    <textarea name="description" class="form-control" rows="4" 
                              placeholder="Please describe the damage and mention your room/bed location..." required><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                </div>

                <button type="submit" class="btn" style="width: 100%; margin-top: 4px;">Submit Ticket</button>
            </form>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>