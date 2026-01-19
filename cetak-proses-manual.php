<?php

// cetak-proses-manual.php

include 'session.php';

// Proteksi Role
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'superadmin')) {
    exit("Akses ditolak.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jenis = $_POST['jenis_form'];

    // Menghapus jenis_form dari data agar tidak ikut masuk ke query string data aset
    unset($_POST['jenis_form']);

    // Menandai bahwa ini adalah akses manual
    $_POST['manual'] = 'true';

    // Membangun query string dari data form
    $query_data = http_build_query($_POST);

    if ($jenis === 'accepted') {
        header("Location: cetak-form.php?" . $query_data);
    } else {
        header("Location: cetak-return-form.php?" . $query_data);
    }
    exit;
} else {
    header("Location: cetak-manual.php");
    exit;
}
