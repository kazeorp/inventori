<?php
// ajax_selesaikan_service.php - VERSI FINAL DENGAN STATUS GANDA

// 1. PENGATURAN ERROR DAN HEADER
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);
header('Content-Type: application/json');

include 'session.php'; 
include "koneksi.php"; // Koneksi DB

// Periksa koneksi
if (mysqli_connect_errno()) {
    http_response_code(500); 
    echo json_encode(['success' => false, 'message' => 'Kesalahan koneksi database.']);
    exit;
}

// --- 2. VALIDASI REQUEST & PENGAMBILAN DATA POST ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(400); 
    echo json_encode(['success' => false, 'message' => 'Permintaan tidak valid.']);
    exit;
}

$id_service = (int)$_POST['id_service'] ?? 0;

// Admin yang menyelesaikan
$admin_id = $_SESSION['admin_id'] ?? 0;
$tanggal_selesai = date('Y-m-d H:i:s');
$admin_nama_lengkap = $_SESSION['nama_lengkap'] ?? 'Admin Tidak Diketahui';

if ($id_service <= 0 || $admin_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Data input tidak lengkap.']);
    exit;
}

$stmt_get_hostname = null;
$stmt_update_inventori = null;
$stmt_update_service = null;

mysqli_begin_transaction($koneksi);

try {
    // --- 3. AMBIL HOSTNAME DARI SERVICE_LIST ---
    $stmt_get_hostname = mysqli_prepare($koneksi, 
        "SELECT hostname FROM service_list WHERE id_service = ?"
    );
    mysqli_stmt_bind_param($stmt_get_hostname, 'i', $id_service);
    mysqli_stmt_execute($stmt_get_hostname);
    $result_hostname = mysqli_stmt_get_result($stmt_get_hostname);
    
    if (!$row_hostname = mysqli_fetch_assoc($result_hostname)) {
        throw new Exception("Service ID #{$id_service} tidak ditemukan.");
    }
    $hostname = $row_hostname['hostname'];
    mysqli_stmt_close($stmt_get_hostname);
    
    // --- 5. UPDATE SERVICE_LIST (PENYELESAIAN STATUS GANDA) ---
    $stmt_update_service = mysqli_prepare($koneksi, 
        "UPDATE service_list 
        SET finish_status = 'Selesai',
            admin_finish_id = ?, 
            admin_finish_name = ?,
            finish_timestamp = ?
        WHERE id_service = ? AND finish_status IS NULL" 
    );
    
    // Binding: string (date), string (ticket), string (action), string (notes), integer (id)
    mysqli_stmt_bind_param($stmt_update_service, 'issi', 
        $admin_id,               // 1. admin_finish_id (i)
        $admin_nama_lengkap,         // 2. admin_finish_name (s)
        $tanggal_selesai,        // 3. finish_timestamp (s) (Gunakan $tanggal_selesai yang sudah di-define)
        $id_service              // 4. id_service (i) (WHERE clause)
    );
    $result_update = mysqli_stmt_execute($stmt_update_service);

    if ($result_update && mysqli_stmt_affected_rows($stmt_update_service) > 0) {
        
        // --- 6. COMMIT TRANSAKSI ---
        mysqli_commit($koneksi);
        
        $msg_inventori = "Status Inventori tidak diubah, tetap pada status terakhir."; 

        echo json_encode([
            'success' => true, 
            'message' => "Servis ID #{$id_service} berhasil diselesaikan. {$msg_inventori}",
        ]);
        
    } else {
        mysqli_rollback($koneksi);
        throw new Exception("Gagal mengupdate service list. Pastikan service belum berstatus Selesai.");
    }

} catch (Exception $e) {
    mysqli_rollback($koneksi);
    $sql_error = mysqli_error($koneksi); 
    error_log("AJAX Complete Service Error: " . $e->getMessage() . " | SQL Error: " . $sql_error);
    
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan sistem saat menyelesaikan servis. Detail: ' . $e->getMessage()]); 
}

// Tutup koneksi database
mysqli_close($koneksi);
?>