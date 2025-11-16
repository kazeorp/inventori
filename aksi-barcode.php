<?php
include "koneksi.php";
$barcodeFolder = 'barcodes/';
if (!is_dir($barcodeFolder)) {
    mkdir($barcodeFolder, 0755, true);
}

// 🔖 Simpan Semua Barcode

if (isset($_POST['simpan_semua'])) {
    $result = mysqli_query($koneksi, "SELECT hostname FROM inventori");
    foreach ($result as $row) {
        $hostname = $row['hostname'];
        $filename = $barcodeFolder . $hostname . ".png";
        if (!file_exists($filename)) {
            // Generate barcode PNG via PHP-GD
            $im = imagecreate(200, 60);
            $white = imagecolorallocate($im, 255, 255, 255);
            $black = imagecolorallocate($im, 0, 0, 0);
            imagefill($im, 0, 0, $white);
            $x = 10;
            for ($i = 0; $i < strlen($hostname); $i++) {
                $ascii = ord($hostname[$i]);
                $barWidth = ($ascii % 10 + 1) * 2;
                imagefilledrectangle($im, $x, 10, $x + $barWidth, 50, $black);
                $x += $barWidth + 2;
            }
            imagepng($im, $filename);
            imagedestroy($im);
        }
    }
    header("Location: generate-barcode.php");
    exit;
}

// 🗑️ Hapus Semua Barcode
if (isset($_POST['hapus_semua'])) {
    $files = glob($barcodeFolder . "*.png");
    foreach ($files as $file) {
        unlink($file);
    }
    header("Location: generate-barcode.php");
    exit;
}

// 🗑️ Hapus Barcode Per Rak
if (isset($_POST['hapus_per_rak']) && !empty($_POST['rak'])) {
    $rak = $_POST['rak'];
    $result = mysqli_query($koneksi, "SELECT hostname FROM inventori WHERE rak = '$rak'");
    foreach ($result as $row) {
        $filename = $barcodeFolder . $row['hostname'] . ".png";
        if (file_exists($filename)) {
            unlink($filename);
        }
    }
    header("Location: generate-barcode.php");
    exit;
}

