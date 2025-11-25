<?php
// ajax_reassign_service.php - DIREVISI FINAL

include 'session.php';
include 'koneksi.php';
include 'helpers.php'; // <--- TAMBAH: Sertakan helper untuk fungsi WebSocket

// Pengaturan Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

// --- 0. Proteksi Akses & Inisialisasi Data ---
$current_admin_login_name = $_SESSION['nama_lengkap'] ?? 'System';
$current_admin_role = $_SESSION['role'] ?? 'normal';

if ($current_admin_role !== 'superadmin' && $current_admin_role !== 'admin') {
  // Saya sarankan admin juga bisa melakukan reassign, sesuaikan jika hanya superadmin
  echo json_encode(['success' => false, 'message' => 'Akses Reassign ditolak. Anda bukan Superadmin/Admin.']);
  exit;
}

// 1. Ambil dan Bersihkan Data Input
$id_service = isset($_POST['service_id']) ? (int)$_POST['service_id'] : 0;
$new_admin_id = isset($_POST['new_admin_id']) ? (int)$_POST['new_admin_id'] : 0;

if ($id_service <= 0 || $new_admin_id <= 0) {
  echo json_encode(['success' => false, 'message' => 'Data ID Service atau ID Admin baru tidak valid.']);
  exit;
}

// Mulai Transaksi untuk memastikan atomisitas
$koneksi->begin_transaction();

try {
  // 2. Ambil Nama Admin Baru (dan Cek Keberadaan Admin)
  $stmt_admin = $koneksi->prepare("SELECT nama_lengkap FROM admin WHERE id = ?"); // Menggunakan nama_lengkap untuk log
  if (!$stmt_admin) throw new Exception("Prepare Admin Gagal: " . $koneksi->error);

  $stmt_admin->bind_param('i', $new_admin_id);
  $stmt_admin->execute();
  $result_admin = $stmt_admin->get_result();

  $new_admin_name = 'Admin Baru (ID ' . $new_admin_id . ')';
  if ($row_admin = $result_admin->fetch_assoc()) {
    $new_admin_name = $row_admin['nama_lengkap']; // Pastikan ini adalah nama yang digunakan di service_list
  } else {
    throw new Exception("Admin baru tidak ditemukan di database.");
  }
  $stmt_admin->close();

  // 3. Update service_list
  $stmt_update = $koneksi->prepare(
    "UPDATE service_list
    SET current_admin_id = ?, current_admin_name = ?
    WHERE id_service = ?"
  );
  if (!$stmt_update) throw new Exception("Prepare Update Service Gagal: " . $koneksi->error);

  $stmt_update->bind_param('isi', $new_admin_id, $new_admin_name, $id_service);
  $result = $stmt_update->execute();

  if (!$result) {
    throw new Exception("Eksekusi Update Service Gagal: " . $stmt_update->error);
  }

  if ($stmt_update->affected_rows === 0) {
    // Jika affected_rows 0, mungkin ID Service tidak ada atau Admin_ID-nya sudah sama
    $stmt_update->close();
    $koneksi->commit(); // Tetap commit jika tidak ada perubahan, tapi kirim pesan yang sesuai
    echo json_encode(['success' => false, 'message' => 'Reassign tidak diperlukan. PIC sudah sama atau ID Service tidak aktif.']);
    exit;
  }
  $stmt_update->close();

  // --- 4. Ambil ID Inventori untuk Log Histori ---
  $stmt_get_inventori_id = $koneksi->prepare("SELECT id_inventori FROM service_list WHERE id_service = ?");
  if (!$stmt_get_inventori_id) throw new Exception("Prepare Get Inventori ID Gagal: " . $koneksi->error);

  $stmt_get_inventori_id->bind_param('i', $id_service);
  $stmt_get_inventori_id->execute();
  $result_inventori = $stmt_get_inventori_id->get_result();

  if (!$inventori_data = $result_inventori->fetch_assoc()) {
    throw new Exception("ID Inventori tidak ditemukan untuk Service ID ini.");
  }
  $id_inventori = $inventori_data['id_inventori'];
  $stmt_get_inventori_id->close();


  // --- 5. Catat ke Histori Aset ---
  $aksi = "Reassign Service";
  $catatan = "Servis ID #{$id_service} di-reassign ke PIC baru: {$new_admin_name}. (Dilakukan oleh: {$current_admin_login_name})";
  $tanggal = date("Y-m-d H:i:s");

  $stmt_log = $koneksi->prepare("INSERT INTO histori_aset (inventori_id, tanggal, aksi, oleh, catatan)
                 VALUES (?, ?, ?, ?, ?)");
  if (!$stmt_log) throw new Exception("Prepare Log Histori Gagal: " . $koneksi->error);

  $stmt_log->bind_param("isiss", $id_inventori, $tanggal, $aksi, $current_admin_login_name, $catatan);

  if (!$stmt_log->execute()) {
    throw new Exception("Eksekusi Log Histori Gagal: " . $stmt_log->error);
  }
  $stmt_log->close();

    // --- 6. Pemicuan WebSocket (Setelah Update Service dan Log Histori berhasil) ---
    pushWebSocketUpdate($id_service, 'update', 'service_list');


  // Semua berhasil, commit transaksi
  $koneksi->commit();
  echo json_encode([
    'success' => true,
    'message' => "Servis ID #{$id_service} berhasil di-reassign ke {$new_admin_name}.",
    'new_name' => $new_admin_name
  ]);

} catch (Exception $e) {
  // Jika ada error, rollback transaksi
  $koneksi->rollback();
  echo json_encode(['success' => false, 'message' => "Reassign Gagal! Error: " . $e->getMessage()]);
}

$koneksi->close();
?>