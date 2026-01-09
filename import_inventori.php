<?php
// Wajib ada untuk memuat PhpSpreadsheet (asumsi sudah diinstal via Composer)
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

include 'session.php';
include 'koneksi.php';
include "helpers.php";

// Proteksi: Hanya user non-normal (Admin/Superadmin) yang boleh import
if (($_SESSION['role'] ?? 'normal') === 'normal') {
    header("Location: tampil.php?status=error&msg=Akses_ditolak_untuk_import");
    exit;
}

// ===================================
// DEFINISI MAPPING STATUS KE RAK (Konsisten dengan main.js)
// ===================================
$statusToRak = [
    "Spare" => "GD-R11",
    "Pending Service" => "GD-R12",
    "Grace Period" => "GD-R13",
    "Scrap" => "GD-R4",
    "MT" => "GD-R8",
    "Ready to Assign" => "GD-R9",
    "Assign" => "Assign",
    "Loan" => "Loan"
];

// --- Fungsi Helper untuk Redirect (Sesuai dengan sistem Toast di index2) ---
function redirect_success($msg) {
    header("Location: tampil.php?res=success&msg=" . urlencode($msg));
    exit;
}
function redirect_error($msg) {
    header("Location: tampil.php?res=danger&msg=" . urlencode($msg));
    exit;
}

// ===================================
// 1. Proses Upload dan Validasi File
// ===================================
if (isset($_POST['import_submit'])) {
    if (empty($_FILES['fileExcel']['name'])) {
        redirect_error("File Excel harus dipilih");
    }

    $file = $_FILES['fileExcel']['tmp_name'];
    $file_extension = pathinfo($_FILES['fileExcel']['name'], PATHINFO_EXTENSION);

    if (strtolower($file_extension) !== 'xlsx') {
        redirect_error("Format file tidak didukung. Gunakan .xlsx");
    }

    try {
        $file_type = IOFactory::identify($file);
        $reader = IOFactory::createReader($file_type);
        $spreadsheet = $reader->load($file);
    } catch (\Exception $e) {
        redirect_error("Gagal membaca file Excel: " . $e->getMessage());
    }

    $sheet = $spreadsheet->getActiveSheet();
    $highestRow = $sheet->getHighestRow();

    $imported_count = 0;
    $error_count = 0;

    // ===================================
    // 2. Loop Data dari Baris 2
    // ===================================

    for ($row = 2; $row <= $highestRow; $row++) {

        // Ambil nilai mentah
    $hostname_raw        = $sheet->getCell('A'.$row)->getCalculatedValue();
    $status_raw          = $sheet->getCell('B'.$row)->getCalculatedValue();
    $domain_raw          = $sheet->getCell('C'.$row)->getCalculatedValue();
    $type_raw            = $sheet->getCell('D'.$row)->getCalculatedValue();
    $sn_raw              = $sheet->getCell('E'.$row)->getCalculatedValue(); // KOLOM BARU (Serial Number)
    $device_category_raw = $sheet->getCell('F'.$row)->getCalculatedValue();
    $rak_excel_raw       = $sheet->getCell('G'.$row)->getCalculatedValue();
    $ram_raw             = $sheet->getCell('H'.$row)->getCalculatedValue();
    $storage_raw         = $sheet->getCell('I'.$row)->getCalculatedValue();
    $win_raw             = $sheet->getCell('J'.$row)->getCalculatedValue();
    $keterangan_raw      = $sheet->getCell('K'.$row)->getCalculatedValue();
    $kelengkapan_raw     = $sheet->getCell('L'.$row)->getCalculatedValue();
    $tgl_masuk_excel     = $sheet->getCell('M'.$row)->getCalculatedValue();
    $tgl_keluar_excel    = $sheet->getCell('N'.$row)->getCalculatedValue();
    $nik_raw             = $sheet->getCell('O'.$row)->getCalculatedValue();
    $nama_raw            = $sheet->getCell('P'.$row)->getCalculatedValue();
    $divisi_raw          = $sheet->getCell('Q'.$row)->getCalculatedValue();

        // --- NORMALISASI DATA ---
        $hostname = mysqli_real_escape_string($koneksi, strtoupper(trim($hostname_raw ?? '')));

        if (empty($hostname)) continue;

        $status_normalized = ucwords(strtolower(trim($status_raw ?? '')));
        $status      = mysqli_real_escape_string($koneksi, $status_normalized);
        $domain      = mysqli_real_escape_string($koneksi, strtoupper(trim($domain_raw ?? '')));
        $type        = mysqli_real_escape_string($koneksi, strtoupper(trim($type_raw ?? '')));
        $serial_number = mysqli_real_escape_string($koneksi, strtoupper(trim($sn_raw ?? '')));
        $device_category = mysqli_real_escape_string($koneksi, strtoupper(trim($device_category_raw ?? '')));
        $rak_excel   = mysqli_real_escape_string($koneksi, trim($rak_excel_raw ?? ''));
        $ram         = mysqli_real_escape_string($koneksi, trim($ram_raw ?? ''));
        $storage     = mysqli_real_escape_string($koneksi, strtoupper(trim($storage_raw ?? '')));
        $win         = mysqli_real_escape_string($koneksi, trim($win_raw ?? ''));
        $keterangan  = mysqli_real_escape_string($koneksi, strtoupper(trim($keterangan_raw ?? '')));
        $kelengkapan = mysqli_real_escape_string($koneksi, strtoupper(trim($kelengkapan_raw ?? '')));
        $nik         = mysqli_real_escape_string($koneksi, trim($nik_raw ?? ''));
        $nama        = mysqli_real_escape_string($koneksi, strtoupper(trim($nama_raw ?? '')));
        $divisi      = mysqli_real_escape_string($koneksi, strtoupper(trim($divisi_raw ?? '')));

        // Penanganan Tanggal
        $tanggal_masuk = null;
        if (is_numeric($tgl_masuk_excel)) {
            $tanggal_masuk = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($tgl_masuk_excel)->format('Y-m-d');
        }
        $tanggal_keluar = null;
        if (is_numeric($tgl_keluar_excel)) {
            $tanggal_keluar = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($tgl_keluar_excel)->format('Y-m-d');
        }

        // OTOMATISASI RAK
        $rak = $rak_excel;
        if (!empty($status) && array_key_exists($status, $statusToRak)) {
            $rak = $statusToRak[$status];
        }

        // ===================================
        // 3. Simpan Data (UPSERT)
        // ===================================
        $check_query = "SELECT id FROM inventori WHERE hostname = '$hostname'";
        $check_result = mysqli_query($koneksi, $check_query);

        if (mysqli_num_rows($check_result) > 0) {
            $query_upsert = "UPDATE inventori SET
                status = '$status', domain = '$domain', type = '$type', serial_number='$serial_number', device_category = '$device_category',
                rak = '$rak', ram = '$ram', storage = '$storage', win = '$win',
                keterangan = '$keterangan', kelengkapan = '$kelengkapan',
                tanggal_masuk = " . ($tanggal_masuk ? "'$tanggal_masuk'" : "NULL") . ",
                tanggal_keluar = " . ($tanggal_keluar ? "'$tanggal_keluar'" : "NULL") . ",
                nik = '$nik', nama = '$nama', divisi = '$divisi'
                WHERE hostname = '$hostname'";
        } else {
            $query_upsert = "INSERT INTO inventori (
                hostname, status, domain, type, serial_number, device_category, rak, ram, storage, win, keterangan, kelengkapan,
                tanggal_masuk, tanggal_keluar, nik, nama, divisi, tanggal_register
            ) VALUES (
                '$hostname', '$status', '$domain', '$type', '$serial_number', '$device_category', '$rak', '$ram', '$storage', '$win', '$keterangan', '$kelengkapan',
                " . ($tanggal_masuk ? "'$tanggal_masuk'" : "NULL") . ",
                " . ($tanggal_keluar ? "'$tanggal_keluar'" : "NULL") . ",
                '$nik', '$nama', '$divisi', NOW()
            )";
        }

        if (mysqli_query($koneksi, $query_upsert)) {
            $imported_count++;
        } else {
            $error_count++;
        }
    }

        // ===================================
        // 4. WebSocket dan Redirect
        // ===================================

        // Kirim sinyal update ke WebSocket agar semua layar admin refresh otomatis
        if (function_exists('pushWebSocketUpdate')) {
            pushWebSocketUpdate(0, 'asset_bulk_insert');
        }

        // Redirect dengan pesan yang jelas
        if ($error_count > 0) {
            // Jika ada yang gagal, beri warna 'danger' (merah)
            $pesan = "Impor selesai. " . $imported_count . " data berhasil, " . $error_count . " gagal.";
            redirect_error($pesan);
        } else {
            // Jika semua sukses, beri warna 'success' (hijau)
            $pesan = $imported_count . " data berhasil diimpor atau diperbarui.";
            redirect_success($pesan);
        }
}
?>