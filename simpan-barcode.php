<?php
header('Content-Type: text/plain'); // Pastikan respons berupa teks biasa

$barcodeFolder = 'barcodes/';

// --- Pengecekan dan Pembuatan Direktori ---
if (!is_dir($barcodeFolder)) {
    // Gunakan pengecekan yang lebih ketat untuk pembuatan folder
    if (!mkdir($barcodeFolder, 0755, true)) {
        // Jika pembuatan folder gagal, hentikan proses dan berikan pesan error
        http_response_code(500); // Internal Server Error
        die("❌ Gagal membuat direktori barcode.");
    }
}

// --- Menerima dan Mendekode Data JSON ---
$json_data = file_get_contents("php://input");
if ($json_data === false) {
    http_response_code(400); // Bad Request
    die("❌ Gagal membaca data input.");
}

$data = json_decode($json_data, true);

// --- Sanitasi dan Validasi Data ---
if (!isset($data['hostname']) || !isset($data['image'])) {
    http_response_code(400); // Bad Request
    die("❌ Data hostname atau image tidak ditemukan.");
}

// Sanitasi Hostname: Hanya biarkan alfanumerik dan tanda hubung
$hostname = preg_replace('/[^a-zA-Z0-9\-]/', '', $data['hostname']);
$imageData = $data['image'];

// Pengecekan nilai kosong setelah sanitasi
if (empty($hostname) || empty($imageData)) {
    http_response_code(400); // Bad Request
    die("❌ Hostname atau data gambar kosong setelah sanitasi.");
}

// --- Proses Decoding Base64 ---
// Hapus prefix data URI
$base64_string = str_replace('data:image/png;base64,', '', $imageData);
// Tambahkan padding jika hilang (untuk keandalan decoding)
$base64_string = str_replace(' ', '+', $base64_string); 

$decoded = base64_decode($base64_string, true); // Gunakan 'true' untuk mode strict

if ($decoded === false) {
    http_response_code(400); // Bad Request
    die("❌ Gagal mendekode data Base64.");
}

// --- Menyimpan File ---
$filename = $barcodeFolder . $hostname . ".png";

if (file_put_contents($filename, $decoded)) {
    // Berhasil disimpan
    http_response_code(200); // OK
    echo "✅ Barcode $hostname berhasil disimpan.";
} else {
    // Gagal menyimpan file (mungkin karena izin folder)
    http_response_code(500); // Internal Server Error
    echo "❌ Gagal menyimpan barcode ke direktori.";
}
?>