<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';

$msg = '';
$error = '';
$editStudent = null;

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
        $stmt->execute([$id]);
        $msg = "Student record deleted successfully!";
    } catch (PDOException $e) {
        $error = "Cannot delete student with active room allocations. Please unallocate first.";
    }
}

// Handle Edit Fetch
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
    $stmt->execute([$id]);
    $editStudent = $stmt->fetch();
}

// Handle Form Submission (Create or Update)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reg = strtoupper(trim($_POST['reg_no']));
    $first = trim($_POST['first_name']);
    $last = trim($_POST['last_name']);
    $gender = $_POST['gender'];
    $batch = (int)$_POST['batch_year'];
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $status = $_POST['status'] ?? 'Active';

    if (isset($_POST['update_student']) && !empty($_POST['student_id'])) {
        $id = (int)$_POST['student_id'];
        try {
            $stmt = $pdo->prepare("UPDATE students SET reg_no = ?, first_name = ?, last_name = ?, gender = ?, batch_year = ?, phone = ?, email = ?, status = ? WHERE id = ?");
            $stmt->execute([$reg, $first, $last, $gender, $batch, $phone, $email, $status, $id]);
            header("Location: manage-students.php?msg=UpdatedSuccessfully");
            exit;
        } catch (PDOException $e) {
            $error = "Error updating student. Registration number might already exist.";
        }
    } elseif (isset($_POST['add_student'])) {
        try {
            $stmt = $pdo->prepare("INSERT INTO students (reg_no, first_name, last_name, gender, batch_year, phone, email, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$reg, $first, $last, $gender, $batch, $phone, $email, $status]);
            $msg = "Student $reg successfully enrolled!";
        } catch (PDOException $e) {
            $error = "A student with Registration No '$reg' already exists.";
        }
    }
}

if (isset($_GET['msg']) && $_GET['msg'] === 'UpdatedSuccessfully') {
    $msg = "Student record updated successfully!";
}

$students = $pdo->query("SELECT * FROM students ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Manage Students - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="../assets/js/app.js"></script>
</head>
<body>
    <?php include '../includes/sidebar.php'; ?>

    <main class="admin-wrapper">
        <h2 style="font-size: 1.35rem; font-weight: 700; margin-bottom: 18px;">Student Directory & CRUD</h2>

        <?php if ($msg): ?>
            <div style="padding: 10px 14px; background: #dcfce7; border-left: 4px solid var(--success); color: #166534; margin-bottom: 18px; border-radius: 4px; font-size: 0.875rem;">
                <?= $msg ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div style="padding: 10px 14px; background: #fee2e2; border-left: 4px solid var(--danger); color: #991b1b; margin-bottom: 18px; border-radius: 4px; font-size: 0.875rem;">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <h3><?= $editStudent ? 'Edit Student Record' : 'Register New Student' ?></h3>
            <form method="POST" class="form-grid-3">
                <?php if ($editStudent): ?>
                    <input type="hidden" name="student_id" value="<?= $editStudent['id'] ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label>Student Reg No</label>
                    <input type="text" name="reg_no" class="form-control" 
                           placeholder="e.g. 2023ICT001" 
                           value="<?= htmlspecialchars($editStudent['reg_no'] ?? '') ?>" 
                           style="text-transform: uppercase;" required>
                </div>
                <div class="form-group">
                    <label>First Name</label>
                    <input type="text" name="first_name" class="form-control" placeholder="Kamal" value="<?= htmlspecialchars($editStudent['first_name'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Last Name</label>
                    <input type="text" name="last_name" class="form-control" placeholder="Perera" value="<?= htmlspecialchars($editStudent['last_name'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Gender</label>
                    <select name="gender" class="form-control">
                        <option value="Male" <?= ($editStudent['gender'] ?? '') === 'Male' ? 'selected' : '' ?>>Male</option>
                        <option value="Female" <?= ($editStudent['gender'] ?? '') === 'Female' ? 'selected' : '' ?>>Female</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Intake Batch Year</label>
                    <input type="number" name="batch_year" class="form-control" value="<?= htmlspecialchars($editStudent['batch_year'] ?? '2023') ?>" required>
                </div>
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="text" name="phone" class="form-control" placeholder="0771234567" value="<?= htmlspecialchars($editStudent['phone'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>University Email</label>
                    <input type="email" name="email" class="form-control" placeholder="student@vau.ac.lk" value="<?= htmlspecialchars($editStudent['email'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="Active" <?= ($editStudent['status'] ?? '') === 'Active' ? 'selected' : '' ?>>Active</option>
                        <option value="Vacated" <?= ($editStudent['status'] ?? '') === 'Vacated' ? 'selected' : '' ?>>Vacated</option>
                    </select>
                </div>
                <div class="col-span-full" style="display: flex; gap: 10px;">
                    <?php if ($editStudent): ?>
                        <button type="submit" name="update_student" class="btn">Update Record</button>
                        <a href="manage-students.php" class="btn" style="background: var(--text-muted);">Cancel</a>
                    <?php else: ?>
                        <button type="submit" name="add_student" class="btn">Register Student</button>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <div class="card">
            <h3>Enrolled Students List</h3>
            <input type="text" id="stSearch" class="form-control" 
                   placeholder="Search by reg number or name..." 
                   onkeyup="filterTable('stSearch', 'stTable')" style="margin: 10px 0 16px 0;">
            
            <div class="table-responsive">
                <table id="stTable">
                    <thead>
                        <tr><th>Reg No</th><th>Full Name</th><th>Gender</th><th>Batch</th><th>Status</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php if (count($students) > 0): ?>
                            <?php foreach ($students as $s): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($s['reg_no']) ?></strong></td>
                                <td><?= htmlspecialchars($s['first_name'] . ' ' . $s['last_name']) ?></td>
                                <td><?= htmlspecialchars($s['gender']) ?></td>
                                <td>Year <?= htmlspecialchars($s['batch_year']) ?></td>
                                <td>
                                    <span class="badge" style="background: #e2e8f0; color: #334155;">
                                        <?= htmlspecialchars($s['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 8px;">
                                        <a href="manage-students.php?edit=<?= $s['id'] ?>" class="btn" style="padding: 4px 10px; font-size: 12px; min-height: 30px;">Edit</a>
                                        <a href="manage-students.php?delete=<?= $s['id'] ?>" class="btn btn-danger" style="padding: 4px 10px; font-size: 12px; min-height: 30px;" onclick="return confirmAction('Delete this student?')">Delete</a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" style="text-align: center;">No students registered yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>
</html>