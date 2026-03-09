<?php
// cetak-form.php
include 'koneksi.php';

// LOGIKA PENGAMBILAN DATA (MANUAL VS DATABASE)
if (isset($_GET['manual']) && $_GET['manual'] == 'true') {
    $d1_type = !empty($_GET['type1']) ? htmlspecialchars($_GET['type1']) : '&nbsp;';
    $d1_sn   = !empty($_GET['sn1']) ? htmlspecialchars($_GET['sn1']) : '&nbsp;';
    $d1_host = !empty($_GET['host1']) ? htmlspecialchars($_GET['host1']) : '&nbsp;';
    $d2_type = !empty($_GET['type2']) ? htmlspecialchars($_GET['type2']) : '&nbsp;';
    $d2_sn   = !empty($_GET['sn2']) ? htmlspecialchars($_GET['sn2']) : '&nbsp;';
    $d2_host = !empty($_GET['host2']) ? htmlspecialchars($_GET['host2']) : '&nbsp;';
    $d3_type = !empty($_GET['type3']) ? htmlspecialchars($_GET['type3']) : '&nbsp;';
    $d3_sn   = !empty($_GET['sn3']) ? htmlspecialchars($_GET['sn3']) : '&nbsp;';
    $d3_host = !empty($_GET['host3']) ? htmlspecialchars($_GET['host3']) : '&nbsp;';

    $employee_name = !empty($_GET['nama']) ? htmlspecialchars($_GET['nama']) : '';
    $employee_id   = !empty($_GET['nik']) ? htmlspecialchars($_GET['nik']) : '';
    $div_dept      = !empty($_GET['divisi']) ? htmlspecialchars($_GET['divisi']) : '';
    $request_date = !empty($_GET['tanggal']) ? date('d/m/Y', strtotime($_GET['tanggal'])) : "........ / ........ / ................";
    $kelengkapan   = '';
} else {
    $id = (int) $_GET['id'];
    $query = mysqli_query($koneksi, "SELECT * FROM inventori WHERE id = $id");
    $aset = mysqli_fetch_assoc($query);
    if (!$aset) {
        die("Data aset tidak ditemukan.");
    }
    $d1_type = htmlspecialchars($aset['type'] ?? '&nbsp;');
    $d1_sn   = htmlspecialchars($aset['serial_number'] ?? '&nbsp;');
    $d1_host = htmlspecialchars($aset['hostname'] ?? '&nbsp;');
    $d2_type = $d2_sn = $d2_host = '&nbsp;';
    $d3_type = $d3_sn = $d3_host = '&nbsp;';
    $employee_name = !empty($aset['nama']) ? htmlspecialchars($aset['nama']) : '';
    $employee_id   = !empty($aset['nik']) ? htmlspecialchars($aset['nik']) : '';
    $div_dept      = !empty($aset['divisi']) ? htmlspecialchars($aset['divisi']) : '';
    $request_date  = date('d/m/Y');
    $kelengkapan   = htmlspecialchars($aset['kelengkapan'] ?? '');
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>ACCEPTED FORM</title>
  <style>
    @page { size: A4; margin: 1cm; }
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 0;
      font-size: 9pt;
      background-color: #fff;
      color: #333;
    }
    .container {
        width: 100%;
        border: none;
        padding: 0;
    }
    .header h1 {
      font-size: 16pt;
      margin: 0 0 15px 0;
      font-family: 'Times New Roman', serif;
      font-weight: normal;
      color: #000;
    }

    /* Menggunakan warna abu-abu gelap (#888) agar terlihat lebih tipis/halus di layar */
    .section-title {
      font-size: 10pt;
      padding: 6px 10px;
      background-color: #212121;
      color: white;
      font-weight: normal;
      border: 0.5pt solid #888;
    }

    .emp-title {
      border-left: none !important;
      border-right: none !important;
      border-top: 0.5pt solid #888 !important;
      border-bottom: 0.5pt solid #888 !important;
    }

    table.info {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 15px;
      border-bottom: 0.5pt solid #888;
    }
    table.info td {
      padding: 5px 10px 10px 10px;
      border-bottom: 0.5pt solid #888;
      vertical-align: bottom;
      height: 32px;
      position: relative;
    }
    .label-top {
      position: absolute;
      top: 4px;
      left: 10px;
      font-size: 7pt;
      font-style: italic;
      color: #666;
    }
    .data-text {
      display: block;
      margin-top: 10px;
      font-size: 9pt;
    }
    table.info td:nth-child(2) { border-right: 0.5pt solid #888; }
    table.info tr:last-child td { border-bottom: none; }

    table.device-list {
      width: 100%;
      border-collapse: collapse;
    }
    table.device-list th, table.device-list td {
      padding: 12px 8px;
      border: 0.5pt solid #888;
      text-align: center;
      font-weight: normal;
    }

    .agreement {
      padding: 10px 0;
      line-height: 1.4;
      text-align: justify;
    }

    .checkbox-section {
      display: flex;
      border: 0.5pt solid #888;
    }
    .column {
      flex: 1;
      padding: 8px;
      border-right: 0.5pt solid #888;
    }
    .column:last-child { border-right: none; }
    .checkbox-item { margin-bottom: 3px; display: flex; align-items: center; }
    input[type="checkbox"] { margin-right: 8px; }

    .comment-box {
      border: 0.5pt solid #888;
      border-top: none;
      padding: 8px;
      font-size: 9pt;
      font-style: italic;
      height: 70px;
    }

    .signatures {
      display: flex;
      border: 0.5pt solid #888;
      border-top: none;
    }
    .signature-box {
      width: 50%;
      height: 120px;
      padding: 10px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }
    .signature-box:first-child { border-right: 0.5pt solid #888; }
    .sig-title { font-style: italic; }

    .sig-footer-container {
      display: flex;
      justify-content: space-between;
      align-items: flex-end;
      font-style: italic;
    }
    .date-fill { font-size: 8pt; color: #666; }

    @media print {
      * { -webkit-print-color-adjust: exact; }
      /* Menghaluskan garis saat diprint */
      .section-title, .emp-title, table.info, table.info td,
      table.device-list th, table.device-list td,
      .checkbox-section, .column, .comment-box, .signatures, .signature-box {
        border-color: #888 !important;
      }
    }
  </style>
</head>
<body>

<div class="container">
  <div class="header">
    <h1>ACCEPTED FORM</h1>
  </div>

  <div class="section-title emp-title">Employee's Information</div>
  <table class="info">
    <tr>
      <td style="width: 25%;">
        <span class="label-top">Request Number (filled by IT)</span>
        <span class="data-text">&nbsp;</span>
      </td>
      <td style="width: 25%;"></td>
      <td style="width: 25%;">
        <span class="label-top">Request Date</span>
        <span class="data-text"><?= $request_date ?></span>
      </td>
      <td style="width: 25%;"></td>
    </tr>
    <tr>
      <td>
        <span class="label-top">Employee Name</span>
        <span class="data-text"><?= $employee_name ?></span>
      </td>
      <td></td>
      <td>
        <span class="label-top">Employee ID</span>
        <span class="data-text"><?= $employee_id ?></span>
      </td>
      <td></td>
    </tr>
    <tr>
      <td>
        <span class="label-top">Div./Dept.</span>
        <span class="data-text"><?= $div_dept ?></span>
      </td>
      <td></td>
      <td>
        <span class="label-top">Cost Center</span>
        <span class="data-text">&nbsp;</span>
      </td>
      <td></td>
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
      <tr><td><?= $d1_type ?></td><td><?= $d1_sn?></td><td><?= $d1_host ?></td></tr>
      <tr><td><?= $d2_type ?></td><td><?= $d2_sn?></td><td><?= $d2_host ?></td></tr>
      <tr><td><?= $d3_type ?></td><td><?= $d3_sn?></td><td><?= $d3_host ?></td></tr>
    </tbody>
  </table>

  <div class="agreement">
    <p style="margin: 0;">I am the undersigned hereby acknowledge receipt from the company and undertake to reimburse the company for its replacement value (decided by IT Division) should the equipment or part of the equipment be lost inside or outside company premises and damaged due to reckless use.</p>
    <p style="margin-top: 5px;">The undersigned understands that the Notebook/PC remains the property of the company at all material times and the company reserves the right to have it returned anytime upon demand.</p>
  </div>

  <div class="checkbox-section">
    <div class="column">
      <h5 style="font-size: 9pt; margin: 0 0 6px 0; font-weight: normal;">Application Installed:</h5>
      <div class="checkbox-item"><input type="checkbox"> Office 365</div>
      <div class="checkbox-item"><input type="checkbox"> SAP GUI</div>
      <div class="checkbox-item"><input type="checkbox"> Adobe Reader</div>
      <div class="checkbox-item"><input type="checkbox"> Google Chrome</div>
      <div class="checkbox-item"><input type="checkbox"> Microsoft Teams</div>
      <div class="checkbox-item"><input type="checkbox"> Remote Tools</div>
      <div class="checkbox-item"><input type="checkbox"> Sophos Antivirus</div>
      <div class="checkbox-item"><input type="checkbox"> Zscaler</div>
      <div class="checkbox-item"><input type="checkbox"> 7zip</div>
    </div>
    <div class="column">
      <h5 style="font-size: 9pt; margin: 0 0 6px 0; font-weight: normal;">Peripherals:</h5>
      <div class="checkbox-item"><input type="checkbox" <?= (stripos($kelengkapan, 'ADAPTOR') !== false) ? 'checked' : '' ?>> Power adaptor</div>
      <div class="checkbox-item"><input type="checkbox" <?= (stripos($kelengkapan, 'LAN') !== false) ? 'checked' : '' ?>> LAN Adaptor</div>
      <div class="checkbox-item"><input type="checkbox" <?= (stripos($kelengkapan, 'VGA') !== false) ? 'checked' : '' ?>> VGA Adaptor</div>
      <div class="checkbox-item"><input type="checkbox" <?= (stripos($kelengkapan, 'TAS') !== false) ? 'checked' : '' ?>> Bag</div>
    </div>
  </div>
  <div class="comment-box">
    User Comment (if any):
  </div>

  <div class="section-title" style="margin-top: 12px;">Acceptance</div>
  <div class="signatures">
    <div class="signature-box">
      <div class="sig-title">Deliver by IT Technical Support</div>
      <div class="sig-footer-container">
        <div class="sig-text">Signature . . . . . . . . . . . . .</div>
        <div class="date-fill">Date: . . . / . . . / . . . . .</div>
      </div>
    </div>
    <div class="signature-box">
      <div class="sig-title">Received by User</div>
      <div class="sig-footer-container">
        <div class="sig-text">Signature . . . . . . . . . . . . .</div>
        <div class="date-fill">Date: . . . / . . . / . . . . .</div>
      </div>
    </div>
  </div>

</div>

<script>window.onload = function() { window.print(); }</script>
</body>
</html>