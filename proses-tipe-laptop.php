<?php
// Pastikan session dan koneksi database di-include
include 'session.php';
include 'koneksi.php';

// Proteksi akses: HANYA SUPERADMIN yang diizinkan memproses data tipe
if (($_SESSION['role'] ?? 'normal') !== 'superadmin') {
    header("Location: tampil.php?status=error&msg=Akses_ditolak_kelola_tipe");
    exit;
}

// --- Fungsi Helper untuk Redirect ---
// Redirect kembali ke tampil.php dan memberikan pesan status
function redirect_success($msg) {
    header("Location: tampil.php?status=success&msg=" . urlencode($msg));
    exit;
}
function redirect_error($msg) {
    header("Location: tampil.php?status=error&msg=" . urlencode($msg));
    exit;
}

// ===================================
// 1. PROSES TAMBAH TIPE (Menggunakan POST)
// ===================================
if (isset($_POST['submit_tambah_tipe']) && $_POST['action'] == 'tambah') {
    $type_name = strtoupper(mysqli_real_escape_string($koneksi, $_POST['nama_tipe']));

    // Validasi sederhana
    if (empty($type_name)) {
        redirect_error("Nama_tipe_tidak_boleh_kosong");
    }

    // Query untuk insert ke tabel device_types
    // Kolom yang digunakan: type_name
    $query = "INSERT INTO device_types (type_name) VALUES ('$type_name')";

    if (mysqli_query($koneksi, $query)) {
        redirect_success("Tipe_'$type_name'_berhasil_ditambahkan");
    } else {
        redirect_error("Gagal_menambah_tipe:_".mysqli_error($koneksi));
    }
}

// ===================================
// 2. PROSES EDIT TIPE (Menggunakan POST)
// ===================================
if (isset($_POST['submit_edit_tipe']) && $_POST['action'] == 'edit') {
    $id = mysqli_real_escape_string($koneksi, $_POST['id_tipe']);
    $type_name = strtoupper(mysqli_real_escape_string($koneksi, $_POST['nama_tipe']));

    // Validasi sederhana
    if (empty($type_name) || empty($id)) {
        redirect_error("Data_edit_tidak_lengkap");
    }

    // Query untuk update kolom type_name di tabel device_types
    $query = "UPDATE device_types SET type_name='$type_name' WHERE id='$id'";

    if (mysqli_query($koneksi, $query)) {
        redirect_success("Tipe_berhasil_diubah_menjadi_'$type_name'");
    } else {
        redirect_error("Gagal_mengedit_tipe:_".mysqli_error($koneksi));
    }
}

// ===================================
// 3. PROSES HAPUS TIPE (Menggunakan GET dari JS)
// ===================================
if (isset($_GET['action']) && $_GET['action'] == 'hapus' && isset($_GET['id_tipe'])) {
    $id = mysqli_real_escape_string($koneksi, $_GET['id_tipe']);

    // WARNING: Sebaiknya periksa dulu apakah ada inventori yang menggunakan ID tipe ini.
    // Jika ada, Anda harus:
    // 1. Mencegah penghapusan, atau
    // 2. Mengubah nilai kolom 'type' di tabel 'inventori' menjadi NULL atau 'Uncategorized'.

    // Query untuk delete dari tabel device_types
    $query = "DELETE FROM device_types WHERE id='$id'";

    if (mysqli_query($koneksi, $query)) {
        redirect_success("Tipe_berhasil_dihapus");
    } else {
        redirect_error("Gagal_menghapus_tipe:_".mysqli_error($koneksi));
    }
}

// ===================================
// 4. PROSES AMBIL DATA TIPE (Menggunakan GET dari JS)
// ===================================
if (isset($_GET['action']) && $_GET['action'] == 'get_all_tipe') {
    // 1. Set header untuk memberi tahu browser bahwa respons adalah JSON
    header('Content-Type: application/json');

    // 2. Query untuk mengambil semua tipe dari database
    $query = "SELECT id, type_name FROM device_types ORDER BY type_name ASC";
    $result = mysqli_query($koneksi, $query);

    $tipe_array = [];
    if ($result) {
        // 3. Loop melalui hasil dan masukkan ke dalam array
        while ($row = mysqli_fetch_assoc($result)) {
            // Penting: Pastikan nama kolom di sini cocok dengan nama kolom database Anda (id dan type_name)
            $tipe_array[] = [
                'id_tipe' => $row['id'], // Gunakan nama yang konsisten dengan frontend (id_tipe)
                'nama_tipe' => $row['type_name']
            ];
        }
        mysqli_free_result($result);
    }

    // 4. Encode array menjadi JSON dan cetak
    echo json_encode($tipe_array);

    // Hentikan eksekusi script setelah mengembalikan JSON
    exit;
}

// Jika tidak ada aksi yang dikenali, redirect default
header("Location: tampil.php");
exit;
?>