<?php
// koneksi.php
date_default_timezone_set('Asia/Jakarta');

// Menggunakan mysqli_connect yang procedural (koneksi akan ada di variabel $koneksi)
// Jika gagal, $koneksi akan berisi objek error, yang akan dicek di handler.
$koneksi = mysqli_connect("localhost","root","","inventori_test");

// HINDARI TAG PENUTUP