<?php

// redirect.php

// 1. PASTIKAN SESI DIMULAI
include 'session.php'; // Anggap session.php berisi session_start()

// Pengecekan keamanan dasar (biasanya sudah ada di session.php)
if (!isset($_SESSION['role'])) {
    header("index.php");
    exit;
}

$user_role = $_SESSION['role'];

// 2. LOGIKA PENGARAHAN BERDASARKAN ROLE
if ($user_role === 'normal') {
    header("Location: index.php");
    exit;
} elseif ($user_role === 'admin' || $user_role === 'superadmin') {
    header("Location: index2.php");
    exit;
}

// 3. JIKA ROLE TIDAK JELAS (PENGAMAN)
header("Location: index.php");
exit;
