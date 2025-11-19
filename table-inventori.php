<?php
// Function untuk menentukan badge status secara visual (TIDAK BERUBAH)
function getStatusBadge($status) {
  switch ($status) {
    case 'Assign':
      return '<span class="badge bg-primary rounded-pill">' . $status . '</span>';
    case 'Loan':
      return '<span class="badge bg-dark rounded-pill">' . $status . '</span>';
    case 'Spare':
    case 'Ready to Assign':
      return '<span class="badge bg-success rounded-pill">' . $status . '</span>';
    case 'Grace Period':
      return '<span class="badge bg-warning text-dark rounded-pill">' . $status . '</span>';
    case 'Pending Service':
    case 'MT':
      return '<span class="badge bg-info text-dark rounded-pill">' . $status . '</span>';
    case 'Scrap':
      return '<span class="badge bg-danger rounded-pill">' . $status . '</span>';
    default:
      return '<span class="badge bg-secondary rounded-pill">' . $status . '</span>';
  }
}

// FUNGSI BARU: Untuk mengganti nilai kosong/nol dengan '-'
function displayValue($value) {
  // Sertakan tanggal 0000-00-00 00:00:00 sebagai nilai kosong
  if (empty($value) || $value === '0' || strpos($value, '0000-00-00') !== false) {
    return '-';
  }
  return $value;
}

// Logika query inventori sudah ada di tampil.php, kita hanya perlu mengolah $result

if (isset($result) && mysqli_num_rows($result) > 0) {
  while ($row = mysqli_fetch_assoc($result)) {

    // Amankan semua data menggunakan htmlspecialchars
    foreach ($row as $key => $value) {
      $row[$key] = htmlspecialchars($value ?? '');
    }

    $warningBadge = '';
    $countdownBadge = '';

    // Logika Grace Period (TIDAK BERUBAH)
    if ($row['status'] === 'Grace Period' && !empty($row['tanggal_keluar']) && $row['tanggal_keluar'] !== '0000-00-00') {
      $tanggal_keluar = new DateTime($row['tanggal_keluar']);
      $batas_grace = clone $tanggal_keluar;
      $batas_grace->add(new DateInterval('P3M'));
      $hari_ini = new DateTime();

      $selisih = $hari_ini->diff($batas_grace);
      $sisa_hari = $selisih->days;

      if ($hari_ini < $batas_grace) {
        $countdownBadge = "<span class='badge bg-warning text-dark rounded-pill ms-2'><i class='bi bi-clock-history'></i> Sisa {$sisa_hari} hari</span>";
      } else {
        $warningBadge = "<span class='badge bg-danger rounded-pill ms-2'><i class='bi bi-exclamation-triangle-fill'></i> MELEBIHI BATAS</span>";
      }
    }

    $editClass = 'edit-btn';

    // --- PEMROSESAN TANGGAL TUNGGAL ---
    $tanggal_masuk_raw = displayValue($row['tanggal_masuk']);
    $tanggal_keluar_raw = displayValue($row['tanggal_keluar']);
    $tanggal_register_raw = displayValue($row['tanggal_register'] ?? ''); // MENGGUNAKAN NAMA LENGKAP

    $tanggal_masuk_display = ($tanggal_masuk_raw !== '-') ? date('d-m-Y', strtotime($tanggal_masuk_raw)) : '-';
    $tanggal_keluar_display = ($tanggal_keluar_raw !== '-') ? date('d-m-Y', strtotime($tanggal_keluar_raw)) : '-';
    $tanggal_register_display = ($tanggal_register_raw !== '-') ? date('d-m-Y', strtotime($tanggal_register_raw)) : '-';

    // --- LOGIKA TANGGAL GABUNGAN (Masuk/Keluar) ---
    $tgl_gabungan_html = '';

    if ($tanggal_masuk_display !== '-' && $tanggal_keluar_display === '-') {
      // Aset baru masuk/dikembalikan (tanggal masuk ada, keluar kosong)
      $tgl_gabungan_html = "<span class='fw-bold text-success'>Masuk:</span> " . $tanggal_masuk_display;
    } elseif ($tanggal_keluar_display !== '-') {
      // Aset sedang keluar (tanggal keluar ada)
      $tgl_gabungan_html = "<span class='fw-bold text-danger'>Keluar:</span> " . $tanggal_keluar_display;
    }


    // --- DATA LAIN (MENGGUNAKAN FUNGSI displayValue) ---
    $rak_display = displayValue($row['rak']);
    $hostname_display = displayValue($row['hostname']);
    $domain_display = displayValue($row['domain'] ?? '');
    $device_category_display = displayValue($row['device_category'] ?? '');
    $type_display = displayValue($row['type']);
    $ram_display = displayValue($row['ram']);
    $storage_display = displayValue($row['storage']);
    $win_display = displayValue($row['win']);
    $kelengkapan_display = displayValue($row['kelengkapan']);
    $keterangan_display = displayValue($row['keterangan']);
    $nik_display = displayValue($row['nik']);
    $nama_display = displayValue($row['nama']);
    $divisi_display = displayValue($row['divisi']);
    $pic_loan_name_display = displayValue($row['pic_loan_name_display'] ?? '');


    // --- Data Digabung ---
    $specs = "RAM: {$ram_display} / Storage: {$storage_display} / OS: {$win_display}";
    $details = "Kelengkapan: {$kelengkapan_display}<br>Ket: {$keterangan_display}";

    // Mulai cetak baris tabel
    echo "<tr>";

    echo "<td>{$domain_display}</td>"; // 1. Domain
    echo "<td>{$rak_display}</td>"; // 2. Rak

    // 3. Kolom Status
    echo "<td>";
    echo getStatusBadge($row['status']);
    if ($row['status'] === 'Loan' && $pic_loan_name_display !== '-') {
      echo "<div class='small text-muted mt-1'>";
      echo "PIC Loan: <strong>{$pic_loan_name_display}</strong>"; // Menampilkan Nama PIC
      echo "</div>";
    }
    echo "</td>";

    // 4. Hostname & Tipe (dengan modal link)
    echo "<td>
      <a href='#' class='$editClass text-decoration-none fw-bold' data-bs-toggle='modal' data-bs-target='#editModal'
        data-id='{$row['id']}'
        data-rak='{$row['rak']}'
        data-status='{$row['status']}'
        data-hostname='{$row['hostname']}'
        data-type='{$row['type']}'
        data-domain='{$row['domain']}'
        data-device_category='{$row['device_category']}'
        data-ram='{$row['ram']}'
        data-storage='{$row['storage']}'
        data-win='{$row['win']}'
        data-keterangan='{$row['keterangan']}'
        data-kelengkapan='{$row['kelengkapan']}'
        data-tanggal_masuk='{$row['tanggal_masuk']}'
        data-tanggal_keluar='{$row['tanggal_keluar']}'
        data-nik='{$row['nik']}'
        data-nama='{$row['nama']}'
        data-divisi='{$row['divisi']}'
        data-tanggal_register='{$row['tanggal_register']}'
      >
      <i class='bi bi-pc-display-horizontal me-1'></i> {$hostname_display}</a>
      {$warningBadge}
      {$countdownBadge}

      <div class='small text-muted mt-1'>
        Kategori: <strong>{$device_category_display}</strong> | Tipe: {$type_display}
      </div>
    </td>"; // 4. Penutup Hostname

    echo "<td>{$specs}</td>"; // 5. Spesifikasi

    // 6. KOLOM BARU: Tanggal Register
    echo "<td>{$tanggal_register_display}</td>";

    // 7. KOLOM GABUNGAN: Tanggal Masuk/Keluar
    echo "<td>{$tgl_gabungan_html}</td>";

    // 8. User & Divisi
    echo "<td>
      <div class='fw-bold'>{$nama_display} (NIK: {$nik_display})</div>
      <div class='small text-muted'>Divisi: {$divisi_display}</div>
    </td>";

    // 9. Kelengkapan & Keterangan
    echo "<td class='small'>{$details}</td>";

    echo "</tr>";
  }
} else {
  // Colspan tetap 9
  echo '<tr><td colspan=\'9\' class=\'text-center text-muted py-4\'>Tidak ada data inventori ditemukan berdasarkan filter yang diterapkan.</td></tr>';
}
?>