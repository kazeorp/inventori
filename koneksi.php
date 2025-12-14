<?php
// koneksi.php
date_default_timezone_set('Asia/Jakarta');

// Menggunakan mysqli_connect yang procedural (koneksi akan ada di variabel $koneksi)
// Jika gagal, $koneksi akan berisi objek error, yang akan dicek di handler.
$koneksi = mysqli_connect("localhost","root","","inventori_test1");

// Pastikan NODE_API_HOST didefinisikan HANYA SEKALI
if (!defined('NODE_API_HOST')) {
    define('NODE_API_HOST', 'http://172.16.3.60:3000');
}

// Pastikan NODE_FULL_BROADCAST_URL didefinisikan HANYA SEKALI
if (!defined('NODE_FULL_BROADCAST_URL')) {
    define('NODE_FULL_BROADCAST_URL', NODE_API_HOST . '/api/websocket/trigger');
}

// HINDARI TAG PENUTUP