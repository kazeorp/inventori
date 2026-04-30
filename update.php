<?php

// Pastikan session start ada di paling atas agar $_SESSION['nama_lengkap'] terbaca
session_start();

include "koneksi.php";
include "helpers.php";

// --- 1. AMBIL DATA INVENTORI (Untuk mendapatkan data lama) ---
// Gunakan POST id jika sedang melakukan update, atau GET id jika baru memuat halaman
$id_for_query = $_POST['id'] ?? $_GET['id'] ?? '';

if (empty($id_for_query)) {
    die("ID data tidak ditemukan!");
}

$id_safe = mysqli_real_escape_string($koneksi, $id_for_query);
$data = mysqli_query($koneksi, "SELECT * FROM inventori WHERE id='$id_safe'");
$row = mysqli_fetch_assoc($data);

if (!$row) {
    die("Data inventori tidak ditemukan di database!");
}

// --- 2. LOGIKA UPDATE DATA ---
// Ubah pengecekan agar tidak bergantung pada name tombol saja
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Simpan DATA LAMA sebelum diupdate
    $data_lama = $row;

    // Ambil dan sanitasi semua data input (HURUF KAPITAL)
    $id_update       = mysqli_real_escape_string($koneksi, $_POST['id']);
    $warna           = mysqli_real_escape_string($koneksi, $_POST['warna'] ?? '');
    $status          = mysqli_real_escape_string($koneksi, $_POST['status'] ?? '');
    $domain          = strtoupper(mysqli_real_escape_string($koneksi, $_POST['domain'] ?? ''));
    $device_category = strtoupper(mysqli_real_escape_string($koneksi, $_POST['device_category'] ?? ''));
    $hostname        = strtoupper(mysqli_real_escape_string($koneksi, $_POST['hostname'] ?? ''));
    $type            = strtoupper(mysqli_real_escape_string($koneksi, $_POST['type'] ?? ''));
    $serial_number   = strtoupper(mysqli_real_escape_string($koneksi, $_POST['serial_number'] ?? ''));
    $ram             = strtoupper(mysqli_real_escape_string($koneksi, $_POST['ram'] ?? ''));
    $storage         = strtoupper(mysqli_real_escape_string($koneksi, $_POST['storage'] ?? ''));
    $win             = strtoupper(mysqli_real_escape_string($koneksi, $_POST['win'] ?? ''));
    $keterangan      = strtoupper(mysqli_real_escape_string($koneksi, $_POST['keterangan'] ?? ''));
    $kelengkapan     = strtoupper(mysqli_real_escape_string($koneksi, $_POST['kelengkapan'] ?? ''));
    $tanggal_masuk  = mysqli_real_escape_string($koneksi, $_POST['tanggal_masuk']);
    $tanggal_keluar  = mysqli_real_escape_string($koneksi, $_POST['tanggal_keluar']);
    $nik             = strtoupper(mysqli_real_escape_string($koneksi, $_POST['nik'] ?? ''));
    $nama            = strtoupper(mysqli_real_escape_string($koneksi, $_POST['nama'] ?? ''));
    $divisi          = strtoupper(mysqli_real_escape_string($koneksi, $_POST['divisi'] ?? ''));

    // Gabungkan data baru untuk dibandingkan
    $data_baru = [
        'warna' => $warna, 'status' => $status, 'hostname' => $hostname, 'type' => $type,
        'domain' => $domain, 'device_category' => $device_category, 'serial_number' => $serial_number,
        'ram' => $ram, 'storage' => $storage, 'win' => $win, 'keterangan' => $keterangan,
        'kelengkapan' => $kelengkapan, 'tanggal_masuk' => $tanggal_masuk, 'tanggal_keluar' => $tanggal_keluar,
        'nik' => $nik, 'nama' => $nama, 'divisi' => $divisi,
    ];

    // Bandingkan perubahan
    $detail_perubahan = hitung_perubahan($data_lama, $data_baru);

    // SQL UPDATE
    $sql = "UPDATE inventori SET
      warna='$warna', status='$status', hostname='$hostname', type='$type',
      domain='$domain', device_category='$device_category', serial_number='$serial_number',
      ram='$ram', storage='$storage', win='$win', keterangan='$keterangan',
      kelengkapan='$kelengkapan', tanggal_keluar='$tanggal_keluar',
      nik='$nik', nama='$nama', divisi='$divisi',
      last_admin='" . ($_SESSION['nama_lengkap'] ?? 'SYSTEM') . "'
      WHERE id='$id_update'";

    if (mysqli_query($koneksi, $sql)) {
        // TULIS LOG HANYA JIKA ADA PERUBAHAN
        if (!empty($detail_perubahan)) {
            logActivity($koneksi, "UPDATE ASSET", $hostname, "MENGUBAH DATA: $detail_perubahan");
        }

        // WebSocket
        if (function_exists('pushWebSocketUpdate')) {
            pushWebSocketUpdate($id_update, 'asset_update');
        }

        echo "<script>alert('Data berhasil diupdate!'); window.location='tampil.php';</script>";
    } else {
        echo "Error: " . mysqli_error($koneksi);
    }
}
