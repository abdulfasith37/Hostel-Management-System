<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';

$msg = '';
$error = '';

if (isset($_GET['delete_hostel'])) {
    $id = (int)$_GET['delete_hostel'];
    try {
        $stmt = $pdo->prepare("DELETE FROM hostels WHERE id = ?");
        $stmt->execute([$id]);
        $msg = "Hostel deleted successfully!";
    } catch (PDOException $e) {
        $error = "Cannot delete hostel containing active blocks or rooms.";
    }
}

if (isset($_GET['delete_block'])) {
    $id = (int)$_GET['delete_block'];
    try {
        $stmt = $pdo->prepare("DELETE FROM blocks WHERE id = ?");
        $stmt->execute([$id]);
        $msg = "Block deleted successfully!";
    } catch (PDOException $e) {
        $error = "Cannot delete block containing active rooms.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_hostel'])) {
        $name = trim($_POST['name']);
        $gender = $_POST['gender'];
        $stmt = $pdo->prepare("INSERT INTO hostels (name, gender_type) VALUES (?, ?)");
        $stmt->execute([$name, $gender]);
        $msg = "Hostel added successfully!";
    } elseif (isset($_POST['add_block'])) {
        $hostel_id = (int)$_POST['hostel_id'];
        $block_name = trim($_POST['block_name']);
        $stmt = $pdo->prepare("INSERT INTO blocks (hostel_id, block_name) VALUES (?, ?)");
        $stmt->execute([$hostel_id, $block_name]);
        $msg = "Block added successfully!";
    }
}

$hostels = $pdo->query("SELECT * FROM hostels ORDER BY id DESC")->fetchAll();
$blocks = $pdo->query("SELECT b.*, h.name as hostel_name, h.gender_type FROM blocks b JOIN hostels h ON b.hostel_id = h.id ORDER BY b.id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Manage Hostels & Blocks - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="../assets/js/app.js"></script>
</head>
<body>
    <?php include '../includes/sidebar.php'; ?>

    <main class="admin-wrapper">
        <h2 style="font-size: 1.35rem; font-weight: 700; margin-bottom: 18px;">Hostels & Blocks Management</h2>

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

        <div class="form-grid-2" style="margin-bottom: 24px;">
            <div class="card">
                <h3>Add New Hostel</h3>
                <form method="POST">
                    <div class="form-group">
                        <label>Hostel Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Boys Hostel" required>
                    </div>
                    <div class="form-group">
                        <label>Resident Gender</label>
                        <select name="gender" class="form-control">
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                    <button type="submit" name="add_hostel" class="btn" style="width: 100%;">Add Hostel</button>
                </form>

                <h4 style="font-size: 0.875rem; margin: 20px 0 8px 0; color: var(--text-muted); font-weight: 600;">Registered Hostels List</h4>
                <div class="table-responsive">
                    <table>
                        <thead><tr><th>Hostel</th><th>Gender</th><th>Action</th></tr></thead>
                        <tbody>
                            <?php foreach ($hostels as $h): ?>
                            <tr>
                                <td><?= htmlspecialchars($h['name']) ?></td>
                                <td><?= htmlspecialchars($h['gender_type']) ?></td>
                                <td><a href="manage-hostels.php?delete_hostel=<?= $h['id'] ?>" class="btn btn-danger" style="padding: 3px 8px; font-size: 11px;" onclick="return confirmAction('Delete this hostel?')">Delete</a></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <h3>Add New Block</h3>
                <form method="POST">
                    <div class="form-group">
                        <label>Hostel Parent</label>
                        <select name="hostel_id" class="form-control" required>
                            <option value="">-- Select Hostel --</option>
                            <?php foreach ($hostels as $h): ?>
                                <option value="<?= $h['id'] ?>"><?= htmlspecialchars($h['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Block Designation</label>
                        <input type="text" name="block_name" class="form-control" placeholder="e.g. Block C" required>
                    </div>
                    <button type="submit" name="add_block" class="btn" style="width: 100%;">Add Block</button>
                </form>

                <h4 style="font-size: 0.875rem; margin: 20px 0 8px 0; color: var(--text-muted); font-weight: 600;">Registered Blocks List</h4>
                <div class="table-responsive">
                    <table>
                        <thead><tr><th>Hostel</th><th>Block</th><th>Action</th></tr></thead>
                        <tbody>
                            <?php foreach ($blocks as $bl): ?>
                            <tr>
                                <td><?= htmlspecialchars($bl['hostel_name']) ?></td>
                                <td><?= htmlspecialchars($bl['block_name']) ?></td>
                                <td><a href="manage-hostels.php?delete_block=<?= $bl['id'] ?>" class="btn btn-danger" style="padding: 3px 8px; font-size: 11px;" onclick="return confirmAction('Delete this block?')">Delete</a></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>
</html>