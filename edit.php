<?php
include "koneksi.php";
include "helpers.php"; // 1. Sertakan helpers.php

// --- 1. AMBIL DATA INVENTORI ---
$id = $_GET['id'] ?? '';
if (empty($id)) {
  die("ID data tidak ditemukan!");
}

$data = mysqli_query($koneksi, "SELECT * FROM inventori WHERE id='$id'");
$row = mysqli_fetch_assoc($data);

if (!$row) {
  die("Data inventori tidak ditemukan!");
}

// --- 2. LOGIKA UPDATE DATA ---
if (isset($_POST['update'])) {
  // Ambil dan sanitasi semua data input
  $id_update = mysqli_real_escape_string($koneksi, $_POST['id']);
  $rak = mysqli_real_escape_string($koneksi, $_POST['rak']);
  $status = mysqli_real_escape_string($koneksi, $_POST['status']);

  // DATA BARU
  $domain = mysqli_real_escape_string($koneksi, $_POST['domain']);
  $device_category = mysqli_real_escape_string($koneksi, $_POST['device_category']);

  // HOSTNAME di-UPPERCASE
  $hostname = strtoupper(mysqli_real_escape_string($koneksi, $_POST['hostname']));

  $type = mysqli_real_escape_string($koneksi, $_POST['type']);
  $ram = mysqli_real_escape_string($koneksi, $_POST['ram']);
  $storage = mysqli_real_escape_string($koneksi, $_POST['storage']);
  $win = mysqli_real_escape_string($koneksi, $_POST['win']);
  $keterangan = mysqli_real_escape_string($koneksi, $_POST['keterangan']);
  $kelengkapan = mysqli_real_escape_string($koneksi, $_POST['kelengkapan']);
  $tanggal_keluar = mysqli_real_escape_string($koneksi, $_POST['tanggal_keluar']);
  $nik = mysqli_real_escape_string($koneksi, $_POST['nik']);
  $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
  $divisi = mysqli_real_escape_string($koneksi, $_POST['divisi']);

  // Tambahkan domain dan device_category ke query UPDATE
  $sql = "UPDATE inventori SET
      rak='$rak', status='$status', hostname='$hostname', type='$type',
      domain='$domain', device_category='$device_category',
      ram='$ram', storage='$storage', win='$win', keterangan='$keterangan',
      kelengkapan='$kelengkapan', tanggal_keluar='$tanggal_keluar',
      nik='$nik', nama='$nama', divisi='$divisi'
      WHERE id='$id_update'";

  if (mysqli_query($koneksi, $sql)) {
        // 2. Panggil fungsi untuk memicu WebSocket setelah update berhasil
        pushWebSocketUpdate($id_update, 'update');

    echo "<script>alert('Data berhasil diupdate!'); window.location='tampil.php';</script>";
  } else {
    echo "<script>alert('Error: Gagal mengupdate data: " . mysqli_error($koneksi) . "');</script>";
  }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Edit Data Inventori</title>
  <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
</head>
<body class="bg-light">

<div class="container mt-4">
  <h2 class="mb-4">Edit Data Inventori</h2>
  <form method="POST" action="">
    <input type="hidden" name="id" value="<?php echo htmlspecialchars($row['id']); ?>">

    <div class="row mb-3">
      <div class="col-md-6">
        <label>Status</label>
        <select name="status" id="status" class="form-control" required>
          <?php
            // Mengambil opsi status dan memastikan nilai yang tersimpan di DB terpilih
            $current_status = $row['status'];
            // Opsi status harus konsisten dengan status.php Anda
            $status_options = [
              '', 'Spare', 'Grace Period', 'Pending Service',
              'Scrap', 'MT', 'Ready To Assign', 'Assign', 'Loan'
            ];

            echo '<option value="">-- Pilih Status --</option>';
            foreach ($status_options as $option) {
              $selected = (strcasecmp($option, $current_status) == 0) ? 'selected' : '';
              if (!empty($option)) {
                echo "<option value=\"$option\" $selected>$option</option>";
              }
            }
          ?>
        </select>
      </div>
      <div class="col-md-6">
        <label>Rak</label>
        <input type="text" name="rak" id="rak" class="form-control" value="<?php echo htmlspecialchars($row['rak']); ?>" readonly required>
      </div>
    </div>

    <div class="row mb-3">
      <div class="col-md-6">
        <label>Domain</label>
        <select name="domain" id="domain" class="form-control" required>
          <?php
            $domain_options = ['APP', 'SMF', 'CKP', 'TGR', 'KRW'];
            foreach ($domain_options as $option) {
              $selected = ($row['domain'] == $option) ? 'selected' : '';
              echo "<option value=\"$option\" $selected>$option</option>";
            }
          ?>
        </select>
      </div>
      <div class="col-md-6">
        <label>Kategori Perangkat</label>
        <select name="device_category" id="device_category" class="form-control" required>
          <?php
            $category_options = ['', 'Laptop', 'Desktop', 'Server', 'Printer', 'Monitor'];
            foreach ($category_options as $option) {
              $selected = ($row['device_category'] == $option) ? 'selected' : '';
              $display = empty($option) ? '-- Pilih Kategori --' : $option;
              echo "<option value=\"$option\" $selected>$display</option>";
            }
          ?>
        </select>
      </div>
    </div>

    <div class="row mb-3">
      <div class="col-md-6">
        <label>Hostname</label>
        <input type="text" name="hostname" class="form-control" value="<?php echo htmlspecialchars($row['hostname']); ?>">
      </div>
      <div class="col-md-6">
        <label>Type</label>
        <select name="type" class="form-control" required>
          <?php
          // Mengambil data tipe dari tabel device_types
          $current_type = $row['type'];
          $query_tipe = "SELECT type_name FROM device_types ORDER BY type_name ASC";
          $result_tipe = mysqli_query($koneksi, $query_tipe);

          echo '<option value="">-- Pilih Type --</option>';

          if ($result_tipe && mysqli_num_rows($result_tipe) > 0) {
            while ($tipe_row = mysqli_fetch_assoc($result_tipe)) {
              $tipe_val = htmlspecialchars($tipe_row['type_name']);
              // Perbandingan untuk menentukan opsi yang dipilih (case-insensitive)
              $selected = (strcasecmp($tipe_val, $current_type) == 0) ? 'selected' : '';
              echo "<option value=\"$tipe_val\" $selected>$tipe_val</option>";
            }
          } else {
            // Fallback: Jika DB kosong, tampilkan nilai yang tersimpan
            echo "<option value=\"$current_type\" selected>$current_type</option>";
          }
          ?>
        </select>
      </div>
    </div>

    <div class="row mb-3">
      <div class="col-md-4">
        <label>RAM</label>
        <input type="text" name="ram" class="form-control" value="<?php echo htmlspecialchars($row['ram']); ?>">
      </div>
      <div class="col-md-4">
        <label>Storage</label>
        <input type="text" name="storage" class="form-control" value="<?php echo htmlspecialchars($row['storage']); ?>">
      </div>
      <div class="col-md-4">
        <label>Windows</label>
        <input type="text" name="win" class="form-control" value="<?php echo htmlspecialchars($row['win']); ?>">
      </div>
    </div>

    <div class="mb-3">
      <label>Keterangan</label>
      <textarea name="keterangan" class="form-control"><?php echo htmlspecialchars($row['keterangan']); ?></textarea>
    </div>

    <div class="mb-3">
      <label>Kelengkapan</label>
      <select name="kelengkapan" id="kelengkapan" class="form-control">
        <?php
        $kelengkapan_options = [
          'Tas', 'Adaptor', 'Tas dan Adaptor', 'Tas dan Converter VGA',
          'Tas dan Converter LAN', 'Tas, Adaptor, Converter LAN',
          'Tas, Adaptor, Converter VGA', 'Tas, Adaptor, Converter LAN & VGA'
        ];
        $current_kel = $row['kelengkapan'];

        echo '<option value="">-- Pilih Kelengkapan --</option>';
        foreach ($kelengkapan_options as $option) {
          $selected = ($current_kel == $option) ? 'selected' : '';
          echo "<option value=\"$option\" $selected>$option</option>";
        }
        ?>
      </select>
    </div>

    <div class="row mb-3">
      <div class="col-md-6">
        <label>Tanggal Keluar</label>
        <input type="date" name="tanggal_keluar" class="form-control" value="<?php echo htmlspecialchars($row['tanggal_keluar']); ?>">
      </div>
      <div class="col-md-6">
        <label>NIK</label>
        <input type="text" name="nik" class="form-control" value="<?php echo htmlspecialchars($row['nik']); ?>">
      </div>
    </div>

    <div class="row mb-3">
      <div class="col-md-6">
        <label>Nama</label>
        <input type="text" name="nama" class="form-control" value="<?php echo htmlspecialchars($row['nama']); ?>">
      </div>
      <div class="col-md-6">
        <label>Divisi</label>
        <input type="text" name="divisi" class="form-control" value="<?php echo htmlspecialchars($row['divisi']); ?>">
      </div>
    </div>

    <button type="submit" name="update" class="btn btn-primary">Update</button>
    <a href="tampil.php" class="btn btn-secondary">Batal</a>
  </form>
</div>

<script>
  // --- Logika untuk memperbarui Rak berdasarkan Status yang dipilih ---
  $(document).ready(function() {
    $("#status").change(function(){
      var status = $(this).val();
      var rakField = $("#rak");

      // Logika pemetaan Status ke Rak
      if(status === "Spare"){ rakField.val("GD-R11"); }
      else if(status === "Grace Period"){ rakField.val("GD-R13"); }
      else if(status === "Pending Service"){ rakField.val("GD-R12"); }
      else if(status === "Scrap"){ rakField.val("GD-R4"); }
      else if(status === "MT"){ rakField.val("GD-R8"); }
      else if(status === "Ready To Assign"){ rakField.val("GD-R9"); }
      else if(status === "Assign"){ rakField.val("Assign"); }
      else if(status === "Loan"){ rakField.val("Loan"); }
      else { rakField.val(""); }
    }).trigger('change'); // Trigger change saat halaman dimuat untuk mengisi Rak awal
  });
</script>

</body>
</html>