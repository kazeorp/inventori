<?php
session_start();
include "koneksi.php";
include "helpers.php";

// 1. KEAMANAN - Gunakan Redirect, bukan Alert untuk proteksi role
if (!isset($_SESSION['role']) || $_SESSION['role'] == 'normal') {
    $msg = urlencode("Anda tidak memiliki akses untuk menghapus data.");
    header("Location: tampil.php?res=danger&msg=$msg");
    exit;
}

// 2. Sanitasi ID
$id = mysqli_real_escape_string($koneksi, $_GET['id']);

$sql = "DELETE FROM inventori WHERE id='$id'";

if (mysqli_query($koneksi, $sql)) {
    // Pemicu WebSocket
    pushWebSocketUpdate($id, 'asset_delete');

    // 3. GANTI ALERT DENGAN REDIRECT KE TAMPIL.PHP
    $msg = urlencode("Data inventori berhasil dihapus!");
    header("Location: tampil.php?res=success&msg=$msg");
    exit;
} else {
    // Jika gagal, kirim pesan error ke toast
    $error = urlencode("Gagal menghapus data: " . mysqli_error($koneksi));
    header("Location: tampil.php?res=danger&msg=$error");
    exit;
}
?>