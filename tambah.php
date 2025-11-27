<?php
session_start();
include 'koneksi.php';
include 'helpers.php'; // 1. Masukkan helpers.php untuk mendapatkan fungsi WebSocket

// Cegah akses user normal
if (isset($_SESSION['role']) && $_SESSION['role'] == 'normal') {
 echo "<script>alert('Anda tidak memiliki akses untuk menambah data.'); window.location='kelola-aset.php';</script>";
 exit;
}

// Proses jika form dikirim
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
 // Ambil dan sanitasi data
 $rak       = mysqli_real_escape_string($koneksi, $_POST['rak'] ?? '');
 $status      = mysqli_real_escape_string($koneksi, $_POST['status'] ?? '');
 $type       = mysqli_real_escape_string($koneksi, $_POST['type'] ?? '');
 $ram       = mysqli_real_escape_string($koneksi, $_POST['ram'] ?? '');
 $storage     = strtoupper(mysqli_real_escape_string($koneksi, $_POST['storage'] ?? ''));
 $win       = strtoupper(mysqli_real_escape_string($koneksi, $_POST['win'] ?? ''));
 $keterangan    = mysqli_real_escape_string($koneksi, $_POST['keterangan'] ?? '');
 $kelengkapan   = strtoupper(mysqli_real_escape_string($koneksi, $_POST['kelengkapan'] ?? ''));
 $tanggal_masuk  = mysqli_real_escape_string($koneksi, $_POST['tanggal_masuk'] ?? '');
 $tanggal_keluar  = mysqli_real_escape_string($koneksi, $_POST['tanggal_keluar'] ?? '');
 $nik       = mysqli_real_escape_string($koneksi, $_POST['nik'] ?? '');
 $nama       = strtoupper(mysqli_real_escape_string($koneksi, $_POST['nama'] ?? ''));
 $divisi      = strtoupper(mysqli_real_escape_string($koneksi, $_POST['divisi'] ?? ''));

 //  DATA BARU: Ambil Domain dan Kategori Perangkat
 $domain      = mysqli_real_escape_string($koneksi, $_POST['domain'] ?? '');
 $device_category = mysqli_real_escape_string($koneksi, $_POST['device_category'] ?? '');

 //  KONVERSI HOSTNAME KE UPPERCASE
 $hostname = strtoupper(mysqli_real_escape_string($koneksi, $_POST['hostname'] ?? ''));

 //  Cek apakah hostname sudah digunakan (gunakan $hostname yang sudah UPPERCASE)
 $cek_duplikat = mysqli_query($koneksi, "SELECT id FROM inventori WHERE hostname='$hostname'");
 if (mysqli_num_rows($cek_duplikat) > 0) {
  echo "<script>alert(' Hostname sudah ada.'); window.location='tampil.php';</script>";
  exit;
 }

 // Query simpan
 $sql = "INSERT INTO inventori (
  rak, status, hostname, type, domain, device_category, ram, storage, win, keterangan, kelengkapan, tanggal_masuk, tanggal_keluar, nik, nama, divisi
 ) VALUES (
  '$rak', '$status', '$hostname', '$type', '$domain', '$device_category', '$ram', '$storage', '$win', '$keterangan', '$kelengkapan','$tanggal_masuk', '$tanggal_keluar', '$nik', '$nama', '$divisi'
 )";

 // Eksekusi dan feedback
 if (mysqli_query($koneksi, $sql)) {
    // 2. Dapatkan ID yang baru dimasukkan
    $new_id = mysqli_insert_id($koneksi);

    // 3. Panggil fungsi untuk memicu WebSocket
    pushWebSocketUpdate($new_id, 'asset_insert');

  echo "<script>alert('Data berhasil ditambahkan!'); window.location='tampil.php';</script>";
 } else {
  echo "<script>alert('Gagal menambahkan data: " . mysqli_error($koneksi) . "'); window.location='tampil.php';</script>";
 }
}
?>