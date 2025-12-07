<?php
// fetch_peripheral_stock.php

// File ini mengasumsikan variabel koneksi database ($koneksi) sudah tersedia
// dari file induk (index.php) yang meng-include file ini.

$koneksi = $koneksi ?? null; // Defensive check for $koneksi

// 1. Query Total RAM (DDR3, DDR4, DDR5)
$sql_ram = "SELECT SUM(stock_count) AS total_ram FROM peripheral_stock WHERE item_name IN ('RAM DDR3', 'RAM DDR4', 'RAM DDR5')";
$q_ram = $koneksi ? mysqli_query($koneksi, $sql_ram) : false;
$d_ram = $q_ram ? mysqli_fetch_assoc($q_ram) : null;
$total_ram = $d_ram ? ($d_ram['total_ram'] ?? 0) : 0;

// 2. Query Total SSD (SATA dan NVMe, semua ukuran digabungkan)
$sql_ssd = "SELECT SUM(stock_count) AS total_ssd FROM peripheral_stock WHERE item_name LIKE 'SSD%'";
$q_ssd = $koneksi ? mysqli_query($koneksi, $sql_ssd) : false;
$d_ssd = $q_ssd ? mysqli_fetch_assoc($q_ssd) : null;
$total_ssd = $d_ssd ? ($d_ssd['total_ssd'] ?? 0) : 0;

// 3. Query Stok Keyboard
$sql_keyboard = "SELECT stock_count AS total_keyboard FROM peripheral_stock WHERE item_name = 'Keyboard'";
$q_keyboard = $koneksi ? mysqli_query($koneksi, $sql_keyboard) : false;
$d_keyboard = $q_keyboard ? mysqli_fetch_assoc($q_keyboard) : null;
$total_keyboard = $d_keyboard ? ($d_keyboard['total_keyboard'] ?? 0) : 0;

// 4. Query Stok Mouse
$sql_mouse = "SELECT stock_count AS total_mouse FROM peripheral_stock WHERE item_name = 'Mouse'";
$q_mouse = $koneksi ? mysqli_query($koneksi, $sql_mouse) : false;
$d_mouse = $q_mouse ? mysqli_fetch_assoc($q_mouse) : null;
$total_mouse = $d_mouse ? ($d_mouse['total_mouse'] ?? 0) : 0;

// 5. Query Stok Monitor
$sql_monitor = "SELECT stock_count AS total_monitor FROM peripheral_stock WHERE item_name = 'Monitor'";
$q_monitor = $koneksi ? mysqli_query($koneksi, $sql_monitor) : false;
$d_monitor = $q_monitor ? mysqli_fetch_assoc($q_monitor) : null;
$total_monitor = $d_monitor ? ($d_monitor['total_monitor'] ?? 0) : 0;
?>