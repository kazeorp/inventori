<?php
// =======================================================
// 1. KONEKSI, SESI, DAN FUNGSI HELPER
// =======================================================

require 'session.php';
require 'koneksi.php';
require 'helpers.php';


// 2. PROTEKSI ROLE
$user_role_login = $_SESSION['role'] ?? 'normal';
if ($user_role_login !== 'admin' && $user_role_login !== 'superadmin') {
    header("Location: index.php"); // Redirect ke dashboard non-admin
    exit;
}

// Ambil ID admin yang sedang login
$admin_id_login = $_SESSION['admin_id'] ?? 0;

// =======================================================
// 3. LOGIKA QUERY PHP (PAGINATION, SUMMARY, SERVICE)
// =======================================================

// --- LOGIKA PAGINATION (Untuk Inventori Utama) ---
$data_per_page = 10;
$current_page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
if ($current_page < 1) {
    $current_page = 1;
}

$start_from = ($current_page - 1) * $data_per_page;

$total_query = mysqli_query($koneksi, "SELECT COUNT(id) AS total FROM inventori");
$total_rows = mysqli_fetch_assoc($total_query)['total'] ?? 0;
$total_pages = ceil($total_rows / $data_per_page);

if ($current_page > $total_pages && $total_pages > 0) {
    $current_page = $total_pages;
    $start_from = ($current_page - 1) * $data_per_page;
}

$inventori_query = mysqli_query(
    $koneksi,
    "SELECT * FROM inventori ORDER BY hostname ASC LIMIT $start_from, $data_per_page",
);

// --- LOGIKA SUMMARY CARDS ---
$stok_total = $stok_spare = $stok_loan = $stok_pending = 0;

if (isset($koneksi)) {
    $summary_query = mysqli_query($koneksi, "
        SELECT
            SUM(CASE WHEN status='Spare' THEN 1 ELSE 0 END) AS stok_spare,
            SUM(CASE WHEN status='Loan' THEN 1 ELSE 0 END) AS stok_loan,
            SUM(CASE WHEN status='Pending Service' THEN 1 ELSE 0 END) AS stok_pending,
            COUNT(id) AS stok_total
        FROM inventori
    ");

    if ($summary_query && mysqli_num_rows($summary_query) > 0) {
        $summary_data = mysqli_fetch_assoc($summary_query);
        $stok_total     = (int) $summary_data['stok_total'];
        $stok_spare     = (int) $summary_data['stok_spare'];
        $stok_loan      = (int) $summary_data['stok_loan'];
        $stok_pending   = (int) $summary_data['stok_pending'];
    }
}

$data = [
    " Total Asset"                 => ["jumlah" => $stok_total, "status" => "all"],
    " Spare"                       => ["jumlah" => $stok_spare, "status" => "Spare"],
    " Loan"                        => ["jumlah" => $stok_loan, "status" => "Loan"],
    " Pending Service"             => ["jumlah" => $stok_pending, "status" => "Pending Service"],
];


// --- LOGIKA QUERY PERIPHERAL DINAMIS ---
$data_peripheral = [];
$sql_peripheral = "
    SELECT
        pt.tipe_barang,
        pt.id_tipe,
        COUNT(pi.id_barang) AS total_stok
    FROM peripheral_types pt
    LEFT JOIN peripheral_items pi ON pt.kode_barang = pi.kode_barang AND pi.status = 'Ready'
    GROUP BY pt.id_tipe
    ORDER BY pt.tipe_barang ASC
";

$result_pt = mysqli_query($koneksi, $sql_peripheral);

if ($result_pt) {
    while ($row_pt = mysqli_fetch_assoc($result_pt)) {
        $data_peripheral[$row_pt['tipe_barang']] = [
            'jumlah' => $row_pt['total_stok'],
            'unit'   => 'Unit',
            'link'   => 'peripherals.php?id_tipe=' . $row_pt['id_tipe'],
            'color'  => 'secondary',
        ];
    }
}

// --- LOGIKA QUERY SERVICE (SESUAI VISIBILITAS) ---
$is_superadmin = ($user_role_login === 'superadmin');

if ($is_superadmin) {
    // Superadmin melihat SEMUA service yang belum selesai
    $visibility_condition = " 1 ";
} else {
    // Admin biasa hanya melihat:
    // 1. Yang belum diklaim (NULL/0)
    // 2. Yang diklaim oleh dirinya sendiri
    $visibility_condition = "
        (sl.current_admin_id IS NULL OR sl.current_admin_id = 0)
        OR
        (sl.current_admin_id = $admin_id_login)
    ";
}

$sql_service = "
    SELECT
        sl.*,
        u1.nama_lengkap AS current_admin_name,
        i.id AS id_inventori,
        i.nama AS nama_user_inventori,
        i.divisi AS divisi_inventori
    FROM service_list sl

    LEFT JOIN admin u1 ON sl.current_admin_id = u1.id
    LEFT JOIN inventori i ON sl.hostname = i.hostname

    WHERE
        ($visibility_condition)
    AND
        sl.finish_status IS NULL
    ORDER BY sl.tanggal_masuk DESC
    LIMIT 10
";

$result_service = mysqli_query($koneksi, $sql_service);

if (!$result_service) {
    // Fatal error jika query gagal
    die(" GAGAL MENJALANKAN QUERY SERVICE: " . mysqli_error($koneksi));
}

// --- LOGIKA LAPORAN BULANAN SERVIS ---
$current_month_name = date('F Y');
$sql_laporan = "SELECT
                                admin_finish_name,
                                COUNT(id_service) AS total_servis_selesai
                            FROM service_list
                            WHERE
                                finish_status IS NOT NULL AND
                                YEAR(finish_timestamp) = YEAR(CURDATE()) AND
                                MONTH(finish_timestamp) = MONTH(CURDATE())
                            GROUP BY
                                admin_finish_name
                            ORDER BY
                                total_servis_selesai DESC";

$result_laporan = mysqli_query($koneksi, $sql_laporan);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin</title>
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/2.0.7/css/dataTables.dataTables.min.css">
</head>
<body>

    <?php include 'header.php'; ?>
    <?php include 'sidebar.php'; ?>

    <main class="main-content">

<h2 class="mb-4">Dashboard Administrator</h2>

        <div class="row g-2"> <?php foreach ($data as $label => $info):
            $link = ($info['status'] === "all") ? "tampil.php" : "tampil.php?status=" . urlencode($info['status']);
            ?>
                <div class="col-lg-3 col-md-6 mb-2">
                    <a href="<?= e($link) ?>" style="text-decoration: none;">
                        <div class="card border-start border-4 border-primary shadow-sm h-100" style="border-radius: var(--radius-md);">
                            <div class="card-body p-2 text-center"> <h6 class="card-title fw-bold mb-1" style="color: var(--app-blue); font-size: 0.75rem;"><?= $label ?></h6>
                                <p class="card-text fw-bold mb-0" style="font-size: 1.1rem;"><?= $info['jumlah'] ?> <span style="font-size: 0.7rem;">Unit</span></p>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>

        <hr class="my-3">

        <div class="row g-2">
            <?php
            $seragam_color = 'secondary';
foreach ($data_peripheral as $label => $info):
    ?>
                <div class="col-lg-2 col-md-4 col-6 mb-2"> <a href="<?= e($info['link']) ?>" style="text-decoration: none;">
                        <div class="card border-top border-3 border-<?= $seragam_color ?> shadow-sm h-100" style="border-radius: var(--radius-md);">
                            <div class="card-body p-2 text-center">
                                <h6 class="card-title fw-bold mb-1" style="font-size: 0.7rem; color: #555;"><?= $label ?></h6>
                                <p class="card-text fw-bold mb-0" style="font-size: 1rem;"><?= $info['jumlah'] ?> <span style="font-size: 0.6rem;"><?= $info['unit'] ?></span></p>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
        <hr class="my-3">


        <h4 class="mt-5 mb-3 text-danger"> Service Aset Masuk Terbaru</h4>

        <div id="service-notification-area" class="mb-4"></div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Hostname</th>
                        <th>Nama User (Pelapor)</th>
                        <th>Divisi</th>
                        <th>Waktu Masuk</th>
                        <th>Catatan</th>
                        <th>Status</th>
                        <th style="width: 150px;">Aksi</th>
                    </tr>
                </thead>
                <tbody id="service-list-body">
                    <?php if ($result_service && mysqli_num_rows($result_service) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result_service)):

                            // Logika Klaim yang Sudah Ada
                            $is_claimed = !empty($row['current_admin_id']) && $row['current_admin_id'] != 0;
                            $is_my_claim = $is_claimed && ((int) $row['current_admin_id'] === (int) ($admin_id_login ?? 0));
                            $is_superadmin_user = $user_role_login === 'superadmin';
                            $can_reassign = $is_claimed && $is_superadmin_user;

                            $claim_badge_text = $is_claimed ? 'DIPICK UP' : 'PENDING';
                            $claim_badge_class = $is_claimed ? 'bg-success' : 'bg-warning text-dark';

                            $admin_klaim_info = $is_claimed
                                                ? ('<small class="text-muted">Oleh: ' . e($row['current_admin_name'] ?? 'N/A') . '</small>')
                                                : '---';

                            //  LOGIKA BARU: Deteksi Status Aset
                            // Jika id_inventori NULL, berarti aset tidak ditemukan di tabel inventori
                            $asset_missing = empty($row['id_inventori']);

                            // Kondisi untuk menampilkan Tombol Registrasi
                            $should_show_registration_button = $asset_missing;

                            // Tentukan Nama User dan Divisi yang akan ditampilkan di tabel (gunakan data service_list jika aset hilang, gunakan data inventori jika ada)
                            $display_nama_user = $asset_missing ? ($row['nama_user'] ?? 'N/A') : ($row['nama_user_inventori'] ?? 'N/A');
                            $display_divisi = $asset_missing ? ($row['divisi'] ?? 'N/A') : ($row['divisi_inventori'] ?? 'N/A');

                            ?>
                            <tr id="service-row-<?= e($row['id_service']) ?>">

                                <td><?= e($row['id_service']) ?></td>

                                <td>
                                    <strong><?= e($row['hostname']) ?></strong><br>
                                    <?= $admin_klaim_info ?>
                                </td>

                                <td><?= e($display_nama_user) ?></td>

                                <td><?= e($display_divisi) ?></td>

                                <td><?= date('d/m/Y H:i', strtotime($row['tanggal_masuk'])) ?></td>

                                <td><?= e($row['catatan'] ?? '-') ?></td>

                                <td>
                                    <span class="badge <?= $claim_badge_class ?>">
                                        <?= $claim_badge_text ?>
                                    </span>
                                </td>

                        <td>
                            <div class="d-flex flex-column gap-1 mx-auto" style="max-width: 140px;">

                                <?php if ($should_show_registration_button): ?>
                                    <button type="button" class="btn btn-sm btn-primary text-white btn-register-service"
                                        data-bs-toggle="modal" data-bs-target="#addModal"
                                        data-service-id="<?= e($row['id_service']) ?>"
                                        data-hostname="<?= e($row['hostname']) ?>"
                                        data-user="<?= e($row['nama_user']) ?>"
                                        data-divisi="<?= e($row['divisi']) ?>">
                                        <i class="bi bi-plus-circle"></i> Registrasi Aset
                                    </button>
                                    <small class="text-danger text-center" style="font-size: 0.65rem;">Aset belum terdaftar</small>

                                <?php else: ?>
                                    <?php if (!$is_claimed): ?>
                                        <button type="button"
                                                class="btn btn-sm btn-success btn-claim"
                                                data-id="<?= $row['id_service'] ?>"
                                                data-hostname="<?= e($row['hostname']) ?>">
                                            <i class="bi bi-person-fill-up"></i> Pick Up
                                        </button>
                                    <?php else: ?>
                                        <?php if ($is_my_claim): ?>
                                            <?php if ($is_superadmin_user): ?>
                                                <button type="button" class="btn btn-sm btn-info text-white btn-reassign"
                                                    data-bs-toggle="modal" data-bs-target="#reassignModal"
                                                    data-id="<?= e($row['id_service']) ?>"
                                                    data-current-admin-name="Saya Sendiri">
                                                    <i class="bi bi-person-fill-gear"></i> Reassign
                                                </button>
                                            <?php endif; ?>

                                            <a href="detail-aset.php?id=<?= e($row['id_inventori']) ?>&action=service_claim" class="btn btn-sm btn-secondary">
                                                <i class="bi bi-gear"></i> Proses
                                            </a>

                                                <button type="button"
                                                        class="btn btn-sm btn-danger btn-selesai"
                                                        data-id="<?= $row['id_service'] ?>"
                                                        data-hostname="<?= e($row['hostname']) ?>">
                                                    <i class="bi bi-check-circle"></i> Selesai
                                                </button>

                                        <?php elseif ($is_superadmin_user): ?>
                                            <button type="button" class="btn btn-sm btn-info text-white btn-reassign"
                                                data-bs-toggle="modal" data-bs-target="#reassignModal"
                                                data-id="<?= e($row['id_service']) ?>"
                                                data-current-admin-name="<?= e($row['current_admin_name'] ?? 'Admin Lain') ?>">
                                                <i class="bi bi-person-fill-gear"></i> Reassign
                                            </button>
                                            <small class="text-muted text-center" style="font-size: 0.65rem italic;">Handle: <?= e($row['current_admin_name']) ?></small>

                                        <?php else: ?>
                                            <small class="text-muted text-center">Sedang diproses</small>
                                        <?php endif; ?>

                                    <?php endif; ?>

                                <?php endif; ?>
                            </div>
                        </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">
                                <?php if ($user_role_login === 'admin'): ?>
                                    Tidak ada aset service yang di pick up/pending untuk Anda.
                                <?php else: ?>
                                    Tidak ada aset service yang masuk saat ini.
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <h4 class="mt-5 mb-3"> Data Asset</h4>

        <?php if ($inventori_query && mysqli_num_rows($inventori_query) > 0): ?>
            <div class="table-responsive mb-4">
                <table class="table table-bordered table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>No.</th>
                            <th>Hostname</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Nama User</th>
                            <th>Divisi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = $start_from + 1;
            while ($row = mysqli_fetch_assoc($inventori_query)):
                ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><a href="detail-aset.php?id=<?= e($row['id']) ?>" class="text-primary text-decoration-none fw-bold"><?= e($row['hostname']) ?></a></td>
                            <td><?= e($row['type']) ?></td>
                            <td><span class="badge bg-secondary"><?= e($row['status']) ?></span></td>
                            <td><?= e($row['nama']) ?></td>
                            <td><?= e($row['divisi']) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <nav>
                <ul class="pagination justify-content-center">
                    <li class="page-item <?= ($current_page <= 1) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $current_page - 1 ?>">Previous</a>
                    </li>

                    <?php
                    $page_range = 5;
            $start_loop = max(1, $current_page - floor($page_range / 2));
            $end_loop = min($total_pages, $current_page + floor($page_range / 2));

            if ($end_loop - $start_loop + 1 < $page_range) {
                $start_loop = max(1, $end_loop - $page_range + 1);
            }

            for ($i = $start_loop; $i <= $end_loop; $i++):
                ?>
                    <li class="page-item <?= ($i == $current_page) ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>

                    <li class="page-item <?= ($current_page >= $total_pages) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $current_page + 1 ?>">Next</a>
                    </li>
                </ul>
            </nav>

        <?php else: ?>
            <div class="alert alert-warning">Tidak ada data inventori yang ditemukan.</div>
        <?php endif; ?>
        </main>

    <?php
    include 'modal-tambahdata.php';
include 'modal-reassign.php';
?>

<script src="bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="http://172.16.3.60:3000/socket.io/socket.io.js"></script>
<script src="main.js"></script>
<script>
    // --- 1. FUNGSI UNTUK PICKUP/CLAIM ---
    function prosesPickup(idService, hostname) {
        if (!confirm('Pick up service untuk ' + hostname + '?')) return;

        const formData = new FormData();
        formData.append('id_service', idService);
        formData.append('hostname', hostname);
        formData.append('loan_hostname', '');

        fetch('ajax_claim_service.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Berhasil: Redirect ke detail-aset.php sesuai response JSON Anda
                window.location.href = data.redirect_url;
            } else {
                showToast(data.message, "danger");
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast("Kesalahan sistem saat memproses pick up.", "danger");
        });
    }

    // --- 2. FUNGSI UNTUK SELESAIKAN SERVICE (Sesuai ajax_selesaikan_service.php Anda) ---
    function prosesSelesai(idService, hostname) {
        if (!confirm('Selesaikan service untuk ' + hostname + '?')) return;

        const formData = new FormData();
        formData.append('id_service', idService); // Mengirim parameter id_service via POST

        fetch('ajax_selesaikan_service.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Tampilkan pesan sukses dari server
                showToast(data.message, "success");

                // Hilangkan baris tabel secara otomatis dengan efek transisi
                const row = document.getElementById('service-row-' + idService);
                if (row) {
                    row.style.transition = "all 0.5s ease";
                    row.style.opacity = "0";
                    row.style.background = "#d1e7dd"; // Hijau muda menandakan selesai
                    setTimeout(() => row.remove(), 500);
                }
            } else {
                showToast(data.message, "danger");
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast("Terjadi kesalahan sistem saat menyelesaikan servis.", "danger");
        });
    }

    // --- 3. LOGIKA TOAST & NOTIFIKASI BAWAAN ANDA ---
    const toastElement = document.getElementById('liveToast');
    const toastInstance = toastElement ? new bootstrap.Toast(toastElement, { delay: 4000 }) : null;

    function showToast(message, type = 'primary') {
        if (!toastInstance) return;
        const body = document.getElementById('toast-body');
        toastElement.className = `toast align-items-center text-white bg-${type} border-0`;
        body.innerText = message;
        toastInstance.show();
    }

    <?php if (isset($_GET['msg'])): ?>
        showToast("<?= $_GET['msg'] ?>", "<?= $_GET['res'] ?? 'primary' ?>");
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.pathname);
        }
    <?php endif; ?>
</script>

</body>
</html>