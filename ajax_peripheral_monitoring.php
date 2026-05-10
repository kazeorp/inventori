<?php
require 'session.php';
require 'koneksi.php';
require 'helpers.php';

$tipe = $_GET['tipe'] ?? 'ALL';
$q = mysqli_real_escape_string($koneksi, $_GET['q'] ?? '');

$where = "p.status = 'Stock'";
if ($tipe !== 'ALL') {
    $where .= " AND t.tipe_barang = '$tipe'";
}
if ($q) {
    $where .= " AND (p.serial_number LIKE '%$q%' OR t.model LIKE '%$q%' OR p.no_po LIKE '%$q%' OR t.tipe_barang LIKE '%$q%')";
}

$sql = "SELECT t.tipe_barang, t.model, t.kode_barang, COUNT(p.id_barang) as stock_count, GROUP_CONCAT(DISTINCT p.no_po SEPARATOR ', ') as pos
        FROM peripheral_items p
        JOIN peripheral_types t ON p.kode_barang = t.kode_barang
        WHERE $where
        GROUP BY t.kode_barang
        ORDER BY t.tipe_barang ASC, t.model ASC";

$res = mysqli_query($koneksi, $sql);
?>

<div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th class="ps-4 py-3">Item Info</th>
                <th class="py-3">Total Stock</th>
                <th class="py-3">Reference POs</th>
                <th class="text-center py-3">S/N List</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($res) > 0): 
                while($row = mysqli_fetch_assoc($res)): ?>
                <tr class="border-bottom">
                    <td class="ps-4">
                        <small class="text-muted fw-bold d-block text-uppercase" style="font-size: 0.65rem;"><?= e($row['tipe_barang']) ?></small>
                        <span class="fw-bold text-dark"><?= e($row['model']) ?></span>
                    </td>
                    <td><span class="badge bg-success px-3"><?= number_format($row['stock_count']) ?> Units</span></td>
                    <td class="small text-muted text-truncate" style="max-width: 300px;" title="<?= e($row['pos']) ?>"><?= e($row['pos']) ?></td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-dark rounded-pill py-0 px-3 fw-bold" style="font-size: 0.7rem;" type="button" data-bs-toggle="collapse" data-bs-target="#sn-<?= $row['kode_barang'] ?>">
                            <i class="bi bi-eye me-1"></i> VIEW
                        </button>
                    </td>
                </tr>
                <tr class="collapse" id="sn-<?= $row['kode_barang'] ?>">
                    <td colspan="4" class="p-0 border-0 bg-white">
                        <div class="p-3 border-bottom shadow-inner">
                            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-2">
                                <?php
                                $sn_sql = "SELECT serial_number, no_po, tanggal_masuk FROM peripheral_items WHERE kode_barang = '{$row['kode_barang']}' AND status='Stock' ORDER BY id_barang DESC";
                                $sn_res = mysqli_query($koneksi, $sn_sql);
                                while($sn = mysqli_fetch_assoc($sn_res)): ?>
                                    <div class="col">
                                        <div class="border rounded p-2 bg-light d-flex justify-content-between align-items-center">
                                            <div>
                                                <code class="fw-bold text-primary"><?= e($sn['serial_number']) ?></code>
                                                <div class="text-muted" style="font-size: 0.65rem;"><i class="bi bi-calendar3 me-1"></i><?= date('d/m/y', strtotime($sn['tanggal_masuk'])) ?></div>
                                            </div>
                                            <span class="badge bg-secondary" style="font-size: 0.6rem;"><?= e($sn['no_po']) ?></span>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        </div>
                    </td>
                </tr>
            <?php endwhile; else: ?>
                <tr><td colspan="4" class="text-center py-5 text-muted"><i class="bi bi-search fs-2 d-block mb-2"></i> No matching items found in this category.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>