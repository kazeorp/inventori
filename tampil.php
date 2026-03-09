<?php
// tampil.php - DIREVISI FINAL (Tampilan Dirapikan)

include 'session.php';
include "koneksi.php";

// Mengambil role dari session
$role = isset($_SESSION['role']) ? $_SESSION['role'] : 'normal';

function e($text)
{
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
    'Ready to Assign' => 'Ready to Assign',
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

// Query utama
$query = "
  SELECT
    i.*,
    t_loan.oleh AS pic_loan_name_display
  FROM
    inventori i
  LEFT JOIN (
    SELECT
      t1.inventori_id,
      t1.oleh,
      t1.tanggal
    FROM
      histori_aset t1
    INNER JOIN (
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
  ) AS t_loan ON i.id = t_loan.inventori_id
  $where_clause
  ORDER BY i.id ASC
";

$result = mysqli_query($koneksi, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Data Inventori Gudang</title>
  <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

  <style>
    /* Global Font Size Adjustment */
    body {
        font-size: 0.875rem; /* Sekitar 14px */
        background-color: #f8f9fa;
    }

    /* Table Specific Styling for Compactness */
    .table thead th {
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        white-space: nowrap; /* Header tidak turun baris */
        vertical-align: middle;
        padding: 0.6rem 0.5rem;
    }

    .table td {
        font-size: 0.825rem; /* Font isi tabel lebih kecil */
        vertical-align: middle;
        padding: 0.4rem 0.5rem; /* Padding diperkecil agar baris lebih pendek */
    }

    /* Filter & Buttons */
    .filter-buttons .btn {
      margin-right: 3px;
      margin-bottom: 3px;
      font-size: 0.75rem; /* Tombol filter lebih kecil */
      padding: 0.25rem 0.5rem;
    }

    .filter-container {
      padding: 1rem !important;
    }

    .main-content h2 {
        font-size: 1.5rem; /* Judul tidak terlalu besar */
    }

    /* Utility */
    .gap-2 { gap: 0.5rem !important; }
  </style>
</head>
<body class="bg-light">

  <?php include 'header.php'; ?>
  <?php include 'sidebar.php'; ?>

<main class="main-content">
  <div class="container-fluid pt-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="text-dark fw-bold m-0">
          Data Inventori <?= ($status_filter === 'all') ? '' : ' <small class="text-muted">/ ' . htmlspecialchars($status_filter) . '</small>' ?>
        </h2>
    </div>

    <div class="mb-3 p-3 bg-white rounded shadow-sm filter-container">
      <div class="row g-2">

        <div class="col-12 mb-2 border-bottom pb-2">
          <small class="fw-bold text-muted d-block mb-1">Filter Status:</small>
          <?php foreach ($status_options as $status_key => $label):
              $is_active = $status_filter === $status_key;
              $btn_class = $is_active ? 'btn-primary' : 'btn-outline-secondary'; // Ubah warna tidak aktif jadi abu-abu agar lebih soft

              $url = 'tampil.php?status=' . urlencode($status_key);
              if (!empty($cari)) {
                  $url .= '&cari=' . urlencode($cari);
              }
              if (!empty($rak_filter)) {
                  $url .= '&rak=' . urlencode($rak_filter);
              }
              if (!empty($type_filter)) {
                  $url .= '&type=' . urlencode($type_filter);
              }
              ?>
            <a href="<?= e($url) ?>" class="btn <?= $btn_class ?> btn-sm filter-buttons">
              <?= e($label) ?>
            </a>
          <?php endforeach; ?>
        </div>

        <div class="col-12">
           <form method="GET" class="row g-2 align-items-end">
                <input type="hidden" name="status" value="<?= e($status_filter); ?>">

                <div class="col-md-4 col-sm-6">
                    <label for="cari_input" class="form-label fw-bold mb-0 small">Cari (Hostname/Nama/NIK)</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input type="text" name="cari" id="cari_input" class="form-control form-control-sm" placeholder="Ketik kata kunci..." value="<?= isset($_GET['cari']) ? htmlspecialchars($_GET['cari']) : ''; ?>">
                    </div>
                </div>

                <div class="col-auto">
                    <button type="submit" class="btn btn-primary btn-sm">Terapkan</button>
                    <a href="tampil.php" class="btn btn-secondary btn-sm">Reset</a>
                </div>
           </form>
        </div>
      </div>
    </div>

    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
        <div class="d-flex flex-wrap gap-2">
            <?php if ($role !== 'normal'): ?>
                <button type="button" class="btn btn-primary btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#addModal">
                    <i class="bi bi-plus-circle"></i> Tambah
                </button>
                <button type="button" class="btn btn-success btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#importModal">
                    <i class="bi bi-file-excel"></i> Import
                </button>
                <a href="#" id="export-link" class="btn btn-outline-success btn-sm shadow-sm">
                    <i class="bi bi-download"></i> Export
                </a>
            <?php endif; ?>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <?php if ($role !== 'normal'): ?>
                <a href="cetak-manual.php" class="btn btn-info text-white btn-sm shadow-sm">
                    <i class="bi bi-gear"></i> Kelola Form
                </a>
            <?php endif; ?>

            <?php if ($role === 'superadmin'): ?>
                <button type="button" class="btn btn-warning text-white btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#tipeLaptopModal">
                    <i class="bi bi-laptop"></i> Master Data
                </button>
            <?php endif; ?>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover table-sm mb-0">
              <thead class="table-dark">
                <tr>
                  <th>Domain</th>
                  <th>Rak</th>
                  <th>Status</th>
                  <th>Hostname & Tipe</th>
                  <th>Spesifikasi</th>
                  <th>Tgl. Register</th>
                  <th>Tgl. In/Out</th>
                  <th>User & Divisi</th>
                  <th>Keterangan</th>
                </tr>
              </thead>
              <tbody>
                <?php include 'table-inventori.php'; ?>
              </tbody>
            </table>
          </div>
        </div>
    </div>

  </div>
</main>

<?php include 'toast.php'; ?>
  <?php include 'modal-edit.php'; ?>
  <?php include 'modal-tambahdata.php'; ?>
  <?php include 'modal-import.php'; ?>
  <?php include 'modal-tipe-laptop.php'; ?>

  <script>
    const userRole = '<?= isset($_SESSION['role']) ? htmlspecialchars($_SESSION['role']) : 'normal' ?>';
    // Hapus baris fireToast jika ada di sini
  </script>



  <script src="http://172.16.3.60:3000/socket.io/socket.io.js"></script>

  <script src="main.js"></script>
  <script src="tipe_inventori.js"></script>

  <script>
    // Logika Export diletakkan paling bawah setelah DOM siap
    document.addEventListener('DOMContentLoaded', function() {
        const exportLink = document.getElementById('export-link');
        if (exportLink) {
            let exportUrl = 'export_inventori.php?status=' + encodeURIComponent('<?= e($status_filter); ?>');
            <?php if (!empty($cari)): ?> exportUrl += '&cari=' + encodeURIComponent('<?= e($cari); ?>'); <?php endif; ?>
            <?php if (!empty($rak_filter)): ?> exportUrl += '&rak=' + encodeURIComponent('<?= e($rak_filter); ?>'); <?php endif; ?>
            <?php if (!empty($type_filter)): ?> exportUrl += '&type=' + encodeURIComponent('<?= e($type_filter); ?>'); <?php endif; ?>
            exportLink.href = exportUrl;
        }
    });
  </script>
</body>
</html>