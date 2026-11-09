<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $cid = (int)$_POST['complaint_id'];
    $status = $_POST['status'];

    $stmt = $pdo->prepare("UPDATE complaints SET status = ? WHERE id = ?");
    $stmt->execute([$status, $cid]);
    $msg = "Ticket status updated to $status!";
}

$complaints = $pdo->query("
    SELECT c.*, s.reg_no, CONCAT(s.first_name, ' ', s.last_name) as student_name, r.room_no
    FROM complaints c
    JOIN students s ON c.student_id = s.id
    LEFT JOIN allocations a ON s.id = a.student_id
    LEFT JOIN beds b ON a.bed_id = b.id
    LEFT JOIN rooms r ON b.room_id = r.id
    ORDER BY c.id DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Tickets - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="../assets/js/app.js"></script>
</head>
<body>
    <?php include '../includes/sidebar.php'; ?>

    <main class="admin-wrapper">
        <h2 style="font-size: 1.35rem; font-weight: 700; margin-bottom: 18px;">Maintenance Complaints Board</h2>

        <?php if ($msg): ?>
            <div style="padding: 10px 14px; background: #dcfce7; border-left: 4px solid var(--success); color: #166534; margin-bottom: 18px; border-radius: 4px; font-size: 0.875rem;">
                <?= $msg ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr><th>Student</th><th>Room</th><th>Issue Type</th><th>Description</th><th>Current Status</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        <?php if (count($complaints) > 0): ?>
                            <?php foreach ($complaints as $c): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($c['student_name']) ?></strong><br><small style="color:var(--text-muted);"><?= $c['reg_no'] ?></small></td>
                                <td><?= $c['room_no'] ? 'Room ' . htmlspecialchars($c['room_no']) : 'N/A' ?></td>
                                <td><?= htmlspecialchars($c['category']) ?></td>
                                <td><?= htmlspecialchars($c['description']) ?></td>
                                <td>
                                    <span class="badge badge-<?= strtolower(str_replace(' ', '', $c['status'])) ?>">
                                        <?= htmlspecialchars($c['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <form method="POST" style="display: flex; gap: 6px; flex-wrap: nowrap;">
                                        <input type="hidden" name="complaint_id" value="<?= $c['id'] ?>">
                                        <select name="status" class="form-control" style="padding: 4px 6px; font-size: 12px; width: auto; min-width: 105px;">
                                            <option value="Pending" <?= $c['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                            <option value="In Progress" <?= $c['status'] === 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                                            <option value="Resolved" <?= $c['status'] === 'Resolved' ? 'selected' : '' ?>>Resolved</option>
                                        </select>
                                        <button type="submit" name="update_status" class="btn" style="padding: 4px 10px; font-size: 12px; min-height: 30px;">Save</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6">No complaints logged yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>
</html>