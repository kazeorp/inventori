<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="icon" type="image/x-icon" href="/inventori/favicon.ico">
    <link rel="stylesheet" href="css/style.css">

</head>
<body>

<div class="logo-header p-3 bg-white border-bottom d-flex justify-content-between align-items-center">
  <div class="d-flex align-items-center">
    <!-- Sidebar Toggle (visible only on mobile) -->
    <button class="btn btn-outline-primary border-0 d-md-none me-2 p-1" id="sidebarToggle" type="button">
      <i class="bi bi-list fs-2"></i>
    </button>

    <a href="index.php">
      <img src="images/logo.jpg" alt="Logo Inventori" class="logo-img">
    </a>
  </div>

  <div class="d-flex align-items-center">
    <?php include "notifikasi.php"; ?>
  </div>
</div>

<!-- Sidebar Overlay for mobile -->
<div id="sidebarOverlay" class="d-none" style="position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.4); z-index:1140;"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btn = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.getElementById('sidebarOverlay');

    if (btn && sidebar) {
        btn.onclick = function() {
            sidebar.classList.toggle('active');
            overlay.classList.toggle('d-none');
        };
        overlay.onclick = function() {
            sidebar.classList.remove('active');
            overlay.classList.add('d-none');
        };
    }
});
</script>

<?php include "notifikasi_modal.php"; ?>