<?php
// helpers.php

// Fungsi untuk keamanan (mencegah XSS)
if (!function_exists('e')) {
  function e($text) {
    return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
  }
}

## FUNGSI BARU UNTUK WEBSOCKETS
if (!function_exists('pushWebSocketUpdate')) {
    /**
     * Mengirim sinyal pembaruan ke Server WebSocket Node.js.
     * * @param int $id ID aset yang diubah.
     * @param string $action 'insert', 'update', atau 'delete'.
     */
    function pushWebSocketUpdate($id, $action) {
        // !!! PENTING: GANTI DENGAN IP STATIS SERVER LAN ANDA jika PC lain tidak bisa mengakses 'localhost' !!!
        // Contoh: $url = 'http://192.168.1.100:3000/push-update';
        $url = 'http://localhost:3000/push-update';

        $data = [
            'id' => $id,
            'action' => $action
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Timeout yang sangat pendek (100ms) untuk memastikan PHP tidak menunggu respons
        // dari Node.js (non-blocking) agar pengalaman user tetap cepat.
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, 100);

        // Jalankan cURL (Non-blocking: PHP tidak akan terhambat)
        curl_exec($ch);

        // Tutup koneksi cURL
        curl_close($ch);
    }
}
// HINDARI TAG PENUTUP