<?php
// ajax_scan_handler.php - VERSI FINAL DENGAN PENYIMPANAN WAKTU SCAN

// 1. PENGATURAN ERROR DAN HEADER
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);
header('Content-Type: application/json');

include 'session.php'; 
include "koneksi.php"; // Koneksi DB

if (mysqli_connect_errno()) {
    http_response_code(500); 
    echo json_encode(['status' => 'error', 'message' => 'Kesalahan koneksi database.']);
    exit;
}

// 2. VALIDASI REQUEST & PENGAMBILAN DATA
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['hostname'])) {
    http_response_code(400); 
    echo json_encode(['status' => 'error', 'message' => 'Permintaan tidak valid.']);
    exit;
}

$hostname = trim($_POST['hostname'] ?? '');
if (empty($hostname)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Hostname tidak boleh kosong.']);
    exit;
}

// 3. QUERY DATA ASET DARI TABEL INVENTORI
// Menggunakan prepared statement untuk keamanan
$sql_aset = "SELECT id, hostname, nama, divisi, status FROM inventori WHERE hostname = ? LIMIT 1";

if ($stmt = $koneksi->prepare($sql_aset)) {
    $stmt->bind_param("s", $hostname);
    $stmt->execute();
    $result_aset = $stmt->get_result();
    
    if ($result_aset->num_rows > 0) {
        $aset_data = $result_aset->fetch_assoc();
        $stmt->close();

        // --- SIMPAN DATA DAN WAKTU SCAN KE SESSION ---
        $current_scan_time = date('Y-m-d H:i:s');
        
        $_SESSION['last_scan'] = [
            'hostname'   => $aset_data['hostname'],
            'nama'       => $aset_data['nama'],
            'divisi'     => $aset_data['divisi'],
            'id_inventori' => $aset_data['id'], // Gunakan id_inventori untuk add_service.php
            'waktu_scan' => $current_scan_time // <-- KUNCI TAMBAHAN
        ];
        
        // Bersihkan pesan error scan sebelumnya jika ada
        unset($_SESSION['scan_error']);

        echo json_encode([
            'status'     => 'success',
            'hostname'   => $aset_data['hostname'],
            'nama'       => $aset_data['nama'],
            'divisi'     => $aset_data['divisi'],
            'id_aset'    => $aset_data['id'],
            'waktu_scan' => $current_scan_time
        ]);

    } else {
        $stmt->close();
        // Aset tidak ditemukan
        $_SESSION['scan_error'] = "Aset dengan Hostname '{$hostname}' TIDAK DITEMUKAN.";
        unset($_SESSION['last_scan']);
        
        echo json_encode([
            'status' => 'error',
            'message' => "Aset '{$hostname}' TIDAK DITEMUKAN dalam database."
        ]);
    }
} else {
    // Gagal menyiapkan query
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Gagal menyiapkan query database.']);
}

$koneksi->close();
exit;
?>