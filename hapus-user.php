<?php
include 'session.php';
include 'koneksi.php';

if ($_SESSION['role'] !== 'superadmin') {
  echo "<div class='main-content'><div class='alert alert-danger'>Akses ditolak!</div></div>";
  exit;
}

$id = $_GET['id'];
$user = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM admin WHERE id='$id'"));

if ($user['role'] === 'superadmin') {
  echo "<script>alert('Akun superadmin tidak bisa dihapus!'); window.location='kelola-user.php';</script>";
  exit;
}

mysqli_query($koneksi, "DELETE FROM admin WHERE id='$id'");
header("Location: kelola-user.php");
exit;
