<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';

$pwd_msg = '';
$pwd_error = '';

$adminUsername = $_SESSION['admin_user'] ?? '';

// Handle Password Change Request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $pwd_error = "Please fill in all password fields.";
    } elseif (strlen($new_password) < 6) {
        $pwd_error = "New password must be at least 6 characters long.";
    } elseif ($new_password !== $confirm_password) {
        $pwd_error = "New password and confirm password do not match.";
    } else {
        try {
            // Retrieve current admin details
            $stmt = $pdo->prepare("SELECT id, password FROM admins WHERE username = ?");
            $stmt->execute([$adminUsername]);
            $admin = $stmt->fetch();

            if (!$admin || !password_verify($current_password, $admin['password'])) {
                $pwd_error = "Current password is incorrect.";
            } elseif (password_verify($new_password, $admin['password'])) {
                $pwd_error = "New password cannot be the same as your current password.";
            } else {
                $newHashedPassword = password_hash($new_password, PASSWORD_DEFAULT);
                $updateStmt = $pdo->prepare("UPDATE admins SET password = ? WHERE id = ?");
                $updateStmt->execute([$newHashedPassword, $admin['id']]);
                $pwd_msg = "Admin password updated successfully!";
            }
        } catch (PDOException $e) {
            $pwd_error = "An error occurred while updating the password. Please try again.";
        }
    }
}

$totalStudents = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
$totalRooms = $pdo->query("SELECT COUNT(*) FROM rooms")->fetchColumn();
$occupiedBeds = $pdo->query("SELECT COUNT(*) FROM beds WHERE is_occupied = 1")->fetchColumn();
$availableBeds = $pdo->query("SELECT COUNT(*) FROM beds WHERE is_occupied = 0")->fetchColumn();
$pendingComplaints = $pdo->query("SELECT COUNT(*) FROM complaints WHERE status = 'Pending'")->fetchColumn();

$batchQuery = $pdo->query("SELECT batch_year, COUNT(*) as count FROM students GROUP BY batch_year ORDER BY batch_year DESC");
$batches = $batchQuery->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="../assets/js/app.js?v=<?= filemtime('../assets/js/app.js') ?>"></script>
</head>
<body>
    <?php include '../includes/sidebar.php'; ?>

    <main class="admin-wrapper">
        <h2 style="font-size: 1.35rem; font-weight: 700; margin-bottom: 18px;">Dashboard Overview</h2>
        
        <div class="stat-grid">
            <div class="stat-box"><h3><?= $totalStudents ?></h3><p>Total Students</p></div>
            <div class="stat-box"><h3><?= $totalRooms ?></h3><p>Total Rooms</p></div>
            <div class="stat-box"><h3><?= $occupiedBeds ?></h3><p>Occupied Beds</p></div>
            <div class="stat-box"><h3><?= $availableBeds ?></h3><p>Available Beds</p></div>
            <div class="stat-box"><h3><?= $pendingComplaints ?></h3><p>Pending Tickets</p></div>
        </div>

        <div class="grid-2" style="align-items: start;">
            <!-- Batch Statistics -->
            <div class="card">
                <h3>Students Per Batch Year</h3>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr><th>Batch Year</th><th>Enrolled Students</th></tr>
                        </thead>
                        <tbody>
                            <?php if (count($batches) > 0): ?>
                                <?php foreach ($batches as $b): ?>
                                <tr>
                                    <td>Year <?= htmlspecialchars($b['batch_year']) ?></td>
                                    <td><?= htmlspecialchars($b['count']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="2">No registered students found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Change Admin Password Section -->
            <div class="card" id="change-password">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                    <h3 style="margin-bottom: 0;">Change Admin Password</h3>
                    <span style="font-size: 0.725rem; font-weight: 600; background: var(--primary-light); color: var(--primary); padding: 3px 8px; border-radius: var(--radius-sm);">
                        Security
                    </span>
                </div>
                <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 16px;">
                    Logged in as <strong><?= htmlspecialchars($adminUsername) ?></strong>. Update your administrator credentials below.
                </p>

                <?php if ($pwd_msg): ?>
                    <div style="padding: 10px 14px; background: #dcfce7; border-left: 4px solid var(--success); color: #166534; margin-bottom: 16px; border-radius: 4px; font-size: 0.875rem;">
                        <?= htmlspecialchars($pwd_msg) ?>
                    </div>
                <?php endif; ?>

                <?php if ($pwd_error): ?>
                    <div style="padding: 10px 14px; background: #fee2e2; border-left: 4px solid var(--danger); color: #991b1b; margin-bottom: 16px; border-radius: 4px; font-size: 0.875rem;">
                        <?= htmlspecialchars($pwd_error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="dashboard.php#change-password">
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <div class="password-wrapper">
                            <input type="password" name="current_password" id="current_password" class="form-control" placeholder="Enter current password" required autocomplete="current-password">
                            <button type="button" class="password-toggle-btn" onclick="toggleSinglePassword('current_password', this)" title="Show/Hide password" aria-label="Toggle Current Password Visibility">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <div class="password-wrapper">
                            <input type="password" name="new_password" id="new_password" class="form-control" placeholder="Minimum 6 characters" minlength="6" required autocomplete="new-password">
                            <button type="button" class="password-toggle-btn" onclick="toggleSinglePassword('new_password', this)" title="Show/Hide password" aria-label="Toggle New Password Visibility">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <div class="password-wrapper">
                            <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Re-enter new password" minlength="6" required autocomplete="new-password">
                            <button type="button" class="password-toggle-btn" onclick="toggleSinglePassword('confirm_password', this)" title="Show/Hide password" aria-label="Toggle Confirm Password Visibility">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px;">
                        <label for="show_passwords" style="display: inline-flex; align-items: center; gap: 8px; font-size: 0.825rem; color: var(--text-muted); cursor: pointer; user-select: none;">
                            <input type="checkbox" id="show_passwords" onchange="toggleAllPasswords(this)" onclick="toggleAllPasswords(this)">
                            <span>Show passwords</span>
                        </label>
                    </div>

                    <button type="submit" name="change_password" class="btn" style="width: 100%;">
                        Update Password
                    </button>
                </form>
            </div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>

    <!-- Inline Script to Guarantee Immediate Password Toggling without Caching Delay -->
    <script>
    (function() {
        var eyeOpenSvg = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>';
        var eyeClosedSvg = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>';

        window.setEyeIcon = function(btn, isVisible) {
            if (btn) {
                btn.innerHTML = isVisible ? eyeClosedSvg : eyeOpenSvg;
            }
        };

        window.updateEyeIcons = function(isVisible) {
            var btns = document.querySelectorAll('.password-toggle-btn');
            btns.forEach(function(btn) {
                window.setEyeIcon(btn, isVisible);
            });
        };

        window.toggleAllPasswords = function(trigger) {
            var cb = document.getElementById('show_passwords');
            var isChecked = cb ? cb.checked : (trigger && trigger.checked);
            var targetType = isChecked ? 'text' : 'password';
            var ids = ['current_password', 'new_password', 'confirm_password'];

            ids.forEach(function(id) {
                var el = document.getElementById(id);
                if (el) {
                    el.type = targetType;
                }
            });

            window.updateEyeIcons(isChecked);
        };

        window.toggleSinglePassword = function(inputId, btn) {
            var input = document.getElementById(inputId);
            if (!input) return;
            var isNowText = (input.type === 'password');
            input.type = isNowText ? 'text' : 'password';

            if (btn) {
                window.setEyeIcon(btn, isNowText);
            }

            var allInputs = ['current_password', 'new_password', 'confirm_password'].map(function(id) {
                return document.getElementById(id);
            }).filter(Boolean);

            var allText = allInputs.length > 0 && allInputs.every(function(el) { return el.type === 'text'; });
            var cb = document.getElementById('show_passwords');
            if (cb) {
                cb.checked = allText;
            }
        };

        window.togglePasswordVisibility = function(trigger) {
            window.toggleAllPasswords(trigger);
        };

        // Attach listener to checkbox
        var cb = document.getElementById('show_passwords');
        if (cb) {
            cb.addEventListener('change', function() {
                window.toggleAllPasswords(this);
            });
            cb.addEventListener('click', function() {
                window.toggleAllPasswords(this);
            });
        }
    })();
    </script>
</body>
</html>
