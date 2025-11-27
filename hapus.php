<?php
session_start(); // Tambahkan session_start jika belum ada di atas
include "koneksi.php";
include "helpers.php";

// SARAN: KEAMANAN - Cegah user normal menghapus data
if (!isset($_SESSION['role']) || $_SESSION['role'] == 'normal') {
echo "<script>alert('Anda tidak memiliki akses untuk menghapus data.'); window.location='tampil.php';</script>";
exit;
}

$id = mysqli_real_escape_string($koneksi, $_GET['id']); // Sanitasi ID

$sql = "DELETE FROM inventori WHERE id='$id'";
if (mysqli_query($koneksi, $sql)) {
  // 2. Panggil fungsi untuk memicu WebSocket
  // GANTI 'delete' menjadi 'asset_delete'
  pushWebSocketUpdate($id, 'asset_delete');

 echo "<script>alert('Data berhasil dihapus!'); window.location='tampil.php';</script>";
} else {
 echo "Error: " . mysqli_error($koneksi);
}
?>