<?php
// PASTIKAN session_start() di sini agar role dapat dibaca
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'modal-login.php';

// Tentukan role: Ambil dari SESSION, jika tidak ada, gunakan 'normal'.
$role = isset($_SESSION['role']) ? $_SESSION['role'] : 'normal';

// MENGAMBIL NAMA LENGKAP: Fallback ke Username jika nama_lengkap tidak ada.
$display_name = $_SESSION['nama_lengkap'] ?? ($_SESSION['username'] ?? 'Guest');
?>

<div class="sidebar">
  <div class="mb-4 border-bottom pb-2">
    <strong><?= htmlspecialchars($display_name) ?></strong><br>
    <span class="badge bg-secondary"><?= ucfirst($role) ?></span>
  </div>

  <a href="index.php">📊 Dashboard</a>


  <?php if ($role !== 'normal'): ?>
    <a href="tampil.php">📦 Inventory Data</a>
    <a href="peripherals.php">📦 Stock Peripheral</a>

  <?php endif; ?>

  <?php if ($role === 'admin' || $role === 'superadmin'): ?>
    <a href="detail-aset.php">📋 Asset Detail</a>
  <?php endif; ?>

  <?php if ($role === 'superadmin'): ?>
    <a href="laporan_servis.php">📈 Service Report</a>
    <a href="kelola-user.php">👥 Manage Admin</a>
  <?php endif; ?>

  <?php if (isset($_SESSION['username'])): ?>
    <a href="logout.php" class="btn btn-sm btn-outline-light mt-3 w-100">🚪 Logout</a>
  <?php else: ?>
    <button class="btn btn-sm btn-outline-light mt-3 w-100" data-bs-toggle="modal" data-bs-target="#loginModal">🔐 Login</button>
  <?php endif; ?>
</div>

<style>
  /* Styling CSS Anda yang dipertahankan */
  .sidebar {
    position: fixed;
    top: 120px;
    left: 0;
    width: 220px;
    height: calc(100vh - 100px);
    background-color: #343a40;
    color: white;
    padding: 20px;
    overflow-y: auto; /* Mempertahankan styling scroll/auto Anda */
    z-index: 1000;
  }

  .sidebar a {
    color: white;
    text-decoration: none;
    display: block;
    margin-bottom: 12px;
    font-weight: 500;
    font-size: 16px;
  }

  .sidebar a:hover {
    text-decoration: none;
    background-color: #495057;
    padding: 6px;
    border-radius: 4px;
  }
</style>

<script src="bootstrap/js/bootstrap.bundle.min.js"></script>