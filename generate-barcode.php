<?php
// WAJIB: Memuat session untuk role dan otentikasi sidebar
include 'session.php'; 
include "koneksi.php";

// Ambil role dari session
$role = isset($_SESSION['role']) ? $_SESSION['role'] : 'normal';

$barcodeFolder = 'barcodes/';

// Pastikan folder barcode/ tersedia
if (!is_dir($barcodeFolder)) {
    // Penggunaan mkdir dengan error checking yang minimal
    @mkdir($barcodeFolder, 0755, true); 
}

$query = mysqli_query($koneksi, "SELECT id, hostname, rak, status, type FROM inventori ORDER BY hostname ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Barcode Inventori</title>
  <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="css/style.css"> 
  <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
  <style>
    /* Styling untuk kotak barcode */
    .barcode-box {
      border: 1px solid #ccc;
      padding: 10px;
      margin: 10px;
      display: inline-block;
      text-align: center;
      width: 220px; /* Lebar tetap untuk konsistensi */
      background-color: #fff;
      border-radius: 6px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    /* Target canvas dan img di dalam barcode-box */
    /* Tinggi yang disesuaikan untuk menampung teks dari JsBarcode */
    .barcode-box canvas, .barcode-box img {
      width: 200px;
      height: 70px; 
    }
  </style>
</head>
<body class="bg-light"> 
  <?php include 'header.php'; ?>
  <?php include 'sidebar.php'; ?>

  <main class="main-content">
    <h2 class="mb-4">🖨️ Barcode Inventori</h2>

    <div class="d-flex flex-wrap align-items-center gap-2 mb-4">
      <button type="button" class="btn btn-success" onclick="simpanSemuaBarcode()">🔖 Simpan Semua Barcode</button>

      <form method="POST" action="aksi-barcode.php" onsubmit="return confirm('Yakin ingin hapus semua barcode?')">
        <button type="submit" name="hapus_semua" class="btn btn-danger">🗑️ Hapus Semua Barcode</button>
      </form>

      <form method="POST" action="aksi-barcode.php" class="d-flex align-items-center">
        <select name="rak" class="form-select me-2" required style="min-width: 150px;">
          <option value="">Pilih Rak</option>
          <?php
          $rakList = mysqli_query($koneksi, "SELECT DISTINCT rak FROM inventori WHERE rak != '' ORDER BY rak ASC");
          while ($rak = mysqli_fetch_assoc($rakList)) {
              $rakValue = htmlspecialchars($rak['rak'] ?? '');
              echo "<option value='{$rakValue}'>{$rakValue}</option>";
          }
          ?>
        </select>
        <button type="submit" name="hapus_per_rak" class="btn btn-warning" onclick="return confirm('Hapus semua barcode di rak ini?')">🗑️ Hapus Per Rak</button>
      </form>
    </div>
    <div class="d-flex flex-wrap">
      <?php while ($row = mysqli_fetch_assoc($query)):
        $id = $row['id'];
        $hostname = htmlspecialchars($row['hostname'] ?? '');
        $rak = htmlspecialchars($row['rak'] ?? '');
        $status = htmlspecialchars($row['status'] ?? '');
        $type = htmlspecialchars($row['type'] ?? '');
        
        // Menggunakan nilai mentah dari database untuk nama file
        $raw_hostname = $row['hostname'];
        $filename = $barcodeFolder . $raw_hostname . ".png";
        $barcodeExists = file_exists($filename);
      ?>
        <div class="barcode-box">
          <?php if ($barcodeExists): ?>
            <img src="<?= $filename ?>" alt="Barcode <?= $hostname ?>">
            
            <div class="text-muted small mt-2"><?= $rak ?> | <?= $type ?> | <?= $status ?></div>

            <form method="POST" action="hapus-barcode.php" onsubmit="return confirm('Yakin ingin hapus barcode <?= $hostname ?>?')">
              <input type="hidden" name="hostname" value="<?= $raw_hostname ?>">
              <button type="submit" class="btn btn-sm btn-danger mt-2">🗑️ Hapus</button>
            </form>

          <?php else: ?>
            <canvas id="barcode-<?= $id ?>" data-hostname="<?= $raw_hostname ?>"></canvas>
            <script>
              // GENERATE BARCODE DENGAN HOSTNAME DI BAWAHNYA
              JsBarcode("#barcode-<?= $id ?>", "<?= $raw_hostname ?>", {
                format: "CODE128",
                displayValue: true, // AKTIFKAN: Tampilkan teks hostname di bawah barcode
                lineColor: "#000",
                width: 2,
                height: 50,
                margin: 5,
                textMargin: 3, // Jarak antara barcode dan teks
                fontSize: 14 // Ukuran font teks hostname
              });
            </script>

            <div class="text-muted small mt-2"><?= $rak ?> | <?= $type ?> | <?= $status ?></div>
            
            <button class="btn btn-sm btn-success mt-2" onclick="saveBarcode('<?= $raw_hostname ?>', <?= $id ?>)">🔖 Simpan</button>
          <?php endif; ?>
        </div>
      <?php endwhile; ?>
    </div>
  </main>

  <script>
    function saveBarcode(hostname, id) {
      const canvas = document.getElementById("barcode-" + id);
      const imageData = canvas.toDataURL("image/png");

      fetch("simpan-barcode.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ hostname: hostname, image: imageData })
      })
      .then(res => res.text())
      .then(msg => {
        alert(msg); 
        location.reload();
      })
      .catch(err => alert("❌ Gagal menyimpan barcode"));
    }

    function simpanSemuaBarcode() {
      // Hanya ambil CANVASES yang belum tersimpan
      const canvases = document.querySelectorAll(".barcode-box canvas[data-hostname]");

      if (canvases.length === 0) {
        alert("🎉 Semua barcode sudah tersimpan!");
        return;
      }

      const fetchPromises = [];

      canvases.forEach((canvas) => {
        const hostname = canvas.getAttribute("data-hostname");

        // RENDER ULANG dengan displayValue: true sebelum mendapatkan data URL
        JsBarcode(canvas, hostname, {
            format: "CODE128",
            displayValue: true, 
            lineColor: "#000",
            width: 2,
            height: 50,
            margin: 5,
            textMargin: 3,
            fontSize: 14 
        });

        const imageData = canvas.toDataURL("image/png");

        const promise = fetch("simpan-barcode.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ hostname: hostname, image: imageData })
        })
        .then(res => res.text())
        .then(msg => console.log(`[${hostname}] Berhasil: ${msg}`))
        .catch(err => console.error(`❌ Gagal simpan ${hostname}:`, err));

        fetchPromises.push(promise);
      });

      Promise.all(fetchPromises)
        .then(() => {
            alert(`✅ ${canvases.length} barcode berhasil diproses! Silakan refresh halaman.`);
            location.reload();
        })
        .catch(() => {
            alert("⚠️ Terjadi error saat memproses beberapa barcode. Cek console log.");
            location.reload();
        });
    }
  </script>

  <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>