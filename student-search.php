<?php
require_once 'config/db.php';

$student = null;
$allocation = null;
$roommates = [];
$complaints = [];$error = '';

if (isset($_GET['reg_no']) && !empty(trim($_GET['reg_no']))) {
    $reg_no = strtoupper(trim($_GET['reg_no']));

    $stmt =$pdo->prepare("SELECT * FROM students WHERE UPPER(reg_no) = ?");
    $stmt->execute([$reg_no]);
    $student =$stmt->fetch();

    if ($student) {
        $allocStmt =$pdo->prepare("
            SELECT a.*, b.bed_label, r.id as room_id, r.room_no, r.floor_no, bl.block_name, h.name as hostel_name
            FROM allocations a
            JOIN beds b ON a.bed_id = b.id
            JOIN rooms r ON b.room_id = r.id
            JOIN blocks bl ON r.block_id = bl.id
            JOIN hostels h ON bl.hostel_id = h.id
            WHERE a.student_id = ?
        ");
        $allocStmt->execute([$student['id']]);
        $allocation =$allocStmt->fetch();

        if ($allocation) {
            // FIXED: Filter strictly by unique room ID (`r.id`) instead of shared room number (`r.room_no`)
            $mateStmt =$pdo->prepare("
                SELECT s.first_name, s.last_name, s.reg_no, b.bed_label
                FROM allocations a
                JOIN students s ON a.student_id = s.id
                JOIN beds b ON a.bed_id = b.id
                JOIN rooms r ON b.room_id = r.id
                WHERE r.id = ? AND s.id != ?
            ");
            $mateStmt->execute([$allocation['room_id'],$student['id']]);
            $roommates =$mateStmt->fetchAll();
        }

        $compStmt =$pdo->prepare("SELECT * FROM complaints WHERE student_id = ? ORDER BY id DESC");
        $compStmt->execute([$student['id']]);
        $complaints =$compStmt->fetchAll();
    } else {
        $error = "No student found with Registration Number: " . htmlspecialchars($reg_no);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Student Portal - Hostel Management</title>
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

    <main class="container">
        <div class="card" style="max-width: 600px; margin: 0 auto 24px auto;">
            <h3>Search Your Hostel Allocation</h3>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 16px;">
                Enter your university student registration number (e.g., <strong>2023/ICT/001</strong>).
            </p>
            <form method="GET" action="student-search.php">
                <div class="form-group">
                    <label>Registration Number</label>
                    <input type="text" name="reg_no" class="form-control" 
                           placeholder="2023/ICT/001" 
                           value="<?= htmlspecialchars($_GET['reg_no'] ?? '') ?>" 
                           style="text-transform: uppercase;" required>
                </div>
                <button type="submit" class="btn" style="width: 100%;">Search Records</button>
            </form>
            <?php if ($error): ?>
                <div style="margin-top: 14px; padding: 10px 14px; background: #fee2e2; border-left: 4px solid var(--danger); border-radius: 4px; color: #991b1b; font-size: 0.85rem;">
                    <?= $error ?>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($student): ?>
            <div class="card">
                <h3>Personal & Hostel Details</h3>
                <div class="table-responsive">
                    <table>
                        <tr><th>Registration No</th><td><strong><?= htmlspecialchars($student['reg_no']) ?></strong></td></tr>
                        <tr><th>Student Name</th><td><?= htmlspecialchars($student['first_name'] . ' ' .$student['last_name']) ?></td></tr>
                        <tr><th>Gender</th><td><?= htmlspecialchars($student['gender']) ?></td></tr>
                        <tr><th>Intake Batch</th><td>Year <?= htmlspecialchars($student['batch_year']) ?></td></tr>
                        <tr><th>Allocated Hostel</th><td><?= $allocation ? htmlspecialchars($allocation['hostel_name']) : '<span style="color:var(--text-muted);">Not Allocated Yet</span>' ?></td></tr>
                        <tr><th>Block & Room</th><td><?= $allocation ? htmlspecialchars($allocation['block_name'] . ' — Room ' . $allocation['room_no'] . ' (Floor ' .$allocation['floor_no'] . ')') : 'N/A' ?></td></tr>
                        <tr><th>Assigned Bed</th><td><?= $allocation ? '<strong>' . htmlspecialchars($allocation['bed_label']) . '</strong>' : 'N/A' ?></td></tr>
                    </table>
                </div>
            </div>

            <?php if ($allocation && count($roommates) > 0): ?>
            <div class="card">
                <h3>Assigned Roommates</h3>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr><th>Reg No</th><th>Name</th><th>Bed</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($roommates as$mate): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($mate['reg_no']) ?></strong></td>
                                <td><?= htmlspecialchars($mate['first_name'] . ' ' .$mate['last_name']) ?></td>
                                <td><?= htmlspecialchars($mate['bed_label']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <div class="card">
                <h3>Maintenance Issues</h3>
                <?php if (count($complaints) > 0): ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr><th>Category</th><th>Issue Details</th><th>Status</th><th>Submitted</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($complaints as$c): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($c['category']) ?></strong></td>
                                <td><?= htmlspecialchars($c['description']) ?></td>
                                <td>
                                    <span class="badge badge-<?= strtolower(str_replace(' ', '', $c['status'])) ?>">
                                        <?= htmlspecialchars($c['status']) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars(date('M d, Y', strtotime($c['created_at']))) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p style="color: var(--text-muted); font-size: 0.875rem;">No complaints filed.</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>