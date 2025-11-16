<?php
// WAJIB: Memuat session untuk role dan otentikasi sidebar
include 'session.php'; 
include "koneksi.php";
include "phpqrcode/qrlib.php";

// Ambil role dari session untuk digunakan di sidebar
$role = isset($_SESSION['role']) ? $_SESSION['role'] : 'normal';

// Pastikan folder qrcodes/ tersedia
$qrFolder = 'qrcodes/';
if (!is_dir($qrFolder)) {
    // Memberi izin 0755
    if (!mkdir($qrFolder, 0755, true)) {
        // Handle error jika mkdir gagal
        // Contoh: die('Gagal membuat folder QR code.'); 
    }
}

// Generate QR jika diminta
if (isset($_GET['generate'])) {
    $id = intval($_GET['generate']);
    // Menggunakan prepared statement (atau minimal mysqli_real_escape_string) untuk keamanan
    $id_safe = mysqli_real_escape_string($koneksi, $id); 
    
    $query = mysqli_query($koneksi, "SELECT hostname FROM inventori WHERE id = $id_safe");
    
    if ($data = mysqli_fetch_assoc($query)) {
        $hostname = $data['hostname'];
        $filename = $qrFolder . $hostname . ".png";
        
        // Data yang dimasukkan ke QR adalah hostname
        QRcode::png($hostname, $filename, QR_ECLEVEL_L, 4);
    }
    
    // REDIRECT setelah generate selesai untuk menghindari refresh dan regenerate
    header("Location: generate-qr.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>QR Code Hostname</title>
  <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
  <!-- MEMUAT STYLE EKSTERNAL (style.css) UNTUK TAMPILAN SIDEBAR YANG KONSISTEN -->
  <link rel="stylesheet" href="css/style.css"> 
  
  <style>
    /* Styling tambahan hanya untuk halaman ini */
    .table img {
      border: 1px solid #ccc;
      padding: 4px;
      background-color: #fff;
      border-radius: 4px;
    }
    .btn-primary {
      font-weight: 500;
    }
    /* CATATAN: Semua CSS untuk .sidebar, .logo-header, dan .main-content telah dihapus 
       dari sini dan dipindahkan ke style.css agar seragam. */
  </style>
</head>
<body>

  <?php include 'header.php'; ?>
  <?php include 'sidebar.php'; ?>

  <main class="main-content">
    <h2 class="mb-4">QR Code Hostname Inventori</h2>
    <div class="table-responsive">
      <table class="table table-bordered table-hover bg-white">
        <thead class="table-dark">
          <tr>
            <th>Hostname</th>
            <th>Rak</th>
            <th>Status</th>
            <th>Type</th>
            <th>Generate</th>
            <th>QR Code</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $result = mysqli_query($koneksi, "SELECT id, hostname, rak, status, type FROM inventori");
          while ($row = mysqli_fetch_assoc($result)) {
              $id = $row['id'];
              // Menggunakan htmlspecialchars() untuk semua output demi keamanan
              $hostname = htmlspecialchars($row['hostname'] ?? '');
              $rak      = htmlspecialchars($row['rak'] ?? '');
              $status   = htmlspecialchars($row['status'] ?? '');
              $type     = htmlspecialchars($row['type'] ?? '');
              
              $filename = $qrFolder . $row['hostname'] . ".png"; // Nama file QR menggunakan nilai mentah hostname
              
              echo "<tr>";
              echo "<td>$hostname</td>";
              echo "<td>$rak</td>";
              echo "<td>$status</td>";
              echo "<td>$type</td>";
              echo "<td><a href='generate-qr.php?generate=$id' class='btn btn-sm btn-primary'>🔖 Generate QR</a></td>";
              echo "<td>";
              if (file_exists($filename)) {
                  echo "<img src='$filename' width='100' alt='QR Code untuk $hostname'>";
              } else {
                  echo "-";
              }
              echo "</td>";
              echo "</tr>";
          }
          ?>
        </tbody>
      </table>
    </div>
  </main>

  <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
