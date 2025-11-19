<?php
// Pastikan session sudah dimulai di file session.php
include 'session.php';
include "koneksi.php";

// Mengambil role dari session
$role = isset($_SESSION['role']) ? $_SESSION['role'] : 'normal';

function e($text) {
    // Fungsi untuk keamanan (mencegah XSS)
    return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
}

// --- Daftar Status yang Tersedia (Digunakan untuk Tombol) ---
$status_options = [
    'all' => 'Semua Data',
    'Assign' => 'Assign',
    'Spare' => 'Spare',
    'Loan' => 'Loan',
    'Pending Service' => 'Pending Service',
    'Grace Period' => 'Grace Period',
    'Scrap' => 'Scrap',
    'MT' => 'MT',
    'Ready to Assign' => 'Ready to Assign'
];

// --- Logika Query Inventori ---

// Tangkap parameter status, cari, rak, dan type dari URL
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all'; // DEFAULT: 'all'
$cari = isset($_GET['cari']) ? mysqli_real_escape_string($koneksi, $_GET['cari']) : '';
$rak_filter = isset($_GET['rak']) ? mysqli_real_escape_string($koneksi, $_GET['rak']) : '';
$type_filter = isset($_GET['type']) ? mysqli_real_escape_string($koneksi, $_GET['type']) : '';

$where = [];

// Filter Status
if ($status_filter !== 'all' && array_key_exists($status_filter, $status_options)) {
    $where[] = "status = '" . mysqli_real_escape_string($koneksi, $status_filter) . "'";
}
// Tambahkan filter pencarian (hostname, nama, nik)
if (!empty($cari)) {
    $where[] = "(hostname LIKE '%$cari%' OR nama LIKE '%$cari%' OR nik LIKE '%$cari%')";
}
// Tambahkan filter Rak dan Type (jika ada di URL)
if (!empty($rak_filter)) {
    $where[] = "rak = '$rak_filter'";
}
if (!empty($type_filter)) {
    $where[] = "type = '$type_filter'";
}

$where_clause = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';

// Query utama yang akan di-include di table-inventori.php
$query = "
    SELECT
        i.*,
        t_loan.oleh AS pic_loan_name_display  -- Mengambil nama PIC dari tabel histori_aset (t_loan)
    FROM
        inventori i

    -- LEFT JOIN untuk menemukan nama PIC yang terakhir melakukan aksi 'Loan'
    LEFT JOIN (
        SELECT
            t1.inventori_id,
            t1.oleh,
            t1.tanggal
        FROM
            histori_aset t1
        INNER JOIN (
            -- Cari ID Log Transaksi TERBARU untuk aksi 'Loan'
            SELECT
                inventori_id,
                MAX(tanggal) AS max_tanggal
            FROM
                histori_aset
            WHERE
                aksi = 'Loan'
            GROUP BY
                inventori_id
        ) t2 ON t1.inventori_id = t2.inventori_id AND t1.tanggal = t2.max_tanggal
        WHERE
            t1.aksi = 'Loan'
    ) AS t_loan ON i.id = t_loan.inventori_id  -- JOIN berdasarkan ID Inventori

    $where_clause
    ORDER BY i.id ASC
";

$result = mysqli_query($koneksi, $query);

// Logika Notifikasi Grace Period (dibiarkan kosong karena tidak digunakan di sini)
$notifikasi_grace = [];
// ... (Logika Grace Period yang ada di file Anda) ...
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Inventori Gudang</title>
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <style>
        .table td {
            vertical-align: middle;
            padding: 0.75rem;
        }
        .filter-buttons .btn {
            margin-right: 5px;
            margin-bottom: 5px;
        }
        .filter-container {
            width: 100%;
        }
        .search-container {
            /* Menyesuaikan jarak antara tombol filter dan pencarian */
            margin-left: 20px;
        }
    </style>
</head>
<body class="bg-light">

    <?php include 'header.php'; ?>
    <?php include 'sidebar.php'; ?>

<main class="main-content">
    <div class="container-fluid pt-4">
        <?php include 'notifikasi.php'; ?>

        <h2 class="mb-4 text-dark fw-bold">
            Data Inventori Gudang <?= ($status_filter === 'all') ? '' : ' - <span class="text-primary">' . htmlspecialchars($status_filter) . '</span>' ?>
        </h2>

        <div class="mb-4 p-3 bg-white rounded shadow-sm filter-container">
            <p class="fw-bold mb-2">Filter Data:</p>

            <form method="GET" class="row align-items-end g-3">

                <div class="col-12 mb-3">
                    <?php foreach ($status_options as $status_key => $label):
                        $is_active = $status_filter === $status_key;
                        $btn_class = $is_active ? 'btn-primary' : 'btn-outline-primary';

                        // Buat URL yang mempertahankan filter 'cari' jika ada
                        $url = 'tampil.php?status=' . urlencode($status_key);
                        if (!empty($cari)) {
                            $url .= '&cari=' . urlencode($cari);
                        }
                    ?>
                        <a href="<?= e($url) ?>" class="btn <?= $btn_class ?> btn-sm filter-buttons">
                            <?= e($label) ?>
                        </a>
                    <?php endforeach; ?>
                </div>

                <div class="col-12">
                    <div class="row g-3 align-items-end">

                        <div class="col-12 col-md-5">
                            <label for="cari_input" class="form-label fw-bold mb-0">Pencarian Hostname/Nama/NIK</label>
                            <input type="text" name="cari" id="cari_input" class="form-control" placeholder="Hostname / Nama / NIK" value="<?= isset($_GET['cari']) ? htmlspecialchars($_GET['cari']) : ''; ?>">
                        </div>

                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Cari</button>
                        </div>
                        <div class="col-auto">
                            <a href="tampil.php" class="btn btn-secondary">Reset Filter</a>
                        </div>

                    </div>
                </div>

                <input type="hidden" name="status" value="<?= e($status_filter); ?>">

            </form>
        </div>
			<div class="d-flex mb-4">

					<?php if ($role !== 'normal'): ?>
						<button type="button" class="btn btn-primary me-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#addModal">
							<i class="bi bi-plus-circle"></i> Tambah Data
						</button>
						<button type="button" class="btn btn-info text-white me-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#importModal">
							<i class="bi bi-file-earmark-arrow-up"></i> Import Excel
						</button>
					<?php endif; ?>

					<?php if ($role !== 'normal'): ?>
						<a href="#" id="export-link" class="btn btn-success shadow-sm me-2">
							<i class="bi bi-file-earmark-spreadsheet"></i> Export Excel
						</a>
					<?php endif; ?>

					<?php if ($role === 'superadmin'): ?>
						<button type="button" class="btn btn-warning text-white shadow-sm" data-bs-toggle="modal" data-bs-target="#tipeLaptopModal">
							<i class="bi bi-laptop-fill"></i> Kelola Tipe Laptop
						</button>
					<?php endif; ?>

				</div>

				<div class="card shadow-sm">

			<div class="card shadow-sm">
				<div class="card-body p-0">
					<div class="table-responsive">
						<table class="table table-bordered table-striped table-hover mb-0">
							<thead class="table-dark">
								<tr>
									<th>Domain</th>
									<th>Rak</th>
									<th>Status</th>
									<th>Hostname & Tipe</th>
									<th>Spesifikasi (RAM/Storage/OS)</th>
                                    <th>Tgl. Register</th>
									<th>Tgl. (Masuk / Keluar)</th>
									<th>User & Divisi</th>
									<th>Kelengkapan & Keterangan</th>
								</tr>
							</thead>
							<tbody>
								<?php
								include 'table-inventori.php';
								?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
</div>
</main>

    <?php include 'modal-edit.php'; ?>
    <?php include 'modal-tambahdata.php'; ?>
    <?php include 'modal-import.php'; ?>
	<?php include 'modal-tipe-laptop.php'; ?>

    <script>
    const userRole = '<?= isset($_SESSION['role']) ? htmlspecialchars($_SESSION['role']) : 'normal' ?>';
    </script>

    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="main.js"></script>

</body>
</html>