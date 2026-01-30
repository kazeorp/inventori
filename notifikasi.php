<?php
require_once "koneksi.php";

// --- 1. LOGIKA GRACE PERIOD (> 3 BULAN DARI TANGGAL MASUK) ---
$notifikasi_grace = [];
$cek_grace = mysqli_query($koneksi, "SELECT * FROM inventori WHERE status='Grace Period' ORDER BY tanggal_masuk ASC");
$hari_ini = new DateTime();

while ($row = mysqli_fetch_assoc($cek_grace)) {
    if (!empty($row['tanggal_masuk']) && $row['tanggal_masuk'] !== '0000-00-00 00:00:00') {
        $tgl_masuk = new DateTime($row['tanggal_masuk']);
        if ($tgl_masuk->format('Y') < 2000) {
            continue;
        }

        $batas_grace = (clone $tgl_masuk)->add(new DateInterval('P3M'));

        if ($hari_ini > $batas_grace) {
            $interval = $batas_grace->diff($hari_ini);
            $row['keterlambatan'] = $interval->days;
            $notifikasi_grace[] = $row;
        }
    }
}

// --- 2. LOGIKA SERVICE BELUM DI PICK-UP ---
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

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

<audio id="notifSound" src="asset/notification.mp3" preload="auto"></audio>
<audio id="graceSound" src="asset/graceperiod.mp3" preload="auto"></audio>

<div class="d-flex justify-content-end mb-3">
    <button class="btn btn-sm btn-white border shadow-sm position-relative" id="btnNotifLonceng">
        <i class="bi bi-bell-fill <?= $total_notif > 0 ? 'text-danger' : 'text-muted' ?>"></i>
        <span class="ms-1 d-none d-sm-inline fw-bold">Pusat Notifikasi</span>
        <?php if ($total_notif > 0): ?>
            <span id="badge-notif" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger shadow">
                <?= $total_notif ?>
            </span>
        <?php endif; ?>
    </button>
</div>