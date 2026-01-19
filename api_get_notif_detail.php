<?php
require_once "koneksi.php";
require_once "helpers.php"; // Untuk fungsi e()

// --- Ambil Data Service ---
$query_service = "SELECT s.id_service, s.hostname, s.tanggal_masuk, i.nama AS nama_user
                  FROM service_list s LEFT JOIN inventori i ON s.hostname = i.hostname
                  WHERE s.claim_status IS NULL OR s.claim_status = '' ORDER BY s.tanggal_masuk ASC";
$res = mysqli_query($koneksi, $query_service);

if (mysqli_num_rows($res) > 0) {
    while ($s = mysqli_fetch_assoc($res)) { ?>
        <div class="list-group-item list-group-item-action p-3 animate__animated animate__fadeIn">
            <div class="row align-items-center">
                <div class="col">
                    <div class="fw-bold" style="font-size: 0.85rem;"><?= e($s['hostname']) ?></div>
                    <small class="text-muted d-block">User: <?= e($s['nama_user'] ?? 'N/A') ?></small>
                    <small class="text-primary" style="font-size: 0.7rem;">Masuk: <?= date('d/m/Y H:i', strtotime($s['tanggal_masuk'])) ?></small>
                </div>
                <div class="col-auto">
                    <form action="proses-service.php" method="POST">
                        <input type="hidden" name="id_service" value="<?= $s['id_service'] ?>">
                        <button type="submit" name="pickup_service" class="btn btn-sm btn-outline-primary rounded-pill px-3 shadow-sm">Pick Up</button>
                    </form>
                </div>
            </div>
        </div>
    <?php }
    } else {
        echo '<div class="p-5 text-center text-muted">Antrean service bersih.</div>';
    }
