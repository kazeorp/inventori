<?php
require_once "koneksi.php";

// --- 1. LOGIKA GRACE PERIOD ---
$count_grace = 0;
$cek_grace = mysqli_query($koneksi, "SELECT tanggal_keluar FROM inventori WHERE status='Grace Period'");
while ($row = mysqli_fetch_assoc($cek_grace)) {
    if (!empty($row['tanggal_keluar'])) {
        $tanggal_keluar = new DateTime($row['tanggal_keluar']);
        $batas_grace = (clone $tanggal_keluar)->add(new DateInterval('P3M'));
        if (new DateTime() >= $batas_grace) {
            $count_grace++;
        }
    }
}

// --- 2. LOGIKA SERVICE BELUM DI PICK-UP ---
$query_service = "SELECT COUNT(*) as total FROM service_list WHERE claim_status IS NULL OR claim_status = ''";
$res_service = mysqli_query($koneksi, $query_service);
$data_service = mysqli_fetch_assoc($res_service);
$count_service = (int)$data_service['total'];

// --- 3. OUTPUT JSON ---
echo json_encode([
    'service_count' => $count_service,
    'grace_count'   => $count_grace,
    'total'         => $count_service + $count_grace
]);