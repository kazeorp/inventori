<?php

// WAJIB: Memulai sesi
session_start();
include 'koneksi.php';

// Sanitize input
$username = mysqli_real_escape_string($koneksi, $_POST['username']);
$password = $_POST['password'];

// Cek 1: Validasi input kosong
if (empty($username) || empty($password)) {
    // Redirect with a clear message using the toast system parameters
    header("Location: index.php?res=danger&msg=" . urlencode("Username dan Password wajib diisi!"));
    exit;
}

// Cek 2: Cari user berdasarkan username
$query = mysqli_query($koneksi, "SELECT * FROM admin WHERE username='$username'");
if (!$query || mysqli_num_rows($query) === 0) {
    header("Location: index.php?res=danger&msg=" . urlencode("Akun tidak ditemukan. Silakan hubungi Superadmin."));
    exit;
}

$user = mysqli_fetch_assoc($query);

// Cek 3: Verifikasi password
if (password_verify($password, $user['password'])) {
    // LOGIN SUKSES
    $_SESSION['admin_id']  = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role']     = $user['role']; // Role sudah ter-set dengan benar
    $_SESSION['nama_lengkap'] = $user['nama_lengkap'];

    // Redirect ke Dashboard (index.php)
    header("Location: index.php");
    exit;
} else {
    // Password salah
    header("Location: index.php?res=danger&msg=" . urlencode("Password yang Anda masukkan salah."));
    exit;
}
