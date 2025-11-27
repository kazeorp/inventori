<?php
// ajax_reassign_service.php - DIREVISI FINAL

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
    t_loan.oleh AS pic_loan_name_display -- Mengambil nama PIC dari tabel histori_aset (t_loan)
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
  ) AS t_loan ON i.id = t_loan.inventori_id -- JOIN berdasarkan ID Inventori

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
                        // Tambahkan filter rak dan type
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
  // Pastikan userRole didefinisikan
  const userRole = '<?= isset($_SESSION['role']) ? htmlspecialchars($_SESSION['role']) : 'normal' ?>';

  // Ambil semua parameter filter URL saat ini untuk digunakan saat reload
  const currentURLParams = window.location.search;

  // FUNGSI 1: Memuat ulang body tabel inventori via AJAX
  function reloadTableData(action, id) {
    console.log(`Memuat ulang data tabel karena aksi: ${action} pada ID: ${id}`);

    // Tampilkan indikator loading (opsional)
    const tableBody = document.querySelector('.table-responsive tbody');
    tableBody.innerHTML = '<tr><td colspan="10" class="text-center"><div class="spinner-border spinner-border-sm me-2"></div> Memuat data terbaru...</td></tr>';

    // Panggil file PHP yang hanya me-render <tr> (asumsi: 'table-inventori-ajax.php')
        // *Jika Anda tidak memiliki file terpisah, Anda bisa me-reload seluruh halaman.*

        // Pilihan Paling Sederhana (Reload Penuh):
        window.location.reload();

        // Pilihan Cepat (Jika Anda punya file AJAX terpisah):
        /*
        fetch('table-inventori-ajax.php' + currentURLParams)
            .then(response => response.text())
            .then(html => {
                tableBody.innerHTML = html;
                // Tampilkan notifikasi toast/alert sukses
                showNotification(`Perubahan aset [${action}] terdeteksi!`);
            })
            .catch(error => {
                console.error('Reload AJAX Gagal:', error);
                tableBody.innerHTML = '<tr><td colspan="10" class="text-center text-danger">Gagal memuat ulang data.</td></tr>';
            });
        */
  }

    // FUNGSI 2: Menghubungkan dan Mendengarkan WebSocket
    function connectWebSocket() {
    // GANTI [ALAMAT_IP_SERVER]:[PORT_WS] dengan alamat server Node.js Anda
    const ws = new WebSocket("ws://localhost:8080"); // Contoh default

    ws.onopen = () => {
      console.log("WebSocket connected.");
    };

    ws.onmessage = (event) => {
      try {
        const data = JSON.parse(event.data);
        console.log("WebSocket message received:", data);

        // Hanya panggil fungsi reload jika aksi terkait Inventori
                // Menggunakan startsWith('asset_') untuk mencakup insert, update, delete, bulk
        if (data.action && data.action.startsWith('asset_')) {
          reloadTableData(data.action, data.id);
        }
                // Opsional: Notifikasi jika ada perubahan Service, tapi di halaman ini tidak perlu reload penuh
                else if (data.action && data.action.startsWith('service_')) {
                     console.log(`Perubahan Service (Aksi: ${data.action}) terdeteksi, abaikan di halaman Inventori.`);
                }
      } catch (e) {
        console.error("Error parsing WebSocket message:", e);
      }
    };

    ws.onclose = () => {
      console.log("WebSocket disconnected. Reconnecting in 5 seconds...");
      setTimeout(connectWebSocket, 5000); // Coba sambung ulang
    };

    ws.onerror = (err) => {
      console.error("WebSocket error observed:", err);
      ws.close();
    };
  }

  // Hanya hubungkan jika peran memiliki akses (Admin/Superadmin)
  if (userRole !== 'normal') {
    connectWebSocket();
  }

  // -------------------------------------

  // --- Logika Export Excel (Sudah Benar) ---
    // ... (Logika Export Excel yang sudah ada) ...

 </script>
  <script>
    // Pastikan userRole didefinisikan
  const userRole = '<?= isset($_SESSION['role']) ? htmlspecialchars($_SESSION['role']) : 'normal' ?>';

    // --- LOGIKA WEBSOCKET (Sisi Klien) ---
    // Tambahkan kode untuk menginisialisasi koneksi WebSocket dan mendengarkan event
    function connectWebSocket() {
        // Ganti URL ini dengan URL server WebSocket Anda (e.g., ws://localhost:8080)
        const ws = new WebSocket("ws://[ALAMAT_IP_SERVER]:[PORT_WS]");

        ws.onopen = () => {
            console.log("WebSocket connected.");
        };

        ws.onmessage = (event) => {
            try {
                const data = JSON.parse(event.data);
                console.log("WebSocket message received:", data);

                // Cek apakah update relevan dengan tabel inventori
                if (data.table === 'inventori') {
                    // Notifikasi atau pemicu reload/update parsial
                    alert("Perubahan Inventori Terdeteksi! Silakan muat ulang halaman atau klik OK.");
                    // Opsional: window.location.reload(); // Muat ulang seluruh halaman
                    // Jika Anda ingin memuat ulang bagian tabel saja, gunakan AJAX.
                } else if (data.table === 'service_list') {
                    // Notifikasi jika ada perubahan di service (tergantung kebutuhan)
                    // Jika data service ada di halaman ini, panggil fungsi update service.
                }

            } catch (e) {
                console.error("Error parsing WebSocket message:", e);
            }
        };

        ws.onclose = () => {
            console.log("WebSocket disconnected. Reconnecting in 5 seconds...");
            setTimeout(connectWebSocket, 5000); // Coba sambung ulang
        };

        ws.onerror = (err) => {
            console.error("WebSocket error observed:", err);
            ws.close();
        };
    }

    // Hanya hubungkan jika peran bukan 'normal' (misalnya admin atau superadmin)
    if (userRole !== 'normal') {
        connectWebSocket();
    }
    // -------------------------------------


    // --- LOGIKA EXPORT EXCEL ---
    document.addEventListener('DOMContentLoaded', function() {
        const exportLink = document.getElementById('export-link');
        if (exportLink) {
            // Ambil semua parameter URL saat ini
            const currentParams = new URLSearchParams(window.location.search);

            // Hapus parameter 'cari' dan 'status' yang mungkin kosong jika ingin meng-export semua
            // Namun, karena kita ingin mengekspor data yang ditampilkan, kita gunakan semua parameter.

            // Construct base export URL (asumsi file handler export adalah ajax_export_excel.php)
            let exportUrl = 'ajax_export_excel.php?';

            // Tambahkan parameter status (wajib ada)
            exportUrl += 'status=' + encodeURIComponent('<?= e($status_filter); ?>');

            // Tambahkan parameter pencarian jika ada
            if ('<?= e($cari); ?>' !== '') {
                exportUrl += '&cari=' + encodeURIComponent('<?= e($cari); ?>');
            }

            // Tambahkan parameter rak jika ada
            if ('<?= e($rak_filter); ?>' !== '') {
                exportUrl += '&rak=' + encodeURIComponent('<?= e($rak_filter); ?>');
            }

            // Tambahkan parameter type jika ada
            if ('<?= e($type_filter); ?>' !== '') {
                exportUrl += '&type=' + encodeURIComponent('<?= e($type_filter); ?>');
            }

            exportLink.href = exportUrl;
            console.log("Export URL set to:", exportUrl);
        }
    });
  </script>

  <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="main.js"></script>

</body>
</html>