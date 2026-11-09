<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout_batch'])) {
    $batch = (int)$_POST['batch_year'];

    $stmt = $pdo->prepare("
        SELECT a.id as alloc_id, a.bed_id, s.id as student_id 
        FROM allocations a 
        JOIN students s ON a.student_id = s.id 
        WHERE s.batch_year = ?
    ");
    $stmt->execute([$batch]);
    $batchAllocations = $stmt->fetchAll();

    if (count($batchAllocations) > 0) {
        $pdo->beginTransaction();
        try {
            $freeBedStmt = $pdo->prepare("UPDATE beds SET is_occupied = 0 WHERE id = ?");
            $delAllocStmt = $pdo->prepare("DELETE FROM allocations WHERE id = ?");
            $vacateStudentStmt = $pdo->prepare("UPDATE students SET status = 'Vacated' WHERE id = ?");

            foreach ($batchAllocations as $item) {
                $freeBedStmt->execute([$item['bed_id']]);
                $delAllocStmt->execute([$item['alloc_id']]);
                $vacateStudentStmt->execute([$item['student_id']]);
            }
            $pdo->commit();
            $msg = "Batch $batch successfully checked out! All associated beds are released.";
        } catch (Exception $e) {
            $pdo->rollBack();
            $msg = "Checkout failed: " . $e->getMessage();
        }
    } else {
        $msg = "No active allocations found for Batch $batch.";
    }
}

$batches = $pdo->query("SELECT DISTINCT batch_year FROM students ORDER BY batch_year ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulk Batch Check-Out - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="../assets/js/app.js"></script>
</head>
<body>
    <?php include '../includes/sidebar.php'; ?>

    <main class="admin-wrapper">
        <h2 style="font-size: 1.35rem; font-weight: 700; margin-bottom: 18px;">Batch Check-Out & Bed Release</h2>

        <?php if ($msg): ?>
            <div style="padding: 10px 14px; background: #e0f2fe; border-left: 4px solid var(--primary); color: var(--primary); margin-bottom: 18px; border-radius: 4px; font-size: 0.875rem;">
                <?= $msg ?>
            </div>
        <?php endif; ?>

        <div class="card" style="max-width: 600px;">
            <h3>Vacate Cohort</h3>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin: 8px 0 18px; line-height: 1.5;">
                Executing check-out frees all occupied beds assigned to this batch and updates the students' status to 'Vacated'.
            </p>
            <form method="POST" onsubmit="return confirmAction('Are you certain you want to check out this batch? All beds will be freed.')">
                <div class="form-group">
                    <label>Select Cohort to Check Out</label>
                    <select name="batch_year" class="form-control" required>
                        <?php foreach ($batches as $b): ?>
                            <option value="<?= $b['batch_year'] ?>">Batch Year: <?= $b['batch_year'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" name="checkout_batch" class="btn btn-danger" style="width: 100%;">Confirm Batch Check-Out</button>
            </form>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>
</html>