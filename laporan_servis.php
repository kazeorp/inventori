<?php
include 'session.php';
include 'koneksi.php';

// Fungsi untuk keamanan (mencegah XSS)
if (!function_exists('e')) {
    function e($text) {
        return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
    }
}

// 1. PROTEKSI ROLE (Hanya Superadmin yang bisa akses)
$user_role_login = $_SESSION['role'] ?? 'normal';
if ($user_role_login !== 'superadmin') {
    header("Location: index.php");
    exit;
}

// 💡 DEFENISI GLOBAL UNTUK BULAN INDONESIA
$bulan_indo = [
    1 => 'Januari',
    2 => 'Februari',
    3 => 'Maret',
    4 => 'April',
    5 => 'Mei',
    6 => 'Juni',
    7 => 'Juli',
    8 => 'Agustus',
    9 => 'September',
    10 => 'Oktober',
    11 => 'November',
    12 => 'Desember'
];

// 2. Tentukan Bulan dan Tahun yang dipilih
$selected_month = isset($_GET['bulan']) ? (int)$_GET['bulan'] : (int)date('n'); // 'n' adalah bulan tanpa nol di depan (1-12)
$selected_year = isset($_GET['tahun']) ? (int)$_GET['tahun'] : (int)date('Y');

// Validasi jika input manual lewat URL ngawur
if ($selected_month < 1 || $selected_month > 12) $selected_month = (int)date('n');
if ($selected_year < 2020 || $selected_year > (int)date('Y') + 1) $selected_year = (int)date('Y');

// 💡 HITUNG NAMA BULAN UNTUK JUDUL
// Pastikan index bulan_indo menggunakan (int) agar match
$month_name_indo = isset($bulan_indo[$selected_month]) ? $bulan_indo[$selected_month] : 'Januari';
$current_month_name = "{$month_name_indo} {$selected_year}";

// 3. Query Laporan Servis Selesai berdasarkan Bulan & Tahun
$sql_laporan = "
    SELECT
        sl.admin_finish_name AS username_admin,
        u.nama_lengkap AS admin_finish_name, -- Mengambil nama lengkap
        COUNT(sl.id_service) AS total_servis_selesai
    FROM service_list sl
    LEFT JOIN admin u ON sl.admin_finish_name = u.username -- JOIN berdasarkan username
    WHERE
        sl.finish_status IS NOT NULL AND
        YEAR(sl.finish_timestamp) = ? AND
        MONTH(sl.finish_timestamp) = ?
    GROUP BY
        sl.admin_finish_name, u.nama_lengkap -- Grouping harus mencakup nama_lengkap
    ORDER BY
        total_servis_selesai DESC
";

// Menggunakan Prepared Statement untuk keamanan
$stmt = mysqli_prepare($koneksi, $sql_laporan);
mysqli_stmt_bind_param($stmt, "ii", $selected_year, $selected_month);
mysqli_stmt_execute($stmt);
$result_laporan = mysqli_stmt_get_result($stmt);

// 4. Query untuk mendapatkan daftar Admin aktif (untuk dropdown)
$sql_admins = "SELECT id, username FROM admin WHERE role IN ('admin', 'superadmin') ORDER BY username";
$result_admins = mysqli_query($koneksi, $sql_admins);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Servis Bulanan</title>
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <?php include 'sidebar.php'; ?>

    <main class="main-content">
        <h2 class="mb-4">📈 Laporan Servis Bulanan</h2>
        <p class="text-muted">Laporan kinerja berdasarkan jumlah servis yang diselesaikan.</p>

    <form method="GET" id="filterForm" class="row g-3 mb-5 align-items-end">
        <div class="col-md-3">
            <label for="bulan" class="form-label">Pilih Bulan</label>
            <select name="bulan" id="bulan" class="form-select" onchange="this.form.submit()">
                <?php
                foreach ($bulan_indo as $month_num => $month_name):
                ?>
                <option value="<?= $month_num ?>" <?= ($selected_month == $month_num) ? 'selected' : '' ?>>
                    <?= $month_name ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label for="tahun" class="form-label">Pilih Tahun</label>
            <select name="tahun" id="tahun" class="form-select" onchange="this.form.submit()">
                <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                <option value="<?= $y ?>" <?= ($selected_year == $y) ? 'selected' : '' ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>
        </div>
        </form>

        <h4 class="mt-4 mb-3 text-success">Total Servis Selesai Bulan: <?= $current_month_name ?></h4>

        <div class="table-responsive mb-5">
            <table class="table table-bordered table-striped table-hover">
                <thead class="table-success">
                    <tr>
                        <th>Engineer</th>
                        <th>Jumah Servis Selesai</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result_laporan && mysqli_num_rows($result_laporan) > 0): ?>
                        <?php while($row_laporan = mysqli_fetch_assoc($result_laporan)): ?>
                            <tr>
                                <td><?= e($row_laporan['admin_finish_name']) ?></td>
                                <td><span class="badge bg-success fs-6"><?= e($row_laporan['total_servis_selesai']) ?></span> Unit</td>
                                <td>
									<button type="button" class="btn btn-sm btn-info text-white btn-detail"
										data-bs-toggle="modal"
										data-bs-target="#detailModal"
										data-admin-name="<?= e($row_laporan['admin_finish_name']) ?>"
										data-admin-username="<?= e($row_laporan['username_admin']) ?>" data-month="<?= e($selected_month) ?>"
										data-year="<?= e($selected_year) ?>">
										Lihat Detail
									</button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="text-center">Tidak ada servis yang diselesaikan pada bulan <?= $current_month_name ?>.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="detailModalLabel">Detail Servis Selesai oleh <span id="admin-name-placeholder"></span></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted">Laporan untuk bulan/tahun: <?= $current_month_name ?></p>
                        <div id="detail-loading-indicator" class="text-center my-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Memuat detail...</p>
                        </div>
                        <div id="detail-content-placeholder">
                            </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>

    </main>
    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
    // Logika JavaScript untuk memuat detail menggunakan AJAX
    document.addEventListener('DOMContentLoaded', function() {
        const detailModal = document.getElementById('detailModal');
        const detailContent = document.getElementById('detail-content-placeholder');
        const loadingIndicator = document.getElementById('detail-loading-indicator');
        const adminNamePlaceholder = document.getElementById('admin-name-placeholder');

		detailModal.addEventListener('show.bs.modal', function (event) {
			const button = event.relatedTarget;
			const adminName = button.getAttribute('data-admin-name'); // Nama lengkap untuk ditampilkan
			const adminUsername = button.getAttribute('data-admin-username'); // Username untuk Query
			const month = button.getAttribute('data-month');
			const year = button.getAttribute('data-year');

			adminNamePlaceholder.textContent = adminName; // Menampilkan nama lengkap
			detailContent.innerHTML = '';
			loadingIndicator.style.display = 'block';

			fetch('ajax_laporan_detail.php', {
				method: 'POST',
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
				// MENGIRIM USERNAME sebagai admin_name
				body: `admin_name=${encodeURIComponent(adminUsername)}&bulan=${month}&tahun=${year}`
			})
            .then(response => response.text())
            .then(html => {
                loadingIndicator.style.display = 'none'; // Sembunyikan loading
                detailContent.innerHTML = html; // Tampilkan data tabel
            })
            .catch(error => {
                loadingIndicator.style.display = 'none';
                detailContent.innerHTML = '<div class="alert alert-danger">Gagal memuat detail: ' + error + '</div>';
                console.error('AJAX Error:', error);
            });
        });
    });
    </script>
</body>
</html>