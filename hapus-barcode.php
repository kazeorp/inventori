<?php
$barcodeFolder = 'barcodes/';
if (!is_dir($barcodeFolder)) {
    mkdir($barcodeFolder, 0755, true);
}

if (isset($_POST['hostname'])) {
    $hostname = preg_replace('/[^a-zA-Z0-9\-]/', '', $_POST['hostname']);
    $filename = $barcodeFolder . $hostname . ".png";

    if (file_exists($filename)) {
        unlink($filename);
    }
    header("Location: generate-barcode.php");
    exit;
}
