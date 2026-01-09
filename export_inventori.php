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

// URUTAN SQL DISESUAIKAN DENGAN STRUKTUR IMPORT (A-Q)
$query = "SELECT
    hostname, status, domain, type, serial_number, device_category, rak, ram, storage, win, keterangan, kelengkapan,
    tanggal_masuk, tanggal_keluar, nik, nama, divisi
    FROM inventori ORDER BY hostname ASC";
$result = mysqli_query($koneksi, $query);

// ===================================
// 2. Tulis Header Kolom (Urutan A-Q)
// ===================================

$header = [
    'Hostname',        // A
    'Status',          // B
    'Domain',          // C
    'Type',            // D
    'Serial Number',   // E ◀️ TAMBAHAN BARU
    'Device Category', // F
    'Rak',             // G
    'RAM',             // H
    'Storage',         // I
    'OS (Win)',        // J
    'Keterangan',      // K
    'Kelengkapan',     // L
    'Tgl Masuk',       // M
    'Tgl Keluar',      // N
    'NIK',             // O
    'Nama',            // P
    'Divisi'           // Q
];

$sheet->fromArray($header, NULL, 'A1');

// ===================================
// 3. Aplikasikan Styling pada Header
// ===================================

$kolomAkhir = 'Q'; // ◀️ BERUBAH KE Q
$headerRange = 'A1:' . $kolomAkhir . '1';

$sheet->getStyle($headerRange)->applyFromArray([
    'font' => [
        'bold' => true,
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => [
            'argb' => 'FFA9D08E',
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

// Atur lebar kolom agar konten terbaca
foreach (range('A', $kolomAkhir) as $columnID) {
    $sheet->getColumnDimension($columnID)->setAutoSize(true);
}


// ===================================
// 4. Tulis Data ke Spreadsheet
// ===================================

$rowNum = 2; // Data dimulai dari baris ke-2
while ($data = mysqli_fetch_assoc($result)) {
    $rowData = [
        $data['hostname'],        // A
        $data['status'],          // B
        $data['domain'],          // C
        $data['type'],            // D
        $data['serial_number'],   // E
        $data['device_category'], // F
        $data['rak'],             // G
        $data['ram'],             // H
        $data['storage'],         // I
        $data['win'],             // J
        $data['keterangan'],      // K
        $data['kelengkapan'],     // L
        $data['tanggal_masuk'],   // M
        $data['tanggal_keluar'],  // N
        $data['nik'],             // O
        $data['nama'],            // P
        $data['divisi']           // Q
    ];

    $sheet->fromArray($rowData, NULL, 'A' . $rowNum);
    $rowNum++;
}


// ===================================
// 5. Proses Download File
// ===================================

$filename = 'data_inventori_' . date('Ymd_His') . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>