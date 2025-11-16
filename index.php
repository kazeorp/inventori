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

// --- LOGIKA CEK MANUAL (Dipertahankan untuk kasus refresh) ---
if (isset($_POST['cek'])) {
    if (!$koneksi) {
        $_SESSION['scan_error'] = "Gagal terhubung ke database.";
        header("Location: index.php"); exit();
    }

    $hostname = mysqli_real_escape_string($koneksi, $_POST['hostname']);

    // Query aset
    $sql_aset = "SELECT id, hostname, nama, divisi, status FROM inventori WHERE hostname = '$hostname' LIMIT 1";
    $result_aset = mysqli_query($koneksi, $sql_aset);

    if ($result_aset === FALSE) {
        $_SESSION['scan_error'] = "Error Query SQL: " . mysqli_error($koneksi);
        unset($_SESSION['last_scan']);
    } else if (mysqli_num_rows($result_aset) > 0) {
        $last_scan_data = mysqli_fetch_assoc($result_aset);
        
        // TAMBAHAN 1: Menyimpan waktu scan saat check manual
        $last_scan_data['waktu_scan'] = date('Y-m-d H:i:s'); 
        
        // Simpan data untuk tampilan manual check
        $_SESSION['last_scan'] = $last_scan_data;
        unset($_SESSION['scan_error']);
    } else {
        $_SESSION['scan_error'] = "Aset dengan Hostname '{$hostname}' TIDAK DITEMUKAN.";
        unset($_SESSION['last_scan']);
    }
    header("Location: index.php");
    exit();
}
// Ambil hasil scan terakhir (jika ada)
$last_scan = $_SESSION['last_scan'] ?? null;


// TAMBAHAN 2: Logika mengambil Histori Service yang sudah selesai
$service_list_done = [];
if ($koneksi) {
// REVISI QUERY: Menggunakan finish_status
    $sql_service = "SELECT id_service, hostname, tanggal_masuk, finish_status
                    FROM service_list 
                    WHERE finish_status = 'Selesai' 
                    ORDER BY tanggal_masuk DESC 
                    LIMIT 20";
    
    $result_service = mysqli_query($koneksi, $sql_service);

    if ($result_service && mysqli_num_rows($result_service) > 0) {
        while ($row = mysqli_fetch_assoc($result_service)) {
            $service_list_done[] = $row;
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
    /* ✅ Dipertahankan: Batasi lebar form agar bisa ditengahkan */
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
<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>

<main class="main-content container py-4">
    <h2 class="mb-4 text-center">Cek Aset dan Service</h2>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div id="manual-check-form-container" class="text-center mb-5">
                <h5 class="mb-3">Input Hostname</h5>
                
                <div id="scan-status" class="fw-bold mb-3 text-primary"></div>

                <form method="POST" id="manual-check-form">
                    <input type="text" name="hostname" id="manual-hostname-input" class="form-control form-control-lg mb-3" placeholder="Input Hostname di sini" required autofocus>
                    
                    <button type="button" id="manual-check-button" class="btn btn-success btn-lg">Cek Aset</button>
                </form>
            </div>
            
            <hr class="mb-5">
            
            <div id="status-alert-container">
                <?php if (isset($_SESSION['scan_error'])): ?>
                    <div class="alert alert-danger mt-3"><?= htmlspecialchars($_SESSION['scan_error']) ?></div>
                    <?php unset($_SESSION['scan_error']); ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="row justify-content-center mt-4">
        
        <div class="col-md-6">
            <h5 class="mb-3 text-center">🛠️ Informasi Aset Terakhir</h5>

            <div class="table-responsive mb-4" id="asset-info-table">
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
                        <?php if ($last_scan): ?>
                        <tr>
                            <td><?= htmlspecialchars($last_scan['hostname']) ?></td>
                            <td><?= htmlspecialchars($last_scan['nama']) ?></td>
                            <td><?= htmlspecialchars($last_scan['divisi']) ?></td>
                            <td><?= htmlspecialchars($last_scan['waktu_scan'] ?? '-') ?></td> 
                        </tr>
                        <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center">Silahkan scan aset untuk menampilkan data.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="col-md-6">
            <h5 class="mb-3 text-center">✅ Histori Service Selesai (Terakhir)</h5>
            <div class="table-responsive">
                <table class="table table-sm table-bordered table-striped table-hover">
                    <thead class="table-info">
                        <tr>
                            <th>ID</th>
                            <th>Hostname</th>
                            <th>Tgl. Masuk</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($service_list_done)): ?>
                        <?php foreach ($service_list_done as $service): ?>
                        <tr>
                            <td><?= htmlspecialchars($service['id_service']) ?></td>
                            <td><?= htmlspecialchars($service['hostname']) ?></td>
                            <td><?= htmlspecialchars($service['tanggal_masuk']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">Tidak ada histori service yang sudah selesai.</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<script>
    // Hanya menggunakan elemen yang diperlukan untuk manual input / scanner fisik
    const scanStatusElement = document.getElementById("scan-status");
    const alertContainer = document.getElementById('status-alert-container');
    const mainHeaderElement = document.querySelector('.main-content h2'); 
    
    const manualInput = document.getElementById('manual-hostname-input');
    const manualButton = document.getElementById('manual-check-button');
    // const manualForm = document.getElementById('manual-check-form'); // Tidak perlu form object

    // --- FUNGSI UTAMA HANDLER AJAX (Mencari Aset dan Mencatat Service) ---
    function handleAssetCheck(code) {
        const processedCode = code ? code.trim() : '';

        if (!processedCode) {
             scanStatusElement.innerText = "⚠️ Hostname kosong. Masukkan data.";
             return;
        }

        scanStatusElement.innerText = "✅ Hostname/Barcode terdeteksi: " + processedCode + ". Mencari data aset...";
        
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
                // ASET DITEMUKAN: Lanjut ke addServiceEntry
                scanStatusElement.innerText = `✅ Aset ${data.hostname} ditemukan. Mencatat service...`;
                addServiceEntry(data.id_aset, data.hostname);

            } else {
                // ASET TIDAK DITEMUKAN
                const errorMessage = data.message || "Aset tidak ditemukan atau respon server tidak valid.";
                scanStatusElement.innerText = "❌ " + errorMessage;
                scanStatusElement.classList.remove('text-success');
                scanStatusElement.classList.add('text-danger');
                alertContainer.innerHTML = `<div class="alert alert-danger mt-3">${errorMessage}</div>`;
            }
        })
        .catch(error => {
            // ERROR KONEKSI/SERVER
            console.error('AJAX Scan Error:', error);
            const displayError = error.message.includes('HTTP status') ? 'Gagal koneksi server.' : 'Gagal koneksi server. Coba lagi.';
            scanStatusElement.innerText = "⚠️ " + displayError;
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
                
                mainHeaderElement.innerHTML = "✅ Setelah scan silahkan tunggu PIC datang";
                mainHeaderElement.classList.remove('text-danger', 'text-primary');
                mainHeaderElement.classList.add('text-success'); 
                
                scanStatusElement.innerText = `✅ Aset ${hostname} berhasil dicatat.`;
                alertContainer.innerHTML = `<div class="alert alert-success mt-3">${message}. Halaman akan di-refresh dalam 3 detik.</div>`;
                
                // Bersihkan input setelah berhasil
                manualInput.value = '';

                refreshPageAfterDelay(3000); 
                
            } else {
                console.error("Gagal menambahkan service entry:", message);
                scanStatusElement.innerText = `❌ Gagal mencatat service aset ${hostname}.`;
                alertContainer.innerHTML = `<div class="alert alert-danger mt-3">⚠️ Gagal mencatat service aset ${hostname}: ${message}</div>`;
            }
        })
        .catch(error => {
            console.error('AJAX Service Error:', error);
            scanStatusElement.innerText = `⚠️ Error koneksi saat mencatat service.`;
            alertContainer.innerHTML = `<div class="alert alert-danger mt-3">⚠️ Error koneksi saat mencatat service.</div>`;
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
</body>
</html>