<?php

require 'koneksi.php';

if (isset($_GET['aksi']) && $_GET['aksi'] == 'hapus_tipe') {
    $id = $_GET['id'];
    // Sebaiknya cek dulu apakah tipe ini sudah dipakai di data stok
    $cek = mysqli_query($koneksi, "SELECT id_barang FROM peripheral_items WHERE id_barang = '$id' LIMIT 1");

    if (mysqli_num_rows($cek) > 0) {
        header("Location: peripherals.php?halaman=3&res=danger&msg=Gagal! Model ini sudah memiliki data stok.");
    } else {
        mysqli_query($koneksi, "DELETE FROM peripheral_types WHERE id_tipe = '$id'");
        header("Location: peripherals.php?halaman=3&res=success&msg=Model berhasil dihapus");
    }
    exit;
}
