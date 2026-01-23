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

<style>
    .modal-backdrop.show:nth-of-type(even) { display: none !important; }
    #notifikasiModal { z-index: 10001 !important; }
</style>

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

<div class="modal fade" id="notifikasiModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white p-2 px-3">
                <h6 class="modal-title"><i class="bi bi-megaphone me-2"></i> Notifikasi Sistem</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0" style="min-height: 400px;">
                <ul class="nav nav-tabs nav-fill bg-light sticky-top shadow-sm" id="notifTab">
                    <li class="nav-item">
                        <button class="nav-link active fw-bold small py-3" data-bs-toggle="tab" data-bs-target="#service-panel">
                            SERVICE (<span id="count-service-tab"><?= count($notifikasi_service) ?></span>)
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link fw-bold small py-3 text-danger" data-bs-toggle="tab" data-bs-target="#grace-panel">
                            GRACE PERIOD (<span id="count-grace-tab"><?= count($notifikasi_grace) ?></span>)
                        </button>
                    </li>
                </ul>
                <div class="tab-content" id="notifTabContent" style="max-height: 400px; overflow-y: auto;">
                    <div class="tab-pane fade show active" id="service-panel">
                        <div id="list-service" class="list-group list-group-flush">
                            <?php foreach ($notifikasi_service as $s): ?>
                                <div class="list-group-item p-3">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <div class="fw-bold small"><?= $s['hostname'] ?></div>
                                            <small class="text-muted d-block">User: <?= $s['nama_user'] ?? 'N/A' ?></small>
                                            <small class="text-primary" style="font-size: 0.7rem;">Masuk: <?= date('d/m/Y H:i', strtotime($s['tanggal_masuk'])) ?></small>
                                        </div>
                                        <div class="col-auto">
                                            <form action="proses-service.php" method="POST">
                                                <input type="hidden" name="id_service" value="<?= $s['id_service'] ?>">
                                                <button type="submit" name="pickup_service" class="btn btn-sm btn-outline-primary rounded-pill">Pick Up</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="grace-panel">
                        <div id="list-grace" class="list-group list-group-flush">
                            <?php foreach ($notifikasi_grace as $g): ?>
                                <div class="list-group-item border-start border-danger border-4">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="fw-bold text-danger small"><?= $g['hostname'] ?></div>
                                            <small class="text-muted d-block">User: <?= $g['nama'] ?? 'N/A' ?></small>
                                            <small class="text-danger fw-bold" style="font-size: 0.7rem;">Overdue <?= $g['keterlambatan'] ?> hari</small>
                                        </div>
                                        <a href="tampil.php?cari=<?= urlencode($g['hostname']) ?>" class="btn btn-sm btn-danger rounded-pill">Tarik</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>
<script>
// --- 1. VARIABEL GLOBAL ---
let serviceReminderInterval = null;
let graceReminderInterval = null;
let notifInitialized = false;

const socket = io("http://172.16.3.60:3000");
const notifSound = document.getElementById('notifSound');
const graceSound = document.getElementById('graceSound');

let countGrace = <?= count($notifikasi_grace) ?>;
let currentServiceCount = <?= count($notifikasi_service) ?>;

// --- 2. FUNGSI INISIALISASI (Dibuat agar langsung jalan) ---
function initNotificationSystem() {
    if (notifInitialized) return;
    notifInitialized = true;

    // Jalankan interval reminder
    startServiceReminder();
    startGraceReminder();

    // Notifikasi awal saat halaman terbuka
    if (countGrace > 0) {
        setTimeout(() => {
            // Kita coba bunyikan suara. Jika klik sidebar tadi dianggap sah oleh browser, suara akan muncul.
            if (graceSound) {
                graceSound.play().catch(e => {
                    console.log("Autoplay dicegah browser, suara akan aktif setelah klik berikutnya.");
                });
            }
            fireToast(`⚠️ Reminder: ${countGrace} aset overdue Grace Period!`, "danger");
        }, 1000);
    }
}

// --- 3. TOAST & MODAL (Tetap Sama) ---
function fireToast(message, type = 'info') {
    let container = document.querySelector('.toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        container.style.zIndex = '10005';
        document.body.appendChild(container);
    }

    const id = 'toast-' + Date.now();
    const bgColor = type === 'danger' ? 'bg-danger' : 'bg-primary';
    const html = `
        <div id="${id}" class="toast align-items-center text-white ${bgColor} border-0 shadow-lg animate__animated animate__fadeInRight"
             role="alert" style="cursor: pointer; min-width: 250px;">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="bi bi-bell-fill me-2"></i> ${message}
                    <div style="font-size: 0.7rem; opacity: 0.8; margin-top: 3px;">Klik untuk detail</div>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>`;

    container.insertAdjacentHTML('beforeend', html);
    const toastElem = document.getElementById(id);
    const bsToast = new bootstrap.Toast(toastElem, { delay: 10000 });
    bsToast.show();

    toastElem.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-close')) return;
        showNotifModal(type === 'danger' ? '#grace-panel' : '#service-panel');
        bsToast.hide();
    });
}

function showNotifModal(targetPanel = null) {
    const modalElem = document.getElementById('notifikasiModal');
    document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
    document.body.classList.remove('modal-open');
    document.body.style.overflow = '';

    const modalInstance = bootstrap.Modal.getOrCreateInstance(modalElem);
    modalInstance.show();

    if (targetPanel) {
        const tabBtn = document.querySelector(`button[data-bs-target="${targetPanel}"]`);
        if (tabBtn) bootstrap.Tab.getOrCreateInstance(tabBtn).show();
    }
}

// --- 4. LOGIKA AUTO-RUN SAAT LOAD ---
document.addEventListener('DOMContentLoaded', () => {
    // 1. Langsung coba aktifkan sistem (mengambil sisa interaksi klik sidebar)
    initNotificationSystem();

    // 2. Jika tombol lonceng diklik manual
    const btnLonceng = document.getElementById('btnNotifLonceng');
    if (btnLonceng) {
        btnLonceng.addEventListener('click', function(e) {
            e.preventDefault();
            initNotificationSystem();
            showNotifModal();
        });
    }
});

// Cadangan: jika DOMContentLoaded terlalu cepat, pancing dengan pergerakan mouse
document.addEventListener('mousemove', initNotificationSystem, { once: true });

// --- 5. DATA UPDATE & SOCKET (Tetap Sama) ---
function updateNotifData() {
    fetch('api_get_notifikasi.php').then(res => res.json()).then(data => {
        if (data.grace_count > countGrace) {
            if (graceSound) graceSound.play().catch(e => {});
            fireToast(`⚠️ Aset baru overdue Grace Period!`, "danger");
        }
        countGrace = data.grace_count;
        currentServiceCount = data.service_count;
        const badge = document.getElementById('badge-notif');
        if (badge) {
            badge.innerText = data.total;
            badge.style.display = data.total > 0 ? 'block' : 'none';
        }
    });
}

// --- 3. REMINDER INTERVALS ---
function startServiceReminder() {
    if (serviceReminderInterval) clearInterval(serviceReminderInterval);
    serviceReminderInterval = setInterval(() => {
        if (currentServiceCount > 0) {
            // TAMBAHKAN INI: Agar suara berbunyi saat reminder muncul
            if (notifSound) {
                notifSound.play().catch(e => console.log("Audio play blocked:", e));
            }
            fireToast(`🔔 Reminder: Ada ${currentServiceCount} service belum di pick-up!`, "primary");
        }
    }, 120000); // 2 Menit
}

function startGraceReminder() {
    if (graceReminderInterval) clearInterval(graceReminderInterval);
    graceReminderInterval = setInterval(() => {
        if (countGrace > 0) {
            if (graceSound) {
                graceSound.play().catch(e => console.log("Audio play blocked:", e));
            }
            fireToast(`⚠️ PERHATIAN: ${countGrace} aset overdue Grace Period!`, "danger");
        }
    }, 21600000); // 6 Jam
}

// --- 5. DATA UPDATE & SOCKET ---
socket.on('service_update', (data) => {
    if (data.action === 'service_insert') {
        // Pastikan suara diputar di sini juga untuk data real-time baru
        if (notifSound) {
            notifSound.play().catch(e => console.log("Audio play blocked:", e));
        }
        fireToast("🔔 Service Request baru masuk!", "primary");
    }
    updateNotifData();
});
</script>