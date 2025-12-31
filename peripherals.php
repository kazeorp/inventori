<?php
require 'session.php';
require 'koneksi.php';

$admin_sekarang = $_SESSION['nama_lengkap'] ?? 'Admin IT';

// =======================================================
// 1. API INTERNAL UNTUK CEK DUPLIKASI SN VIA AJAX
// =======================================================
if (isset($_GET['cek_sn'])) {
    $sn = mysqli_real_escape_string($koneksi, $_GET['cek_sn']);
    $cek = mysqli_query($koneksi, "SELECT serial_number FROM peripheral_items WHERE serial_number = '$sn'");
    echo json_encode(['exists' => mysqli_num_rows($cek) > 0]);
    exit;
}

// =======================================================
// 2. LOGIKA PHP: PROSES FORM
// =======================================================

// SIMPAN TIPE BARU
if (isset($_POST['simpan_tipe'])) {
    $kode_barang = strtoupper(mysqli_real_escape_string($koneksi, $_POST['kode_barang']));
    $tipe_barang = strtoupper(mysqli_real_escape_string($koneksi, $_POST['tipe_barang']));
    $model = strtoupper(mysqli_real_escape_string($koneksi, $_POST['model'])); // Tambah ini
    $deskripsi_tipe = strtoupper(mysqli_real_escape_string($koneksi, $_POST['deskripsi_tipe']));
    mysqli_query($koneksi, "INSERT INTO peripheral_types (kode_barang, tipe_barang, model, deskripsi_tipe) VALUES ('$kode_barang', '$tipe_barang', '$model', '$deskripsi_tipe')");
    header("Location: peripherals.php?halaman=2&res=success&msg=Tipe berhasil ditambah"); exit;
}

// UPDATE TIPE
if (isset($_POST['update_tipe'])) {
    $id_tipe = $_POST['id_tipe'];
    $kode_barang = strtoupper(mysqli_real_escape_string($koneksi, $_POST['kode_barang']));
    $tipe_barang = strtoupper(mysqli_real_escape_string($koneksi, $_POST['tipe_barang']));
    $model = strtoupper(mysqli_real_escape_string($koneksi, $_POST['model'])); // Tambah ini
    $deskripsi_tipe = strtoupper(mysqli_real_escape_string($koneksi, $_POST['deskripsi_tipe']));
    mysqli_query($koneksi, "UPDATE peripheral_types SET kode_barang='$kode_barang', tipe_barang='$tipe_barang', model='$model', deskripsi_tipe='$deskripsi_tipe' WHERE id_tipe='$id_tipe'");
    header("Location: peripherals.php?halaman=2&res=success&msg=Tipe berhasil diupdate"); exit;
}

// BARANG MASUK
if (isset($_POST['simpan_masuk'])) {
    $kode_barang = $_POST['kode_barang'];
    $no_po = strtoupper(mysqli_real_escape_string($koneksi, $_POST['no_po']));
    $peruntukan = $_POST['peruntukan'];
    $nama_user = ($peruntukan == 'User') ? strtoupper(mysqli_real_escape_string($koneksi, $_POST['nama_user'])) : NULL;
    $sns = $_POST['sn'];
    foreach ($sns as $sn) {
        if (!empty(trim($sn))) {
            $sn_clean = strtoupper(mysqli_real_escape_string($koneksi, trim($sn)));
            mysqli_query($koneksi, "INSERT INTO peripheral_items (kode_barang, no_po, serial_number, peruntukan, nama_user, tanggal_masuk, admin_input, status) VALUES ('$kode_barang', '$no_po', '$sn_clean', '$peruntukan', '$nama_user', NOW(), '$admin_sekarang', 'Stock')");
        }
    }
    header("Location: peripherals.php?halaman=1&res=success&msg=Barang masuk berhasil disimpan"); exit;
}

// BARANG KELUAR
if (isset($_POST['simpan_keluar'])) {
    $sn = strtoupper(mysqli_real_escape_string($koneksi, $_POST['serial_number']));
    $no_tkt = strtoupper(mysqli_real_escape_string($koneksi, $_POST['no_ticket']));
    $ket = strtoupper(mysqli_real_escape_string($koneksi, $_POST['keterangan']));

    mysqli_query($koneksi, "UPDATE peripheral_items SET no_ticket='$no_tkt', keterangan='$ket', tanggal_keluar=NOW(), admin_keluar='$admin_sekarang', status='Out' WHERE serial_number='$sn' AND status='Stock'");
    $res = mysqli_affected_rows($koneksi) > 0 ? "success" : "danger";
    $msg = $res == "success" ? "Barang keluar berhasil diproses" : "Gagal! S/N tidak ditemukan atau sudah OUT";
    header("Location: peripherals.php?halaman=3&res=$res&msg=$msg"); exit;
}

$halaman_aktif = isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1;
$keyword = isset($_GET['q']) ? mysqli_real_escape_string($koneksi, $_GET['q']) : '';
$f_bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
$f_tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Peripheral Management</title>
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        .timeline { border-left: 3px solid #dee2e6; padding: 0 0 20px 20px; margin-left: 20px; position: relative; }
        .timeline-item { position: relative; margin-bottom: 20px; }
        .timeline-item::before { content: ""; position: absolute; left: -29px; top: 5px; width: 15px; height: 15px; background: #dc3545; border-radius: 50%; border: 3px solid #fff; }
        .timeline-date { font-weight: bold; color: #6c757d; font-size: 0.8rem; margin-bottom: 5px; }
    </style>
</head>
<body>

    <?php include 'header.php'; ?>
    <?php include 'sidebar.php'; ?>

    <main class="main-content">
        <?php include 'notifikasi.php'; ?>

        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
            <div class="d-flex align-items-center flex-grow-1 flex-wrap">
                <nav class="me-3">
                    <ul class="pagination pagination-sm mb-0 shadow-sm">
                        <li class="page-item <?= $halaman_aktif == 1 ? 'active' : '' ?>"><a class="page-link px-3" href="peripherals.php?halaman=1">1. Monitoring Stok</a></li>
                        <li class="page-item <?= $halaman_aktif == 2 ? 'active' : '' ?>"><a class="page-link px-3" href="peripherals.php?halaman=2">2. Master Tipe</a></li>
                        <li class="page-item <?= $halaman_aktif == 3 ? 'active' : '' ?>"><a class="page-link px-3" href="peripherals.php?halaman=3">3. Riwayat Keluar</a></li>
                    </ul>
                </nav>

                <form action="" method="GET" class="d-flex align-items-center">
                    <input type="hidden" name="halaman" value="<?= $halaman_aktif ?>">
                    <div class="input-group input-group-sm shadow-sm me-2" style="width: 200px;">
                        <input type="text" name="q" class="form-control" placeholder="Cari data..." value="<?= htmlspecialchars($keyword) ?>">
                        <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i></button>
                    </div>

                    <?php if($halaman_aktif == 3): ?>
                        <select name="bulan" class="form-select form-select-sm shadow-sm me-1" style="width: 130px;" onchange="this.form.submit()">
                            <?php
                            foreach($nama_bulan as $num => $nama) {
                                $sel = ($f_bulan == $num) ? 'selected' : '';
                                echo "<option value='$num' $sel>$nama</option>";
                            }
                            ?>
                        </select>

                        <select name="tahun" class="form-select form-select-sm shadow-sm" style="width: 100px;" onchange="this.form.submit()">
                            <?php
                            $start_year = 2020;
                            $end_year = date('Y') + 10;
                            for($y=$start_year; $y<=$end_year; $y++) {
                                $sel = ($f_tahun == $y) ? 'selected' : '';
                                echo "<option value='$y' $sel>$y</option>";
                            }
                            ?>
                        </select>
                    <?php endif; ?>
                </form>
            </div>

            <div class="btn-group shadow-sm ms-2">
                <button class="btn btn-dark btn-sm" data-bs-toggle="modal" data-bs-target="#modalTipe"><i class="bi bi-tag"></i> Buat Tipe</button>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalMasuk"><i class="bi bi-plus-lg"></i> Masuk</button>
                <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalKeluar"><i class="bi bi-box-arrow-up"></i> Keluar</button>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body <?= $halaman_aktif == 3 ? 'p-4' : 'p-0' ?>">

            <?php if($halaman_aktif == 1): ?>
                <div class="accordion accordion-flush" id="accordionStok">
                    <?php
                    $filter = $keyword ? "AND (p.serial_number LIKE '%$keyword%' OR t.tipe_barang LIKE '%$keyword%' OR t.model LIKE '%$keyword%' OR p.no_po LIKE '%$keyword%')" : "";

                    // Ambil daftar PO unik
                    $sql_po = "SELECT p.no_po
                               FROM peripheral_items p
                               JOIN peripheral_types t ON p.kode_barang = t.kode_barang
                               WHERE p.status='Stock' $filter
                               GROUP BY p.no_po
                               ORDER BY p.id_barang DESC";
                    $res_po = mysqli_query($koneksi, $sql_po);

                    $i = 0;
                    while ($po = mysqli_fetch_assoc($res_po)):
                        $current_po = $po['no_po'];
                        $i++;

                        $count_q = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM peripheral_items WHERE no_po = '$current_po' AND status = 'Stock'");
                        $count_data = mysqli_fetch_assoc($count_q);
                    ?>
                    <div class="accordion-item border-bottom">
                        <h2 class="accordion-header" id="heading<?= $i ?>">
                            <button class="accordion-button collapsed py-2 px-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $i ?>" aria-expanded="false" aria-controls="collapse<?= $i ?>">
                                <div class="d-flex align-items-center w-100">

                                    <div style="flex: 0 0 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        <span class="badge bg-dark me-2" style="font-family: monospace; font-size: 0.85rem;">PO: <?= $current_po ?></span>
                                    </div>

                                    <div class="flex-grow-1 border-start ps-3">
                                        <span class="small text-muted">Klik untuk detail unit</span>
                                    </div>

                                    <div class="text-end me-3">
                                        <span class="badge rounded-pill bg-primary" style="width: 70px;"><?= $count_data['total'] ?> Unit</span>
                                    </div>

                                </div>
                            </button>
                        </h2>
                        <div id="collapse<?= $i ?>" class="accordion-collapse collapse" aria-labelledby="heading<?= $i ?>" data-bs-parent="#accordionStok">
                            <div class="accordion-body p-0 bg-light">
                                <table class="table table-hover mb-0 bg-white" style="font-size: 0.9rem;">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-4" style="width: 25%;">Barang</th>
                                            <th style="width: 25%;">S/N</th>
                                            <th style="width: 15%;">Status</th>
                                            <th style="width: 20%;">User/Ticket</th>
                                            <th style="width: 15%;">Tgl Masuk</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $query_item = mysqli_query($koneksi, "SELECT p.*, t.tipe_barang, t.model
                                                                              FROM peripheral_items p
                                                                              JOIN peripheral_types t ON p.kode_barang = t.kode_barang
                                                                              WHERE p.status='Stock' AND p.no_po = '$current_po' $filter
                                                                              ORDER BY p.id_barang DESC");
                                        while ($row = mysqli_fetch_assoc($query_item)): ?>
                                        <tr>
                                            <td class="ps-4">
                                                <small class="text-muted fw-bold d-block" style="font-size: 0.65rem;"><?= $row['tipe_barang'] ?></small>
                                                <span class="fw-bold"><?= $row['model'] ?></span>
                                            </td>
                                            <td class="fw-bold text-primary align-middle"><?= $row['serial_number'] ?></td>
                                            <td class="align-middle"><span class="badge bg-success" style="font-size: 0.7rem;">STOK</span></td>
                                            <td class="align-middle"><small><?= !empty($row['nama_user']) ? $row['nama_user'] : 'SPARE IT' ?></small></td>
                                            <td class="align-middle"><small><?= date('d/m/y', strtotime($row['tanggal_masuk'])) ?></small></td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>

                    <?php if(mysqli_num_rows($res_po) == 0): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-search text-muted" style="font-size: 2rem;"></i>
                            <p class="text-muted mt-2">Data tidak ditemukan.</p>
                        </div>
                    <?php endif; ?>
                </div>

<?php elseif($halaman_aktif == 2): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Kode Barang</th>
                                <th>Tipe</th>
                                <th>Model</th>
                                <th>Deskripsi Tipe</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $filter = $keyword ? "WHERE tipe_barang LIKE '%$keyword%' OR model LIKE '%$keyword%' OR kode_barang LIKE '%$keyword%'" : "";
                            $query = mysqli_query($koneksi, "SELECT * FROM peripheral_types $filter ORDER BY tipe_barang ASC");
                            while ($row = mysqli_fetch_assoc($query)): ?>
                            <tr>
                                <td><code class="fw-bold"><?= $row['kode_barang'] ?></code></td>
                                <td><?= $row['tipe_barang'] ?></td>
                                <td class="fw-bold"><?= $row['model'] ?></td>
                                <td><?= $row['deskripsi_tipe'] ?></td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-edit-tipe"
                                            data-id="<?= $row['id_tipe'] ?>"
                                            data-kode="<?= $row['kode_barang'] ?>"
                                            data-tipe="<?= $row['tipe_barang'] ?>"
                                            data-model="<?= $row['model'] ?>"
                                            data-desc="<?= $row['deskripsi_tipe'] ?>">
                                        <i class="bi bi-pencil-square"></i> Edit
                                    </button>

                                    <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'superadmin'): ?>
                                    <a href="proses_tipe.php?aksi=hapus&id=<?= $row['kode_barang'] ?>"
                                       class="btn btn-sm btn-outline-danger"
                                       onclick="return confirm('Apakah Anda yakin ingin menghapus tipe ini?')">
                                        <i class="bi bi-trash"></i> Hapus
                                    </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

<?php elseif($halaman_aktif == 3): ?>
                <div class="p-3">
                    <h5 class="fw-bold mb-5 text-muted"><i class="bi bi-clock-history me-2"></i>Timeline Riwayat Keluar</h5>

                    <div class="timeline">
                        <?php
                        // Filter Data Berdasarkan Bulan dan Tahun
                        $filter = "AND MONTH(p.tanggal_keluar) = '$f_bulan' AND YEAR(p.tanggal_keluar) = '$f_tahun'";
                        if($keyword) {
                            $filter .= " AND (p.serial_number LIKE '%$keyword%' OR t.tipe_barang LIKE '%$keyword%' OR t.model LIKE '%$keyword%' OR p.no_ticket LIKE '%$keyword%')";
                        }

                        $query = mysqli_query($koneksi, "SELECT p.*, t.tipe_barang, t.model
                                                         FROM peripheral_items p
                                                         JOIN peripheral_types t ON p.kode_barang = t.kode_barang
                                                         WHERE p.status='Out' $filter
                                                         ORDER BY p.tanggal_keluar DESC");

                        if (mysqli_num_rows($query) > 0):
                            while ($row = mysqli_fetch_assoc($query)): ?>

                            <div class="timeline-item">
                                <span class="timeline-date">
                                    <i class="bi bi-calendar3 me-1"></i> <?= date('d M Y - H:i', strtotime($row['tanggal_keluar'])) ?>
                                </span>

                                <div class="card border-0 shadow-sm" style="background-color: #ffffff; border-radius: 12px;">
                                    <div class="card-body p-3">
                                        <div class="row align-items-center text-center">

                                            <div class="col-3 text-start border-end">
                                                <small class="text-muted fw-bold d-block" style="font-size: 0.65rem;"><?= $row['tipe_barang'] ?></small>
                                                <span class="fw-bold d-block text-truncate text-dark" title="<?= $row['model'] ?>"><?= $row['model'] ?></span>
                                                <span class="badge bg-light text-primary border border-primary mt-1" style="font-size: 0.7rem;"><?= $row['serial_number'] ?></span>
                                            </div>

                                            <div class="col-2 border-end">
                                                <small class="text-muted d-block" style="font-size: 0.65rem;">No. PO</small>
                                                <span class="badge bg-dark px-2" style="font-size: 0.75rem;"><?= !empty($row['no_po']) ? $row['no_po'] : '-' ?></span>
                                            </div>

                                            <div class="col-2 border-end">
                                                <small class="text-muted d-block" style="font-size: 0.65rem;">No. Ticket</small>
                                                <b class="text-danger" style="font-size: 0.85rem;"><?= $row['no_ticket'] ?></b>
                                            </div>

                                            <div class="col-3 text-start border-end">
                                                <small class="text-muted d-block" style="font-size: 0.65rem;">Keterangan</small>
                                                <div class="small text-secondary" style="font-size: 0.75rem; line-height: 1.2;">
                                                    <?= !empty($row['keterangan']) ? $row['keterangan'] : '-' ?>
                                                </div>
                                            </div>

                                            <div class="col-2">
                                                <small class="text-muted d-block" style="font-size: 0.65rem;">PIC</small>
                                                <span class="fw-bold text-dark text-truncate d-block" style="font-size: 0.8rem;"><?= $row['admin_keluar'] ?></span>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php endwhile;
                        else: ?>
                            <div class="text-center py-5">
                                <i class="bi bi-cloud-slash text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-3">Tidak ada riwayat keluar pada periode <?= $nama_bulan[(int)$f_bulan] ?> <?= $f_tahun ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 3000">
        <div id="liveToast" class="toast align-items-center text-white bg-primary border-0" role="alert"><div class="d-flex"><div class="toast-body" id="toast-body"></div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div></div>
    </div>

    <div class="modal fade" id="modalTipe" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered"><form class="modal-content border-0" method="POST">
            <div class="modal-header bg-dark text-white p-2 px-3"><h6>Buat Tipe Baru</h6><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <label class="small fw-bold">Kode Barang</label><input type="text" name="kode_barang" class="form-control form-control-sm text-uppercase mb-2" required>
                <label class="small fw-bold">Nama Tipe</label><input type="text" name="tipe_barang" class="form-control form-control-sm text-uppercase mb-2" required placeholder="Contoh: BATTERY">
                <label class="small fw-bold">Model</label><input type="text" name="model" class="form-control form-control-sm text-uppercase mb-2" required placeholder="Contoh: LENOVO L13">
                <label class="small fw-bold">Deskripsi</label><input type="text" name="deskripsi_tipe" class="form-control form-control-sm text-uppercase">
            </div>
            <div class="modal-footer p-2"><button type="submit" name="simpan_tipe" class="btn btn-sm btn-dark w-100">Simpan Tipe</button></div>
        </form></div>
    </div>

    <div class="modal fade" id="modalEditTipe" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered"><form class="modal-content border-0" method="POST">
            <div class="modal-header bg-primary text-white p-2 px-3"><h6>Edit Tipe</h6><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="hidden" name="id_tipe" id="ed_id">
                <label class="small fw-bold">Kode Barang</label><input type="text" name="kode_barang" id="ed_kd" class="form-control form-control-sm text-uppercase mb-2" required>
                <label class="small fw-bold">Nama Tipe</label><input type="text" name="tipe_barang" id="ed_tp" class="form-control form-control-sm text-uppercase mb-2" required>
                <label class="small fw-bold">Model</label><input type="text" name="model" id="ed_md" class="form-control form-control-sm text-uppercase mb-2" required>
                <label class="small fw-bold">Deskripsi</label><input type="text" name="deskripsi_tipe" id="ed_ds" class="form-control form-control-sm text-uppercase">
            </div>
            <div class="modal-footer p-2"><button type="submit" name="update_tipe" class="btn btn-sm btn-primary w-100">Simpan Perubahan</button></div>
        </form></div>
    </div>

    <div class="modal fade" id="modalMasuk" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered"><form class="modal-content border-0" method="POST">
            <div class="modal-header bg-primary text-white p-2 px-3"><h6>Barang Masuk</h6><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="row g-2 mb-3">
                    <div class="col-md-4">
                        <label class="small fw-bold">Tipe</label>
                        <select id="select_tipe" class="form-select form-select-sm" required onchange="filterModel()">
                            <option value="">- Pilih Tipe -</option>
                            <?php
                            // Ambil tipe unik saja
                            $t_q = mysqli_query($koneksi, "SELECT DISTINCT tipe_barang FROM peripheral_types ORDER BY tipe_barang ASC");
                            while($t = mysqli_fetch_assoc($t_q)) echo "<option value='{$t['tipe_barang']}'>{$t['tipe_barang']}</option>";
                            ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="small fw-bold">Model [Kode]</label>
                        <select name="kode_barang" id="select_model" class="form-select form-select-sm" required disabled>
                            <option value="">- Pilih Tipe Dulu -</option>
                        </select>
                    </div>
                    <div class="col-md-4"><label class="small fw-bold">No. PO</label><input type="text" name="no_po" class="form-control form-control-sm text-uppercase" required></div>
                    <div class="col-md-4"><label class="small fw-bold">Peruntukan</label><select name="peruntukan" id="ps" class="form-select form-select-sm" onchange="toggleU()" required><option value="Spare IT">Spare IT</option><option value="User">User</option></select></div>
                    <div class="col-12 d-none" id="ud"><label class="small fw-bold text-primary">Nama User</label><input type="text" name="nama_user" class="form-control form-control-sm text-uppercase"></div>
                </div>
                <div id="sa"><input type="text" name="sn[]" class="form-control form-control-sm mb-2 sn-input text-uppercase" placeholder="S/N (ENTER)..." autocomplete="off"></div>
            </div>
            <div class="modal-footer p-2"><button type="submit" name="simpan_masuk" class="btn btn-sm btn-primary px-4">Simpan</button></div>
        </form></div>
    </div>

    <div class="modal fade" id="modalKeluar" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered"><form class="modal-content border-0" method="POST">
            <div class="modal-header bg-danger text-white p-2 px-3"><h6>Barang Keluar</h6><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <label class="small fw-bold">S/N</label><input type="text" name="serial_number" class="form-control form-control-sm text-uppercase mb-2" required autofocus>
                <label class="small fw-bold">No. Ticket</label><input type="text" name="no_ticket" class="form-control form-control-sm text-uppercase mb-2" required>
                <label class="small fw-bold">Keterangan</label><textarea name="keterangan" class="form-control form-control-sm text-uppercase" rows="2" placeholder="Contoh: Ganti Mouse..."></textarea>
            </div>
            <div class="modal-footer p-2"><button type="submit" name="simpan_keluar" class="btn btn-sm btn-danger w-100">Konfirmasi Keluar</button></div>
        </form></div>
    </div>

    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
<script>
        const toast = new bootstrap.Toast(document.getElementById('liveToast'));
        function showToast(m, t='danger'){ document.getElementById('toast-body').innerText=m; document.getElementById('liveToast').className=`toast align-items-center text-white bg-${t} border-0`; toast.show(); }

        <?php if(isset($_GET['msg'])): ?>
            showToast("<?= $_GET['msg'] ?>", "<?= $_GET['res'] ?? 'primary' ?>");
            window.history.replaceState({}, document.title, "peripherals.php?halaman=<?= $halaman_aktif ?>");
        <?php endif; ?>

        document.addEventListener('keydown', async function(e) {
            if (e.target.classList.contains('sn-input')) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const val = e.target.value.trim().toUpperCase();
                    if(!val) return;
                    const all = Array.from(document.querySelectorAll('.sn-input'));
                    if(all.filter(i => i !== e.target && i.value.toUpperCase() === val).length > 0) { showToast(`S/N ${val} duplikat di baris!`); e.target.value=""; return; }
                    const res = await fetch(`peripherals.php?cek_sn=${val}`);
                    const data = await res.json();
                    if(data.exists) { showToast(`S/N ${val} sudah ada di database!`); e.target.value=""; return; }
                    const i = document.createElement('input'); i.name='sn[]'; i.className='form-control form-control-sm mb-2 sn-input text-uppercase'; i.placeholder='S/N...'; i.autocomplete='off';
                    document.getElementById('sa').appendChild(i); i.focus();
                }
                if (e.key === 'Delete' && document.querySelectorAll('.sn-input').length > 1) { e.preventDefault(); const p = e.target.previousElementSibling; e.target.remove(); if(p) p.focus(); }
            }
        });

        function toggleU() { document.getElementById('ud').classList.toggle('d-none', document.getElementById('ps').value !== 'User'); }

        // --- PERBAIKAN EDIT TIPE ---
        // Kita gunakan event delegation agar tombol tetap berfungsi meski halaman difilter
        document.addEventListener('click', function (e) {
            if (e.target.closest('.btn-edit-tipe')) {
                const btn = e.target.closest('.btn-edit-tipe');
                document.getElementById('ed_id').value = btn.dataset.id;
                document.getElementById('ed_kd').value = btn.dataset.kode;
                document.getElementById('ed_tp').value = btn.dataset.tipe;
                document.getElementById('ed_md').value = btn.dataset.model;
                document.getElementById('ed_ds').value = btn.dataset.desc;
                new bootstrap.Modal(document.getElementById('modalEditTipe')).show();
            }
        });

        // Menyimpan data tipe dan model dari PHP ke JS
        const dataRelasi = [
            <?php
            $all_types = mysqli_query($koneksi, "SELECT kode_barang, tipe_barang, model FROM peripheral_types");
            while($row = mysqli_fetch_assoc($all_types)) {
                echo "{kode: '{$row['kode_barang']}', tipe: '{$row['tipe_barang']}', model: '{$row['model']}'},";
            }
            ?>
        ];

        function filterModel() {
            const tipeTerpilih = document.getElementById('select_tipe').value;
            const modelSelect = document.getElementById('select_model');
            modelSelect.innerHTML = '<option value="">- Pilih Model -</option>';
            if (tipeTerpilih === "") { modelSelect.disabled = true; return; }
            const models = dataRelasi.filter(item => item.tipe === tipeTerpilih);
            models.forEach(item => {
                const opt = document.createElement('option');
                opt.value = item.kode;
                opt.textContent = `${item.model} [${item.kode}]`;
                modelSelect.appendChild(opt);
            });
            modelSelect.disabled = false;
        }
    </script>
</body>
</html>