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

        // --- REVISI: SIMPAN DATA SCAN KE ARRAY RIWAYAT (scan_history) ---
        $current_scan_time = date('Y-m-d H:i:s');
        
        $current_scan_data = [
            'hostname'     => $aset_data['hostname'],
            'nama'         => $aset_data['nama'],
            'divisi'       => $aset_data['divisi'],
            'id_inventori' => $aset_data['id'],
            'waktu_scan'   => $current_scan_time
        ];

        // PENTING A: Simpan data ke LAST_SCAN (untuk di-POST oleh Add Service)
        $_SESSION['last_scan'] = $current_scan_data;

        // 1. Ambil atau inisialisasi array riwayat
        if (!isset($_SESSION['scan_history'])) {
            $_SESSION['scan_history'] = [];
        }
        
        // 2. Tambahkan data scan terbaru ke awal array (unshift)
        array_unshift($_SESSION['scan_history'], $current_scan_data);

        // 3. Batasi riwayat maksimal 10 entri (opsional: 10 data, untuk performa)
        if (count($_SESSION['scan_history']) > 10) {
            $_SESSION['scan_history'] = array_slice($_SESSION['scan_history'], 0, 10);
        }
        // --- AKHIR REVISI SESSION ---

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