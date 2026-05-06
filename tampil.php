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

// --- Parameter Baru ---
$status_filter = $_GET['status'] ?? 'all';
$cari          = mysqli_real_escape_string($koneksi, $_GET['cari'] ?? '');
$divisi_filter = mysqli_real_escape_string($koneksi, $_GET['divisi'] ?? ''); // Filter Divisi
$type_filter   = mysqli_real_escape_string($koneksi, $_GET['type'] ?? '');   // Filter Tipe Laptop

// --- Logika Sorting ---
$sort_column = $_GET['sort'] ?? 'id';
$sort_order  = $_GET['order'] ?? 'ASC';
$next_order  = ($sort_order === 'ASC') ? 'DESC' : 'ASC'; // Untuk toggle link

// Daftar kolom yang diizinkan untuk disortir (keamanan)
$allowed_sort = ['domain', 'status', 'hostname', 'type', 'nama', 'divisi', 'tanggal_masuk', 'warna'];
if (!in_array($sort_column, $allowed_sort)) {
    $sort_column = 'id';
}

$where = [];
if ($status_filter !== 'all') {
    $where[] = "status = '$status_filter'";
}
if (!empty($cari)) {
    $where[] = "(hostname LIKE '%$cari%' OR nama LIKE '%$cari%' OR nik LIKE '%$cari%')";
}
if (!empty($divisi_filter)) {
    $where[] = "divisi = '$divisi_filter'";
}
if (!empty($type_filter)) {
    $where[] = "type = '$type_filter'";
}

$where_clause = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';

$query = "SELECT i.* FROM inventori i $where_clause ORDER BY $sort_column $sort_order";
$result = mysqli_query($koneksi, $query);

// Fungsi pembantu untuk membuat link sort
function sort_link($column, $current_sort, $current_order, $next_order)
{
    $params = $_GET;
    $params['sort'] = $column;
    $params['order'] = ($current_sort === $column) ? $next_order : 'ASC';
    return 'tampil.php?' . http_build_query($params);
}
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
              $btn_class = $is_active ? 'btn-dark' : 'btn-outline-secondary';

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

                <div class="col-md-3">
                    <label class="form-label fw-bold mb-0 small">Cari</label>
                    <input type="text" name="cari" class="form-control form-control-sm" placeholder="Hostname/Nama/NIK" value="<?= e($cari) ?>">
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-bold mb-0 small">Divisi</label>
                    <select name="divisi" class="form-select form-select-sm">
                        <option value="">Semua Divisi</option>
                        <?php
                        $q_divisi = mysqli_query($koneksi, "SELECT DISTINCT divisi FROM inventori WHERE divisi != '' ORDER BY divisi");
while ($d = mysqli_fetch_assoc($q_divisi)) {
    $sel = ($divisi_filter == $d['divisi']) ? 'selected' : '';
    echo "<option value='" . e($d['divisi']) . "' $sel>" . e($d['divisi']) . "</option>";
}
?>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-bold mb-0 small">Tipe Laptop</label>
                    <select name="type" class="form-select form-select-sm">
                        <option value="">Semua Tipe</option>
                        <?php
$q_type = mysqli_query($koneksi, "SELECT DISTINCT type FROM inventori WHERE type != '' ORDER BY type");
while ($t = mysqli_fetch_assoc($q_type)) {
    $sel = ($type_filter == $t['type']) ? 'selected' : '';
    echo "<option value='" . e($t['type']) . "' $sel>" . e($t['type']) . "</option>";
}
?>
                    </select>
                </div>

                <div class="col-auto">
                    <button type="submit" class="btn btn-dark btn-sm">Terapkan</button>
                    <a href="tampil.php" class="btn btn-secondary btn-sm">Reset</a>
                </div>
            </form>
        </div>
      </div>
    </div>

    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
        <div class="d-flex flex-wrap gap-2">
            <?php if ($role !== 'normal'): ?>
                <button type="button" class="btn btn-dark btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#addModal">
                    <i class="bi bi-plus-circle"></i> Tambah
                </button>
                <button type="button" class="btn btn-outline-dark btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#importModal">
                    <i class="bi bi-file-excel"></i> Import
                </button>
                <a href="#" id="export-link" class="btn btn-outline-dark btn-sm shadow-sm">
                    <i class="bi bi-download"></i> Export
                </a>
            <?php endif; ?>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <?php if ($role !== 'normal'): ?>
                <a href="cetak-manual.php" class="btn btn-outline-secondary btn-sm shadow-sm">
                    <i class="bi bi-gear"></i> Kelola Form
                </a>
            <?php endif; ?>

            <?php if ($role === 'superadmin'): ?>
                <button type="button" class="btn btn-outline-secondary btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#tipeLaptopModal">
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
                  <th style="width: 30px;"></th>
                  <th>Domain</th>
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
            <?php if (!empty($type_filter)): ?> exportUrl += '&type=' + encodeURIComponent('<?= e($type_filter); ?>'); <?php endif; ?>
            <?php if (!empty($divisi_filter)): ?> exportUrl += '&divisi=' + encodeURIComponent('<?= e($divisi_filter); ?>'); <?php endif; ?>
            exportLink.href = exportUrl;
        }
    });
  </script>
</body>
</html>