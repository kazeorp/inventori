<?php
include "koneksi.php";

// Tangkap filter status dari URL
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

// Siapkan query
$where = "";
if ($status_filter !== 'all') {
    $status_filter = mysqli_real_escape_string($koneksi, $status_filter);
    $where = "WHERE status = '$status_filter'";
}

$query = "SELECT * FROM inventori $where ORDER BY id ASC";
$result = mysqli_query($koneksi, $query);

// Header untuk download file CSV
header('Content-Type: text/csv');
header('Content-Disposition: attachment;filename="inventori_export.csv"');

$output = fopen('php://output', 'w');

// Header kolom
fputcsv($output, ['ID', 'Rak', 'Status', 'Hostname', 'Type', 'RAM', 'Storage', 'Windows', 'Keterangan', 'Kelengkapan', 'Tanggal Keluar', 'NIK', 'Nama', 'Divisi']);

// Isi data
while ($row = mysqli_fetch_assoc($result)) {
    fputcsv($output, [
        $row['id'],
        $row['rak'],
        $row['status'],
        $row['hostname'],
        $row['type'],
        $row['ram'],
        $row['storage'],
        $row['win'],
        $row['keterangan'],
        $row['kelengkapan'],
        $row['tanggal_keluar'],
        $row['nik'],
        $row['nama'],
        $row['divisi']
    ]);
}

fclose($output);
exit;
?>
