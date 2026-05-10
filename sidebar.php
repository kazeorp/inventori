<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'modal-login.php';

$role = $_SESSION['role'] ?? 'normal';
$display_name = $_SESSION['nama_lengkap'] ?? ($_SESSION['username'] ?? 'Guest');
?>

<div class="sidebar">
    <div class="user-profile-sidebar">
        <div class="fw-bold text-truncate"><?= htmlspecialchars($display_name) ?></div>
        <span class="badge bg-primary mt-1 mb-2"><?= ucfirst($role) ?></span>
    </div>

    <div class="sidebar-menu">
        <small class="text-uppercase text-muted fw-bold mb-2 d-block" style="font-size: 0.65rem;">Main Menu</small>

        <a href="index.php"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>

        <?php if ($role !== 'normal'): ?>
            <a href="tampil.php"><i class="bi bi-box-seam me-2"></i> Inventory Data</a>
            <a href="peripherals.php"><i class="bi bi-mouse2 me-2"></i> Stock Peripheral</a>
        <?php endif; ?>

        <?php if (in_array($role, ['admin', 'superadmin'])): ?>
            <div class="mt-3 mb-2 border-top pt-2">
                <small class="text-uppercase text-muted fw-bold d-block" style="font-size: 0.65rem;">Management</small>
            </div>
            <a href="detail-aset.php"><i class="bi bi-card-list me-2"></i> Asset Detail</a>
            <a href="cetak-manual.php"><i class="bi bi-printer me-2"></i> Print Form</a>
            <a href="kelola-user.php"><i class="bi bi-people me-2"></i> Manage Account</a>
        <?php endif; ?>

        <?php if ($role === 'superadmin'): ?>
            <div class="mt-3 mb-2 border-top pt-2">
                <small class="text-uppercase text-muted fw-bold d-block" style="font-size: 0.65rem;">Reports</small>
            </div>
            <a href="laporan_servis.php"><i class="bi bi-graph-up-arrow me-2"></i> Service Report</a>
            <a href="log-activity.php"><i class="bi bi-clock-history me-2"></i> Log Activity</a>
        <?php endif; ?>
    </div>

    <div class="mt-4 border-top pt-3">
        <?php if (isset($_SESSION['username'])): ?>
            <a href="logout.php" class="btn btn-sm btn-danger w-100 text-white" style="font-size: 0.75rem;">
                <i class="bi bi-box-arrow-right"></i> Logout
            </a>
        <?php else: ?>
            <button class="btn btn-sm btn-primary w-100" data-bs-toggle="modal" data-bs-target="#loginModal" style="font-size: 0.75rem;">
                <i class="bi bi-lock"></i> Login
            </button>
        <?php endif; ?>
    </div>
</div>