<?php
// Wajib ada untuk memuat PhpSpreadsheet (asumsi sudah diinstal via Composer)
require 'vendor/autoload.php'; 

use PhpOffice\PhpSpreadsheet\IOFactory;

include 'session.php';
include 'koneksi.php';

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

// --- Fungsi Helper untuk Redirect ---
function redirect_success($msg) {
    header("Location: tampil.php?status=success&msg=" . urlencode($msg));
    exit;
}
function redirect_error($msg) {
    header("Location: tampil.php?status=error&msg=" . urlencode($msg));
    exit;
}

// ===================================
// 1. Proses Upload dan Validasi File
// ===================================
if (isset($_POST['import_submit'])) {
    if (empty($_FILES['fileExcel']['name'])) {
        redirect_error("File_Excel_harus_dipilih");
    }

    $file = $_FILES['fileExcel']['tmp_name'];
    
    $allowed_extensions = ['xlsx'];
    $file_extension = pathinfo($_FILES['fileExcel']['name'], PATHINFO_EXTENSION);

    if (!in_array(strtolower($file_extension), $allowed_extensions)) {
        redirect_error("Format_file_tidak_didukung._Gunakan_.xlsx");
    }

    try {
        $file_type = IOFactory::identify($file);
        $reader = IOFactory::createReader($file_type);
        $spreadsheet = $reader->load($file);
    } catch (\Exception $e) {
        redirect_error("Gagal_membaca_file_Excel:_".htmlspecialchars($e->getMessage()));
    }
    
    $sheet = $spreadsheet->getActiveSheet();
    $highestRow = $sheet->getHighestRow();
    
    $imported_count = 0;
    $error_count = 0;
    
    // ===================================
    // 2. Loop Data dari Baris 2 (Lewati Header)
    // ===================================
    
    for ($row = 2; $row <= $highestRow; $row++) {
        
        // Ambil nilai mentah dari sel berdasarkan urutan baru:
        // A: Hostname | B: Status | C: Domain | D: Type | E: Device Category | F: Rak | G: RAM | H: Storage | I: OS(Win) | J: Keterangan | K: Kelengkapan | L: Tgl Masuk | M: Tgl Keluar | N: NIK | O: Nama | P: Divisi

        $hostname_raw           = $sheet->getCell('A'.$row)->getCalculatedValue() ?? '';
        $status_raw             = $sheet->getCell('B'.$row)->getCalculatedValue() ?? '';
        $domain_raw             = $sheet->getCell('C'.$row)->getCalculatedValue() ?? ''; // ◀️ KOLOM BARU
        $type_raw               = $sheet->getCell('D'.$row)->getCalculatedValue() ?? '';
        $device_category_raw    = $sheet->getCell('E'.$row)->getCalculatedValue() ?? ''; // ◀️ KOLOM BARU
        $rak_excel_raw          = $sheet->getCell('F'.$row)->getCalculatedValue() ?? ''; // ◀️ BERGESER
        $ram_raw                = $sheet->getCell('G'.$row)->getCalculatedValue() ?? ''; // ◀️ BERGESER
        $storage_raw            = $sheet->getCell('H'.$row)->getCalculatedValue() ?? ''; // ◀️ BERGESER
        $win_raw                = $sheet->getCell('I'.$row)->getCalculatedValue() ?? ''; // ◀️ BERGESER
        $keterangan_raw         = $sheet->getCell('J'.$row)->getCalculatedValue() ?? ''; // ◀️ BERGESER
        $kelengkapan_raw        = $sheet->getCell('K'.$row)->getCalculatedValue() ?? ''; // ◀️ BERGESER
        // Tanggal Masuk (L) dan Tanggal Keluar (M)
        $tgl_masuk_excel        = $sheet->getCell('L'.$row)->getCalculatedValue();
        $tgl_keluar_excel       = $sheet->getCell('M'.$row)->getCalculatedValue();
        $nik_raw                = $sheet->getCell('N'.$row)->getCalculatedValue() ?? ''; // ◀️ BERGESER
        $nama_raw               = $sheet->getCell('O'.$row)->getCalculatedValue() ?? ''; // ◀️ BERGESER
        $divisi_raw             = $sheet->getCell('P'.$row)->getCalculatedValue() ?? ''; // ◀️ BERGESER


        // --- NORMALISASI DAN SANITASI DATA ---
        
        // 1. SANITASI DAN PENYERAGAMAN WAJIB UPPERCASE
        $hostname           = mysqli_real_escape_string($koneksi, strtoupper(trim($hostname_raw)));
        $storage            = mysqli_real_escape_string($koneksi, strtoupper(trim($storage_raw)));
        $keterangan         = mysqli_real_escape_string($koneksi, strtoupper(trim($keterangan_raw)));
        $nik                = mysqli_real_escape_string($koneksi, strtoupper(trim($nik_raw)));
        $nama               = mysqli_real_escape_string($koneksi, strtoupper(trim($nama_raw)));
        $divisi             = mysqli_real_escape_string($koneksi, strtoupper(trim($divisi_raw)));
        $type               = mysqli_real_escape_string($koneksi, strtoupper(trim($type_raw)));
        $kelengkapan        = mysqli_real_escape_string($koneksi, strtoupper(trim($kelengkapan_raw))); 
        $domain             = mysqli_real_escape_string($koneksi, strtoupper(trim($domain_raw))); // ✅ UPPERCASE
        $device_category    = mysqli_real_escape_string($koneksi, strtoupper(trim($device_category_raw))); // ✅ UPPERCASE

        // 2. SANITASI DAN PENYERAGAMAN STATUS (Proper Case: Assign, Grace Period)
        $status_normalized = ucwords(strtolower(trim($status_raw)));
        $status = mysqli_real_escape_string($koneksi, $status_normalized);
        
        // 3. SANITASI DATA LAINNYA
        $rak_excel      = mysqli_real_escape_string($koneksi, $rak_excel_raw);
        $ram            = mysqli_real_escape_string($koneksi, $ram_raw); // Menggunakan RAM mentah yang sudah di trim
        $win            = mysqli_real_escape_string($koneksi, $win_raw); 


        // Penanganan Tanggal (KRITIS!)
        $tanggal_masuk = null;
        if (is_numeric($tgl_masuk_excel) && $tgl_masuk_excel > 0) {
            $tanggal_masuk = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($tgl_masuk_excel)->format('Y-m-d');
        }
            
        $tanggal_keluar = null;
        if (is_numeric($tgl_keluar_excel) && $tgl_keluar_excel > 0) {
            $tanggal_keluar = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($tgl_keluar_excel)->format('Y-m-d');
        }

        
        // Lewati baris jika Hostname kosong
        if (empty($hostname)) {
            continue;
        }

        // --- LOGIKA OTOMATISASI RAK DARI STATUS ---
        $rak = $rak_excel; // Gunakan nilai Excel sebagai default
        if (!empty($status) && array_key_exists($status, $statusToRak)) {
            // Jika Status terdeteksi, timpa nilai Rak dengan mapping yang benar
            $rak = $statusToRak[$status];
        }
        // ------------------------------------------

        // ===================================
        // 3. Simpan Data ke Database (UPSERT)
        // ===================================

        // Query untuk memeriksa apakah hostname sudah ada
        $check_query = "SELECT id FROM inventori WHERE hostname = '$hostname'";
        $check_result = mysqli_query($koneksi, $check_query);
        
        if (!$check_result) {
            $error_count++;
            continue;
        }

        if (mysqli_num_rows($check_result) > 0) {
            // JIKA DATA SUDAH ADA (UPDATE)
            
            $row_data = mysqli_fetch_assoc($check_result);
            $id_existing = $row_data['id'];

            $query_upsert = "UPDATE inventori SET 
                status = '$status', 
                domain = '$domain',
                type = '$type', 
                device_category = '$device_category',
                rak = '$rak', 
                ram = '$ram', 
                storage = '$storage', 
                win = '$win', 
                keterangan = '$keterangan', 
                kelengkapan = '$kelengkapan', 
                tanggal_masuk = " . ($tanggal_masuk ? "'$tanggal_masuk'" : "NULL") . ", 
                tanggal_keluar = " . ($tanggal_keluar ? "'$tanggal_keluar'" : "NULL") . ", 
                nik = '$nik', 
                nama = '$nama', 
                divisi = '$divisi'
                WHERE id = '$id_existing'";
            
        } else {
            // JIKA DATA BELUM ADA (INSERT)
            
            $query_upsert = "INSERT INTO inventori (
                hostname, status, domain, type, device_category, rak, ram, storage, win, keterangan, kelengkapan, 
                tanggal_masuk, tanggal_keluar, nik, nama, divisi,  tanggal_register
            ) VALUES (
                '$hostname', '$status', '$domain', '$type', '$device_category', '$rak', '$ram', '$storage', '$win', '$keterangan', '$kelengkapan',
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
    } // Akhir loop
    
    // Redirect setelah selesai
    if ($error_count > 0) {
        redirect_error("Impor_selesai._$imported_count_data_berhasil,_$error_count_data_gagal_disimpan");
    } else {
        redirect_success("$imported_count_data_berhasil_diimpor_atau_diperbarui");
    }
}
?>