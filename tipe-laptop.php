<?php

// HAPUS include "koneksi.php" di sini karena sudah dipanggil di tampil.php

if (isset($koneksi)) {
    $query = "SELECT type_name FROM device_types ORDER BY type_name ASC";
    $result_tipe = mysqli_query($koneksi, $query);

    echo '<option value="">-- Pilih Tipe (Merk/Model) --</option>';

    if ($result_tipe && mysqli_num_rows($result_tipe) > 0) {
        while ($row = mysqli_fetch_assoc($result_tipe)) {
            $tipe = htmlspecialchars($row['type_name']);
            echo "<option value=\"$tipe\">$tipe</option>";
        }
    } else {
        echo '<option value="">(Belum ada tipe di database)</option>';
    }
} else {
    // Ini akan muncul di dalam dropdown jika koneksi benar-benar tidak terbaca
    echo '<option value="">Error: Koneksi tidak ditemukan</option>';
}
