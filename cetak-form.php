<?php
// ... (Bagian PHP data aset tetap sama)
// cetak-form.php - Revisi Final dengan format ACCEPTED FORM

include 'koneksi.php'; 

if (!isset($_GET['id']) || empty($_GET['id'])) {
  die("ID Aset tidak ditemukan.");
}

$id = (int) $_GET['id'];
$query = mysqli_query($koneksi, "SELECT * FROM inventori WHERE id = $id");
$aset = mysqli_fetch_assoc($query);

if (!$aset) {
  die("Data aset tidak ditemukan.");
}

// Data Aset
$hostname = htmlspecialchars($aset['hostname'] ?? 'N/A');
$type = htmlspecialchars($aset['type'] ?? 'N/A'); 
$ram = htmlspecialchars($aset['ram'] ?? 'N/A');
$storage = htmlspecialchars($aset['storage'] ?? 'N/A');
$keterangan = htmlspecialchars($aset['keterangan'] ?? '');
// Jika serial number tidak ada kolom, kita asumsikan ia berada di keterangan atau tidak terisi
$serial_number = empty($keterangan) ? 'N/A' : $keterangan; 
$kelengkapan = htmlspecialchars($aset['kelengkapan'] ?? ''); 

// Data Karyawan
$employee_name = htmlspecialchars($aset['nama'] ?? '____________________');
$nik = htmlspecialchars($aset['nik'] ?? '____________________');
$div_dept = htmlspecialchars($aset['divisi'] ?? '____________________');

// Tanggal
$request_date = date('d/m/Y'); 
$employee_id = $nik; 
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>ACCEPTED FORM</title>
  <style>
    body { 
      font-family: Arial, sans-serif; 
      margin: 20px; 
      font-size: 9pt; 
    }
    .container { 
      width: 750px; 
      margin: 0 auto; 
      border: 2px solid #000; 
      padding: 15px; 
    }
    .header h1 { 
      font-size: 16pt; 
      margin: 0; 
      font-family: 'Times New Roman', serif;
    }
    
    table { 
      width: 100%; 
      border-collapse: collapse; 
      margin-bottom: 10px; 
    }
    table.info td { 
      padding: 3px 5px; 
      border: 1px solid #000; 
    }
    table.device-list th, table.device-list td { 
      padding: 5px; 
      border: 1px solid #000; 
      text-align: center; 
    }
    table.device-list th { 
      background-color: #f0f0f0; 
      font-weight: bold; 
    }
    
    .section-title { 
      font-size: 10pt;
      padding: 5px; 
      border: 1px solid #000; 
      background-color: #222222; 
      color: white; 
      padding-left: 5px; 
      margin-top: 10px; 
    }
        
        /* Modifikasi untuk menempelkan Acceptance dengan Signature Box */
        #acceptance-title {
            margin-bottom: 0 !important;
            margin-top: 15px;
        }

    .checkbox-section { 
      display: flex; 
      border: 1px solid #000; 
      border-top: none; 
      margin-bottom: 0;
    }
    .column { 
      flex: 1; 
      padding: 8px 10px; 
      border-right: 1px solid #000; 
    }
    .column:last-child { 
      border-right: none; 
    }
    .checkbox-item { 
      margin-bottom: 3px; 
    }
    
    input[type="checkbox"] {
      margin-right: 5px; 
      border: 1px solid black !important; /* Jamin border hitam */
      background-color: white !important; /* Jamin latar putih */
      width: 12px; 
      height: 12px;
      vertical-align: middle;
    }

    .agreement { 
      margin-top: 15px; 
      line-height: 1.4; 
    }
    
    .signatures { 
      display: flex; 
      justify-content: space-between; 
      margin-top: 0; /* Menempel ke section-title */
      margin-bottom: 0;
      border: 1px solid #000;
      border-top: none;
    }
    .signature-box { 
      width: 33%; 
      text-align: center; 
      padding: 5px; 
      /* border: 1px solid #000; <- Dihapus karena sudah ada border di .signatures */
             border-right: 1px solid #000;
    }
        .signature-box:last-child {
            border-right: none;
        }
    .signature-title { 
      background-color: #e0e0e0; 
      font-weight: bold; 
      padding: 5px; 
      border-bottom: 1px solid #000; 
    }
    .signature-area { 
      height: 40px; 
    }
        
        /* <-- PERBAIKAN: Memudarkan Teks Signature --> */
        .signature-box p {
            margin: 5px 0 0 0;
            color: #666; /* Warna abu-abu yang lebih lembut */
            font-weight: normal;
        }
        .signature-box p:first-of-type {
            margin-top: 10px; 
            font-weight: bold; /* Teks "Signature . . ." tetap tebal */
        }
        
    .filled-data { 
      font-weight: bold; 
    }

    @media print {
            html, body {
                height: 100%;
                overflow: hidden;
            }
            .container {
                border: none;
                padding: 0; 
                width: 100%;
                margin: 0;
            }
            .signatures, .agreement, .checkbox-section {
                page-break-inside: avoid;
            }
            
            input[type="checkbox"]:checked {
                /* Hanya centang yang tercentang yang perlu kita fokuskan */
                /* Coba atur centang menjadi grayscale atau filter hitam */
                filter: invert(100%) grayscale(100%); 
            }
            
            /* (Opsional, jika filter di atas tidak bekerja) */
            * {
                -webkit-print-color-adjust: exact; /* Penting untuk konsistensi warna */
                color-adjust: exact;
            }
        }
  </style>
</head>
<body>

<div class="container">
      <div class="header">
    <h1>ACCEPTED FORM</h1>
  </div>

    <div class="section-title">Employee's Information</div>
  <table class="info" style="border: none;">
    <tr>
      <td style="width: 30%; border: none; padding-left: 0;">Request Number</td>
      <td style="width: 20%; border: none;"></td>
      <td style="width: 30%; border: none;">Request Date</td>
      <td style="width: 20%; border: none;" class="filled-data"><?= $request_date ?></td>
    </tr>
    <tr>
      <td style="border: none; padding-left: 0;">Employee Name</td>
      <td style="border: none;" class="filled-data"><?= $employee_name ?></td>
      <td style="border: none;">Employee ID</td>
      <td style="border: none;" class="filled-data"><?= $employee_id ?></td>
    </tr>
    <tr>
      <td style="border: none; padding-left: 0;">Div./Dept.</td>
      <td style="border: none;" class="filled-data"><?= $div_dept ?></td>
      <td style="border: none;">Cost Center</td>
      <td style="border: none;"></td>
    </tr>
  </table>

    <div class="section-title">Device List</div>
  <table class="device-list">
    <thead>
      <tr>
        <th style="width: 35%;">Devices Name</th>
        <th style="width: 30%;">Serial Number</th>
        <th style="width: 35%;">Hostname</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td><?= $type ?></td>
        <td><?= $serial_number ?></td>
        <td><?= $hostname ?></td>
      </tr>
      <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
      </tr>
      <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
      </tr>
    </tbody>
  </table>

    <div class="agreement">
    <p style="margin: 0;">I am the undersigned hereby acknowledge receipt from the company and undertake to reimburse the company for its replacement value (decided by IT Division) should the equipment or part of the equipment be lost inside or outside company premises and damaged due to reckless use.</p>
    <p style="margin-top: 5px;">The undersigned understands that the Notebook/PC remains the property of the company at all material times and the company reserves the right to have it returned anytime upon demand.</p>
  </div>
  
    <div class="section-title" style="margin-top: 15px;">Configuration Checklist</div>
  <div class="checkbox-section">
    <div class="column">
      <h5 style="font-size: 10pt; margin-top: 0; margin-bottom: 5px;">Application Installed:</h5>
      <div class="checkbox-item"><input type="checkbox"> Office 365</div>
      <div class="checkbox-item"><input type="checkbox"> SAP GUI</div>
      <div class="checkbox-item"><input type="checkbox"> Adobe Reader</div>
      <div class="checkbox-item"><input type="checkbox"> Google Chrome</div>
      <div class="checkbox-item"><input type="checkbox"> Microsoft Teams</div>
      <div class="checkbox-item"><input type="checkbox"> Sophos Antivirus</div>
      <div class="checkbox-item"><input type="checkbox"> Zscaler</div>
      <div class="checkbox-item"><input type="checkbox"> 7zip</div>
      <div class="checkbox-item">Windows OS: <?= htmlspecialchars($aset['win'] ?? 'N/A') ?></div>
    </div>
    
    <div class="column">
      <h5 style="font-size: 10pt; margin-top: 0; margin-bottom: 5px;">Peripherals:</h5>
      <div class="checkbox-item"><input type="checkbox" <?= (stripos($kelengkapan, 'ADAPTOR') !== false) ? 'checked' : '' ?>> Power adaptor</div>
      <div class="checkbox-item"><input type="checkbox" <?= (stripos($kelengkapan, 'LAN') !== false) ? 'checked' : '' ?>> LAN Adaptor</div>
      <div class="checkbox-item"><input type="checkbox" <?= (stripos($kelengkapan, 'VGA') !== false) ? 'checked' : '' ?>> VGA Adaptor</div>
      <div class="checkbox-item"><input type="checkbox" <?= (stripos($kelengkapan, 'TAS') !== false) ? 'checked' : '' ?>> Bag</div>
    </div>
  </div>
  <div style="border: 1px solid #000; border-top: none; padding: 5px; font-size: 10pt;">
    User Comment (if any): 
        <pre>
        <pre>
        <pre>
  </div>


      <div class="section-title" id="acceptance-title">Acceptance</div>
  <div class="signatures">
    <div class="signature-box">
      <div class="signature-title">Deliver by IT Technical Support</div>
      <div class="signature-area">
        <pre>
      </div>
      <p style="margin-top: 10px;">Signature . . . . . . . . . . . . .</p>
      <p>(IT Administrator)</p>
    </div>
    
    <div class="signature-box">
      <div class="signature-title">Received by User</div>
      <div class="signature-area">
        <pre>
      </div>
      <p style="margin-top: 10px;">Signature . . . . . . . . . . . . .</p>
      <p>(<?= $employee_name ?>)</p>
    </div>
    
    <div class="signature-box">
      <div class="signature-title">Approver (Direct Superior)</div>
      <div class="signature-area">
        <pre>
      </div>
      <p style="margin-top: 10px;">Signature . . . . . . . . . . . . .</p>
      <p>(Superior)</p>
    </div>
  </div>

</div>

<script>
  // Memicu dialog cetak secara otomatis
  window.onload = function() {
    window.print();
  }
</script>

</body>
</html>