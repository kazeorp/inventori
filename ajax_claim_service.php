<?php
// ajax_claim_service.php
// HANYA MENGKLAIM DI SERVICE_LIST, TIDAK MENGUBAH STATUS INVENTORI.
// Logika perubahan status inventori dipindahkan ke handler lain (saat loan/scrap).

// --- 0. Konfigurasi Awal & Setup Lingkungan ---
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

// Memuat file-file esensial
require 'session.php';
require 'koneksi.php';
require 'helpers.php'; // Pastikan file ini ada dan berisi fungsi e()

// Debugging: Cek apakah ada error di awal sebelum memproses
if (connection_aborted() || !isset($koneksi)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Internal Error: Connection Aborted or DB not ready.']);
    exit;
}

// Validasi Koneksi dan Otentikasi Dasar
if (!isset($koneksi) || mysqli_connect_errno()) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Koneksi database gagal.']);
    exit;
}
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'superadmin')) {
    echo json_encode(['success' => false, 'message' => 'Akses ditolak. Silakan login kembali.']);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// 1. Validasi dan Ambil Data
$loan_hostname = trim($_POST['loan_hostname'] ?? '');
$id_service     = isset($_POST['id_service']) ? (int)$_POST['id_service'] : 0;
$hostname       = trim($_POST['hostname'] ?? '');
$admin_id       = $_SESSION['admin_id'] ?? 0;
$admin_username = $_SESSION['username'] ?? 'Admin Tidak Diketahui';
$admin_namalengkap = $_SESSION['nama_lengkap'] ?? 'Admin Tidak Diketahui';

if ($id_service <= 0 || empty($hostname) || $admin_id <= 0) {
      echo json_encode(['success' => false, 'message' => 'Data admin, ID Service, atau Hostname tidak valid.']);
      exit;
}

// Inisialisasi statement
$stmt_update_service = null;
$stmt_get_inventori_status = null; // Dipertahankan untuk validasi dan info status

// Mulai Transaksi
mysqli_begin_transaction($koneksi);

try {
    // 1A. VALIDASI: Pastikan Aset Ada di INVENTORI (status saat ini harus 'Assign')
    $stmt_get_inventori_status = mysqli_prepare($koneksi, "SELECT status FROM inventori WHERE hostname = ?");
    if ($stmt_get_inventori_status === false) throw new Exception("Prepare 1A Gagal: " . mysqli_error($koneksi));

    mysqli_stmt_bind_param($stmt_get_inventori_status, 's', $hostname);
    mysqli_stmt_execute($stmt_get_inventori_status);
    $result_inventori_status = mysqli_stmt_get_result($stmt_get_inventori_status);

    if (!$row_inventori_status = mysqli_fetch_assoc($result_inventori_status)) {
        throw new Exception("Aset dengan Hostname: {$hostname} tidak ditemukan di inventori.");
    }
    $current_inventori_status = $row_inventori_status['status'];
    mysqli_stmt_close($stmt_get_inventori_status);

    // --- 2. HILANGKAN LOGIKA UPDATE INVENTORI PADA TAHAP KLAIM ---
    $claim_status_val = 'On Service';

    // 3. Update service_list: set admin claim, current admin, dan claim status
    $stmt_update_service = mysqli_prepare($koneksi,
        "UPDATE service_list
        SET claim_status = ?, current_admin_id = ?, current_admin_name = ?,
        admin_claim_id = COALESCE(admin_claim_id, ?),
        admin_claim_name = COALESCE(admin_claim_name, ?),
        loan_hostname = ?,
        claim_timestamp = NOW()
        WHERE id_service = ? AND finish_status IS NULL AND claim_status IS NULL"
    );

    if ($stmt_update_service === false) {
        throw new Exception("Prepare 3 Gagal (Cek Nama Kolom di service_list!): " . mysqli_error($koneksi));
    }

    mysqli_stmt_bind_param($stmt_update_service, 'sisissi',
        $claim_status_val,          // 1. s (claim_status: String 'On Service')
        $admin_id,                  // 2. i (current_admin_id: Integer)
        $admin_namalengkap,         // 3. s (current_admin_name: String)
        $admin_id,                  // 4. i (COALESCE admin_claim_id: Integer)
        $admin_namalengkap,         // 5. s (COALESCE admin_claim_name: String)
        $loan_hostname,             // 6. s (loan_hostname: String)
        $id_service                 // 7. i (id_service: Integer, untuk WHERE clause)
    );
    $result_update = mysqli_stmt_execute($stmt_update_service);

    if ($result_update && mysqli_stmt_affected_rows($stmt_update_service) > 0) {
        // 4. Commit transaksi jika semua berhasil
        mysqli_commit($koneksi);

        echo json_encode([
            'success' => true,
            'message' => "Servis berhasil dipick up oleh {$admin_namalengkap}. Status Inventori Tetap: {$current_inventori_status}",
            'redirect_url' => "detail-aset.php?hostname=" . urlencode($hostname) . "&action=service_claim"
        ]);

        exit();

    } else {
        mysqli_rollback($koneksi);
        echo json_encode(['success' => false, 'message' => 'Gagal mempick up servis. Servis mungkin sudah dipick up atau sudah selesai.']);
    }

} catch (Exception $e) {
    // Tangani semua error SQL/Logic dalam transaksi
    mysqli_rollback($koneksi);
    error_log("AJAX Pick Up Error: " . $e->getMessage());

    $error_msg = strpos($e->getMessage(), 'SQL Prepare Gagal') !== false
                 ? "FATAL SQL ERROR (Prepare): " . $e->getMessage()
                 : $e->getMessage();

    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan sistem saat mempick up ' . $error_msg]);

} finally {
    // Tutup statement
    if ($stmt_update_service) mysqli_stmt_close($stmt_update_service);
    if ($stmt_get_inventori_status) mysqli_stmt_close($stmt_get_inventori_status);

    // Tutup koneksi database
    if ($koneksi) mysqli_close($koneksi);
}
// HINDARI TAG PENUTUP