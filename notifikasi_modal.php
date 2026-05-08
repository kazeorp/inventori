<?php
// Ensure notification data is available even if notifikasi.php was not included
if (!isset($notifikasi_service) || !isset($notifikasi_grace)) {
    require_once "koneksi.php";

    // --- 1. Fetch Overdue Grace Period Assets ---
    $notifikasi_grace = [];
    $cek_grace = mysqli_query($koneksi, "SELECT * FROM inventori WHERE status='Grace Period' ORDER BY tanggal_masuk ASC");
    $hari_ini = new DateTime();

    if ($cek_grace) {
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
    }

    // --- 2. Fetch Pending Service Requests ---
    $notifikasi_service = [];
    $query_service = "
        SELECT s.id_service, s.hostname, s.tanggal_masuk, s.catatan, i.nama AS nama_user
        FROM service_list s
        LEFT JOIN inventori i ON s.hostname = i.hostname
        WHERE s.claim_status IS NULL OR s.claim_status = ''
        ORDER BY s.tanggal_masuk ASC";
    $cek_service = mysqli_query($koneksi, $query_service);
    if ($cek_service) {
        while ($row = mysqli_fetch_assoc($cek_service)) {
            $notifikasi_service[] = $row;
        }
    }
}
?>
<audio id="notifSound" src="asset/notification.mp3" preload="auto"></audio>
<audio id="graceSound" src="asset/graceperiod.mp3" preload="auto"></audio>

<div class="modal fade" id="notifikasiModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: var(--radius-lg);">
            <div class="modal-header bg-dark text-white px-4 py-3">
                <h6 class="modal-title fw-bold"><i class="bi bi-megaphone me-2"></i> System Notifications</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <nav class="bg-light sticky-top shadow-sm px-4 pt-3">
                    <div class="nav nav-tabs nav-fill border-bottom-0" id="notifTab">
                        <button class="nav-link active fw-bold small pb-3 border-0 border-bottom border-3 border-transparent" data-bs-toggle="tab" data-bs-target="#service-panel">
                            <i class="bi bi-tools me-1"></i> SERVICE (<?= count($notifikasi_service) ?>)
                        </button>
                        <button class="nav-link fw-bold small pb-3 text-danger border-0 border-bottom border-3 border-transparent" data-bs-toggle="tab" data-bs-target="#grace-panel">
                            <i class="bi bi-clock-history me-1"></i> OVERDUE (<?= count($notifikasi_grace) ?>)
                        </button>
                    </div>
                </nav>
                <div class="tab-content px-4 py-3" id="notifTabContent" style="max-height: 500px; overflow-y: auto;">
                    <div class="tab-pane fade show active" id="service-panel">
                        <div id="list-service" class="list-group list-group-flush gap-2">
                            <?php foreach ($notifikasi_service as $s): ?>
                                <div class="list-group-item rounded border p-3 shadow-sm hover-lift">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <div class="fw-bold text-dark mb-1"><?= $s['hostname'] ?></div>
                                            <div class="text-muted small"><i class="bi bi-person me-1"></i> <?= $s['nama_user'] ?? 'N/A' ?></div>
                                            <div class="text-primary mt-1" style="font-size: 0.75rem;"><i class="bi bi-calendar-event me-1"></i> <?= date('d M Y, H:i', strtotime($s['tanggal_masuk'])) ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <form action="proses-service.php" method="POST">
                                                <input type="hidden" name="id_service" value="<?= $s['id_service'] ?>">
                                                <button type="submit" name="pickup_service" class="btn btn-sm btn-primary rounded-pill px-3">Assign to Me</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="grace-panel">
                        <div id="list-grace" class="list-group list-group-flush gap-2">
                            <?php foreach ($notifikasi_grace as $g): ?>
                                <div class="list-group-item rounded border-start border-danger border-4 p-3 shadow-sm hover-lift">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="fw-bold text-danger mb-1"><?= $g['hostname'] ?></div>
                                            <div class="text-muted small"><i class="bi bi-person me-1"></i> <?= $g['nama'] ?? 'N/A' ?></div>
                                            <div class="text-danger fw-bold mt-1" style="font-size: 0.75rem;"><i class="bi bi-exclamation-circle me-1"></i> Late for <?= $g['keterlambatan'] ?> days</div>
                                        </div>
                                        <a href="tampil.php?cari=<?= urlencode($g['hostname']) ?>" class="btn btn-sm btn-danger px-3 rounded-pill shadow-sm">Recall Asset</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light py-2 px-4">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>
<script>
let serviceRem = null, graceRem = null, notifInit = false;
let serviceReminderInterval = null;
let graceReminderInterval = null;
let notifInitialized = false;

const socket = (typeof io !== 'undefined') ? io("http://172.16.3.60:3000") : null;
const notifSound = document.getElementById('notifSound');
const graceSound = document.getElementById('graceSound');

let countGrace = <?= (int) count($notifikasi_grace) ?>;
let currentServiceCount = <?= (int) count($notifikasi_service) ?>;

// Helper function to check if Bootstrap is ready
function isBootstrapReady() {
    return typeof bootstrap !== 'undefined';
}

// --- 1. FIRE TOAST FUNCTION ---
function fireToast(message, type = 'info') {
    if (!isBootstrapReady()) {
        // If Bootstrap is not ready, try again after a short delay
        return setTimeout(() => fireToast(message, type), 200);
    }

    let container = document.querySelector('.toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        container.style.zIndex = '10005';
        container.style.pointerEvents = 'none'; // Ensure container doesn't block clicks
        document.body.appendChild(container);
    }

    const id = 'toast-' + Date.now();
    const bgColor = type === 'danger' ? 'bg-danger' : 'bg-primary';
    const html = `
        <div id="${id}" class="toast align-items-center text-white ${bgColor} border-0 shadow-lg"
             role="alert" style="cursor: pointer; min-width: 250px; pointer-events: auto;">
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

    try {
        const bsToast = new bootstrap.Toast(toastElem, { delay: 10000 });
        bsToast.show();

        toastElem.addEventListener('click', function(e) {
            if (e.target.classList.contains('btn-close')) return;
            showNotifModal(type === 'danger' ? '#grace-panel' : '#service-panel');
            bsToast.hide();
        });
    } catch (err) {
        console.error("Gagal inisialisasi Toast:", err);
    }
}

// --- 2. SHOW NOTIFICATION MODAL FUNCTION ---
function showNotifModal(targetPanel = null) {
    if (!isBootstrapReady()) {
        console.error("Bootstrap library tidak ditemukan!");
        return;
    }

    const modalElem = document.getElementById('notifikasiModal');
    if (!modalElem) return;

    try {
        const modalInstance = bootstrap.Modal.getOrCreateInstance(modalElem);
        modalInstance.show();

        if (targetPanel) {
            const tabBtn = document.querySelector(`button[data-bs-target="${targetPanel}"]`);
            if (tabBtn) {
                const tabInstance = bootstrap.Tab.getOrCreateInstance(tabBtn);
                tabInstance.show();
            }
        }
    } catch (err) {
        console.error("Gagal membuka Modal:", err);
    }
}

// --- 3. UPDATE NOTIFICATION DATA & REMINDER LOGIC ---
function updateNotifData() {
    fetch('api_get_notifikasi.php')
        .then(res => res.json())
        .then(data => {
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
        })
        .catch(err => console.error("Gagal fetch notifikasi:", err));
}

function startServiceReminder() {
    if (serviceReminderInterval) clearInterval(serviceReminderInterval);
    serviceReminderInterval = setInterval(() => {
        if (currentServiceCount > 0) {
            if (notifSound) notifSound.play().catch(e => {});
            fireToast(`🔔 Reminder: Ada ${currentServiceCount} service belum di pick-up!`, "primary");
        }
    }, 120000); // 2 Menit
}

function startGraceReminder() {
    if (graceReminderInterval) clearInterval(graceReminderInterval);
    graceReminderInterval = setInterval(() => {
        if (countGrace > 0) {
            if (graceSound) graceSound.play().catch(e => {});
            fireToast(`⚠️ PERHATIAN: ${countGrace} aset overdue Grace Period!`, "danger");
        }
    }, 21600000); // 6 Jam
}

// --- 4. INITIALIZATION ---
function initNotificationSystem() {
    if (notifInitialized) return;
    notifInitialized = true;

    startServiceReminder();
    startGraceReminder();

    // Initial check for grace period on load
    if (countGrace > 0) {
        setTimeout(() => {
            if (graceSound) graceSound.play().catch(e => {});
            fireToast(`⚠️ Reminder: ${countGrace} aset overdue Grace Period!`, "danger");
        }, 1500);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    initNotificationSystem();

    const btnLonceng = document.getElementById('btnNotifLonceng');
    if (btnLonceng) {
        btnLonceng.addEventListener('click', function(e) {
            e.preventDefault();
            showNotifModal();
        });
    }
});

// Run system if there is mouse movement (to handle browser autoplay policy)
document.addEventListener('mousemove', initNotificationSystem, { once: true });

// --- 5. SOCKET LISTENER ---
if (socket) {
    socket.on('service_update', (data) => {
        if (data.action === 'service_insert') {
            if (notifSound) notifSound.play().catch(e => {});
            fireToast("🔔 Service Request baru masuk!", "primary");
        }
        updateNotifData();
    });
}
</script>