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

function e($text) {
    return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
}

$aset = null;
$id = 0;
$mode_awal = true;
$is_service_claim_redirect = false;

// Logika Pencarian/Akses Aset
if (isset($_GET['cari']) && !empty($_GET['keyword'])) {
    $keyword = mysqli_real_escape_string($koneksi, $_GET['keyword']);
    $query = mysqli_query($koneksi, "SELECT * FROM inventori WHERE hostname LIKE '%$keyword%' OR ticket LIKE '%$keyword%' LIMIT 1");

    //  KOREKSI 1: Hanya fetch jika query berhasil
    if ($query) {
        $aset = mysqli_fetch_assoc($query);
    } else {
        $aset = null; // Pastikan $aset null jika query gagal
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
                <input type="text" name="keyword" class="form-control mb-3" placeholder=" Masukkan hostname" required>
                <button type="submit" name="cari" class="btn btn-primary w-100">Cari Aset</button>
            </form>
        </div>
    <?php elseif (!$aset): ?>
        <div class="alert alert-warning">
            Aset dengan keyword tersebut tidak ditemukan. Silakan coba kata kunci lain.
        </div>
        <form method="GET" class="mb-4">
            <div class="row">
                <div class="col-md-5">
                    <input type="text" name="keyword" class="form-control" placeholder="Cari berdasarkan hostname atau ticket" required>
                </div>
                <div class="col-md-2">
                    <button type="submit" name="cari" class="btn btn-primary"> Cari</button>
                </div>
            </div>
        </form>
    <?php else: ?>
        <form method="GET" class="mb-4">
            <div class="row">
                <div class="col-md-5">
                    <input type="text" name="keyword" class="form-control" placeholder="Cari berdasarkan hostname atau ticket" value="<?= isset($_GET['keyword']) ? e($_GET['keyword']) : '' ?>" required>
                </div>
                <div class="col-md-2">
                    <button type="submit" name="cari" class="btn btn-primary"> Cari</button>
                </div>
            </div>
        </form>

        <button type="button" class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#aktivitasModal">
             <i class="bi bi-search"></i> Tambah Aktivitas
        </button>

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
            <tr><th>RAM</th><td><?= e($aset['ram'] ?? '') ?></td></tr>
            <tr><th>Storage</th><td><?= e($aset['storage'] ?? '') ?></td></tr>
            <tr><th>Windows</th><td><?= e($aset['win'] ?? '') ?></td></td></tr>
            <tr><th>Kelengkapan</th><td><?= e($aset['kelengkapan'] ?? '') ?></td></tr>
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
          <?php endwhile; else: ?>
            <li><div class="note text-muted">Belum ada histori aktivitas.</div></li>
          <?php endif; ?>
        </ul>
      </div>
    </div>

    <?php
    include 'modal-aktivitas.php';
    ?>
  <?php endif; ?>

    <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'superadmin'): ?>
        <?php include 'modal-edit.php'; ?>
    <?php endif; ?>
</main>

<script src="main.js"></script>
<script src="aktivitas-modal-handler.js"></script>
<script src="bootstrap/js/bootstrap.bundle.min.js"></script>
<script>
    window.isServiceClaimRedirect = <?= $is_service_claim_redirect ? 'true' : 'false' ?>;
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
		if (window.isServiceClaimRedirect) {
				const modalElement = document.getElementById('aktivitasModal');

				if (modalElement) {
					const aktivitasModal = new bootstrap.Modal(modalElement);

					// --- PENAMBAHAN UNTUK TOMBOL PROSES (Default ON) ---
					const loanToggle = document.getElementById('toggleLoan');

					if (loanToggle && typeof window.controlLoanFields === 'function') {
						loanToggle.checked = false; // Matikan secara visual
						window.controlLoanFields(false); // Matikan secara fungsional (tampilkan field NIK/Nama)
						console.log("JS DEBUG: Proses Claim Selesai. Default Loan diatur OFF.");
					}
					// ---------------------------------------------------

					aktivitasModal.show();

					// Hapus parameter URL agar refresh tidak memicu ulang modal
					if (window.history.replaceState) {
						const cleanUrl = window.location.protocol + "//" +
										 window.location.host +
										 window.location.pathname +
										 window.location.search.replace(/[?&]action=service_claim/, '');
						history.replaceState(null, null, cleanUrl);
					}
				} else {
					console.warn('Modal element with ID #aktivitasModal not found.');
				}
			}

        // Listener untuk Tombol Edit Aset (untuk memuat data ke modal-edit.php)
        document.querySelector('.edit-btn').addEventListener('click', function() {
            const button = this;
            const modal = document.getElementById('editModal');

            // Memuat data ke form di modal-edit.php
            modal.querySelector('#edit-id').value = button.getAttribute('data-id');
            modal.querySelector('#edit-hostname').value = button.getAttribute('data-hostname');
            modal.querySelector('#edit-rak').value = button.getAttribute('data-rak');
            modal.querySelector('#edit-status').value = button.getAttribute('data-status');
            modal.querySelector('#edit-type').value = button.getAttribute('data-type');
            modal.querySelector('#edit-ram').value = button.getAttribute('data-ram');
            modal.querySelector('#edit-storage').value = button.getAttribute('data-storage');
            modal.querySelector('#edit-win').value = button.getAttribute('data-win');
            modal.querySelector('#edit-keterangan').value = button.getAttribute('data-keterangan');
            modal.querySelector('#edit-kelengkapan').value = button.getAttribute('data-kelengkapan');
            modal.querySelector('#edit-tanggal_masuk').value = button.getAttribute('data-tanggal_masuk');
            modal.querySelector('#edit-tanggal_keluar').value = button.getAttribute('data-tanggal_keluar');
            modal.querySelector('#edit-nik').value = button.getAttribute('data-nik');
            modal.querySelector('#edit-nama').value = button.getAttribute('data-nama');
            modal.querySelector('#edit-divisi').value = button.getAttribute('data-divisi');
            // Tambahkan kolom baru ke Modal Edit
            modal.querySelector('#edit-domain').value = button.getAttribute('data-domain');
            modal.querySelector('#edit-device_category').value = button.getAttribute('data-device-category');
        });
    });
</script>




</body>
</html>