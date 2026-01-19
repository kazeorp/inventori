<?php

// Query mengambil semua tipe peripheral dan menjumlahkan stoknya
$sql_peripheral_dinamis = "
    SELECT
        pt.nama_type,
        IFNULL(SUM(p.stok), 0) AS total_stok
    FROM peripheral_types pt
    LEFT JOIN peripherals p ON pt.nama_type = p.tipe
    GROUP BY pt.nama_type
    ORDER BY pt.nama_type ASC
";

$result_peripheral = mysqli_query($koneksi, $sql_peripheral_dinamis);
