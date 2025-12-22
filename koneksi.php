<?php
// koneksi.php
date_default_timezone_set('Asia/Jakarta');

$koneksi = mysqli_connect("localhost","root","","inventori_test");

if (!defined('NODE_API_HOST')) {
    define('NODE_API_HOST', 'http://172.16.3.60:3000');
}

if (!defined('NODE_FULL_BROADCAST_URL')) {
    define('NODE_FULL_BROADCAST_URL', NODE_API_HOST . '/api/websocket/trigger');
}

// BUNGKUS DENGAN IF AGAR TIDAK DOUBLE DECLARE
if (!function_exists('logActivity')) {
    /**
     * Fungsi Log Activity + Real-time Notification via WebSocket
     */
    function logActivity($koneksi, $aksi, $hostname, $detail) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $admin_id   = $_SESSION['admin_id'] ?? 0;
        $admin_nama = $_SESSION['nama_lengkap'] ?? 'System';
        $waktu      = date('Y-m-d H:i:s');

        // 1. SIMPAN KE DATABASE (MySQL)
        $aksi_safe     = mysqli_real_escape_string($koneksi, $aksi);
        $hostname_safe = mysqli_real_escape_string($koneksi, $hostname);
        $detail_safe   = mysqli_real_escape_string($koneksi, $detail);
        $admin_nama_db = mysqli_real_escape_string($koneksi, $admin_nama);

        $query = "INSERT INTO admin_log (admin_id, admin_nama_lengkap, aksi, hostname, detail, log_time)
                  VALUES ('$admin_id', '$admin_nama_db', '$aksi_safe', '$hostname_safe', '$detail_safe', '$waktu')";
        $db_insert = mysqli_query($koneksi, $query);

        // 2. KIRIM KE WEBSOCKET (Node.js)
        $payload = json_encode([
            'event'    => 'admin_activity',
            'admin'    => $admin_nama,
            'aksi'     => $aksi,
            'hostname' => $hostname,
            'detail'   => $detail,
            'waktu'    => date('H:i')
        ]);

        $ch = curl_init(NODE_FULL_BROADCAST_URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 2);

        curl_exec($ch);
        curl_close($ch);

        return $db_insert;
    }
}