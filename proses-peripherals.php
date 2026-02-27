<?php

session_start();
require 'koneksi.php';

// Pastikan admin sudah login untuk semua proses di bawah
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$admin_sekarang = $_SESSION['username'];

// =======================================================
// 1. SIMPAN MODEL BARU
// =======================================================
if (isset($_POST['simpan_model'])) {
    if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'superadmin') {
        header("Location: peripherals.php?res=danger&msg=Akses Ditolak!");
        exit;
    }

    // Ambil data dari form
    $tipe_barang = strtoupper(mysqli_real_escape_string($koneksi, $_POST['tipe_barang']));
    $model       = strtoupper(mysqli_real_escape_string($koneksi, $_POST['model'])); // Menyesuaikan name="model"
    $deskripsi   = strtoupper(mysqli_real_escape_string($koneksi, $_POST['deskripsi_tipe'])); // Menyesuaikan name="deskripsi_tipe"

    // Logika Kode Otomatis
    $query_max = mysqli_query($koneksi, "SELECT MAX(CAST(kode_barang AS UNSIGNED)) as max_kode FROM peripheral_types");
    $row_max   = mysqli_fetch_assoc($query_max);
    $next_number = (int) $row_max['max_kode'] + 1;
    $kode_barang = str_pad($next_number, 3, "0", STR_PAD_LEFT);

    $query = "INSERT INTO peripheral_types (kode_barang, tipe_barang, model, deskripsi_tipe)
              VALUES ('$kode_barang', '$tipe_barang', '$model', '$deskripsi')";

    if (mysqli_query($koneksi, $query)) {
        header("Location: peripherals.php?halaman=3&res=success&msg=Model $model berhasil disimpan!");
    } else {
        header("Location: peripherals.php?halaman=3&res=danger&msg=Gagal Simpan: " . mysqli_error($koneksi));
    }
    exit;
}

// =======================================================
// 2. UPDATE MODEL
// =======================================================
if (isset($_POST['update_model'])) {
    $id_tipe     = $_POST['id_tipe'];
    $tipe_barang = strtoupper(mysqli_real_escape_string($koneksi, $_POST['tipe_barang']));
    $model       = strtoupper(mysqli_real_escape_string($koneksi, $_POST['model']));
    $deskripsi   = strtoupper(mysqli_real_escape_string($koneksi, $_POST['deskripsi_tipe']));

    $query = "UPDATE peripheral_types SET
              tipe_barang='$tipe_barang',
              model='$model',
              deskripsi_tipe='$deskripsi'
              WHERE id_tipe='$id_tipe'";

    mysqli_query($koneksi, $query);
    header("Location: peripherals.php?halaman=3&res=success&msg=Data Model berhasil diupdate");
    exit;
}

// =======================================================
// 3. HAPUS MODEL
// =======================================================
if (isset($_GET['aksi']) && $_GET['aksi'] == 'hapus_model') {
    $id = $_GET['id'];
    // Cek relasi menggunakan kode_barang (karena tabel items biasanya simpan kode_barang)
    // Ambil dulu kode_barangnya berdasarkan id_tipe
    $get_kode = mysqli_query($koneksi, "SELECT kode_barang FROM peripheral_types WHERE id_tipe = '$id'");
    $data_kode = mysqli_fetch_assoc($get_kode);
    $kb = $data_kode['kode_barang'];

    $cek = mysqli_query($koneksi, "SELECT kode_barang FROM peripheral_items WHERE kode_barang = '$kb' LIMIT 1");

    if (mysqli_num_rows($cek) > 0) {
        header("Location: peripherals.php?halaman=3&res=danger&msg=Gagal! Model ini sudah memiliki data stok.");
    } else {
        mysqli_query($koneksi, "DELETE FROM peripheral_types WHERE id_tipe = '$id'");
        header("Location: peripherals.php?halaman=3&res=success&msg=Model berhasil dihapus");
    }
    exit;
}

// =======================================================
// 4. BARANG MASUK (MULTI-GROUP & KUPON SN)
// =======================================================
if (isset($_POST['simpan_masuk'])) {
    $no_po = strtoupper(mysqli_real_escape_string($koneksi, $_POST['no_po']));
    $groups = $_POST['group'];

    mysqli_begin_transaction($koneksi);

    try {
        foreach ($groups as $group) {
            $kode_barang = mysqli_real_escape_string($koneksi, $group['kode_barang']);
            $peruntukan  = mysqli_real_escape_string($koneksi, $group['peruntukan']);
            $nama_user   = ($peruntukan == 'User') ? strtoupper(mysqli_real_escape_string($koneksi, $group['nama_user'])) : null;

            // Memecah string Kupon SN (koma) menjadi array
            $sns = explode(',', $group['sn']);

            foreach ($sns as $sn) {
                $sn_clean = strtoupper(mysqli_real_escape_string($koneksi, trim($sn)));
                if (empty($sn_clean)) {
                    continue;
                }

                // Cek Duplikat SN
                $cek_sn = mysqli_query($koneksi, "SELECT serial_number FROM peripheral_items WHERE serial_number = '$sn_clean' LIMIT 1");
                if (mysqli_num_rows($cek_sn) > 0) {
                    throw new Exception("Serial Number $sn_clean sudah terdaftar!");
                }

                $sql = "INSERT INTO peripheral_items
                        (kode_barang, no_po, serial_number, peruntukan, nama_user, tanggal_masuk, admin_input, status)
                        VALUES
                        ('$kode_barang', '$no_po', '$sn_clean', '$peruntukan', '$nama_user', NOW(), '$admin_sekarang', 'Stock')";

                if (!mysqli_query($koneksi, $sql)) {
                    throw new Exception("Database Error saat simpan SN: $sn_clean");
                }
            }
        }
        mysqli_commit($koneksi);
        header("Location: peripherals.php?halaman=1&res=success&msg=Barang masuk PO $no_po berhasil disimpan");
        exit;
    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        header("Location: peripherals.php?halaman=1&res=danger&msg=" . $e->getMessage());
        exit;
    }
}
