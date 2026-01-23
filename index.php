<?php
// Tampilkan alert error login (jika ada)
if (isset($_GET['error'])) {
    $error_msg = "";
    $error_type = htmlspecialchars($_GET['error']);

    if ($error_type === 'empty') {
        $error_msg = "Username dan Password tidak boleh kosong!";
    } elseif ($error_type === 'user') {
        $error_msg = "Username tidak ditemukan.";
    } elseif ($error_type === 'pass') {
        $error_msg = "Password salah. Silakan coba lagi.";
    }

    if (!empty($error_msg)) {
        ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $error_msg ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php
    }
}
?>

<?php
// index.php (Dashboard Default / Normal Role)

include 'session.php';
include "koneksi.php";

// 1. Tentukan apakah user sudah login sebagai Admin (Cek Kunci Utama Sesi)
$is_admin_logged_in = isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);

// 2. Jika user sudah login, ambil role-nya. Jika belum, set role default 'normal'.
$user_role = $is_admin_logged_in ? ($_SESSION['role'] ?? 'normal') : 'normal';


// 3. Logic Router: Jika sudah login dan memiliki role Admin/Superadmin, alihkan.
if ($is_admin_logged_in && ($user_role === 'admin' || $user_role === 'superadmin')) {
    // Alihkan ke dashboard admin (index2.php)
    header("Location: index2.php");
    exit();
}

$service_list_pending = [];

if ($koneksi) {
    // Query Pending Claim: Sudah masuk service_list (telah di-scan dan dicatat)
    // TAPI claim_status BELUM 'On Service' DAN BELUM 'Selesai'
    $sql_pending = "SELECT sl.id_service, sl.hostname, sl.tanggal_masuk,
                            sl.claim_status, sl.admin_claim_name,
                            i.nama AS nama_user, i.divisi
                     FROM service_list sl
                     LEFT JOIN inventori i ON sl.id_inventori = i.id
                     WHERE sl.claim_status IS NULL AND sl.finish_status IS NULL
                     ORDER BY sl.tanggal_masuk DESC
                     LIMIT 20";

    $result_pending = mysqli_query($koneksi, $sql_pending);

    if ($result_pending && mysqli_num_rows($result_pending) > 0) {
        while ($row = mysqli_fetch_assoc($result_pending)) {
            $service_list_pending[] = $row;
        }
    }
}


// TAMBAHAN 2: Logika mengambil Histori Service yang sudah selesai
// >>> REVISI INI: Mengambil data Aset yang SEDANG DALAM SERVICE (On Service)
$service_list_on_service = [];
if ($koneksi) {
    // Query On Service: Sudah diklaim (claim_status='On Service') DAN Belum Selesai (finish_status IS NULL)
    $sql_service = "SELECT sl.id_service, sl.hostname, sl.tanggal_masuk,
                           sl.claim_status, sl.admin_claim_name,
                           i.nama AS nama_user, i.divisi
                    FROM service_list sl
                    LEFT JOIN inventori i ON sl.id_inventori = i.id
                    WHERE sl.claim_status = 'On Service' AND sl.finish_status IS NULL
                    ORDER BY sl.tanggal_masuk ASC
                    LIMIT 20";

    $result_service = mysqli_query($koneksi, $sql_service);

    if ($result_service && mysqli_num_rows($result_service) > 0) {
        while ($row = mysqli_fetch_assoc($result_service)) {
            $service_list_on_service[] = $row;
        }
    }
}
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
    /* Dipertahankan: Batasi lebar form agar bisa ditengahkan */
    #manual-check-form-container {
        max-width: 400px; /* Batas lebar agar input tidak terlalu lebar */
        margin: 0 auto; /* Menengahkan container */
    }
    .scan-success-message {
        padding: 20px;
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
        border-radius: 5px;
        font-size: 1.1rem;
        text-align: center;
        margin-top: 20px;
    }
    </style>
</head>
<body class="bg-light">
<?php include 'header_user.php'; ?>
<?php include 'sidebar.php'; ?>

<main class="main-content container py-4">
    <h2 class="mb-4 text-center">Scan Barcode</h2>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div id="manual-check-form-container" class="text-center mb-5">

                <div id="scan-status" class="fw-bold mb-3 text-primary"></div>

                <form method="POST" id="manual-check-form">
                    <input type="text" name="hostname" id="manual-hostname-input" class="form-control form-control-lg mb-3" placeholder="Input Hostname di sini" required autofocus>

                    <button type="button" id="manual-check-button" class="btn btn-success btn-lg">Input Aset</button>
                </form>
            </div>

            <hr class="mb-1">

            <div id="status-alert-container">
                <?php if (isset($_SESSION['scan_error'])): ?>
                    <div class="alert alert-danger mt-3"><?= htmlspecialchars($_SESSION['scan_error']) ?></div>
                    <?php unset($_SESSION['scan_error']); ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="row justify-content-center mt-2">

<div class="col-md-6">
            <h5 class="mb-3 text-center">User Dalam Antrian</h5>

            <?php $scan_chunks = array_chunk($service_list_pending, 5); // Bagi data menjadi potongan 5?>

            <div id="assetScanCarousel" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner" id="scanHistoryInner">

                <?php if (empty($service_list_pending)): ?>
                    <div class="carousel-item active">
                        <div class="table-responsive mb-4">
                            <table class="table table-sm table-bordered table-striped table-hover">
                                <thead class="table-dark">
                                    <tr><th colspan="4">Riwayat Scan Aset</th></tr>
                                </thead>
                                <tbody>
                                    <tr><td colspan="4" class="text-center">Tidak ada aset yang menunggu dipick up.</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php else: ?>

                    <?php foreach ($scan_chunks as $index => $chunk): ?>
                    <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                        <div class="table-responsive mb-4">
                            <table class="table table-sm table-bordered table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>No.</th>
                                        <th>Hostname</th>
                                        <th>Nama User</th>
                                        <th>Divisi</th>
                                        <th>Waktu Scan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $no = ($index * 5) + 1;
                        foreach ($chunk as $data): ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><?= htmlspecialchars($data['hostname'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($data['nama_user'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($data['divisi'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($data['tanggal_masuk'] ?? '-') ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php endforeach; ?>

                <?php endif; ?>

                </div>

                <?php if (count($scan_chunks) > 1): // Tampilkan kontrol jika lebih dari 1 halaman?>
                <button class="carousel-control-prev" type="button" data-bs-target="#assetScanCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#assetScanCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
                <?php endif; ?>

            </div>
        </div>

<div class="col-md-6">
            <h5 class="mb-3 text-center"> Daftar Aset On Service</h5>

            <?php $service_chunks = array_chunk($service_list_on_service, 5); // Bagi data menjadi potongan 5?>

            <div id="serviceOnCarousel" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner">

                <?php if (empty($service_list_on_service)): ?>
                    <div class="carousel-item active">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered table-striped table-hover">
                                <thead class="table-warning">
                                    <tr><th colspan="4">Daftar Aset On Service</th></tr>
                                </thead>
                                <tbody>
                                    <tr><td colspan="4" class="text-center">Tidak ada aset yang sedang dalam service (On Service).</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php else: ?>

                    <?php foreach ($service_chunks as $index => $chunk): ?>
                    <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered table-striped table-hover">
                                <thead class="table-warning">
                                    <tr>
                                        <th>No.</th>
                                        <th>Hostname</th>
                                        <th>Nama User</th>
                                        <th>Technician</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $no = ($index * 5) + 1;
                        foreach ($chunk as $service): ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><?= htmlspecialchars($service['hostname'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($service['nama_user'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($service['admin_claim_name'] ?? 'Belum Dipick Up') ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php endforeach; ?>

                <?php endif; ?>

                </div>

                <?php if (count($service_chunks) > 1): // Tampilkan kontrol jika lebih dari 1 halaman?>
                <button class="carousel-control-prev" type="button" data-bs-target="#serviceOnCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#serviceOnCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
                <?php endif; ?>

            </div>
        </div>
</main>
<script src="http://172.16.3.60:3000/socket.io/socket.io.js"></script>
<script>
// Di file main.js atau di tag <script> di index2.php

// Ganti [IP_SERVER_ANDA] dengan IP yang sama seperti di koneksi.php
const socket = io('http://172.16.3.60:3000');

socket.on('connect', () => {
    console.log('[Client WS] Connected to Socket.IO server!');
});

socket.on('service_update', (data) => {
    console.log('[Client WS] Received Update:', data);
    const { id, action } = data;

    // --- LOGIKA PEMBARUAN TAMPILAN ---
    if (action === 'service_claim') {
        window.location.reload();

    } else if (action === 'service_complete') {
        window.location.reload();
    }
    // ... Tambahkan logika untuk action lain (cancel, dll.)
});

socket.on('disconnect', () => {
    console.log('[Client WS] Disconnected.');
});

</script>
<script>

    // Hanya menggunakan elemen yang diperlukan untuk manual input / scanner fisik
    const scanStatusElement = document.getElementById("scan-status");
    const alertContainer = document.getElementById('status-alert-container');
    const mainHeaderElement = document.querySelector('.main-content h2');

    const manualInput = document.getElementById('manual-hostname-input');
    const manualButton = document.getElementById('manual-check-button');
    // const manualForm = document.getElementById('manual-check-form'); // Tidak perlu form object

    // Fungsi untuk menambahkan item riwayat scan baru ke carousel
function appendHistoryItem(data) {
    const historyContainer = document.getElementById('scanHistoryInner');

    // 1. Buat baris baru (<tr>) untuk data yang baru di-scan
    const newRow = `
        <tr>
            <td>${data.hostname ?? '-'}</td>
            <td>${data.nama ?? '-'}</td>
            <td>${data.divisi ?? '-'}</td>
            <td>${data.waktu_scan ?? '-'}</td>
        </tr>
    `;

    // 2. Buat seluruh struktur tabel/carousel item
    const newCarouselItem = `
        <div class="carousel-item active">
            <div class="table-responsive mb-4">
                <table class="table table-sm table-bordered table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Hostname</th>
                            <th>Nama User</th>
                            <th>Divisi</th>
                            <th>Waktu Scan</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${newRow}
                        </tbody>
                </table>
            </div>
        </div>
    `;

    // 3. Hapus kelas 'active' dari item yang sekarang aktif
    const activeItem = historyContainer.querySelector('.carousel-item.active');
    if (activeItem) {
        activeItem.classList.remove('active');
    }

    // 4. Sisipkan item baru sebagai yang aktif (di awal)
    // Untuk menyederhanakan, kita hanya membuat item baru. Jika sebelumnya kosong:
    if (historyContainer.innerHTML.includes('Tidak ada aset')) {
        historyContainer.innerHTML = ''; // Kosongkan pesan "Tidak ada aset"
    }

    // Karena logika Riwayat Scan Anda di-chunk per 5,
    // cara paling sederhana adalah mengganti seluruh inner HTML saat ada scan baru
    // agar data scan terbaru terlihat di slide pertama.
    // Namun, itu terlalu kompleks. Kita akan buat entry baru muncul di slide pertama.

    // *Modifikasi Sederhana:* Hanya menampilkan entri terbaru di DOM tanpa mengurus chunking.

    const tempTable = document.createElement('table');
    tempTable.innerHTML = `<thead class="table-dark"><tr><th>Hostname</th><th>Nama User</th><th>Divisi</th><th>Waktu Scan</th></tr></thead><tbody>${newRow}</tbody>`;

    const scanContainer = document.querySelector('.main-content .col-md-6:first-child');
    if(scanContainer) {
        // Hapus elemen lama (yang mungkin berisi pesan "Tidak ada aset")
        const oldCarousel = document.getElementById('assetScanCarousel');
        if (oldCarousel) oldCarousel.remove();

        // Tampilkan hanya item yang baru di-scan dalam bentuk tabel sederhana (bukan carousel)
        const instantAlert = document.createElement('div');
        instantAlert.classList.add('alert', 'alert-info', 'mb-3');
        instantAlert.innerHTML = `**Aset ${data.hostname} berhasil di-scan.** Data Service sedang dicatat...`;
        scanContainer.prepend(instantAlert);
    }
}



    // --- FUNGSI UTAMA HANDLER AJAX (Mencari Aset dan Mencatat Service) ---
    function handleAssetCheck(code) {
        const processedCode = code ? code.trim() : '';

        if (!processedCode) {
             scanStatusElement.innerText = " Hostname kosong. Masukkan data.";
             return;
        }

        scanStatusElement.innerText = " Hostname/Barcode terdeteksi: " + processedCode + ". Mencari data aset...";

        // --- FETCH AJAX KE HANDLER SCAN AWAL (ajax_scan_handler.php) ---
        fetch('ajax_scan_handler.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'hostname=' + encodeURIComponent(processedCode)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('HTTP status ' + response.status);
            }
            return response.json();
        })
        .then(data => {
      alertContainer.innerHTML = '';
      scanStatusElement.classList.remove('text-danger', 'text-primary');
      scanStatusElement.classList.add('text-success');

      if (data.status === 'success') {

                // >>> BARU: PERBARUI TAMPILAN SECARA INSTAN DI BROWSER <<<
                appendHistoryItem(data);
                // >>> END BARU <<<

        // ASET DITEMUKAN: Lanjut ke addServiceEntry (Alur otomatis dikembalikan)
        scanStatusElement.innerText = ` Aset ${data.hostname} ditemukan. Mencatat service...`;
        addServiceEntry(data.id_aset, data.hostname); // KEMBALIKAN PANGGILAN INI

      } else{
                // ASET TIDAK DITEMUKAN
                const errorMessage = data.message || "Aset tidak ditemukan atau respon server tidak valid.";
                scanStatusElement.innerText = " " + errorMessage;
                scanStatusElement.classList.remove('text-success');
                scanStatusElement.classList.add('text-danger');
                alertContainer.innerHTML = `<div class="alert alert-danger mt-3">${errorMessage}</div>`;
            }
        })
        .catch(error => {
            // ERROR KONEKSI/SERVER
            console.error('AJAX Scan Error:', error);
            const displayError = error.message.includes('HTTP status') ? 'Gagal koneksi server.' : 'Gagal koneksi server. Coba lagi.';
            scanStatusElement.innerText = " " + displayError;
            scanStatusElement.classList.remove('text-success');
            scanStatusElement.classList.add('text-danger');
            alertContainer.innerHTML = `<div class="alert alert-danger mt-3">Koneksi gagal atau server bermasalah.</div>`;
        });
    }

    // --- FUNGSI MENAMBAHKAN ENTRI SERVICE (Dipanggil setelah aset ditemukan) ---
    function addServiceEntry(id_aset, hostname) {
        fetch('ajax_add_service.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'id_inventori=' + encodeURIComponent(id_aset) + '&hostname=' + encodeURIComponent(hostname)
        })
        .then(response => response.json())
        .then(data => {
            let message = data.message;

            if (data.status === 'success' || data.status === 'warning') {

                mainHeaderElement.innerHTML = " Setelah scan silahkan tunggu PIC datang";
                mainHeaderElement.classList.remove('text-danger', 'text-primary');
                mainHeaderElement.classList.add('text-success');

                scanStatusElement.innerText = ` Aset ${hostname} berhasil dicatat.`;
                alertContainer.innerHTML = `<div class="alert alert-success mt-3">${message}. Halaman akan di-refresh dalam 3 detik.</div>`;

                // Bersihkan input setelah berhasil
                manualInput.value = '';

                refreshPageAfterDelay(3000);

            } else {
                console.error("Gagal menambahkan service entry:", message);
                scanStatusElement.innerText = ` Gagal mencatat service aset ${hostname}.`;
                alertContainer.innerHTML = `<div class="alert alert-danger mt-3"> Gagal mencatat service aset ${hostname}: ${message}</div>`;
            }
        })
        .catch(error => {
            console.error('AJAX Service Error:', error);
            scanStatusElement.innerText = ` Error koneksi saat mencatat service.`;
            alertContainer.innerHTML = `<div class="alert alert-danger mt-3"> Error koneksi saat mencatat service.</div>`;
        });
    }

    // Fungsi untuk me-refresh halaman setelah delay
    function refreshPageAfterDelay(delay = 3000) {
        setTimeout(() => {
            window.location.reload();
        }, delay);
    }


    // --- EVENT KLIK TOMBOL MANUAL ---
    manualButton.addEventListener('click', function() {
        // Menggunakan tombol klik sebagai trigger manual
        const code = manualInput.value;
        handleAssetCheck(code);
    });

    // --- EVENT KEYDOWN (Merespons Tombol ENTER dari Scanner Fisik) ---
    manualInput.addEventListener('keydown', function(event) {
        // Kode 13 adalah tombol Enter (yang dikirim oleh scanner)
        if (event.keyCode === 13) {
            event.preventDefault(); // Mencegah form di-submit default

            const code = manualInput.value;
            // Panggil handler utama
            handleAssetCheck(code);
        }
    });

</script>

<script src="bootstrap/js/bootstrap.bundle.min.js"></script>
<?php
// Memuat file modal_register_aset.php
include 'modal-register-aset.php';
?>

</body>
</html>