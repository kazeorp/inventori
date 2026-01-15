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
                    <i class="bi bi-megaphone me-2"></i> Notifikasi Sistem
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-0" style="min-height: 400px;">
                <ul class="nav nav-tabs nav-fill bg-light sticky-top shadow-sm" id="notifTab" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active fw-bold small py-3" data-bs-toggle="tab" data-bs-target="#service-panel" type="button">
                            <i class="bi bi-tools me-1"></i> SERVICE (<span id="count-service-tab"><?= count($notifikasi_service) ?></span>)
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link fw-bold small py-3 text-danger" data-bs-toggle="tab" data-bs-target="#grace-panel" type="button">
                            <i class="bi bi-calendar-x me-1"></i> GRACE PERIOD (<span id="count-grace-tab"><?= count($notifikasi_grace) ?></span>)
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="notifTabContent" style="max-height: 400px; overflow-y: auto;">
                    <div class="tab-pane fade show active" id="service-panel" role="tabpanel">
                        <div id="list-service" class="list-group list-group-flush">
                            <?php if (!empty($notifikasi_service)): ?>
                                <?php foreach ($notifikasi_service as $s): ?>
                                    <div class="list-group-item list-group-item-action p-3 service-item" id="item-service-<?= $s['id_service'] ?>">
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
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="p-5 text-center text-muted">Antrean service bersih.</div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="grace-panel" role="tabpanel">
                        <div id="list-grace" class="list-group list-group-flush">
                            <?php if (!empty($notifikasi_grace)): ?>
                                <?php foreach ($notifikasi_grace as $g): ?>
                                    <div class="list-group-item list-group-item-action border-start border-danger border-4">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <div class="fw-bold text-danger" style="font-size: 0.85rem;"><?= e($g['hostname']) ?></div>
                                                <small class="text-muted d-block">User: <?= e($g['nama'] ?? 'N/A') ?></small>
                                                <small class="text-danger fw-bold" style="font-size: 0.7rem;">Overdue <?= $g['keterlambatan'] ?> hari</small>
                                            </div>
                                            <a href="tampil.php?cari=<?= urlencode($g['hostname']) ?>" class="btn btn-sm btn-danger rounded-pill px-3 shadow-sm">Tarik</a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="p-5 text-center text-muted">Semua aman.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer p-1">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>
<script>
const socket = io("http://172.16.3.60:3000");
const notifSound = document.getElementById('notifSound');
const graceSound = document.getElementById('graceSound'); // Audio baru

let currentServiceCount = <?= count($notifikasi_service) ?>;
let countGrace = <?= count($notifikasi_grace) ?>;
let serviceReminderInterval = null;

// Fungsi Suara Service
function playNotifSound() {
    if (notifSound) {
        notifSound.currentTime = 0;
        notifSound.play().catch(e => console.log("Autoplay blocked"));
    }
}

// Fungsi Suara Grace Period
function playGraceSound() {
    if (graceSound) {
        graceSound.currentTime = 0;
        graceSound.play().catch(e => console.log("Autoplay blocked"));
    }
}

// --- LOGIKA REMINDER GRACE PERIOD ---
function startGraceReminder() {
    // Jalankan setiap 6 jam
    setInterval(() => {
        if (countGrace > 0) {
            playGraceSound(); // Bunyikan suara khusus grace period
            showToast(`⚠️ PERHATIAN: Ada ${countGrace} aset melewati batas Grace Period!`, "danger");
        }
    }, 21600000);
}

socket.on('service_update', (data) => {
    console.log("[WS] Received update:", data);

    // Jika ada update (bisa jadi aset baru saja diubah statusnya ke Grace Period di sisi PHP)
    if (data.action === 'service_insert' || data.action === 'asset_update' || data.action === 'service_claim') {

        // Cek apakah ini service baru untuk bunyikan suara service
        if (data.action === 'service_insert') {
            playNotifSound();
            showToast("🔔 Service Request baru masuk!", "info");
        }

        // Update data (ini akan mengupdate nilai countGrace juga)
        updateNotifData();
    }
});

function updateNotifData() {
    fetch('api_get_notifikasi.php')
    .then(res => res.json())
    .then(data => {
        // Cek jika jumlah grace period bertambah dari sebelumnya (ada aset baru expired)
        if (data.grace_count > countGrace) {
            playGraceSound();
            showToast(`⚠️ Aset baru terdeteksi Overdue Grace Period!`, "danger");
        }

        currentServiceCount = data.service_count;
        countGrace = data.grace_count; // Update variabel global grace

        // Update UI
        const badge = document.getElementById('badge-notif');
        if (badge) {
            badge.innerText = data.total;
            badge.style.display = data.total > 0 ? 'block' : 'none';
        }
        document.getElementById('count-service-tab').innerText = data.service_count;
        document.getElementById('count-grace-tab').innerText = data.grace_count;

        if (currentServiceCount === 0) clearInterval(serviceReminderInterval);
    });

    // Update Detail List
    fetch('api_get_notif_detail.php')
    .then(res => res.text())
    .then(html => {
        const listContainer = document.getElementById('list-service');
        if (listContainer) listContainer.innerHTML = html;
        // Anda juga bisa buat api_get_grace_detail.php jika ingin list grace update real-time
    });
}

// Jalankan saat pertama kali load
if (countGrace > 0) {
    setTimeout(() => {
        playGraceSound();
        showToast(`⚠️ Reminder: ${countGrace} aset sudah overdue Grace Period.`, "danger");
    }, 5000);
    startGraceReminder();
}

startServiceReminder();
</script>