<?php
include "koneksi.php";
require_once 'Classes/PHPExcel.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel_file']['tmp_name'])) {
    $file = $_FILES['excel_file']['tmp_name'];

    try {
        $excelReader = PHPExcel_IOFactory::createReaderForFile($file);
        $excelObj = $excelReader->load($file);
        $sheet = $excelObj->getActiveSheet();
        $rowCount = $sheet->getHighestRow();

        $inserted = 0;
        $updated = 0;
        $skipped = 0;

        for ($row = 2; $row <= $rowCount; $row++) {
            // Kolom dimulai dari indeks 1 karena kolom 0 (ID) diabaikan
            $rak = strtoupper(trim($sheet->getCellByColumnAndRow(1, $row)->getValue()));
            $status = ucfirst(strtolower(trim($sheet->getCellByColumnAndRow(2, $row)->getValue())));
            $hostname = trim($sheet->getCellByColumnAndRow(3, $row)->getValue());
            $type = trim($sheet->getCellByColumnAndRow(4, $row)->getValue());
            $ram = trim($sheet->getCellByColumnAndRow(5, $row)->getValue());
            $storage = trim($sheet->getCellByColumnAndRow(6, $row)->getValue());
            $win = trim($sheet->getCellByColumnAndRow(7, $row)->getValue());
            $keterangan = trim($sheet->getCellByColumnAndRow(8, $row)->getValue());
            $kelengkapan = trim($sheet->getCellByColumnAndRow(9, $row)->getValue());
            $tanggal_keluar = $sheet->getCellByColumnAndRow(10, $row)->getValue();
            $nik = trim($sheet->getCellByColumnAndRow(11, $row)->getValue());
            $nama = trim($sheet->getCellByColumnAndRow(12, $row)->getValue());
            $divisi = trim($sheet->getCellByColumnAndRow(13, $row)->getValue());

            // Konversi tanggal
            if (PHPExcel_Shared_Date::isDateTime($sheet->getCellByColumnAndRow(10, $row))) {
                $tanggal_keluar = date('Y-m-d', PHPExcel_Shared_Date::ExcelToPHP($tanggal_keluar));
            } else {
                $tanggal_keluar = date('Y-m-d', strtotime($tanggal_keluar));
            }

            // Lewati jika hostname kosong
            if (empty($hostname)) {
                $skipped++;
                continue;
            }

            // Cek apakah hostname sudah ada
            $cek = mysqli_query($koneksi, "SELECT id FROM inventori WHERE hostname='$hostname'");
            if (mysqli_num_rows($cek) > 0) {
                // Update data
                $query = "UPDATE inventori SET
                            rak='$rak',
                            status='$status',
                            type='$type',
                            ram='$ram',
                            storage='$storage',
                            win='$win',
                            keterangan='$keterangan',
                            kelengkapan='$kelengkapan',
                            tanggal_keluar='$tanggal_keluar',
                            nik='$nik',
                            nama='$nama',
                            divisi='$divisi'
                          WHERE hostname='$hostname'";
                mysqli_query($koneksi, $query);
                $updated++;
            } else {
                // Insert data baru (tanpa ID)
                $query = "INSERT INTO inventori (rak, status, hostname, type, ram, storage, win, keterangan, kelengkapan, tanggal_keluar, nik, nama, divisi)
                          VALUES ('$rak', '$status', '$hostname', '$type', '$ram', '$storage', '$win', '$keterangan', '$kelengkapan', '$tanggal_keluar', '$nik', '$nama', '$divisi')";
                mysqli_query($koneksi, $query);
                $inserted++;
            }
        }

        echo "<div style='padding:20px; font-family:sans-serif'>";
        echo "<h3>✅ Import Selesai</h3>";
        echo "<p>Data baru ditambahkan: <strong>$inserted</strong></p>";
        echo "<p>Data diperbarui: <strong>$updated</strong></p>";
        echo "<p>Baris dilewati (hostname kosong): <strong>$skipped</strong></p>";
        echo "<a href='tampil.php'>← Kembali ke Inventori</a>";
        echo "</div>";

    } catch (Exception $e) {
        echo "❌ Gagal membaca file: " . $e->getMessage();
    }
} else {
    echo "❌ File tidak ditemukan atau metode tidak sesuai.";
}
?>
