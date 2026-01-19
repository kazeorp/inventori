<?php

include "koneksi.php";

// tipe-laptop.php - Mengambil daftar tipe/model dari tabel device_types

// Pastikan variabel koneksi ($koneksi) sudah tersedia sebelum file ini di-include.

if (isset($koneksi)) {
    // Ambil data dari database, diurutkan secara alphabet
    $query = "SELECT type_name FROM device_types ORDER BY type_name ASC";
    $result_tipe = mysqli_query($koneksi, $query);

    // Opsi default
    echo '<option value="">-- Pilih Tipe (Merk/Model) --</option>';

    if ($result_tipe && mysqli_num_rows($result_tipe) > 0) {
        while ($row = mysqli_fetch_assoc($result_tipe)) {
            $tipe = htmlspecialchars($row['type_name']);
            // Data yang diambil sudah dalam format UPPERCASE
            echo "<option value=\"$tipe\">$tipe</option>";
        }
    } else {
        echo '<option value="">(Belum ada tipe yang ditambahkan di database)</option>';
    }
} else {
    // Fallback jika koneksi gagal (Error ini jarang terjadi jika koneksi.php sudah di-include)
    echo '<option value="">Error: Koneksi database tidak tersedia.</option>';
}
