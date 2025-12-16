<?php
// Pastikan session sudah dimulai sebelum include koneksi
include "session.php";
include "koneksi.php";
include "helpers.php";
//  PASTIKAN ANDA JUGA INCLUDE FILE SESSION UNTUK MENGAMBIL USERNAME

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // ----------------------------------------------------
    // 1. AMBIL NAMA ADMIN DARI SESSION
    // ----------------------------------------------------
    $admin_name = $_SESSION['nama_lengkap'] ?? 'System'; // Ambil username, default 'System'
    $admin_name_safe = mysqli_real_escape_string($koneksi, $admin_name);

    // ----------------------------------------------------
    // 2. AMBIL DAN ESCAPE DATA FORM
    // ----------------------------------------------------
    $id             = $_POST['id'];
    $rak            = mysqli_real_escape_string($koneksi, $_POST['rak']);
    $status         = mysqli_real_escape_string($koneksi, $_POST['status']);
    $hostname       = mysqli_real_escape_string($koneksi, trim($_POST['hostname']));
    $type           = mysqli_real_escape_string($koneksi, $_POST['type']);
    $serial_number  = strtoupper(mysqli_real_escape_string($koneksi, $_POST['serial_number'] ?? ''));
    $ram            = mysqli_real_escape_string($koneksi, $_POST['ram']);
    $storage        = mysqli_real_escape_string($koneksi, $_POST['storage']);
    $win            = mysqli_real_escape_string($koneksi, $_POST['win']);
    $keterangan     = mysqli_real_escape_string($koneksi, $_POST['keterangan']);
    $kelengkapan    = mysqli_real_escape_string($koneksi, $_POST['kelengkapan']);
    $tanggal_masuk  = mysqli_real_escape_string($koneksi, $_POST['tanggal_masuk']);
    $tanggal_keluar = mysqli_real_escape_string($koneksi, $_POST['tanggal_keluar']);
    $nik            = mysqli_real_escape_string($koneksi, $_POST['nik']);
    $nama           = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $divisi         = mysqli_real_escape_string($koneksi, $_POST['divisi']);

    // ----------------------------------------------------
    // 3. CEK DUPLIKAT HOSTNAME
    // ----------------------------------------------------
    $cek_duplikat = mysqli_query($koneksi, "SELECT id FROM inventori WHERE hostname='$hostname' AND id != '$id'");
    if (mysqli_num_rows($cek_duplikat) > 0) {
        echo "<script>alert(' Hostname sudah digunakan oleh data lain. Silakan gunakan hostname yang unik.'); window.location='tampil.php';</script>";
        exit;
    }

    // ----------------------------------------------------
    // 4. LANJUTKAN PROSES UPDATE
    // ----------------------------------------------------
    $sql = "UPDATE inventori SET
        rak='$rak', status='$status', hostname='$hostname', type='$type',serial_number='$serial_number',
        ram='$ram', storage='$storage', win='$win', keterangan='$keterangan',
        kelengkapan='$kelengkapan', tanggal_masuk = '$tanggal_masuk', tanggal_keluar='$tanggal_keluar',
        nik='$nik', nama='$nama', divisi='$divisi',
        last_admin='$admin_name_safe'
        WHERE id='$id'";

    if (mysqli_query($koneksi, $sql)) {
        // Panggil fungsi untuk memicu WebSocket
        pushWebSocketUpdate($id, 'asset_update');

        echo "<script>alert('Data berhasil diupdate!'); window.location='tampil.php';</script>";
    } else {
        echo "<script>alert('Gagal mengupdate data: " . mysqli_error($koneksi) . "'); window.location='tampil.php';</script>";
    }
}
?>