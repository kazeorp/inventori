<?php
header('Content-Type: application/json');
include 'koneksi.php';

$hostname = $_GET['hostname'] ?? '';

if (!empty($hostname)) {
    // Mencari data di tabel inventori
    $query = "SELECT hostname, serial_number, type, nik, nama, divisi
              FROM inventori
              WHERE hostname = ? LIMIT 1";

    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "s", $hostname);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        echo json_encode([
            'status' => 'success',
            'type' => $row['type'],
            'sn' => $row['serial_number'],
            'nik' => $row['nik'],
            'nama' => $row['nama'],
            'divisi' => $row['divisi']
        ]);
    } else {
        echo json_encode(['status' => 'not_found']);
    }
}
exit;