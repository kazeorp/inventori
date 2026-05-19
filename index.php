<?php
include 'session.php';
include "koneksi.php";

$is_admin_logged_in = isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
$user_role = $is_admin_logged_in ? ($_SESSION['role'] ?? 'normal') : 'normal';

if ($is_admin_logged_in && ($user_role === 'admin' || $user_role === 'superadmin')) {
    header("Location: index2.php");
    exit();
}

function getServices($koneksi, $condition)
{
    $data = [];
    $sql = "SELECT sl.*, i.nama AS nama_user, i.divisi FROM service_list sl
            LEFT JOIN inventori i ON sl.id_inventori = i.id
            WHERE $condition AND sl.finish_status IS NULL
            ORDER BY sl.tanggal_masuk DESC LIMIT 20";
    $res = mysqli_query($koneksi, $sql);
    while ($row = $res ? mysqli_fetch_assoc($res) : null) {
        if ($row) {
            $data[] = $row;
        }
    }
    return $data;
}

$sections = [
    'pending' => [
        'title' => 'Dalam Antrian', 'table' => 'table-dark', 'id' => 'assetScanCarousel',
        'data' => getServices($koneksi, "sl.claim_status IS NULL"),
        'cols' => ['No.', 'Hostname', 'Nama User', 'Divisi', 'Waktu Scan'],
        'keys' => ['hostname', 'nama_user', 'divisi', 'tanggal_masuk'],
    ],
    'progress' => [
        'title' => 'Dalam Perbaikan', 'table' => 'table-warning', 'id' => 'serviceOnCarousel',
        'data' => getServices($koneksi, "sl.claim_status = 'On Service'"),
        'cols' => ['No.', 'Hostname', 'Nama User', 'Technician'],
        'keys' => ['hostname', 'nama_user', 'admin_claim_name'],
    ],
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cek Aset & Service</title>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        #manual-check-form-container { max-width: 400px; margin: 0 auto; }
    </style>
</head>
<body class="bg-light">
<?php include 'header_user.php'; ?>
<?php include 'sidebar.php'; ?>
<main class="main-content container py-4">
    <h3 class="mb-4 text-center">Scan Barcode</h3>
    <div class="row justify-content-center">
        <div class="col-md-8 text-center mb-5" id="manual-check-form-container">
            <div id="scan-status" class="fw-bold mb-3 text-primary"></div>
            <input type="text" id="manual-hostname-input" class="form-control mb-3 text-center text-uppercase" placeholder="Input Hostname di sini" autofocus autocomplete="off">
            <button id="manual-check-button" class="btn btn-primary btn-sm px-4">Input Aset</button>
        </div>
    </div>
    <div id="status-alert-container"></div>
    <div class="row justify-content-center mt-2">
        <?php foreach ($sections as $key => $s): ?>
        <div class="col-md-6">
            <h6 class="mb-3 text-center"><?= $s['title'] ?></h6>
            <div id="<?= $s['id'] ?>" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner">
                <?php if (empty($s['data'])): ?>
                    <div class="carousel-item active"><table class="table table-sm table-bordered"><tbody><tr><td class="text-center">Kosong</td></tr></tbody></table></div>
                <?php else: $chunks = array_chunk($s['data'], 5);
                    foreach ($chunks as $idx => $chunk): ?>
                    <div class="carousel-item <?= $idx === 0 ? 'active' : '' ?>">
                        <table class="table table-sm table-bordered table-striped">
                            <thead class="<?= $s['table'] ?>"><tr><?php foreach ($s['cols'] as $c) {
                                echo "<th>$c</th>";
                            } ?></tr></thead>
                            <tbody>
                                <?php $no = ($idx * 5) + 1;
                        foreach ($chunk as $d): echo "<tr><td>" . $no++ . "</td>";
                            foreach ($s['keys'] as $k) {
                                echo "<td>" . htmlspecialchars($d[$k] ?? '-') . "</td>";
                            } echo "</tr>"; endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endforeach; endif; ?>
                </div>
                <?php if (isset($chunks) && count($chunks) > 1): ?>
                    <button class="carousel-control-prev" data-bs-target="#<?= $s['id'] ?>" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span></button>
                    <button class="carousel-control-next" data-bs-target="#<?= $s['id'] ?>" data-bs-slide="next"><span class="carousel-control-next-icon"></span></button>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</main>
<script src="http://172.16.3.60:3000/socket.io/socket.io.js"></script>
<script>
if (typeof io !== 'undefined') {
    const socket = io('http://172.16.3.60:3000');
    socket.on('service_update', (d) => { if(['service_claim','service_complete'].includes(d.action)) location.reload(); });
}
const $ = id => document.getElementById(id);
const statusEl = $("scan-status"), alertBox = $('status-alert-container'), input = $('manual-hostname-input');

window.handleAssetCheck = async function(code) {
    const host = code.trim();
    if (!host) return;
    statusEl.innerText = "Mencari data...";
    try {
        const res = await fetch('ajax_scan_handler.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'hostname=' + encodeURIComponent(host) });
        const data = await res.json();
        if (data.status === 'success') {
            statusEl.innerText = "Aset ditemukan. Mencatat service...";
            const sRes = await fetch('ajax_add_service.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: `id_inventori=${data.id_aset}&hostname=${data.hostname}` });
            const sData = await sRes.json();
            const cls = sData.status === 'success' ? 'alert-success' : 'alert-warning';
            alertBox.innerHTML = `<div class="alert ${cls} mt-3">${sData.message}. Refreshing...</div>`;
            input.value = '';
            if (sData.status !== 'error') setTimeout(() => location.reload(), 2000);
        } else {
            statusEl.innerHTML = `<span class="text-danger">${data.message}</span>`;
            const reg = document.querySelector('#registerModal [name="hostname"]');
            if (reg) reg.value = host.toUpperCase();
            // Show register modal if asset not found
            const registerModal = new bootstrap.Modal(document.getElementById('registerModal'));
            registerModal.show();
        }
    } catch (e) { statusEl.innerText = "Koneksi server gagal."; }
};
$('manual-check-button').onclick = () => handleAssetCheck(input.value);
input.onkeydown = (e) => { if (e.keyCode === 13) handleAssetCheck(input.value); };
</script>
<script src="bootstrap/js/bootstrap.bundle.min.js"></script>
<script>
// Auto-show login modal if there's a login error in URL
document.addEventListener("DOMContentLoaded", function() {
    const urlParams = new URLSearchParams(window.location.search);
    const msg = urlParams.get('msg');
    const res = urlParams.get('res');

    // Detect if we have a "danger" response with a message (typical of login errors)
    if (res === 'danger' && msg) {
        const errorAlert = document.getElementById('loginErrorAlert');
        const loginModalEl = document.getElementById('loginModal');

        if (errorAlert && loginModalEl) {
            // Populate and show the error inside the modal
            errorAlert.textContent = decodeURIComponent(msg.replace(/\+/g, ' '));
            errorAlert.classList.remove('d-none');

            // Trigger the Bootstrap Modal to open automatically
            bootstrap.Modal.getOrCreateInstance(loginModalEl).show();
        }
    }
});
</script>
<?php include 'toast.php'; ?>
<?php include 'modal-register-aset.php'; ?>
<?php include 'virtual-keyboard.php'; ?>
</body>
</html>