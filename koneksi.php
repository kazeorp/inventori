<?php

// koneksi.php
date_default_timezone_set('Asia/Jakarta');

$koneksi = mysqli_connect("localhost", "root", "", "inventori_test");

if (!defined('NODE_API_HOST')) {
    define('NODE_API_HOST', 'http://172.16.3.60:3000');
}

if (!defined('NODE_FULL_BROADCAST_URL')) {
    define('NODE_FULL_BROADCAST_URL', NODE_API_HOST . '/api/websocket/trigger');
}

if (!function_exists('logActivity')) {
    /**
     * Fungsi Log Activity: Menyimpan ke Database & Broadcast ke WebSocket
     */
    function logActivity($koneksi, $aksi, $hostname, $detail)
    {
        // 1. Pastikan Session aktif untuk mengambil data Admin
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $admin_id   = $_SESSION['id_admin'] ?? 0; // Sesuaikan dengan key session Anda
        $admin_nama = $_SESSION['nama_lengkap'] ?? 'System';
        $waktu_db   = date('Y-m-d H:i:s');

        // 2. SIMPAN KE DATABASE (MySQL)
        $aksi_safe     = mysqli_real_escape_string($koneksi, $aksi);
        $hostname_safe = mysqli_real_escape_string($koneksi, $hostname);
        $detail_safe   = mysqli_real_escape_string($koneksi, $detail);
        $admin_nama_db = mysqli_real_escape_string($koneksi, $admin_nama);

        $query = "INSERT INTO admin_log (admin_id, admin_nama_lengkap, aksi, hostname, detail, log_time)
                  VALUES ('$admin_id', '$admin_nama_db', '$aksi_safe', '$hostname_safe', '$detail_safe', '$waktu_db')";
        $db_insert = mysqli_query($koneksi, $query);

        // 3. KIRIM KE WEBSOCKET (Node.js) - Real-time Notification
        // Pastikan konstanta NODE_FULL_BROADCAST_URL sudah di-define
        if (defined('NODE_FULL_BROADCAST_URL')) {
            $payload = json_encode([
                'event'    => 'admin_activity',
                'admin'    => $admin_nama,
                'aksi'     => $aksi,
                'hostname' => $hostname,
                'detail'   => $detail,
                'waktu'    => date('H:i'),
            ]);

            $ch = curl_init(NODE_FULL_BROADCAST_URL);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_TIMEOUT, 2); // Timeout singkat agar tidak memperlambat loading aplikasi

            curl_exec($ch);
            curl_close($ch);
        }

        return $db_insert;
    }
}

/**
 * Membandingkan data lama dan baru secara otomatis berdasarkan kolom tabel
 */
function hitung_perubahan($data_lama, $data_baru)
{
    $perubahan = [];

    // Daftar kolom yang ingin diabaikan (tidak perlu masuk log jika berubah)
    $ignore_columns = ['id', 'tanggal_register', 'last_admin'];

    foreach ($data_baru as $kolom => $nilai_baru) {
        // Hanya bandingkan jika kolom ada di data lama dan tidak diabaikan
        if (array_key_exists($kolom, $data_lama) && !in_array($kolom, $ignore_columns)) {

            $nilai_lama = $data_lama[$kolom];

            // Gunakan trim untuk menghindari deteksi perubahan karena spasi kosong
            if (trim($nilai_lama) != trim($nilai_baru)) {
                $label = ucwords(str_replace('_', ' ', $kolom)); // Merapikan nama kolom (misal: serial_number -> Serial Number)
                $lama  = ($nilai_lama == "") ? "Kosong" : $nilai_lama;
                $baru  = ($nilai_baru == "") ? "Kosong" : $nilai_baru;

                $perubahan[] = "$label: '$lama' → '$baru'";
            }
        }
    }

    return implode(", ", $perubahan);
}
