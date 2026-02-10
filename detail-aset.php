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
        .center-search { height: 70vh; display: flex; justify-content: center; align-items: center; }
        .search-large input { font-size: 1.5rem; padding: 20px; }
        .timeline { list-style: none; padding-left: 20px; border-left: 3px solid #0d6efd; margin-left: 10px; }
        .timeline li { position: relative; margin-bottom: 30px; padding-left: 20px; }
        .timeline li::before { content: ""; position: absolute; left: -11px; top: 0; width: 16px; height: 16px; background-color: #0d6efd; border-radius: 50%; }
        .timestamp { font-weight: bold; color: #333; }
        .status { font-size: 1rem; margin-top: 5px; }
        .ticket, .note { font-size: 0.9rem; color: #555; }
    </style>

</head>
<body class="bg-light">
<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>

<main class="main-content">
<?php if ($mode_awal): ?>
        <div class="center-search w-100">
            <form method="GET" class="search-large w-50">
                <input type="text" name="keyword" class="form-control mb-3" placeholder="Masukkan Hostname atau Nomor Ticket" required autofocus>
                <button type="submit" name="cari" class="btn btn-primary w-100">Cari Aset</button>
            </form>
        </div>
    <?php elseif (!$aset): ?>
        <div class="alert alert-warning border-start border-4 border-warning">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            Aset dengan keyword <strong>"<?= e($_GET['keyword'] ?? '') ?>"</strong> tidak ditemukan. Silakan coba Hostname atau Ticket lain.
        </div>

        <form method="GET" class="mb-4">
            <div class="row g-2">
                <div class="col-md-5">
                    <input type="text" name="keyword" class="form-control" placeholder="Cari hostname atau ticket..." required>
                </div>
                <div class="col-md-2">
                    <button type="submit" name="cari" class="btn btn-primary w-100"> Cari</button>
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
        data-rak="<?= e($aset['rak'] ?? '') ?>"
        data-status="<?= e($aset['status'] ?? '') ?>"
        data-type="<?= e($aset['type'] ?? '') ?>"
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
        data-domain="<?= e($aset['domain'] ?? '') ?>"      data-device-category="<?= e($aset['device_category'] ?? '') ?>" >
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
            <tr><th>Rak</th><td><?= e($aset['rak'] ?? '') ?></td></tr>
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
    const urlParams = new URLSearchParams(window.location.search);
    window.isServiceClaimRedirect = (urlParams.get('action') === 'service_claim');

    document.addEventListener('DOMContentLoaded', function() {
        // --- LOGIKA MODAL SERVICE CLAIM ---
        if (window.isServiceClaimRedirect) {
            const modalElement = document.getElementById('aktivitasModal');
            if (modalElement) {
                const aktivitasModal = new bootstrap.Modal(modalElement);
                const loanToggle = document.getElementById('toggleLoan');

                if (loanToggle && typeof window.controlLoanFields === 'function') {
                    loanToggle.checked = false;
                    window.controlLoanFields(false);
                }

                aktivitasModal.show();

                if (window.history.replaceState) {
                    const cleanUrl = window.location.href.replace(/[?&]action=service_claim/, '');
                    history.replaceState(null, null, cleanUrl);
                }
            }
        }

        // --- LOGIKA TOMBOL EDIT (PERBAIKAN ERROR) ---
        const editBtn = document.querySelector('.edit-btn'); // Cari tombolnya
        const modal = document.getElementById('editModal');  // Cari modalnya

        // HANYA jalankan listener jika tombolnya ditemukan di halaman
        if (editBtn && modal) {
            editBtn.addEventListener('click', function() {
                const button = this;

                // Fungsi internal untuk mengisi value secara aman
                const setModalValue = (selector, dataAttr) => {
                    const input = modal.querySelector(selector);
                    if (input) {
                        input.value = button.getAttribute(dataAttr) || '';
                    }
                };

                setModalValue('#edit-id', 'data-id');
                setModalValue('#edit-hostname', 'data-hostname');
                setModalValue('#edit-rak', 'data-rak');
                setModalValue('#edit-status', 'data-status');
                setModalValue('#edit-type', 'data-type');
                setModalValue('#edit-serial_number', 'data-serial_number');
                setModalValue('#edit-ram', 'data-ram');
                setModalValue('#edit-storage', 'data-storage');
                setModalValue('#edit-win', 'data-win');
                setModalValue('#edit-keterangan', 'data-keterangan');
                setModalValue('#edit-kelengkapan', 'data-kelengkapan');
                setModalValue('#edit-tanggal_masuk', 'data-tanggal_masuk');
                setModalValue('#edit-tanggal_keluar', 'data-tanggal_keluar');
                setModalValue('#edit-nik', 'data-nik');
                setModalValue('#edit-nama', 'data-nama');
                setModalValue('#edit-divisi', 'data-divisi');
                setModalValue('#edit-domain', 'data-domain');
                setModalValue('#edit-device_category', 'data-device-category');
            });
        }
    });
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