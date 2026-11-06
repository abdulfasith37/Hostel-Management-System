<?php
// Determine active page to highlight active tab
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<header class="admin-navbar">
    <div class="admin-nav-container">
        <!-- Logo / Title -->
        <a href="dashboard.php" class="admin-brand">
            <span class="brand-badge">Admin</span> Hostel Management
        </a>

        <!-- Mobile Hamburger Toggle -->
        <button class="menu-toggle" type="button" onclick="toggleAdminNav()" aria-label="Toggle navigation">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="3" y1="12" x2="21" y2="12"></line>
                <line x1="3" y1="6" x2="21" y2="6"></line>
                <line x1="3" y1="18" x2="21" y2="18"></line>
            </svg>
        </button>

        <!-- Navigation Menu -->
        <nav class="admin-menu" id="adminMenu">
            <!-- Core Management Links -->
            <ul class="admin-links">
                <li>
                    <a href="dashboard.php" class="<?= $currentPage === 'dashboard.php' ? 'active' : '' ?>">Dashboard</a>
                </li>

                <!-- Infrastructure Dropdown -->
                <li class="nav-dropdown">
                    <a href="javascript:void(0)" class="dropdown-trigger <?= in_array($currentPage, ['manage-hostels.php', 'manage-rooms.php']) ? 'active' : '' ?>">
                        Hostels & Rooms <span class="arrow-down">&#9662;</span>
                    </a>
                    <div class="dropdown-panel">
                        <a href="manage-hostels.php" class="<?= $currentPage === 'manage-hostels.php' ? 'active' : '' ?>">Manage Hostels & Blocks</a>
                        <a href="manage-rooms.php" class="<?= $currentPage === 'manage-rooms.php' ? 'active' : '' ?>">Manage Rooms & Beds</a>
                    </div>
                </li>

                <!-- Students Directory -->
                <li>
                    <a href="manage-students.php" class="<?= $currentPage === 'manage-students.php' ? 'active' : '' ?>">Students Directory</a>
                </li>

                <!-- Operations Dropdown -->
                <li class="nav-dropdown">
                    <a href="javascript:void(0)" class="dropdown-trigger <?= in_array($currentPage, ['allocate.php', 'checkout-batch.php', 'complaints.php', 'export.php']) ? 'active' : '' ?>">
                        Operations <span class="arrow-down">&#9662;</span>
                    </a>
                    <div class="dropdown-panel">
                        <a href="allocate.php" class="<?= $currentPage === 'allocate.php' ? 'active' : '' ?>">Assign Bed</a>
                        <a href="complaints.php" class="<?= $currentPage === 'complaints.php' ? 'active' : '' ?>">Complaints Board</a>
                        <a href="checkout-batch.php" class="<?= $currentPage === 'checkout-batch.php' ? 'active' : '' ?>">Batch Check-Out</a>
                        <a href="export.php" class="<?= $currentPage === 'export.php' ? 'active' : '' ?>">Export Residents (CSV)</a>
                    </div>
                </li>
            </ul>

            <!-- Right Side: User & Logout -->
            <div class="admin-user-action">
                <a href="dashboard.php#change-password" class="user-greeting" title="Account Settings - Change Password" style="text-decoration: none; cursor: pointer; display: inline-flex; align-items: center; gap: 4px;">
                    👤 <strong><?= htmlspecialchars($_SESSION['admin_user'] ?? 'Admin') ?></strong>
                </a>
                <a href="logout.php" class="btn-logout">Logout</a>
            </div>
        </nav>
    </div>
</header>
