<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';

if (isset($_GET['download']) && $_GET['download'] === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="hostel_residents_' . date('Ymd') . '.csv"');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['Student ID', 'Full Name', 'Gender', 'Batch', 'Hostel', 'Room', 'Bed', 'Phone']);

    $stmt = $pdo->query("
        SELECT s.reg_no, CONCAT(s.first_name, ' ', s.last_name) AS full_name, s.gender, s.batch_year, 
               h.name AS hostel_name, r.room_no, b.bed_label, s.phone
        FROM allocations a
        JOIN students s ON a.student_id = s.id
        JOIN beds b ON a.bed_id = b.id
        JOIN rooms r ON b.room_id = r.id
        JOIN blocks bl ON r.block_id = bl.id
        JOIN hostels h ON bl.hostel_id = h.id
    ");

    while ($row = $stmt->fetch()) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export Data - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="../assets/js/app.js"></script>
</head>
<body>
    <?php include '../includes/sidebar.php'; ?>

    <main class="admin-wrapper">
        <h2 style="font-size: 1.35rem; font-weight: 700; margin-bottom: 18px;">Export Resident Details</h2>

        <div class="card" style="max-width: 600px;">
            <h3>Resident Directory Export</h3>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin: 8px 0 20px; line-height: 1.5;">
                Generate and download the active directory of allocated residents formatted for spreadsheet applications (CSV format).
            </p>
            <a href="export.php?download=csv" class="btn" style="width: 100%;">Download CSV File</a>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>
</html>