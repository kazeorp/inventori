<?php
require_once "koneksi.php";

// --- 1. LOGIKA GRACE PERIOD (> 3 BULAN) ---
$notifikasi_grace = [];
$cek_grace = mysqli_query($koneksi, "SELECT * FROM inventori WHERE status='Grace Period' ORDER BY tanggal_keluar ASC");
while ($row = mysqli_fetch_assoc($cek_grace)) {
    if (!empty($row['tanggal_keluar'])) {
        $tanggal_keluar = new DateTime($row['tanggal_keluar']);
        $batas_grace = (clone $tanggal_keluar)->add(new DateInterval('P3M'));
        $hari_ini = new DateTime();

        if ($hari_ini >= $batas_grace) {
            $diff = $hari_ini->diff($batas_grace);
            $row['keterlambatan'] = $diff->days;
            $notifikasi_grace[] = $row;
        }
    }
}

// --- 2. LOGIKA SERVICE BELUM DI PICK-UP (claim_status IS NULL) ---
$notifikasi_service = [];
$query_service = "
    SELECT s.id_service, s.hostname, s.tanggal_masuk, s.catatan, i.nama AS nama_user
    FROM service_list s
    LEFT JOIN inventori i ON s.hostname = i.hostname
    WHERE s.claim_status IS NULL OR s.claim_status = ''
    ORDER BY s.tanggal_masuk ASC
";
$cek_service = mysqli_query($koneksi, $query_service);
while ($row = mysqli_fetch_assoc($cek_service)) {
    $notifikasi_service[] = $row;
}

$total_notif = count($notifikasi_grace) + count($notifikasi_service);
?>

<div class="d-flex justify-content-end mb-3">
    <button class="btn btn-sm btn-white border shadow-sm position-relative" data-bs-toggle="modal" data-bs-target="#notifikasiModal">
        <i class="bi bi-bell-fill <?= $total_notif > 0 ? 'text-danger' : 'text-muted' ?>"></i>
        <span class="ms-1 d-none d-sm-inline fw-bold">Pusat Notifikasi</span>
        <?php if ($total_notif > 0): ?>
            <span id="badge-notif" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger shadow">
                <?= $total_notif ?>
            </span>
        <?php endif; ?>
    </button>
</div>

<div class="modal fade" id="notifikasiModal" tabindex="-1" aria-labelledby="notifikasiModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white p-2 px-3">
                <h6 class="modal-title" id="notifikasiModalLabel">
                    <i class="bi bi- megaphone me-2"></i> Notifikasi Sistem
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-0" style="max-height: 450px; overflow-y: auto;">

                <div class="p-2 bg-light border-bottom border-top sticky-top">
                    <small class="fw-bold text-primary px-2"><i class="bi bi-tools me-1"></i> SERVICE MASUK (<?= count($notifikasi_service) ?>)</small>
                </div>
                <div class="list-group list-group-flush">
                    <?php if (!empty($notifikasi_service)): ?>
                        <?php foreach ($notifikasi_service as $s): ?>
                            <div class="list-group-item list-group-item-action p-3">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <div class="fw-bold" style="font-size: 0.85rem;"><?= htmlspecialchars($s['hostname']) ?></div>
                                        <small class="text-muted d-block">User: <?= htmlspecialchars($s['nama_user'] ?? 'N/A') ?></small>
                                        <small class="text-primary" style="font-size: 0.7rem;">Masuk: <?= date('d/m/Y H:i', strtotime($s['tanggal_masuk'])) ?></small>
                                    </div>
                                    <div class="col-auto">
                                        <form action="proses-service.php" method="POST">
                                            <input type="hidden" name="id_service" value="<?= $s['id_service'] ?>">
                                            <button type="submit" name="pickup_service" class="btn btn-sm btn-outline-primary rounded-pill px-3 shadow-sm" style="font-size: 0.75rem;">
                                                Pick Up
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="p-3 text-center text-muted small">Tidak ada antrean service baru.</div>
                    <?php endif; ?>
                </div>

                <div class="p-2 bg-light border-bottom border-top mt-1">
                    <small class="fw-bold text-danger px-2"><i class="bi bi-calendar-x me-1"></i> OVERDUE GRACE PERIOD (<?= count($notifikasi_grace) ?>)</small>
                </div>
                <div class="list-group list-group-flush">
                    <?php if (!empty($notifikasi_grace)): ?>
                        <?php foreach ($notifikasi_grace as $g): ?>
                            <div class="list-group-item list-group-item-action border-start border-danger border-4">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-bold text-danger" style="font-size: 0.85rem;"><?= htmlspecialchars($g['hostname']) ?></div>
                                        <small class="text-muted d-block">User: <?= htmlspecialchars($g['nama'] ?? 'N/A') ?></small>
                                        <small class="text-danger fw-bold" style="font-size: 0.7rem;">Terlewat <?= $g['keterlambatan'] ?> hari</small>
                                    </div>
                                    <a href="tampil.php?cari=<?= urlencode($g['hostname']) ?>" class="btn btn-sm btn-danger rounded-pill px-3 shadow-sm" style="font-size: 0.75rem;">Tarik</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="p-3 text-center text-muted small">Aset grace period aman.</div>
                    <?php endif; ?>
                </div>

            </div>
            <div class="modal-footer p-1">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>