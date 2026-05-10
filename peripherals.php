<?php
require 'session.php';
require 'koneksi.php';
require 'helpers.php';

// Tambahkan ini jika belum ada
$nama_bulan = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
];

// Pastikan variabel filter bulan & tahun juga memiliki nilai default agar tidak error
$f_bulan = isset($_GET['bulan']) ? (int) $_GET['bulan'] : (int) date('n');
$f_tahun = isset($_GET['tahun']) ? (int) $_GET['tahun'] : (int) date('Y');
$keyword = isset($_GET['q']) ? $_GET['q'] : '';
$halaman_aktif = isset($_GET['halaman']) ? (int) $_GET['halaman'] : 1;

$admin_sekarang = $_SESSION['nama_lengkap'] ?? 'Admin IT';

// =======================================================
// 1. API INTERNAL UNTUK CEK DUPLIKASI SN VIA AJAX
// =======================================================
if (isset($_GET['cek_sn'])) {
    $sn = mysqli_real_escape_string($koneksi, $_GET['cek_sn']);
    $cek = mysqli_query($koneksi, "SELECT serial_number FROM peripheral_items WHERE serial_number = '$sn'");
    echo json_encode(['exists' => mysqli_num_rows($cek) > 0]);
    exit;
}

// API UNTUK AMBIL MODEL BERDASARKAN TIPE
if (isset($_GET['get_models_by_tipe'])) {
    $tipe = mysqli_real_escape_string($koneksi, $_GET['get_models_by_tipe']);
    $query = mysqli_query($koneksi, "SELECT kode_barang, model FROM peripheral_types WHERE tipe_barang = '$tipe' ORDER BY model ASC");

    $models = [];
    while ($row = mysqli_fetch_assoc($query)) {
        $models[] = $row;
    }
    echo json_encode($models);
    exit;
}

$halaman_aktif = isset($_GET['halaman']) ? (int) $_GET['halaman'] : 1;
$keyword = isset($_GET['q']) ? mysqli_real_escape_string($koneksi, $_GET['q']) : '';
$f_bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
$f_tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Peripheral Management</title>
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="bg-light">

    <?php include 'header.php'; ?>
    <?php include 'sidebar.php'; ?>

    <main class="main-content">
        <?php include 'toast.php'; ?>

        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <h2 class="text-dark fw-bold m-0"><i class="bi bi-mouse2 me-2"></i>Stock Peripheral</h2>
            
            <div class="d-flex gap-2">
                <button class="btn btn-primary btn-sm px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalMasuk">
                    <i class="bi bi-plus-lg me-1"></i> Barang Masuk
                </button>
                <button class="btn btn-danger btn-sm px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalKeluar">
                    <i class="bi bi-box-arrow-up me-1"></i> Barang Keluar
                </button>
            </div>
        </div>

        <div class="mb-4 p-3 bg-white rounded shadow-sm border-0">
            <div class="row g-3 align-items-center">
                <div class="col-md-auto">
                    <ul class="nav nav-pills nav-pills-custom shadow-sm p-1 bg-light rounded">
                        <li class="nav-item">
                            <a class="nav-link <?= $halaman_aktif == 1 ? 'active bg-dark text-white' : 'text-secondary' ?> fw-bold py-1 px-4" href="peripherals.php?halaman=1">Monitoring</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $halaman_aktif == 2 ? 'active bg-dark text-white' : 'text-secondary' ?> fw-bold py-1 px-4" href="peripherals.php?halaman=2">Riwayat</a>
                        </li>
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'superadmin'): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= $halaman_aktif == 3 ? 'active bg-dark text-white' : 'text-secondary' ?> fw-bold py-1 px-4" href="peripherals.php?halaman=3">Master Data</a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>

                <div class="col-md">
                    <form action="" method="GET" class="row g-2 justify-content-md-end">
                        <input type="hidden" name="halaman" value="<?= $halaman_aktif ?>">
                        
                        <?php if ($halaman_aktif == 2 || $halaman_aktif == 3): ?>
                        <div class="col-auto">
                            <select name="bulan" class="form-select form-select-sm" onchange="this.form.submit()">
                                <?php foreach ($nama_bulan as $num => $nama): ?>
                                    <option value="<?= $num ?>" <?= ($f_bulan == $num) ? 'selected' : '' ?>><?= $nama ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-auto">
                            <select name="tahun" class="form-select form-select-sm" onchange="this.form.submit()">
                                <?php
                                $current_year = (int) date('Y');
                                for ($y = $current_year; $y >= ($current_year - 5); $y--): ?>
                                    <option value="<?= $y ?>" <?= ($f_tahun == $y) ? 'selected' : '' ?>><?= $y ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <?php endif; ?>
                        
                        <div class="col-md-4 <?= $halaman_aktif == 1 ? 'd-none' : '' ?>">
                            <div class="input-group input-group-sm">
                                <input type="text" name="q" class="form-control" placeholder="Cari S/N, PO, atau Model..." value="<?= htmlspecialchars($keyword) ?>">
                                <button class="btn btn-dark" type="submit"><i class="bi bi-search"></i></button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <?php if ($halaman_aktif == 1): ?>
            <div class="row g-3" id="category-grid">
                <!-- ALL CATEGORY CARD -->
                <div class="col-xl-2 col-lg-3 col-md-4 col-6">
                    <div class="card h-100 category-card border-0 shadow-sm bg-category-all" onclick="openCategoryDetail('ALL')">
                        <div class="card-body text-center py-3">
                            <i class="bi bi-boxes category-icon"></i>
                            <h5 class="fw-bold mb-1">ALL CATEGORIES</h5>
                            <?php
                                $total_all = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM peripheral_items WHERE status='Stock'"))['total'];
                            ?>
                            <span class="badge bg-white text-dark rounded-pill shadow-sm px-3"><?= number_format($total_all) ?> Items</span>
                        </div>
                    </div>
                </div>

                <?php
                // Get current stock counts for mapping
                $q_stock = mysqli_query($koneksi, "SELECT t.tipe_barang, COUNT(p.id_barang) as total 
                                                   FROM peripheral_types t
                                                   LEFT JOIN peripheral_items p ON t.kode_barang = p.kode_barang AND p.status = 'Stock'
                                                   GROUP BY t.tipe_barang");
                $stock_map = [];
                while($r = mysqli_fetch_assoc($q_stock)) { $stock_map[$r['tipe_barang']] = $r['total']; }

                foreach (getPeripheralCategories() as $tipe_val): 
                    $total = $stock_map[$tipe_val] ?? 0; ?>
                <div class="col-xl-2 col-lg-3 col-md-4 col-6">
                    <div class="card h-100 category-card border-0 shadow-sm bg-white" onclick="openCategoryDetail('<?= $tipe_val ?>')">
                        <div class="card-body text-center py-2">
                            <i class="bi bi-folder-fill category-icon text-warning"></i>
                            <h5 class="fw-bold mb-1 text-uppercase text-dark"><?= $tipe_val ?></h5>
                            <span class="badge bg-primary rounded-pill shadow-sm px-3" style="font-size: 0.7rem;"><?= number_format($total) ?> Units</span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

        <?php elseif ($halaman_aktif == 3): ?>
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-body p-0">
                <div class="table-responsive p-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0 text-muted">Master Data Models</h6>
                        <button class="btn btn-dark btn-sm px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTipe">
                            <i class="bi bi-plus-lg me-1"></i> New Model
                        </button>
                    </div>
                    <table class="table table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Kode Barang</th>
                                <th>Kategori</th>
                                <th>Model</th>
                                <th>Deskripsi</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $filter = $keyword ? "WHERE tipe_barang LIKE '%$keyword%' OR model LIKE '%$keyword%' OR kode_barang LIKE '%$keyword%'" : "";
    $query = mysqli_query($koneksi, "SELECT * FROM peripheral_types $filter ORDER BY kode_barang ASC, tipe_barang ASC");
    while ($row = mysqli_fetch_assoc($query)): ?>
                            <tr>
                                <td><span class="badge bg-dark"><?= $row['kode_barang'] ?></span></td>
                                <td><span class="text-muted small fw-bold"><?= $row['tipe_barang'] ?></span></td>
                                <td class="fw-bold"><?= $row['model'] ?></td>
                                <td class="small text-truncate" style="max-width: 200px;"><?= $row['deskripsi_tipe'] ?></td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-edit-tipe"
                                            data-id="<?= $row['id_tipe'] ?>"
                                            data-kode="<?= $row['kode_barang'] ?>"
                                            data-tipe="<?= $row['tipe_barang'] ?>"
                                            data-model="<?= $row['model'] ?>"
                                            data-desc="<?= $row['deskripsi_tipe'] ?>">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>

                                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'superadmin'): ?>
                                    <a href="proses-peripherals.php?aksi=hapus_tipe&id=<?= $row['id_tipe'] ?>"
                                    class="btn btn-sm btn-outline-danger"
                                    onclick="return confirm('Menghapus model ini mungkin berdampak pada data stok. Yakin?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>                    </table>
                </div>
                </div>
            </div>
        <?php elseif ($halaman_aktif == 2): ?>
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-body p-0">
                <div class="p-3">
                    <h5 class="fw-bold mb-5 text-muted"><i class="bi bi-clock-history me-2"></i>Timeline Riwayat Keluar</h5>

                    <div class="timeline">
                        <?php
                        // Filter Data Berdasarkan Bulan dan Tahun
                        $filter = "AND MONTH(p.tanggal_keluar) = '$f_bulan' AND YEAR(p.tanggal_keluar) = '$f_tahun'";
    if ($keyword) {
        $filter .= " AND (p.serial_number LIKE '%$keyword%' OR t.tipe_barang LIKE '%$keyword%' OR t.model LIKE '%$keyword%' OR p.no_ticket LIKE '%$keyword%')";
    }

    $query = mysqli_query($koneksi, "SELECT p.*, t.tipe_barang, t.model
                                                         FROM peripheral_items p
                                                         JOIN peripheral_types t ON p.kode_barang = t.kode_barang
                                                         WHERE p.status='Out' $filter
                                                         ORDER BY p.tanggal_keluar DESC");

    if (mysqli_num_rows($query) > 0):
        while ($row = mysqli_fetch_assoc($query)): ?>

                            <div class="timeline-item">
                                <span class="timeline-date">
                                    <i class="bi bi-calendar3 me-1"></i> <?= date('d M Y - H:i', strtotime($row['tanggal_keluar'])) ?>
                                </span>

                                <div class="card border-0 shadow-sm" style="background-color: #ffffff; border-radius: 12px;">
                                    <div class="card-body p-3">
                                        <div class="row g-3 align-items-center text-center">

                                            <div class="col-12 col-md-4 col-lg-3 text-start border-md-end">
                                                <small class="text-muted fw-bold d-block" style="font-size: 0.65rem;"><?= $row['tipe_barang'] ?></small>
                                                <span class="fw-bold d-block text-truncate text-dark" title="<?= $row['model'] ?>"><?= $row['model'] ?></span>
                                                <span class="badge bg-light text-primary border border-primary mt-1" style="font-size: 0.7rem;"><?= $row['serial_number'] ?></span>
                                            </div>

                                            <div class="col-6 col-md-4 col-lg-2 border-md-end">
                                                <div class="mb-2">
                                                    <small class="text-muted d-block" style="font-size: 0.65rem;">No. PO</small>
                                                    <span class="badge bg-dark px-2" style="font-size: 0.75rem;">
                                                        <?= !empty($row['no_po']) ? $row['no_po'] : '-' ?>
                                                    </span>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block" style="font-size: 0.65rem;">No. PR</small>
                                                    <span class="badge bg-secondary px-2" style="font-size: 0.75rem;">
                                                        <?= !empty($row['no_pr']) ? $row['no_pr'] : '-' ?>
                                                    </span>
                                                </div>
                                            </div>

                                            <div class="col-6 col-md-4 col-lg-2 border-md-end">
                                                <small class="text-muted d-block" style="font-size: 0.65rem;">No. Ticket</small>
                                                <b class="text-danger" style="font-size: 0.85rem;"><?= $row['no_ticket'] ?></b>
                                            </div>

                                            <div class="col-12 col-md-8 col-lg-3 text-start border-lg-end">
                                                <small class="text-muted d-block" style="font-size: 0.65rem;">Keterangan</small>
                                                <div class="small text-secondary" style="font-size: 0.75rem; line-height: 1.2;">
                                                    <?= !empty($row['keterangan']) ? $row['keterangan'] : '-' ?>
                                                </div>
                                            </div>

                                            <div class="col-12 col-md-4 col-lg-2">
                                                <small class="text-muted d-block" style="font-size: 0.65rem;">PIC</small>
                                                <span class="fw-bold text-dark text-truncate d-block" style="font-size: 0.8rem;"><?= $row['admin_keluar'] ?></span>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php endwhile;
    else: ?>
                            <div class="text-center py-5">
                                <i class="bi bi-cloud-slash text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-3">Tidak ada riwayat keluar pada periode <?= $nama_bulan[(int) $f_bulan] ?> <?= $f_tahun ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-warning mt-4">Halaman tidak ditemukan.</div>
        <?php endif; ?>
    </main>

    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 3000">
        <div id="liveToast" class="toast align-items-center text-white bg-primary border-0" role="alert"><div class="d-flex"><div class="toast-body" id="toast-body"></div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div></div>
    </div>

    <!-- Modal Detail Category -->
    <div class="modal fade" id="modalDetailCategory" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-dark text-white py-3">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-view-list me-2"></i> Category Details: <span id="modal-category-title"></span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="p-3 bg-light border-bottom sticky-top" style="z-index: 10;">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" id="searchCategoryDetail" class="form-control border-start-0" placeholder="Cari S/N, Model, atau Nomor PO...">
                        </div>
                    </div>
                    <div id="modal-category-content" class="modal-body-scroll">
                        <!-- Loaded via AJAX -->
                    </div>
                </div>
            </div>
        </div>
    </div>

<div class="modal fade" id="modalTipe" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form class="modal-content border-0 shadow-lg" method="POST" action="proses-peripherals.php">
            <div class="modal-header bg-dark text-white py-3">
                <h6 class="modal-title fw-bold"><i class="bi bi-tag-fill me-2"></i>Tambah Model Baru</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="small fw-bold">PILIH KATEGORI</label>
                    <select name="tipe_barang" class="form-select form-select-sm" required>
                        <option value="">-- Pilih Kategori --</option>
                        <?php include 'peripheral_category.php'; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="small fw-bold">NAMA MODEL/TIPE</label>
                    <input type="text" name="model" class="form-control form-control-sm" placeholder="Contoh: Logitech G102" required>
                </div>

                <div class="mb-3">
                    <label class="small fw-bold">DESKRIPSI MODEL</label>
                    <textarea name="deskripsi_tipe" class="form-control form-control-sm" rows="2" placeholder="Contoh: Wireless Mouse Black"></textarea>
                </div>

                <div class="alert alert-info py-2 mb-0">
                    <small><i class="bi bi-info-circle"></i> Kode Barang akan digenerate otomatis (3 digit).</small>
                </div>
            </div>
            <div class="modal-footer p-2">
                <button type="submit" name="simpan_model" class="btn btn-primary w-100">SIMPAN MODEL</button>
            </div>
        </form>
    </div>
</div>

    <div class="modal fade" id="modalEditTipe" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <form class="modal-content border-0 shadow-lg" method="POST" action="proses-peripherals.php">
                <div class="modal-header bg-dark text-white py-3">
                    <h6 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2"></i>Edit Model Barang</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id_tipe" id="edit_id">

                    <div class="mb-2">
                        <label class="small fw-bold">KODE BARANG (Permanen)</label>
                        <input type="text" id="edit_kode" class="form-control form-control-sm bg-light" readonly>
                    </div>

                    <div class="mb-2">
                        <label class="small fw-bold">KATEGORI</label>
                        <select name="tipe_barang" id="edit_tipe" class="form-select form-select-sm" required>
                            <?php include 'peripheral_category.php'; ?>
                        </select>
                    </div>

                    <div class="mb-2">
                        <label class="small fw-bold">MODEL / BRAND</label>
                        <input type="text" name="model" id="edit_model" class="form-control form-control-sm" required>
                    </div>

                    <div class="mb-0">
                        <label class="small fw-bold">DESKRIPSI</label>
                        <textarea name="deskripsi_tipe" id="edit_desc" class="form-control form-control-sm" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer p-2">
                    <button type="submit" name="update_model" class="btn btn-primary btn-sm px-4">SIMPAN PERUBAHAN</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="modalMasuk" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <form class="modal-content border-0 shadow-lg" method="POST" action="proses-peripherals.php" id="formMasuk">
                <div class="modal-header bg-dark text-white py-3">
                    <h6 class="modal-title fw-bold"><i class="bi bi-arrow-down-circle me-2"></i>Input Barang Masuk Per PO</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body bg-light">
                    <div class="card mb-3 shadow-sm">
                        <div class="card-body p-2">
                            <div class="row g-2 align-items-center">
                                <div class="col-md-4">
                                    <label class="small fw-bold">NOMOR PO</label>
                                    <input type="text" name="no_po" class="form-control form-control-sm text-uppercase" placeholder="Masukkan No. PO..." required>
                                </div>
                                <div class="col-md-8 text-end">
                                    <button type="button" class="btn btn-sm btn-dark" onclick="addGroup()">+ Tambah Group Item (Tipe/Model)</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="groupContainer">
                        </div>
                </div>
                <div class="modal-footer p-2">
                    <button type="submit" name="simpan_masuk" class="btn btn-sm btn-primary px-5 fw-bold">SIMPAN SEMUA DATA</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="modalKeluar" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content border-0 shadow-lg" method="POST" action="proses-peripherals.php">
            <div class="modal-header bg-danger text-white py-3">
                <h6 class="modal-title fw-bold"><i class="bi bi-arrow-up-circle me-2"></i>Barang Keluar</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label class="small fw-bold">SERIAL NUMBER (S/N)</label>
                <input type="text" name="serial_number" class="form-control form-control-sm text-uppercase mb-3 shadow-sm" required autofocus>
                <label class="small fw-bold">NOMOR TICKET / SRV</label>
                <input type="text" name="no_ticket" class="form-control form-control-sm text-uppercase mb-3 shadow-sm" required placeholder="SRV-2023xxxx">
                <label class="form-label small fw-bold">NOMOR PR (PURCHASE REQUISITION)</label>
                <input type="text" name="no_pr" class="form-control form-control-sm mb-3 shadow-sm">
                <label class="small fw-bold">KETERANGAN / USER</label>
                <textarea name="keterangan" class="form-control form-control-sm text-uppercase shadow-sm" rows="2" placeholder="Contoh: GANTI MOUSE USER NIK 123..."></textarea>
            </div>
            <div class="modal-footer border-0 p-3">
                <button type="submit" name="simpan_keluar" class="btn btn-danger w-100 fw-bold py-2 shadow-sm">KONFIRMASI PENGELUARAN BARANG</button>
            </div>
        </form></div>
    </div>

    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="peripherals.js"></script>
<script>
    // Ambil data tipe untuk dropdown di Group
    const dataTipeBarang = [
        <?php
        $t_q = mysqli_query($koneksi, "SELECT DISTINCT tipe_barang FROM peripheral_types ORDER BY tipe_barang ASC");
while ($t = mysqli_fetch_assoc($t_q)) {
    echo "'" . e($t['tipe_barang']) . "',";
}
?>
    ];

    // Ambil data relasi untuk filter model
    const dataRelasi = [
        <?php
$all_types = mysqli_query($koneksi, "SELECT kode_barang, tipe_barang, model FROM peripheral_types");
while ($row = mysqli_fetch_assoc($all_types)) {
    echo "{kode: '{$row['kode_barang']}', tipe: '{$row['tipe_barang']}', model: '{$row['model']}'},";
}
?>
    ];

    // Inisialisasi pesan dari URL jika ada
    const urlParams = new URLSearchParams(window.location.search);
    const msg = urlParams.get('msg');
    const res = urlParams.get('res');

    // --- CATEGORY MODAL LOGIC ---
    const detailModalElem = document.getElementById('modalDetailCategory');
    const detailModal = new bootstrap.Modal(detailModalElem);
    const modalTitle = document.getElementById('modal-category-title');
    const modalContent = document.getElementById('modal-category-content');
    const modalSearch = document.getElementById('searchCategoryDetail');
    let currentActiveTipe = '';

    window.openCategoryDetail = function(tipe) {
        currentActiveTipe = tipe;
        modalTitle.innerText = tipe === 'ALL' ? 'ALL CATEGORIES' : tipe;
        modalSearch.value = '';
        loadAjaxData(tipe, '');
        detailModal.show();
    };

    modalSearch.addEventListener('input', function() {
        loadAjaxData(currentActiveTipe, this.value);
    });

    function loadAjaxData(tipe, query) {
        modalContent.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-2 text-muted">Searching database...</p></div>';
        fetch(`ajax_peripheral_monitoring.php?tipe=${encodeURIComponent(tipe)}&q=${encodeURIComponent(query)}`)
            .then(response => response.text())
            .then(html => {
                modalContent.innerHTML = html;
            })
            .catch(err => modalContent.innerHTML = '<div class="alert alert-danger m-3">Connection error.</div>');
    }

    // Auto-open category if parameter exists
    document.addEventListener('DOMContentLoaded', function() {
        if (urlParams.get('cat') && urlParams.get('halaman') == '1') {
            openCategoryDetail(urlParams.get('cat'));
        }
    });
</script>
</body>
</html>