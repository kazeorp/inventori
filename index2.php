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

// --- LOGIKA QUERY SERVICE TERPISAH ---
$is_superadmin = ($user_role_login === 'superadmin');

// 1. QUERY ANTREAN MASUK (Belum di Pick Up)
// Syarat: current_admin_id kosong/NULL
// Urutan: Tanggal Masuk Terlama ke Terbaru (FIFO)
$sql_service_masuk = "
    SELECT sl.*, i.id AS id_inventori, i.nama AS nama_user_inv, i.divisi AS divisi_inv
    FROM service_list sl
    LEFT JOIN inventori i ON sl.hostname = i.hostname
    WHERE (sl.current_admin_id IS NULL OR sl.current_admin_id = 0)
    AND sl.finish_status IS NULL
    ORDER BY sl.tanggal_masuk ASC
";
$result_service_masuk = mysqli_query($koneksi, $sql_service_masuk);

// 2. QUERY ON PROGRESS (Sudah di Pick Up)
if ($is_superadmin) {
    // Superadmin melihat semua yang sudah diklaim tapi belum selesai
    $progress_condition = "sl.current_admin_id IS NOT NULL AND sl.claim_status = 'On Service'";
} else {
    // Admin biasa hanya melihat yang dia klaim sendiri
    $progress_condition = "sl.current_admin_id = $admin_id_login AND sl.claim_status = 'On Service'";
}

$sql_service_progress = "
    SELECT
        sl.*,
        u1.nama_lengkap AS current_admin_name,
        i.id AS id_inventori
    FROM service_list sl
    LEFT JOIN admin u1 ON sl.current_admin_id = u1.id
    LEFT JOIN inventori i ON sl.hostname = i.hostname
    WHERE $progress_condition
    AND (sl.finish_status IS NULL OR sl.finish_status = '')
    ORDER BY sl.tanggal_masuk ASC
";
$result_service_progress = mysqli_query($koneksi, $sql_service_progress);

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

<div class="row g-2">
    <?php foreach ($data as $label => $info):
        $link = ($info['status'] === "all") ? "tampil.php" : "tampil.php?status=" . urlencode($info['status']);
        ?>
    <div class="col-xl-2 col-lg-3 col-md-4 col-6 mb-1">
        <a href="<?= e($link) ?>" style="text-decoration: none;">
            <div class="card border-0 shadow-sm h-100 overflow-hidden" style="background: #f8f9fa;">
                <div class="card-body p-2 d-flex align-items-center justify-content-between">
                    <div class="text-start overflow-hidden">
                        <h6 class="text-muted mb-0 text-truncate" style="font-size: 0.65rem; text-transform: uppercase;"><?= $label ?></h6>
                        <p class="fw-bold mb-0 text-primary" style="font-size: 1rem;"><?= $info['jumlah'] ?> <small class="fw-normal text-secondary" style="font-size: 0.6rem;">Unit</small></p>
                    </div>
                    <div class="ms-2">
                        <i class="bi bi-box-seam text-light-emphasis" style="font-size: 1.2rem; opacity: 0.5;"></i>
                    </div>
                </div>
                <div style="height: 3px; background-color: var(--bs-primary);"></div>
            </div>
        </a>
    </div>
    <?php endforeach; ?>
</div>

    <hr class="my-3">

    <h4 class="mt-4 mb-3 text-danger"><i class="bi bi-megaphone-fill"></i> Antrean Service Masuk (Belum Pick Up)</h4>
    <div class="table-responsive shadow-sm mb-5">
        <table class="table table-bordered table-hover">
            <thead class="table-dark">
                <tr>
                    <th width="80" class="text-center">No. Antrean</th>
                    <th>Hostname</th>
                    <th>User / Pelapor</th>
                    <th>Divisi</th>
                    <th>Waktu Masuk</th>
                    <th>Keluhan / Catatan</th>
                    <th width="150" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody id="service-list-body">
<?php
$antrean = 1;
if ($result_service_masuk && mysqli_num_rows($result_service_masuk) > 0):
    while ($row = mysqli_fetch_assoc($result_service_masuk)):
        $is_registered = !empty($row['id_inventori']);
        ?>
    <tr>
        <td class="text-center fw-bold text-primary"><?= $antrean++ ?></td>
        <td><strong><?= e($row['hostname']) ?></strong></td>
        <td><?= e($row['nama_user'] ?? $row['nama_user_inv'] ?? 'N/A') ?></td>
        <td><?= e($row['divisi'] ?? $row['divisi_inv'] ?? 'N/A') ?></td>
        <td><small><?= date('d/m/Y H:i', strtotime($row['tanggal_masuk'])) ?></small></td>
        <td><?= e($row['catatan'] ?? '-') ?></td>
        <td class="text-center">
            <?php if ($is_registered): ?>
                <button type="button"
                        class="btn btn-sm btn-success btn-claim"
                        data-id="<?= $row['id_service'] ?>"
                        data-hostname="<?= e($row['hostname']) ?>">
                    <i class="bi bi-hand-index-thumb"></i> Pick Up
                </button>
            <?php else: ?>
                <button type="button" class="btn btn-sm btn-primary btn-register-service"
                        data-bs-toggle="modal" data-bs-target="#addModal"
                        data-service-id="<?= $row['id_service'] ?>"
                        data-hostname="<?= e($row['hostname']) ?>"
                        data-user="<?= e($row['nama_user'] ?? '') ?>"
                        data-divisi="<?= e($row['divisi'] ?? '') ?>">
                    <i class="bi bi-plus-circle"></i> Registrasi Aset
                </button>
            <?php endif; ?>
        </td>
    </tr>
<?php endwhile;
else: ?>
                    <tr><td colspan="7" class="text-center text-muted">Tidak ada antrean service saat ini.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

<h4 class="mt-4 mb-3 text-primary"><i class="bi bi-gear-fill animate__animated animate__rotateIn animate__infinite"></i> Service On Progress</h4>
<div class="table-responsive shadow-sm">
    <table class="table table-bordered table-hover">
        <thead class="table-primary">
            <tr>
                <th width="50" class="text-center">No.</th>
                <th>Hostname</th>
                <th>Teknisi (PIC)</th>
                <th>Waktu Masuk</th>
                <th width="180" class="text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $no_p = 1; // Inisialisasi nomor urut
if ($result_service_progress && mysqli_num_rows($result_service_progress) > 0):
    while ($row = mysqli_fetch_assoc($result_service_progress)):
        // Logika Kepemilikan & Role
        $is_my_job = ($row['current_admin_id'] == $_SESSION['admin_id']);
        $is_superadmin = ($_SESSION['role'] === 'superadmin');
        ?>
            <tr id="service-row-<?= e($row['id_service']) ?>">
                <td class="text-center"><?= $no_p++ ?></td>
                <td><strong><?= e($row['hostname']) ?></strong></td>
                <td><span class="badge bg-info text-dark"><?= e($row['current_admin_name']) ?></span></td>
                <td><small><?= date('d/m/Y H:i', strtotime($row['tanggal_masuk'])) ?></small></td>
                <td class="text-center">
                    <div class="btn-group">
                        <a href="detail-aset.php?id=<?= $row['id_inventori'] ?>" class="btn btn-sm btn-outline-secondary" title="Lihat Detail Aset">
                            <i class="bi bi-search"></i> Detail
                        </a>

                        <?php if ($is_my_job): ?>
                            <a href="detail-aset.php?id=<?= $row['id_inventori'] ?>&id_service=<?= $row['id_service'] ?>&trigger=aktivitas"
                               class="btn btn-sm btn-danger" title="Selesaikan Service">
                                <i class="bi bi-check-circle"></i> Selesai
                            </a>
                        <?php endif; ?>

                        <?php if ($is_superadmin): ?>
                            <button type="button"
                                    class="btn btn-sm btn-warning"
                                    data-bs-toggle="modal"
                                    data-bs-target="#reassignModal"
                                    data-id="<?= $row['id_service'] ?>"
                                    data-current-admin-name="<?= e($row['current_admin_name']) ?>"
                                    title="Pindahkan ke Teknisi Lain">
                                <i class="bi bi-person-gear"></i> Reassign
                            </button>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
        <?php
    endwhile;
else:
    ?>
            <tr>
                <td colspan="5" class="text-center text-muted">Belum ada service yang sedang dikerjakan.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
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

    fetch('ajax_claim_service.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Gunakan reload agar tabel "Antrean" berkurang dan "On Progress" bertambah
            window.location.reload();
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
/**
 * Mengarahkan admin ke halaman detail untuk mengisi form aktivitas
 * @param {number} idService - ID dari tabel service_list
 * @param {string} hostname - Hostname aset
 * @param {number} idInventori - ID dari tabel inventori
 */
function prosesSelesai(idService, hostname, idInventori) {
    if (!idInventori) {
        alert("Gagal mengalihkan: ID Inventori tidak ditemukan.");
        return;
    }

    // Konfirmasi kepada admin
    const tanya = confirm(`Selesaikan servis untuk ${hostname}?\n\nAnda akan diarahkan ke halaman detail untuk mengisi:\n1. Nomor Tiket/WO\n2. Catatan Perbaikan\n3. Unit Pengganti (Loan) jika ada.`);

    if (tanya) {
        // Redirect dengan parameter lengkap
        // id_service akan digunakan untuk menutup tiket di tambah-histori.php
        // trigger=aktivitas akan digunakan untuk otomatis buka modal
        window.location.href = `detail-aset.php?id=${idInventori}&id_service=${idService}&trigger=aktivitas`;
    }
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