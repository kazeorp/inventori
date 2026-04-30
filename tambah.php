<?php

session_start();
include 'koneksi.php';
include 'helpers.php'; // Digunakan untuk fungsi pushWebSocketUpdate

// Ambil ID admin yang sedang login
$admin_id_login = $_SESSION['admin_id'] ?? 0;
$user_role_login = $_SESSION['role'] ?? 'normal';

// Tentukan halaman redirect default (untuk non-admin atau error fatal)
$redirect_page = ($user_role_login === 'admin' || $user_role_login === 'superadmin') ? 'index2.php' : 'kelola-aset.php';

// Cegah akses user normal
if ($user_role_login !== 'admin' && $user_role_login !== 'superadmin') {
    $msg = urlencode("Anda tidak memiliki akses untuk menambah data.");
    header("Location: $redirect_page?status=danger&msg=$msg");
    exit;
}

// Proses jika form dikirim
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil dan sanitasi data
    $warna          = mysqli_real_escape_string($koneksi, $_POST['warna'] ?? '');
    $status         = mysqli_real_escape_string($koneksi, $_POST['status'] ?? '');
    $type           = mysqli_real_escape_string($koneksi, $_POST['type'] ?? '');
    $serial_number  = strtoupper(mysqli_real_escape_string($koneksi, $_POST['serial_number'] ?? ''));
    $ram            = mysqli_real_escape_string($koneksi, $_POST['ram'] ?? '');
    $storage        = strtoupper(mysqli_real_escape_string($koneksi, $_POST['storage'] ?? ''));
    $win            = strtoupper(mysqli_real_escape_string($koneksi, $_POST['win'] ?? ''));
    $keterangan     = strtoupper(mysqli_real_escape_string($koneksi, $_POST['keterangan'] ?? ''));
    $kelengkapan    = strtoupper(mysqli_real_escape_string($koneksi, $_POST['kelengkapan'] ?? ''));
    // Jika kosong, set variabel menjadi NULL (sebagai string untuk query)
    $tanggal_masuk_val  = !empty($_POST['tanggal_masuk']) ? "'" . mysqli_real_escape_string($koneksi, $_POST['tanggal_masuk']) . "'" : "NULL";
    $tanggal_keluar_val = !empty($_POST['tanggal_keluar']) ? "'" . mysqli_real_escape_string($koneksi, $_POST['tanggal_keluar']) . "'" : "NULL";
    $nik            = mysqli_real_escape_string($koneksi, $_POST['nik'] ?? '');
    $nama           = strtoupper(mysqli_real_escape_string($koneksi, $_POST['nama'] ?? ''));
    $divisi         = strtoupper(mysqli_real_escape_string($koneksi, $_POST['divisi'] ?? ''));
    $domain         = mysqli_real_escape_string($koneksi, $_POST['domain'] ?? '');
    $device_category = mysqli_real_escape_string($koneksi, $_POST['device_category'] ?? '');

    // 🚀 DATA BARU: Ambil service_id_to_update dari hidden field
    $service_id_to_update = (int) ($_POST['service_id_to_update'] ?? 0);

    // KONVERSI HOSTNAME KE UPPERCASE
    $hostname = strtoupper(mysqli_real_escape_string($koneksi, $_POST['hostname'] ?? ''));

    // Cek apakah hostname sudah digunakan
    $cek_duplikat = mysqli_query($koneksi, "SELECT id FROM inventori WHERE hostname='$hostname'");
    if (mysqli_num_rows($cek_duplikat) > 0) {
        $msg = urlencode("Hostname $hostname sudah ada di inventori.");
        // Redirect ke tampil.php (halaman inventori utama) dengan pesan error
        header("Location: tampil.php?res=danger&msg=$msg");
        exit;
    }

    // Query simpan ke tabel inventori
    $sql = "INSERT INTO inventori (
        warna, status, hostname, type, serial_number, domain, device_category, ram, storage, win, keterangan, kelengkapan, tanggal_masuk, tanggal_keluar, nik, nama, divisi
    ) VALUES (
        '$warna', '$status', '$hostname', '$type', '$serial_number', '$domain', '$device_category', '$ram', '$storage', '$win', '$keterangan', '$kelengkapan', $tanggal_masuk_val, $tanggal_keluar_val, '$nik', '$nama', '$divisi'
    )";

    // Eksekusi dan feedback
    if (mysqli_query($koneksi, $sql)) {
        // 1. Dapatkan ID inventori yang baru dimasukkan
        $new_inventori_id = mysqli_insert_id($koneksi);

        // 2. Logika Khusus untuk Registrasi Aset dari Service Request
        if ($service_id_to_update > 0 && $admin_id_login > 0) {

            // Update Service List: Isi id_inventori dan klaim service tersebut
            $update_service_query = "
                UPDATE service_list SET
                    id_inventori = $new_inventori_id,
                    current_admin_id = $admin_id_login
                WHERE id_service = $service_id_to_update
            ";

            if (mysqli_query($koneksi, $update_service_query)) {
                // Sukses Registrasi dan Claim
                pushWebSocketUpdate($service_id_to_update, 'service_claim');
                $msg = urlencode("Aset berhasil diregistrasi (ID #$new_inventori_id) dan otomatis diclaim untuk service request #$service_id_to_update.");
                header("Location: index2.php?res=success&msg=$msg");
                exit;
            } else {
                // Sukses Registrasi, Gagal Claim
                pushWebSocketUpdate($new_inventori_id, 'asset_insert');
                $msg = urlencode("Aset berhasil diregistrasi, tetapi gagal mengklaim service request #$service_id_to_update. Silakan klaim secara manual.");
                header("Location: index2.php?res=warning&msg=$msg");
                exit;
            }
        }

        // 3. Logika untuk Tambah Inventori Biasa (Jika service_id_to_update tidak ada)
        pushWebSocketUpdate($new_inventori_id, 'asset_insert');
        $msg = urlencode("Data inventori berhasil ditambahkan!");
        header("Location: tampil.php?res=success&msg=$msg"); // Redirect ke halaman inventori
        exit;

    } else {
        // Gagal INSERT ke inventori
        $msg = urlencode("Gagal menambahkan data: " . mysqli_error($koneksi));
        header("Location: tampil.php?res=danger&msg=$msg"); // Redirect ke halaman inventori
        exit;
    }
} else {
    // Akses langsung ke tambah.php tanpa POST
    header("Location: tampil.php");
    exit;
}
