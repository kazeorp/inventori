<?php
include 'session.php';
include 'koneksi.php';
include 'helpers.php';

if (!in_array($_SESSION['role'] ?? '', ['admin', 'superadmin'])) {
    header("Location: index.php");
    exit;
}
$admin_id = $_SESSION['admin_id'] ?? 0;

// 1. Summary Counts
$counts = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(id) AS total,
    SUM(status='Spare') AS spare, SUM(status='Scrap') AS scrap, SUM(status='Pending Service') AS pending,
    SUM(status='Loan') AS loan, SUM(status='Grace Period') AS grace, SUM(status='Ready To Assign') AS ready,
    SUM(status='Assign') AS assign, SUM(status='MT (Management Trainee)') AS mt FROM inventori"));

$dash_items = [
    "Total Asset" => ["v" => $counts['total'], "s" => "all"],
    "Ready To Assign" => ["v" => $counts['ready'], "s" => "Ready To Assign"],
    "Spare" => ["v" => $counts['spare'], "s" => "Spare"],
    "Assign" => ["v" => $counts['assign'], "s" => "Assign"],
    "Loan" => ["v" => $counts['loan'], "s" => "Loan"],
    "Pending Service" => ["v" => $counts['pending'], "s" => "Pending Service"],
    "Grace Period" => ["v" => $counts['grace'], "s" => "Grace Period"],
    "MT" => ["v" => $counts['mt'], "s" => "MT (Management Trainee)"],
    "Scrap" => ["v" => $counts['scrap'], "s" => "Scrap"],
];

// 2. Service Lists
$sql_base = "SELECT sl.*, i.id AS id_inv, i.nama AS user_inv, i.divisi AS div_inv, u.nama_lengkap AS pic FROM service_list sl
             LEFT JOIN inventori i ON sl.hostname = i.hostname LEFT JOIN admin u ON sl.current_admin_id = u.id ";

$q_antrean = mysqli_query($koneksi, $sql_base . "WHERE (sl.current_admin_id IS NULL OR sl.current_admin_id = 0) AND sl.finish_status IS NULL ORDER BY sl.tanggal_masuk ASC");
$prog_cond = ($_SESSION['role'] === 'superadmin') ? "sl.current_admin_id IS NOT NULL" : "sl.current_admin_id = $admin_id";
$q_progress = mysqli_query($koneksi, $sql_base . "WHERE $prog_cond AND sl.claim_status='On Service' AND sl.finish_status IS NULL ORDER BY sl.tanggal_masuk ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin</title>
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body>
    <?php include 'header.php';
include 'sidebar.php'; ?>
    <main class="main-content">
        <h2 class="mb-4">Dashboard Administrator</h2>
        <div class="row g-2">
            <?php foreach ($dash_items as $lbl => $inf): $slug = strtolower(preg_replace('/[^a-z0-9]/', '-', $lbl)); ?>
            <div class="col-xl-2 col-lg-3 col-md-4 col-6">
                <a href="tampil.php?status=<?= urlencode($inf['s']) ?>" class="dash-card-link">
                    <div class="card dash-card stat-<?= $slug ?>"><div class="card-body">
                        <div class="dash-card-label"><?= $lbl ?></div>
                        <div class="dash-card-value"><?= number_format($inf['v'] ?? 0) ?><span class="dash-card-unit">Unit</span></div>
                    </div></div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
        <hr class="my-3">
        <ul class="nav nav-pills mb-3 shadow-sm p-1 bg-light rounded" id="serviceTab" style="width: fit-content;">
            <li class="nav-item"><button class="nav-link active fw-bold" data-bs-toggle="tab" data-bs-target="#antrean-content"><i class="bi bi-megaphone-fill me-1"></i> Antrean</button></li>
            <li class="nav-item"><button class="nav-link fw-bold" data-bs-toggle="tab" data-bs-target="#progress-content"><i class="bi bi-gear-fill me-1"></i> On Progress</button></li>
        </ul>
        <div class="tab-content" id="serviceTabContent">
            <div class="tab-pane fade show active" id="antrean-content">
                <table class="table table-bordered table-hover bg-white">
                    <thead class="table-dark"><tr><th>No.</th><th>Hostname</th><th>User</th><th>Divisi</th><th>Waktu</th><th>Info</th><th>Aksi</th></tr></thead>
                    <tbody id="service-list-body">
                        <?php $no = 1;
while ($r = mysqli_fetch_assoc($q_antrean)): $reg = !empty($r['id_inv']); ?>
                        <tr><td><?= $no++ ?></td><td><strong><?= e($r['hostname']) ?></strong></td><td><?= e($r['nama_user'] ?? $r['user_inv'] ?? '-') ?></td><td><?= e($r['divisi'] ?? $r['div_inv'] ?? '-') ?></td><td><small><?= date('d/m/y H:i', strtotime($r['tanggal_masuk'])) ?></small></td><td><?= e($r['catatan'] ?? '-') ?></td><td>
                            <?php if ($reg): ?><button class="btn btn-sm btn-primary btn-claim" data-id="<?= $r['id_service'] ?>" data-hostname="<?= e($r['hostname']) ?>"><i class="bi bi-hand-index"></i> Pick Up</button>
                            <?php else: ?><button class="btn btn-sm btn-outline-primary btn-register-service" data-bs-toggle="modal" data-bs-target="#addModal" data-service-id="<?= $r['id_service'] ?>" data-hostname="<?= e($r['hostname']) ?>" data-user="<?= e($r['nama_user'] ?? '') ?>" data-divisi="<?= e($r['divisi'] ?? '') ?>"><i class="bi bi-plus"></i> Register</button><?php endif; ?>
                        </td></tr>
                        <?php endwhile;
if ($no === 1) {
    echo '<tr><td colspan="7" class="text-center">Kosong</td></tr>';
} ?>
                    </tbody>
                </table>
            </div>
            <div class="tab-pane fade" id="progress-content">
                <table class="table table-bordered table-hover bg-white">
                    <thead class="table-light"><tr><th>No.</th><th>Hostname</th><th>PIC</th><th>Waktu</th><th class="text-center">Aksi</th></tr></thead>
                    <tbody>
                        <?php $no = 1;
while ($r = mysqli_fetch_assoc($q_progress)): ?>
                        <tr><td><?= $no++ ?></td><td><strong><?= e($r['hostname']) ?></strong></td><td><span class="badge bg-info text-dark"><?= e($r['pic']) ?></span></td><td><small><?= date('d/m/y H:i', strtotime($r['tanggal_masuk'])) ?></small></td><td class="text-center"><div class="btn-group">
                            <a href="detail-aset.php?id=<?= $r['id_inv'] ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-search"></i></a>
                            <?php if ($r['current_admin_id'] == $admin_id): ?><a href="detail-aset.php?id=<?= $r['id_inv'] ?>&id_service=<?= $r['id_service'] ?>&trigger=aktivitas" class="btn btn-sm btn-dark" title="Selesaikan"><i class="bi bi-check2-circle"></i></a><?php endif; ?>
                            <?php if ($_SESSION['role'] === 'superadmin'): ?><button class="btn btn-sm btn-outline-dark" data-bs-toggle="modal" data-bs-target="#reassignModal" data-id="<?= $r['id_service'] ?>" data-current-admin-name="<?= e($r['pic']) ?>" title="Reassign"><i class="bi bi-person-gear"></i></button><?php endif; ?>
                        </div></td></tr>
                        <?php endwhile;
if ($no === 1) {
    echo '<tr><td colspan="5" class="text-center">Kosong</td></tr>';
} ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
    <?php include 'modal-tambahdata.php';
include 'modal-reassign.php'; ?>
    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="http://172.16.3.60:3000/socket.io/socket.io.js"></script>
    <script src="main.js"></script> <!-- Pastikan main.js dimuat sebelum script ini -->
<script>
    // index2.php utilizes global logic in main.js for claiming and redirecting.
    <?php if (isset($_GET['msg'])): ?>
        const toast = new bootstrap.Toast(document.getElementById('liveToast')); // Assuming liveToast is defined in toast.php
        document.getElementById('toast-body').innerText = "<?= $_GET['msg'] ?>";
        document.getElementById('liveToast').classList.add('bg-<?= $_GET['res'] ?? 'primary' ?>');
        toast.show();
        if (window.history.replaceState) history.replaceState(null, null, window.location.pathname);
    <?php endif; ?>
</script>
</body>
</html>