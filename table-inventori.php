<?php

// Function untuk menentukan badge status secara visual
function getStatusBadge($status)
{
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

// Fungsi untuk mengganti nilai kosong/nol dengan '-'
function displayValue($value)
{
    if (empty($value) || $value === '0' || strpos($value, '0000-00-00') !== false) {
        return '-';
    }
    return $value;
}

// Fungsi untuk memetakan teks warna ke class CSS
function getColorClass($warna)
{
    $warna = strtolower(trim($warna));
    switch ($warna) {
        case 'merah':  return 'bg-merah';
        case 'kuning': return 'bg-kuning';
        case 'hijau':  return 'bg-hijau'; // Menghasilkan hijau tua secara visual
        case 'biru':   return 'bg-biru';  // Menghasilkan biru tua secara visual
        case 'hitam':  return 'bg-hitam';
        case 'putih':  return 'bg-putih';
        default:       return '';
    }
}

if (isset($result) && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {

        // Sanitasi Data
        foreach ($row as $key => $value) {
            $row[$key] = htmlspecialchars($value ?? '');
        }

        // Pengolahan Tanggal
        $tgl_reg = displayValue($row['tanggal_register']);
        $tgl_reg_display = ($tgl_reg !== '-') ? date('d-m-Y', strtotime($tgl_reg)) : '-';

        $tgl_masuk_raw = displayValue($row['tanggal_masuk']);
        $tgl_keluar_raw = displayValue($row['tanggal_keluar']);

        // Logika Tanggal Gabungan (Masuk/Keluar Terbaru)
        $tgl_gabungan_html = '-';
        $ts = [];
        if ($tgl_masuk_raw !== '-') {
            $ts[] = strtotime($tgl_masuk_raw);
        }
        if ($tgl_keluar_raw !== '-') {
            $ts[] = strtotime($tgl_keluar_raw);
        }
        if (!empty($ts)) {
            $latest = max($ts);
            $label = (strtotime($tgl_masuk_raw) === $latest) ? "<span class='text-success fw-bold'>Masuk:</span>" : "<span class='text-danger fw-bold'>Keluar:</span>";
            $tgl_gabungan_html = $label . " " . date('d-m-Y', $latest);
        }

        // Indikator Warna
        $color_class = getColorClass($row['warna']);
        $dot = !empty($color_class) ? "<span class='color-indicator {$color_class}' title='Warna: " . ucwords($row['warna']) . "'></span>" : "";

        // Tampilan Baris
        echo "<tr>";
        echo "<td class='text-center'>{$dot}</td>"; // 0. Warna (Indikator)
        echo "<td>" . displayValue($row['domain']) . "</td>"; // 1. Domain
        echo "<td>" . getStatusBadge($row['status']) . "</td>"; // 2. Status

        // 3. Hostname (Warna Disisipkan di sini)
        echo "<td>
                <div class='d-flex align-items-center'>
                    <a href='#' class='edit-btn text-decoration-none fw-bold' data-bs-toggle='modal' data-bs-target='#editModal' data-id='{$row['id']}' data-hostname='{$row['hostname']}' data-warna='{$row['warna']}'>
                        {$row['hostname']}
                    </a>
                </div>
                <div class='small text-muted mt-1' style='font-size: 0.75rem;'>Tipe: {$row['type']}</div>
              </td>";

        echo "<td>RAM: {$row['ram']} | SSD: {$row['storage']}</td>"; // 4. Spek
        echo "<td>{$tgl_reg_display}</td>"; // 5. Tanggal Register (TIDAK DIHAPUS)
        echo "<td>{$tgl_gabungan_html}</td>"; // 6. In/Out
        echo "<td><div class='fw-bold'>{$row['nama']}</div><div class='small text-muted'>{$row['divisi']}</div></td>"; // 7. User
        echo "<td class='small'>{$row['keterangan']}</td>"; // 8. Keterangan
        echo "</tr>";
    }
} else {
    echo '<tr><td colspan="9" class="text-center text-muted py-4">Data tidak ditemukan.</td></tr>';
}
