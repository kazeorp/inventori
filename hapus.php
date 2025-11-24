<?php
include "koneksi.php";
include "helpers.php"; // 1. Masukkan helpers.php

$id = $_GET['id'];

$sql = "DELETE FROM inventori WHERE id='$id'";
if (mysqli_query($koneksi, $sql)) {
    // 2. Panggil fungsi untuk memicu WebSocket sebelum redirect
    pushWebSocketUpdate($id, 'delete');
    
  echo "<script>alert('Data berhasil dihapus!'); window.location='tampil.php';</script>";
} else {
  echo "Error: " . mysqli_error($koneksi);
}
?>