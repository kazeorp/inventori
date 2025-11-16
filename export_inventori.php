<?php
// Wajib ada untuk memuat PhpSpreadsheet (asumsi sudah diinstal via Composer)
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

include 'session.php';
include 'koneksi.php';

// Proteksi: Hanya user non-normal (Admin/Superadmin) yang boleh melakukan export
if (($_SESSION['role'] ?? 'normal') === 'normal') {
    header("Location: tampil.php?status=error&msg=Akses_ditolak_untuk_export");
    exit;
}

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// ===================================
// 1. Ambil Data dari Database
// ===================================

// PASTIKAN URUTAN KOLOM SAMA PERSIS DENGAN FILE IMPORT!
// Urutan kolom SQL: Hostname, Status, Rak, Domain, Type, Device Category, RAM, Storage, OS(Win), Keterangan, Kelengkapan, Tgl Masuk, Tgl Keluar, NIK, Nama, Divisi
$query = "SELECT 
    hostname, status, rak, domain, type, device_category, ram, storage, win, keterangan, kelengkapan, 
    tanggal_masuk, tanggal_keluar, nik, nama, divisi
    FROM inventori ORDER BY hostname ASC";
$result = mysqli_query($koneksi, $query);

// ===================================
// 2. Tulis Header Kolom
// ===================================

$header = [
    // Kolom A
    'Hostname',
    // Kolom B
    'Status',
    // Kolom C
    'Rak', // ◀️ BERGESER KE KOLOM C
    // Kolom D
    'Domain', // ◀️ BERGESER KE KOLOM D
    // Kolom E
    'Type', // ◀️ BERGESER KE KOLOM E
    // Kolom F
    'Device Category', // ◀️ BERGESER KE KOLOM F
    // Kolom G - K
    'RAM', 
    'Storage', 
    'OS (Win)', 
    'Keterangan', 
    'Kelengkapan', 
    // Kolom L - M (Tanggal)
    'Tgl Masuk', 
    'Tgl Keluar', 
    // Kolom N - P (Identitas Pengguna)
    'NIK', 
    'Nama', 
    'Divisi' 
];

$sheet->fromArray($header, NULL, 'A1');

// ===================================
// 3. Aplikasikan Styling pada Header
// ===================================

$kolomAkhir = 'P';
$headerRange = 'A1:' . $kolomAkhir . '1';

// Terapkan Bold, Warna Latar Belakang, dan Alignment
$sheet->getStyle($headerRange)->applyFromArray([
    'font' => [
        'bold' => true,
    ],
    'fill' => [
        // Menggunakan warna biru muda/hijau (contoh: Light Turquoise)
        'fillType' => Fill::FILL_SOLID,
        'startColor' => [
            'argb' => 'FFA9D08E', // Kode warna Hex untuk warna latar
        ],
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
        ],
    ],
]);

// Atur lebar kolom agar konten terbaca (Opsional)
foreach (range('A', $kolomAkhir) as $columnID) {
    $sheet->getColumnDimension($columnID)->setAutoSize(true);
}


// ===================================
// 4. Tulis Data ke Spreadsheet
// ===================================

$row = 2; // Data dimulai dari baris ke-2
while ($data = mysqli_fetch_assoc($result)) {
    // Membangun array data baris (urutan harus sinkron dengan header)
    $rowData = [
        // A
        $data['hostname'],
        // B
        $data['status'],
        // C
        $data['rak'],
        // D
        $data['domain'],
        // E
        $data['type'],
        // F
        $data['device_category'],
        // G
        $data['ram'],
        // H
        $data['storage'],
        // I
        $data['win'],
        // J
        $data['keterangan'],
        // K
        $data['kelengkapan'],
        
        // L
        $data['tanggal_masuk'],
        // M
        $data['tanggal_keluar'],
        
        // N
        $data['nik'],
        // O
        $data['nama'],
        // P
        $data['divisi']
    ];
    
    // Tulis array data ke baris saat ini
    $sheet->fromArray($rowData, NULL, 'A' . $row);
    $row++;
}


// ===================================
// 5. Proses Download File
// ===================================

// Set nama file Excel
$filename = 'data_inventori_' . date('Ymd_His') . '.xlsx';

// Set header HTTP untuk download file
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>