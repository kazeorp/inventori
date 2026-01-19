<?php

// session.php - Pastikan ini adalah KODE PERTAMA di file
// Hapus semua baris kosong atau spasi sebelum tag pembuka <?php

// Pastikan sesi sudah dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ----------------------------------------------------
// 1. Tentukan status Role SAAT INI
// ----------------------------------------------------

$current_role = 'normal';

if (isset($_SESSION['admin_id']) && isset($_SESSION['role'])) {
    $current_role = $_SESSION['role'];
} else {
    // Tetapkan role default jika belum ada login
    $_SESSION['role'] = 'normal';
    $current_role = 'normal';
}

// HINDARI TAG PENUTUP PHP SAMA SEKALI
