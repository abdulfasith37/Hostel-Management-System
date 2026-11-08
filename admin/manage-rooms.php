<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';

$msg = '';
$error = '';
$editRoom = null;
$managingRoomId = isset($_GET['manage_beds']) ? (int)$_GET['manage_beds'] : null;

// Handle Room Delete
if (isset($_GET['delete_room'])) {
    $roomId = (int)$_GET['delete_room'];
    try {
        // Check if any beds in this room are occupied
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM beds WHERE room_id = ? AND is_occupied = 1");
        $checkStmt->execute([$roomId]);
        if ($checkStmt->fetchColumn() > 0) {
            $error = "Cannot delete room because one or more beds are currently occupied by students.";
        } else {
            $pdo->beginTransaction();
            // Delete beds first due to foreign key constraint
            $pdo->prepare("DELETE FROM beds WHERE room_id = ?")->execute([$roomId]);
            $pdo->prepare("DELETE FROM rooms WHERE id = ?")->execute([$roomId]);
            $pdo->commit();
            $msg = "Room and its associated beds deleted successfully!";
        }
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = "Error deleting room: " . $e->getMessage();
    }
}

// Handle Bed Delete
if (isset($_GET['delete_bed']) && isset($_GET['room_id'])) {
    $bedId = (int)$_GET['delete_bed'];
    $rId = (int)$_GET['room_id'];
    try {
        $checkBed = $pdo->prepare("SELECT is_occupied FROM beds WHERE id = ?");
        $checkBed->execute([$bedId]);
        $bedData = $checkBed->fetch();

        if ($bedData && $bedData['is_occupied'] == 1) {
            $error = "Cannot delete this bed because it is currently assigned to a student.";
        } else {
            $pdo->prepare("DELETE FROM beds WHERE id = ?")->execute([$bedId]);
            // Decrease room capacity count
            $pdo->prepare("UPDATE rooms SET capacity = GREATEST(1, capacity - 1) WHERE id = ?")->execute([$rId]);
            $msg = "Bed removed successfully!";
            $managingRoomId = $rId;
        }
    } catch (PDOException $e) {
        $error = "Error removing bed.";
    }
}

// Handle Room Edit Fetch
if (isset($_GET['edit_room'])) {
    $rId = (int)$_GET['edit_room'];
    $stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = ?");
    $stmt->execute([$rId]);
    $editRoom = $stmt->fetch();
}

// Handle Form Submissions (Create, Update Room, Update Bed, Add Bed)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_room'])) {
        $block_id = (int)$_POST['block_id'];
        $room_no = trim($_POST['room_no']);
        $floor_no = (int)$_POST['floor_no'];
        $capacity = (int)$_POST['capacity'];

        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("INSERT INTO rooms (block_id, room_no, floor_no, capacity) VALUES (?, ?, ?, ?)");
            $stmt->execute([$block_id, $room_no, $floor_no, $capacity]);
            $room_id = $pdo->lastInsertId();

            $bedStmt = $pdo->prepare("INSERT INTO beds (room_id, bed_label) VALUES (?, ?)");
            for ($i = 1; $i <= $capacity; $i++) {
                $label = "Bed " . $room_no . "-" . chr(64 + $i);
                $bedStmt->execute([$room_id, $label]);
            }
            $pdo->commit();
            $msg = "Room $room_no with $capacity beds generated successfully!";
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Error creating room. Room number may already exist in this block.";
        }
    } elseif (isset($_POST['update_room'])) {
        $room_id = (int)$_POST['room_id'];
        $block_id = (int)$_POST['block_id'];
        $room_no = trim($_POST['room_no']);
        $floor_no = (int)$_POST['floor_no'];

        try {
            $stmt = $pdo->prepare("UPDATE rooms SET block_id = ?, room_no = ?, floor_no = ? WHERE id = ?");
            $stmt->execute([$block_id, $room_no, $floor_no, $room_id]);
            header("Location: manage-rooms.php?msg=RoomUpdated");
            exit;
        } catch (PDOException $e) {
            $error = "Error updating room details.";
        }
    } elseif (isset($_POST['update_bed_label'])) {
        $bed_id = (int)$_POST['bed_id'];
        $new_label = trim($_POST['bed_label']);
        $rId = (int)$_POST['room_id'];

        $pdo->prepare("UPDATE beds SET bed_label = ? WHERE id = ?")->execute([$new_label, $bed_id]);
        $msg = "Bed label updated successfully!";
        $managingRoomId = $rId;
    } elseif (isset($_POST['add_extra_bed'])) {
        $rId = (int)$_POST['room_id'];
        $label = trim($_POST['bed_label']);

        try {
            $pdo->beginTransaction();
            $pdo->prepare("INSERT INTO beds (room_id, bed_label) VALUES (?, ?)")->execute([$rId, $label]);
            $pdo->prepare("UPDATE rooms SET capacity = capacity + 1 WHERE id = ?")->execute([$rId]);
            $pdo->commit();
            $msg = "New bed added to room successfully!";
            $managingRoomId = $rId;
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Error adding bed.";
        }
    }
}

if (isset($_GET['msg']) && $_GET['msg'] === 'RoomUpdated') {
    $msg = "Room details updated successfully!";
}

$blocks = $pdo->query("SELECT b.id, b.block_name, h.name as hostel_name FROM blocks b JOIN hostels h ON b.hostel_id = h.id ORDER BY h.name ASC")->fetchAll();
$rooms = $pdo->query("
    SELECT r.*, b.block_name, h.name as hostel_name,
           (SELECT COUNT(*) FROM beds bds WHERE bds.room_id = r.id) as total_beds,
           (SELECT COUNT(*) FROM beds bds WHERE bds.room_id = r.id AND bds.is_occupied = 1) as occupied_beds
    FROM rooms r 
    JOIN blocks b ON r.block_id = b.id 
    JOIN hostels h ON b.hostel_id = h.id 
    ORDER BY r.id DESC
")->fetchAll();

$managingRoomData = null;
$managingBeds = [];
if ($managingRoomId) {
    $roomStmt = $pdo->prepare("SELECT r.*, b.block_name, h.name as hostel_name FROM rooms r JOIN blocks b ON r.block_id = b.id JOIN hostels h ON b.hostel_id = h.id WHERE r.id = ?");
    $roomStmt->execute([$managingRoomId]);
    $managingRoomData = $roomStmt->fetch();

    $bedStmt = $pdo->prepare("SELECT * FROM beds WHERE room_id = ? ORDER BY id ASC");
    $bedStmt->execute([$managingRoomId]);
    $managingBeds = $bedStmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Manage Rooms & Beds - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="../assets/js/app.js"></script>
</head>
<body>
    <?php include '../includes/sidebar.php'; ?>

    <main class="admin-wrapper">
        <h2 style="font-size: 1.35rem; font-weight: 700; margin-bottom: 18px;">Rooms & Beds Management (CRUD)</h2>

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

        <?php if ($managingRoomId && $managingRoomData): ?>
            <!-- Bed Management Modal/Card View -->
            <div class="card" style="border: 2px solid var(--primary); margin-bottom: 24px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
                    <h3>Manage Beds for Room <?= htmlspecialchars($managingRoomData['room_no']) ?> (<?= htmlspecialchars($managingRoomData['hostel_name'] . ' - ' . $managingRoomData['block_name']) ?>)</h3>
                    <a href="manage-rooms.php" class="btn" style="background: var(--text-muted); padding: 4px 10px; font-size: 12px;">Close / Back to Rooms</a>
                </div>

                <form method="POST" style="display: flex; gap: 10px; margin-bottom: 16px; align-items: flex-end;">
                    <input type="hidden" name="room_id" value="<?= $managingRoomId ?>">
                    <div class="form-group" style="flex: 1; margin-bottom: 0;">
                        <label>Add New Bed Label</label>
                        <input type="text" name="bed_label" class="form-control" placeholder="e.g. Bed <?= $managingRoomData['room_no'] ?>-C" required>
                    </div>
                    <button type="submit" name="add_extra_bed" class="btn" style="height: 40px;">Add Bed</button>
                </form>

                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr><th>Bed ID</th><th>Bed Label</th><th>Occupancy Status</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($managingBeds as $bed): ?>
                            <tr>
                                <td>#<?= $bed['id'] ?></td>
                                <td>
                                    <form method="POST" style="display: flex; gap: 6px;">
                                        <input type="hidden" name="bed_id" value="<?= $bed['id'] ?>">
                                        <input type="hidden" name="room_id" value="<?= $managingRoomId ?>">
                                        <input type="text" name="bed_label" class="form-control" value="<?= htmlspecialchars($bed['bed_label']) ?>" style="padding: 4px 8px; font-size: 13px;" required>
                                        <button type="submit" name="update_bed_label" class="btn" style="padding: 4px 10px; font-size: 12px; min-height: 30px;">Save</button>
                                    </form>
                                </td>
                                <td>
                                    <?php if ($bed['is_occupied']): ?>
                                        <span class="badge" style="background: #fee2e2; color: #991b1b;">Occupied</span>
                                    <?php else: ?>
                                        <span class="badge badge-resolved">Available</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!$bed['is_occupied']): ?>
                                        <a href="manage-rooms.php?delete_bed=<?= $bed['id'] ?>&room_id=<?= $managingRoomId ?>" class="btn btn-danger" style="padding: 4px 10px; font-size: 12px; min-height: 30px;" onclick="return confirmAction('Delete this bed?')">Delete</a>
                                    <?php else: ?>
                                        <span style="font-size: 12px; color: var(--text-muted);">In Use</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <!-- Create or Edit Room Form -->
        <div class="card">
            <h3><?= $editRoom ? 'Edit Room Details' : 'Add Room & Auto-Generate Beds' ?></h3>
            <form method="POST" class="form-grid-4">
                <?php if ($editRoom): ?>
                    <input type="hidden" name="room_id" value="<?= $editRoom['id'] ?>">
                <?php endif; ?>
                <div class="form-group">
                    <label>Assigned Block</label>
                    <select name="block_id" class="form-control" required>
                        <option value="">-- Select Block --</option>
                        <?php foreach ($blocks as $bl): ?>
                            <option value="<?= $bl['id'] ?>" <?= ($editRoom && $editRoom['block_id'] == $bl['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($bl['hostel_name'] . ' - ' . $bl['block_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Room Number</label>
                    <input type="text" name="room_no" class="form-control" placeholder="e.g. 101" value="<?= htmlspecialchars($editRoom['room_no'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Floor Number</label>
                    <input type="number" name="floor_no" class="form-control" value="<?= htmlspecialchars($editRoom['floor_no'] ?? '1') ?>" required>
                </div>
                <div class="form-group">
                    <label>Initial Bed Capacity</label>
                    <input type="number" name="capacity" class="form-control" value="<?= htmlspecialchars($editRoom['capacity'] ?? '2') ?>" min="1" max="6" <?= $editRoom ? 'readonly style="background:#f1f5f9;"' : 'required' ?>>
                </div>
                <div class="col-span-full" style="display: flex; gap: 10px;">
                    <?php if ($editRoom): ?>
                        <button type="submit" name="update_room" class="btn">Update Room</button>
                        <a href="manage-rooms.php" class="btn" style="background: var(--text-muted);">Cancel</a>
                    <?php else: ?>
                        <button type="submit" name="add_room" class="btn">Create Room & Beds</button>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Rooms Directory Table -->
        <div class="card">
            <h3>Existing Rooms Directory</h3>
            <input type="text" id="roomSearch" class="form-control" placeholder="Search by room number, block, or hostel..." onkeyup="filterTable('roomSearch', 'roomTable')" style="margin: 10px 0 16px 0;">
            <div class="table-responsive">
                <table id="roomTable">
                    <thead>
                        <tr><th>Hostel</th><th>Block</th><th>Room No</th><th>Floor</th><th>Beds (Total/Occ)</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php if (count($rooms) > 0): ?>
                            <?php foreach ($rooms as $r): ?>
                            <tr>
                                <td><?= htmlspecialchars($r['hostel_name']) ?></td>
                                <td><?= htmlspecialchars($r['block_name']) ?></td>
                                <td><strong>Room <?= htmlspecialchars($r['room_no']) ?></strong></td>
                                <td>Floor <?= $r['floor_no'] ?></td>
                                <td><?= $r['total_beds'] ?> Beds (<?= $r['occupied_beds'] ?> Occ)</td>
                                <td>
                                    <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                                        <a href="manage-rooms.php?manage_beds=<?= $r['id'] ?>" class="btn" style="padding: 4px 8px; font-size: 11px; min-height: 28px; background: var(--success);">Beds</a>
                                        <a href="manage-rooms.php?edit_room=<?= $r['id'] ?>" class="btn" style="padding: 4px 8px; font-size: 11px; min-height: 28px;">Edit</a>
                                        <a href="manage-rooms.php?delete_room=<?= $r['id'] ?>" class="btn btn-danger" style="padding: 4px 8px; font-size: 11px; min-height: 28px;" onclick="return confirmAction('Delete this room and its unoccupied beds?')">Delete</a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" style="text-align: center;">No rooms registered yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>
</html>