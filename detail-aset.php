<?php
// detail-aset.php - FINAL (Diperbarui)

include 'session.php';
include 'koneksi.php';

if (!isset($_SESSION['role'])) {
    header("Location: modal-login.php");
    exit;
}

if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'superadmin') {
    echo "<!DOCTYPE html><html lang='id'><head><meta charset='UTF-8'><title>Akses Ditolak</title><link rel='stylesheet' href='bootstrap/css/bootstrap.min.css'><link rel='stylesheet' href='css/style.css'></head><body class='bg-light'>";
    include 'header.php';
    include 'sidebar.php';
    echo "<div class='main-content container py-4'><div class='alert alert-danger'>Akses ditolak! Anda tidak memiliki izin untuk melihat halaman ini.</div></div>";
    echo "<script src='bootstrap/js/bootstrap.bundle.min.js'></script></body></html>";
    exit;
}

function e($text)
{
    return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
}

$aset = null;
$id = 0;
$mode_awal = true;
$is_service_claim_redirect = false;

// Logika Pencarian/Akses Aset

if (isset($_GET['cari']) && !empty($_GET['keyword'])) {
    // Trim untuk spasi, strtoupper untuk paksa kapital
    $keyword = strtoupper(trim(mysqli_real_escape_string($koneksi, $_GET['keyword'])));

    // Query mencari di hostname
    $query = mysqli_query($koneksi, "SELECT * FROM inventori WHERE hostname = '$keyword' OR hostname LIKE '%$keyword%' LIMIT 1");

    if ($query && mysqli_num_rows($query) > 0) {
        $aset = mysqli_fetch_assoc($query);
    } else {
        $aset = null;
    }

    $id = $aset ? $aset['id'] : 0;
    $mode_awal = false;
}

// Jika akses langsung via ID
elseif (isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    if ($id > 0) {
        $query = mysqli_query($koneksi, "SELECT * FROM inventori WHERE id = $id");

        //  KOREKSI 2: Hanya fetch jika query berhasil
        if ($query) {
            $aset = mysqli_fetch_assoc($query);
        } else {
            $aset = null;
        }

        $mode_awal = false;
    }
}
// Jika akses langsung via hostname (dari alur service claim/lanjutkan proses)
elseif (isset($_GET['hostname'])) {
    $hostname = mysqli_real_escape_string($koneksi, $_GET['hostname']);

    // Tambahkan LIMIT 1 (Opsional, tapi disarankan)
    $query = mysqli_query($koneksi, "SELECT * FROM inventori WHERE hostname = '$hostname' LIMIT 1");

    //  KOREKSI 3: Hanya fetch jika query berhasil
    if ($query) {
        $aset = mysqli_fetch_assoc($query);
    } else {
        $aset = null;
    }

    $id = $aset ? $aset['id'] : 0;
    $mode_awal = false;

    // DETEKSI REDIRECT: Cek parameter service claim redirect
    if (isset($_GET['action']) && $_GET['action'] === 'service_claim') {
        $is_service_claim_redirect = true;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Aset</title>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        /* Gaya tetap seperti sebelumnya */
        .main-content { margin-left: 240px; margin-top: 120px; padding: 30px; }
        .timeline { list-style: none; padding-left: 20px; border-left: 3px solid #0d6efd; margin-left: 10px; }
        .timeline li { position: relative; margin-bottom: 30px; padding-left: 20px; }
        .timeline li::before { content: ""; position: absolute; left: -11px; top: 0; width: 16px; height: 16px; background-color: #0d6efd; border-radius: 50%; }
        .timestamp { font-weight: bold; color: #333; }
        .status { font-size: 1rem; margin-top: 5px; }
        .ticket, .note { font-size: 0.9rem; color: #555; }

        /* Modern Improvements */
        .hover-lift { transition: all 0.3s ease; }
        .hover-lift:hover { transform: translateY(-5px); box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.1) !important; }
        .search-container { max-width: 650px; margin: 0 auto; }
        .recent-item { border-radius: 10px; transition: background 0.2s; }
    </style>

</head>
<body class="bg-light">
<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>

<main class="main-content">
<?php if ($mode_awal): ?>
        <div class="container py-4">
            <div class="search-container text-center py-5">
                <div class="mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center bg-white rounded-circle shadow-sm p-4" style="width: 100px; height: 100px;">
                        <i class="bi bi-search text-primary fs-1"></i>
                    </div>
                </div>

                <h2 class="fw-bold text-dark mb-2">Asset Lookup Center</h2>
                <p class="text-muted mb-5 px-md-5">Quickly access detailed hardware specifications, assignment history, and activity logs by searching below.</p>

                <form method="GET" class="mb-5">
                    <div class="input-group input-group-lg shadow-sm">
                        <span class="input-group-text bg-white border-end-0 border-primary pe-0"><i class="bi bi-search text-muted small"></i></span>
                        <input type="text" name="keyword" class="form-control border-start-0 border-primary ps-2" placeholder="Hostname..." required autofocus autocomplete="off">
                        <button type="submit" name="cari" class="btn btn-primary px-4 fw-bold">Search</button>
                    </div>
                </form>

                <?php if (isset($_SESSION['scan_history']) && !empty($_SESSION['scan_history'])): ?>
                    <div class="text-start mt-5 pt-3">
                        <h6 class="text-muted fw-bold mb-3 small" style="letter-spacing: 0.5px;">RECENTLY VIEWED</h6>
                        <div class="row g-3">
                            <?php
                            $recent = array_slice($_SESSION['scan_history'], 0, 3);
                    foreach ($recent as $item): ?>
                                <div class="col-md-4">
                                    <a href="detail-aset.php?hostname=<?= e($item['hostname']) ?>" class="card h-100 text-decoration-none bg-white border-0 shadow-sm hover-lift recent-item">
                                        <div class="card-body p-3">
                                            <div class="d-flex align-items-center mb-1">
                                                <div class="rounded-circle bg-light p-2 me-2">
                                                    <i class="bi bi-laptop text-primary" style="font-size: 0.8rem;"></i>
                                                </div>
                                                <span class="fw-bold text-dark text-truncate small"><?= e($item['hostname']) ?></span>
                                            </div>
                                            <div class="text-muted text-truncate" style="font-size: 0.75rem;"><?= e($item['nama']) ?></div>
                                        </div>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php elseif (!$aset): ?>
        <div class="alert alert-warning border-start border-4 border-warning">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            Aset dengan keyword <strong>"<?= e($_GET['keyword'] ?? '') ?>"</strong> tidak ditemukan. Silakan coba Hostname lain.
        </div>

        <form method="GET" class="mb-4">
            <div class="row g-2">
                <div class="col-md-5">
                    <input type="text" name="keyword" class="form-control" placeholder="Cari hostname..." required>
                </div>
                <div class="col-md-2">
                    <button type="submit" name="cari" class="btn btn-primary w-100">Search</button>
                </div>
            </div>
        </form>
    <?php else: ?>
        <form method="GET" class="mb-4">
            <div class="row g-2">
                <div class="col-md-5">
                    <input type="text" name="keyword" class="form-control" placeholder="Cari hostname atau ticket..." value="<?= isset($_GET['keyword']) ? e($_GET['keyword']) : '' ?>" required>
                </div>
                <div class="col-md-2">
                    <button type="submit" name="cari" class="btn btn-primary w-100"> Cari</button>
                </div>
            </div>
        </form>

    <div class="d-flex align-items-center gap-2 mb-3">

        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#aktivitasModal">
            <i class="bi bi-plus"></i> Tambah Aktivitas
        </button>

        <a href="cetak-form.php?id=<?= e($aset['id']) ?>" target="_blank" class="btn btn-info">
            <i class="bi bi-printer"></i> Accepted Form
        </a>

        <a href="cetak-return-form.php?id=<?= e($aset['id']) ?>" target="_blank" class="btn btn-danger">
            <i class="bi bi-arrow-left-right"></i> Return Form
        </a>

    </div>

<h3 class="mb-4">
       Detail Aset:
      <a href="#" class="edit-btn text-primary text-decoration-none"
        data-bs-toggle="modal" data-bs-target="#editModal"
        data-id="<?= e($aset['id'] ?? '') ?>"
        data-hostname="<?= e($aset['hostname'] ?? '') ?>"
        data-warna="<?= e($aset['warna'] ?? '') ?>"
        data-status="<?= e($aset['status'] ?? '') ?>"
        data-type="<?= e($aset['type'] ?? '') ?>"
        data-serial_number="<?= e($aset['serial_number'] ?? '') ?>"
        data-ram="<?= e($aset['ram'] ?? '') ?>"
        data-storage="<?= e($aset['storage'] ?? '') ?>"
        data-win="<?= e($aset['win'] ?? '') ?>"
        data-keterangan="<?= e($aset['keterangan'] ?? '') ?>"
        data-kelengkapan="<?= e($aset['kelengkapan'] ?? '') ?>"
        data-tanggal_masuk="<?= e($aset['tanggal_masuk'] ?? '') ?>"
        data-tanggal_keluar="<?= e($aset['tanggal_keluar'] ?? '') ?>"
        data-nik="<?= e($aset['nik'] ?? '') ?>"
        data-nama="<?= e($aset['nama'] ?? '') ?>"
        data-divisi="<?= e($aset['divisi'] ?? '') ?>"
        data-domain="<?= e($aset['domain'] ?? '') ?>"
        data-device-category="<?= e($aset['device_category'] ?? '') ?>" >
      <?= e($aset['hostname'] ?? '') ?>
      </a>
    </h3>

        <ul class="nav nav-tabs mb-3" id="asetTabs" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="detail-tab" data-bs-toggle="tab" data-bs-target="#detail" type="button" role="tab" aria-controls="detail" aria-selected="true">Detail Aset</button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="history-tab" data-bs-toggle="tab" data-bs-target="#history" type="button" role="tab" aria-controls="history" aria-selected="false">Histori Aktivitas</button>
      </li>
    </ul>

        <div class="tab-content border border-top-0 p-3 bg-white">

            <div class="tab-pane fade show active" id="detail" role="tabpanel" aria-labelledby="detail-tab">
        <table class="table table-bordered w-100 mb-0">
          <tbody>
            <tr><th>ID Aset</th><td><?= e($aset['id'] ?? '') ?></td></tr>
            <tr><th>Hostname</th><td><?= e($aset['hostname'] ?? '') ?></td></tr>
            <tr><th>Status</th><td><span class="badge bg-primary"><?= e($aset['status'] ?? '') ?></span></td></tr>
            <tr><th>Warna</th><td><?= e($aset['warna'] ?? '') ?></td></tr>
            <tr><th>Type</th><td><?= e($aset['type'] ?? '') ?></td></tr>
            <tr><th>Device Category</th><td><?= e($aset['device_category'] ?? '') ?></td></tr>
            <tr><th>Domain</th><td><?= e($aset['domain'] ?? '') ?></td></tr>
            <tr><th>Serial Number</th><td><?= e($aset['serial_number'] ?? '') ?></td></tr>
            <tr><th>RAM</th><td><?= e($aset['ram'] ?? '') ?></td></tr>
            <tr><th>Storage</th><td><?= e($aset['storage'] ?? '') ?></td></tr>
            <tr><th>Windows</th><td><?= e($aset['win'] ?? '') ?></td></td></tr>
            <tr><th>Kelengkapan</th><td><?= e($aset['kelengkapan'] ?? '') ?></td></tr>
            <tr><th>Tanggal Register</th><td><?= e($aset['tanggal_register'] ?? '') ?></td></tr>
            <tr><th>Tanggal Masuk</th><td><?= e($aset['tanggal_masuk'] ?? '') ?></td></tr>
            <tr><th>Tanggal Keluar</th><td><?= e($aset['tanggal_keluar'] ?? '') ?></td></tr>
            <tr><th>NIK</th><td><?= e($aset['nik'] ?? '') ?></td></tr>
            <tr><th>Nama</th><td><?= e($aset['nama'] ?? '') ?></td></tr>
            <tr><th>Divisi</th><td><?= e($aset['divisi'] ?? '') ?></td></tr>
            <tr><th>Keterangan</th><td><?= e($aset['keterangan'] ?? '') ?></td></tr>
          </tbody>
        </table>
      </div>

            <div class="tab-pane fade" id="history" role="tabpanel" aria-labelledby="history-tab">
        <h5 class="mb-3">Histori Aktivitas</h5>
        <ul class="timeline">
          <?php
          $histori_query = mysqli_query($koneksi, "SELECT * FROM histori_aset WHERE inventori_id = $id ORDER BY tanggal DESC");
        // Pastikan $histori_query berhasil dieksekusi sebelum fetch
        if ($histori_query && mysqli_num_rows($histori_query) > 0):
            while ($row = mysqli_fetch_assoc($histori_query)):
                ?>
            <li>
              <div class="timestamp"><?= e($row['hari'] ?? '') ?><?= e(date('d-m-Y H:i', strtotime($row['tanggal'] ?? ''))) ?></div>
              <div class="status"><strong><?= e($row['aksi'] ?? '') ?></strong> oleh <?= e($row['oleh'] ?? '') ?></div>
              <div class="ticket"> Ticket: <?= e($row['ticket'] ?? 'N/A') ?></div>
              <div class="note"> <?= e($row['catatan'] ?? 'Tidak ada catatan.') ?></div>
            </li>
          <?php endwhile;
        else: ?>
            <li><div class="note text-muted">Belum ada histori aktivitas.</div></li>
          <?php endif; ?>
        </ul>
      </div>
    </div>

    <?php
    include 'modal-aktivitas.php';
        include 'modal-pencarian.php';
        echo '<script src="aktivitas-modal-handler.js"></script>';
        ?>
  <?php endif; ?>

    <?php if ($_SESSION['role'] !== 'normal'): ?>
        <?php include 'modal-edit.php'; ?>
    <?php endif; ?>
</main>

<script src="main.js"></script>
<script src="bootstrap/js/bootstrap.bundle.min.js"></script>
<script>
    window.isServiceClaimRedirect = <?= $is_service_claim_redirect ? 'true' : 'false' ?>;
</script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const urlParams = new URLSearchParams(window.location.search);

    // Cek apakah ada trigger untuk membuka modal aktivitas
    if (urlParams.get('trigger') === 'aktivitas') {
        const modalEl = document.getElementById('aktivitasModal');
        if (modalEl) {
            const aktivitasModal = new bootstrap.Modal(modalEl);

            // 1. Ambil ID Service dari URL
            const idService = urlParams.get('id_service');

            // 2. Isi ke Input Hidden (Penting agar tiket bisa ditutup di tambah-histori.php)
            const inputHiddenService = document.getElementById('modal_id_service');
            if (idService && inputHiddenService) {
                inputHiddenService.value = idService;
            }

            // 3. Isi ke Input Tiket (Visual untuk user)
            const inputTiket = document.getElementById('inputTiket');
            if (idService && inputTiket) {
                inputTiket.value = "SRV-" + idService;
            }

            // 4. Tampilkan Modal
            aktivitasModal.show();
        }
    }
});
</script>

</body>
</html>