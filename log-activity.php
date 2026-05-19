<?php
include 'session.php';
include 'koneksi.php';

// Ambil bulan dan tahun dari filter, default ke bulan & tahun sekarang
$selected_month = $_GET['bulan'] ?? date('m');
$selected_year = $_GET['tahun'] ?? date('Y');

// Query dengan filter bulan dan tahun
$query = "SELECT * FROM admin_log
          WHERE MONTH(log_time) = '$selected_month'
          AND YEAR(log_time) = '$selected_year'
          ORDER BY log_time DESC";
$logs = mysqli_query($koneksi, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Log Activity Admin</title>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <?php include 'header.php'; ?>
    <?php include 'sidebar.php'; ?>

<main class="main-content">
    <div class="container-fluid">
        <h3 class="mb-4"><i class="bi bi-journal-text"></i> Log Activity Admin</h3>

        <div class="card mb-4 shadow-sm">
            <div class="card-body">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Bulan</label>
                        <select name="bulan" class="form-select">
                            <?php
                            $bulan_nama = [
                                '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
                                '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
                                '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember',
                            ];
foreach ($bulan_nama as $val => $nama) {
    $sel = ($val == $selected_month) ? 'selected' : '';
    echo "<option value='$val' $sel>$nama</option>";
}
?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Tahun</label>
                        <select name="tahun" class="form-select">
                            <?php
$year_now = date('Y');
for ($i = $year_now; $i >= 2023; $i--) {
    $sel = ($i == $selected_year) ? 'selected' : '';
    echo "<option value='$i' $sel>$i</option>";
}
?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-filter"></i> Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <div class="timeline">
                    <?php if (mysqli_num_rows($logs) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($logs)):
                            // Tentukan warna badge & icon berdasarkan kata kunci aksi
                            $icon_color = "bg-info";
                            $icon_bi = "bi-info-circle";

                            if (strpos($row['aksi'], 'ADD') !== false) {
                                $icon_color = "bg-success";
                                $icon_bi = "bi-plus-circle";
                            } elseif (strpos($row['aksi'], 'DELETE') !== false) {
                                $icon_color = "bg-danger";
                                $icon_bi = "bi-trash";
                            } elseif (strpos($row['aksi'], 'UPDATE') !== false) {
                                $icon_color = "bg-warning text-dark";
                                $icon_bi = "bi-pencil-square";
                            }
                            ?>
                            <div class="timeline-item pb-4">
                                <div class="timeline-date">
                                    <i class="bi bi-clock"></i> <?= date('d M Y, H:i', strtotime($row['log_time'])) ?>
                                </div>
                                <div class="card border-0 shadow-none bg-light mb-0">
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-center mb-2">
                                            <span class="badge <?= $icon_color ?> me-2">
                                                <i class="bi <?= $icon_bi ?>"></i> <?= $row['aksi'] ?>
                                            </span>
                                            <strong class="text-dark"><?= htmlspecialchars($row['admin_nama_lengkap']) ?></strong>
                                        </div>
                                        <p class="mb-1">
                                            <span class="text-muted small">Target Hostname:</span>
                                            <code class="fw-bold text-primary"><?= htmlspecialchars($row['hostname']) ?></code>
                                        </p>
                                        <div class="text-secondary small mt-2 bg-white p-2 rounded border-start border-3 border-secondary">
                                            <strong>Detail:</strong> <?= htmlspecialchars($row['detail']) ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="bi bi-calendar-x text-muted" style="font-size: 3rem;"></i>
                            <p class="mt-2 text-muted">Tidak ada aktivitas pada periode ini.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
<script>
// Gunakan blok pembungkus agar variabel tidak bentrok dengan global
(function() {
    // Cek apakah socket sudah ada (dari notifikasi_modal.php atau script lain)
    // Jika belum ada, baru kita inisialisasi. Jika sudah ada, gunakan yang sudah ada.
    const activeSocket = (typeof socket !== 'undefined') ? socket : io("http://172.16.3.60:3000");

    activeSocket.on('service_update', (data) => {
        // Mendengarkan sinyal update
        if (data.action === 'asset_update' || data.action === 'log_update') {
            console.log("Aktivitas baru terdeteksi: " + data.action);

            // Refresh halaman agar data log terbaru muncul
            location.reload();
        }
    });
})();
</script>
</body>
</html>