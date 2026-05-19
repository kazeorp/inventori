<?php

include 'session.php';
include 'koneksi.php';

// Fungsi untuk keamanan
function e($text)
{
    return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
}

// Hanya Superadmin yang boleh mengakses endpoint ini
if (($_SESSION['role'] ?? 'normal') !== 'superadmin') {
    http_response_code(403);
    die("Akses ditolak.");
}

$admin_name = $_POST['admin_name'] ?? '';
$month = (int) ($_POST['bulan'] ?? date('m'));
$year = (int) ($_POST['tahun'] ?? date('Y'));

if (empty($admin_name) || $month < 1 || $month > 12 || $year < 2020) {
    echo '<div class="alert alert-danger">Parameter tidak valid.</div>';
    exit;
}

$sql_detail = "
    SELECT
        sl.id_service,
        sl.hostname,
        sl.tanggal_masuk,
        -- KOREKSI: Mengganti sl.catatan_user menjadi sl.catatan
        sl.catatan AS catatan_user,
        sl.finish_timestamp,
        i.type,
        i.nama AS nama_user
    FROM service_list sl
    LEFT JOIN inventori i ON sl.hostname = i.hostname
    WHERE
        sl.finish_status IS NOT NULL AND
        sl.admin_finish_name = ? AND
        YEAR(sl.finish_timestamp) = ? AND
        MONTH(sl.finish_timestamp) = ?
    ORDER BY sl.finish_timestamp DESC
";
$stmt = mysqli_prepare($koneksi, $sql_detail);
if (!$stmt) {
    http_response_code(500);
    // Tampilkan error SQL yang sebenarnya untuk debugging
    echo '<div class="alert alert-danger">❌ Query preparation failed: ' . e(mysqli_error($koneksi)) . '</div>';
    exit;
}

// Baris 45 yang baru adalah baris berikut
mysqli_stmt_bind_param($stmt, "sii", $admin_name, $year, $month);
mysqli_stmt_execute($stmt);
$result_detail = mysqli_stmt_get_result($stmt);

if (!$result_detail) {
    echo '<div class="alert alert-danger">Gagal menjalankan query: ' . e(mysqli_error($koneksi)) . '</div>';
    exit;
}

// Tampilkan Tabel Detail
echo '<div class="table-responsive">';
echo '<table class="table table-sm table-bordered table-striped">';
echo '<thead class="table-dark"><tr>';
echo '<th>ID Servis</th>';
echo '<th>Hostname (Tipe)</th>';
echo '<th>Nama User</th>';
echo '<th>Catatan Awal</th>';
echo '<th>Waktu Masuk</th>';
echo '<th>Waktu Selesai</th>';
echo '</tr></thead>';
echo '<tbody>';

if (mysqli_num_rows($result_detail) > 0) {
    while ($row = mysqli_fetch_assoc($result_detail)) {
        echo '<tr>';
        echo '<td>' . e($row['id_service']) . '</td>';
        echo '<td><strong>' . e($row['hostname']) . '</strong><br><small class="text-muted">(' . e($row['type'] ?? 'N/A') . ')</small></td>';
        echo '<td>' . e($row['nama_user'] ?? 'N/A') . '</td>';
        echo '<td>' . (empty($row['catatan_user']) ? '-' : e(substr($row['catatan_user'], 0, 50)) . '...') . '</td>';
        echo '<td>' . date('d/m/Y H:i:s', strtotime($row['tanggal_masuk'])) . '</td>';
        echo '<td><span class="badge bg-success">' . date('d/m/Y H:i:s', strtotime($row['finish_timestamp'])) . '</span></td>';
        echo '</tr>';
    }
} else {
    echo '<tr><td colspan="6" class="text-center">Tidak ada data servis selesai yang ditemukan untuk admin ini di bulan ini.</td></tr>';
}

echo '</tbody>';
echo '</table>';
echo '</div>';

mysqli_close($koneksi);
