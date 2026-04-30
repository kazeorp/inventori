<audio id="notifSound" src="asset/notification.mp3" preload="auto"></audio>
<audio id="graceSound" src="asset/graceperiod.mp3" preload="auto"></audio>

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