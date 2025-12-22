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
                                    '01'=>'Januari', '02'=>'Februari', '03'=>'Maret', '04'=>'April',
                                    '05'=>'Mei', '06'=>'Juni', '07'=>'Juli', '08'=>'Agustus',
                                    '09'=>'September', '10'=>'Oktober', '11'=>'November', '12'=>'Desember'
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

            <div class="card shadow-sm">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>Waktu</th>
                                <th>Admin</th>
                                <th>Aksi</th>
                                <th>Hostname</th>
                                <th>Detail</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($logs) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($logs)): ?>
                                    <tr>
                                        <td><small class="text-muted"><?= date('d/m/Y H:i', strtotime($row['log_time'])) ?></small></td>
                                        <td><strong><?= htmlspecialchars($row['admin_nama_lengkap']) ?></strong></td>
                                        <td>
                                            <?php
                                                $badge = 'bg-info';
                                                if(strpos($row['aksi'], 'ADD') !== false) $badge = 'bg-success';
                                                if(strpos($row['aksi'], 'DELETE') !== false) $badge = 'bg-danger';
                                            ?>
                                            <span class="badge <?= $badge ?>"><?= $row['aksi'] ?></span>
                                        </td>
                                        <td><code class="fw-bold"><?= htmlspecialchars($row['hostname']) ?></code></td>
                                        <td><?= htmlspecialchars($row['detail']) ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">Tidak ada aktivitas pada periode ini.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>