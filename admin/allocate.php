<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = (int)$_POST['student_id'];
    $bed_id = (int)$_POST['bed_id'];

    if ($student_id && $bed_id) {
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("INSERT INTO allocations (student_id, bed_id, allocated_at) VALUES (?, ?, CURDATE())");
            $stmt->execute([$student_id, $bed_id]);

            $upStmt = $pdo->prepare("UPDATE beds SET is_occupied = 1 WHERE id = ?");
            $upStmt->execute([$bed_id]);

            $pdo->commit();
            $message = "Bed allocated successfully!";
        } catch (Exception $e) {
            $pdo->rollBack();
            $message = "Error: Student or Bed is already allocated.";
        }
    }
}

$unallocatedStudents = $pdo->query("
    SELECT s.id, s.reg_no, s.first_name, s.last_name 
    FROM students s
    LEFT JOIN allocations a ON s.id = a.student_id 
    WHERE a.id IS NULL AND s.status = 'Active'
    ORDER BY s.reg_no ASC
")->fetchAll();

$availableBeds = $pdo->query("
    SELECT b.id, b.bed_label, r.room_no, h.name as hostel_name 
    FROM beds b 
    JOIN rooms r ON b.room_id = r.id 
    JOIN blocks bl ON r.block_id = bl.id 
    JOIN hostels h ON bl.hostel_id = h.id 
    WHERE b.is_occupied = 0
    ORDER BY h.name ASC, r.room_no ASC
")->fetchAll();

$allocations = $pdo->query("
    SELECT a.id, s.reg_no, s.first_name, s.last_name, b.bed_label, r.room_no
    FROM allocations a
    JOIN students s ON a.student_id = s.id
    JOIN beds b ON a.bed_id = b.id
    JOIN rooms r ON b.room_id = r.id
    ORDER BY a.id DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Allocate Beds - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="../assets/js/app.js"></script>
</head>
<body>
    <?php include '../includes/sidebar.php'; ?>

    <main class="admin-wrapper">
        <h2 style="font-size: 1.35rem; font-weight: 700; margin-bottom: 18px;">Student Bed Allocation</h2>

        <?php if ($message): ?>
            <div style="padding: 10px 14px; background: #e0f2fe; border-left: 4px solid var(--primary); color: var(--primary); margin-bottom: 18px; border-radius: 4px; font-size: 0.875rem;">
                <?= $message ?>
            </div>
        <?php endif; ?>
        
        <div class="card">
            <h3>Assign Bed</h3>
            <form method="POST" class="form-grid-2">
                <div class="form-group">
                    <label>Select Unallocated Student</label>
                    <select name="student_id" class="form-control" required>
                        <option value="">-- Choose Student --</option>
                        <?php foreach ($unallocatedStudents as $st): ?>
                        <option value="<?= $st['id'] ?>"><?= $st['reg_no'] ?> - <?= $st['first_name'] . ' ' . $st['last_name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Select Available Bed</label>
                    <select name="bed_id" class="form-control" required>
                        <option value="">-- Choose Bed --</option>
                        <?php foreach ($availableBeds as $bd): ?>
                        <option value="<?= $bd['id'] ?>"><?= $bd['hostel_name'] ?> - Room <?= $bd['room_no'] ?> (<?= $bd['bed_label'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-span-full">
                    <button type="submit" class="btn">Allocate Bed</button>
                </div>
            </form>
        </div>

        <div class="card">
            <h3>Active Bed Allocations</h3>
            <input type="text" id="allocSearch" class="form-control" style="margin: 10px 0 16px 0;" placeholder="Filter by registration number or student name..." onkeyup="filterTable('allocSearch', 'allocTable')">
            <div class="table-responsive">
                <table id="allocTable">
                    <thead>
                        <tr><th>Reg No</th><th>Student Name</th><th>Room</th><th>Bed</th></tr>
                    </thead>
                    <tbody>
                        <?php if (count($allocations) > 0): ?>
                            <?php foreach ($allocations as $row): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($row['reg_no']) ?></strong></td>
                                <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
                                <td>Room <?= htmlspecialchars($row['room_no']) ?></td>
                                <td><?= htmlspecialchars($row['bed_label']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4">No allocations currently recorded.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>
</html>