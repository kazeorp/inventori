<?php
// ajax_check_new_service.php (di root folder)

include 'session.php'; 
include 'koneksi.php'; 
header('Content-Type: application/json');

// Cek hanya jika Admin/Superadmin login
$user_role = $_SESSION['role'] ?? 'normal';
if ($user_role !== 'admin' && $user_role !== 'superadmin') {
    http_response_code(403);
    echo json_encode(['new_entries' => 0, 'message' => 'Akses ditolak.']);
    exit;
}

// Ambil ID service terakhir yang dilihat admin dari session
// Gunakan id_service tertinggi di database sebagai default jika belum ada session
$last_checked_id = $_SESSION['last_service_id'] ?? 0;

// Ambil jumlah entri yang ID-nya lebih besar dari ID terakhir yang dilihat admin
$sql = "SELECT COUNT(id_service) as total_new, MAX(id_service) as max_id
        FROM service_list 
        WHERE id_service > ?";

if ($stmt = $koneksi->prepare($sql)) {
    $stmt->bind_param("i", $last_checked_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $stmt->close();
    
    $total_new = (int)$data['total_new'];
    $max_id = (int)$data['max_id'];

    $response = [
        'new_entries' => $total_new,
        'max_id' => $max_id
    ];

    // Jika ada entri baru, update session ID yang dilihat admin
    // Ini penting agar notifikasi tidak terus muncul setelah Admin tahu ada data baru.
    if ($total_new > 0) {
        $_SESSION['last_service_id'] = $max_id;
    }

    echo json_encode($response);
} else {
    echo json_encode(['new_entries' => 0, 'max_id' => $last_checked_id, 'message' => 'Database error.']);
}

// Tidak perlu menutup koneksi jika file PHP lain menggunakannya
// $koneksi->close(); 
?>