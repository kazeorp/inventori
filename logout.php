<?php

// logout.php - Final Version
session_start(); // Wajib: Mulai sesi untuk mengakses variabel sesi

// 1. Amankan data scan terakhir (last_scan)
// Data ini perlu dipertahankan agar aset terakhir tetap tampil di index.php
$last_scan_data = $_SESSION['last_scan'] ?? null;

// 2. Hapus SEMUA variabel sesi yang terkait dengan admin/login secara spesifik
// Pastikan semua kunci yang dipakai saat login dimasukkan di sini.
unset($_SESSION['admin_id']);     // Asumsi ID admin disimpan sebagai 'admin_id'
unset($_SESSION['username']);     // Username untuk otentikasi
unset($_SESSION['role']);         // Role admin
unset($_SESSION['nama_lengkap']); // Nama lengkap admin

// 3. Optional: Jika Anda ingin menghapus semua variabel sesi selain last_scan, gunakan cara ini:
/*
$last_scan_data = $_SESSION['last_scan'] ?? null;
$_SESSION = array(); // Menghapus SEMUA variabel sesi
if ($last_scan_data) {
    $_SESSION['last_scan'] = $last_scan_data; // Kembalikan hanya data scan
}
*/

// 4. Redirect user kembali ke index.php
header("Location: index.php");
exit;
