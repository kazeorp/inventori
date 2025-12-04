// register_service.php (FINAL DENGAN INSTANT REDIRECT)

<?php
include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Ambil dan Sanitasi Data
    $hostname = mysqli_real_escape_string($koneksi, strtoupper(trim($_POST['hostname'])));
    $nama_user = mysqli_real_escape_string($koneksi, strtoupper(trim($_POST['nama_user'])));
    $divisi = mysqli_real_escape_string($koneksi, strtoupper(trim($_POST['divisi'])));

    $catatan_registrasi = "Aset memerlukan registrasi";
    $msg = null;

    // --- VALIDASI DUPLIKASI ---

    // 1. Cek apakah Hostname sudah terdaftar di tabel inventori
    $check_inventori_query = "SELECT hostname FROM inventori WHERE hostname = '$hostname'";
    $result_inventori = mysqli_query($koneksi, $check_inventori_query);

    if (mysqli_num_rows($result_inventori) > 0) {
        $msg = "error&msg=Aset_dengan_Hostname_".$hostname."_sudah_terdaftar_di_Inventori._Silakan_cek_status_aset_di_aplikasi.";
    }

    // 2. Cek apakah Hostname sudah memiliki request 'Registrasi Aset' yang pending di service_list
    if (!$msg) {
        $check_service_query = "SELECT hostname FROM service_list WHERE hostname = '$hostname' AND catatan = '$catatan_registrasi'";
        $result_service_check = mysqli_query($koneksi, $check_service_query);

        if (mysqli_num_rows($result_service_check) > 0) {
            $msg = "error&msg=Aset_dengan_Hostname_".$hostname."_sudah_memiliki_Request_Registrasi_yang_sedang_menunggu_diproses.";
        }
    }

    // --- PROSES INSERT ---
    if (!$msg) {
        $tanggal_masuk = date('Y-m-d H:i:s');
        $catatan = $catatan_registrasi;

        $query = "INSERT INTO service_list (hostname, nama_user, divisi, catatan, tanggal_masuk)
                  VALUES ('$hostname', '$nama_user', '$divisi', '$catatan', '$tanggal_masuk')";

        if (mysqli_query($koneksi, $query)) {
            // pushWebSocketUpdate(mysqli_insert_id($koneksi), 'service_insert');
            $msg = "success&msg=Request_Registrasi_Aset_untuk_$hostname_telah_disimpan";
        } else {
            $msg = "error&msg=Gagal_menyimpan_request_ke_Service_List_Error:_".mysqli_error($koneksi);
        }
    }

    // --- INSTANT REDIRECT ---

    $redirect_page = basename($_SERVER['HTTP_REFERER'] ?? 'index.php');
    $refresh_url = $redirect_page . '?status=' . $msg;

    // 💡 Menggunakan header("Location: ...") untuk redirect instan
    header("Location: $refresh_url");

    exit;

} else {
    header("Location: index.php");
    exit;
}
?>