<?php
// ajax_add_service.php - VERSI FINAL DENGAN LOGIKA DUPLIKASI BARU

// 1. PENGATURAN ERROR DAN HEADER
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);
header('Content-Type: application/json');

include 'session.php';
include "koneksi.php"; // Koneksi DB
include "helpers.php"; // <--- TAMBAH: Sertakan helper untuk fungsi WebSocket

if (mysqli_connect_errno()) {
  http_response_code(500);
  echo json_encode(['status' => 'error', 'message' => 'Kesalahan koneksi database.']);
  exit;
}

// 2. VALIDASI REQUEST & PENGAMBILAN DATA
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id_inventori'])) {
  http_response_code(400);
  echo json_encode(['status' => 'error', 'message' => 'Permintaan tidak valid.']);
  exit;
}

$id_inventori = (int)$_POST['id_inventori'];
$hostname = trim($_POST['hostname'] ?? 'N/A');

// Ambil data Nama dan Divisi dari SESSION yang dibuat oleh ajax_scan_handler.php
// Pastikan struktur sesi sesuai.
$nama_user = $_SESSION['last_scan']['nama'] ?? 'Data Tidak Tersedia';
$divisi = $_SESSION['last_scan']['divisi'] ?? 'Data Tidak Tersedia';

$tanggal_masuk = date('Y-m-d H:i:s');
$default_catatan = 'Aset masuk service via scan mandiri.';


// --- 3. CEK DUPLIKAT DIREVISI ---
// Memastikan service baru dapat dicatat hanya jika service sebelumnya SUDAH SELESAI (finish_status = 'Selesai')
$sql_check = "SELECT id_service FROM service_list
       WHERE id_inventori = ?

       -- Kunci Pengecekan Duplikasi BARU:
       -- 1. Cek jika service finish_status-nya masih NULL (aktif/on service)
       AND finish_status IS NULL

       -- 2. Memastikan claim_status adalah status aktif (belum diklaim ATAU sudah diklaim)
       AND (claim_status IS NULL OR claim_status = 'On Service')

       LIMIT 1";

if ($stmt_check = $koneksi->prepare($sql_check)) {
  $stmt_check->bind_param("i", $id_inventori);
  $stmt_check->execute();
  $stmt_check->store_result();

  if ($stmt_check->num_rows > 0) {
    $stmt_check->close();
    // Mengembalikan sukses agar kamera restart, tetapi beri status 'warning'
    echo json_encode([
      'status' => 'warning',
      'message' => "Aset {$hostname} masih memiliki servis aktif yang belum diselesaikan pada hari ini. Aksi dibatalkan."
    ]);
    $koneksi->close();
    exit;
  }
  $stmt_check->close();
}


// 4. INSERT DATA SERVICE ke tabel PERMANEN
// Kolom: id_inventori, hostname, nama_user, divisi, tanggal_masuk, catatan
$sql_insert = "INSERT INTO service_list (id_inventori, hostname, nama_user, divisi, tanggal_masuk, catatan) VALUES (?, ?, ?, ?, ?, ?)";

if ($stmt = $koneksi->prepare($sql_insert)) {
  // Binding: isss s s (integer, string, string, string, string, string)
  $stmt->bind_param("isssss",
    $id_inventori,
    $hostname,
    $nama_user,
    $divisi,
    $tanggal_masuk,
    $default_catatan
  );

    if ($stmt->execute()) {

    // --- LOGIKA RIWAYAT SCAN DARI SESI ---

    // Hapus item yang baru saja di-service dari array riwayat
        if (isset($_SESSION['scan_history']) && is_array($_SESSION['scan_history'])) {
          $hostname_to_remove = $hostname;
          $updated_history = [];

          foreach ($_SESSION['scan_history'] as $item) {
            // HANYA pertahankan item yang TIDAK SAMA
            if (isset($item['hostname']) && $item['hostname'] !== $hostname_to_remove) {
              $updated_history[] = $item;
            }
          }
          $_SESSION['scan_history'] = $updated_history;
        }

        // Hapus last_scan setelah data berhasil dimasukkan ke DB
        unset($_SESSION['last_scan']);

    echo json_encode([
      'status' => 'success',
      'message' => "Service aset {$hostname} berhasil dicatat. Admin kini dapat memprosesnya."
    ]);
  }else {
    http_response_code(500);
    echo json_encode([
      'status' => 'error',
      'message' => "Gagal eksekusi query service: " . $stmt->error
    ]);
  }

  $stmt->close();
} else {
  http_response_code(500);
  echo json_encode(['status' => 'error', 'message' => 'Gagal menyiapkan query database untuk service list.']);
}

$koneksi->close();
exit;
?>