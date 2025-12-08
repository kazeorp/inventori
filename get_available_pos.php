<?php
// get_available_pos.php
include 'koneksi.php';

header('Content-Type: application/json');

$peripheral_id = (int)($_GET['peripheral_id'] ?? 0);

if ($peripheral_id > 0 && $koneksi) {
    // Query untuk menghitung saldo (IN - OUT) per PO untuk peripheral_id tertentu
    $sql = "
        SELECT po_number, SUM(CASE WHEN action = 'IN' THEN quantity ELSE -quantity END) as saldo
        FROM peripheral_history
        WHERE peripheral_id = $peripheral_id AND po_number IS NOT NULL AND po_number != ''
        GROUP BY po_number
        HAVING saldo > 0
        ORDER BY po_number DESC
    ";

    $result = mysqli_query($koneksi, $sql);
    $available_pos = [];

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $available_pos[] = [
                'po_number' => $row['po_number'],
                'saldo' => (int)$row['saldo']
            ];
        }
        echo json_encode(['status' => 'success', 'data' => $available_pos]);
    } else {
        echo json_encode(['status' => 'error', 'message' => mysqli_error($koneksi)]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'ID Peripheral tidak valid.']);
}

mysqli_close($koneksi);
?>