<?php
// cetak-return-form.php
include 'koneksi.php';

if (isset($_GET['manual']) && $_GET['manual'] == 'true') {
    $devices = [];
    for ($i = 1; $i <= 5; $i++) {
        $devices[$i] = [
            'type' => !empty($_GET["type$i"]) ? htmlspecialchars($_GET["type$i"]) : '&nbsp;',
            'sn'   => !empty($_GET["sn$i"]) ? htmlspecialchars($_GET["sn$i"]) : '&nbsp;',
            'host' => !empty($_GET["host$i"]) ? htmlspecialchars($_GET["host$i"]) : '&nbsp;',
        ];
    }
    $employee_name = !empty($_GET['nama']) ? htmlspecialchars($_GET['nama']) : '';
    $employee_id   = !empty($_GET['nik']) ? htmlspecialchars($_GET['nik']) : '';
    $div_dept      = !empty($_GET['divisi']) ? htmlspecialchars($_GET['divisi']) : '';
    $return_date = !empty($_GET['tanggal']) ? date('d/m/Y', strtotime($_GET['tanggal'])) : '. . . / . . . / . . . . .';
} else {
    $id = (int) $_GET['id'];
    $query = mysqli_query($koneksi, "SELECT * FROM inventori WHERE id = $id");
    $aset = mysqli_fetch_assoc($query);
    if (!$aset) {
        die("Data aset tidak ditemukan.");
    }

    $devices[1] = [
        'type' => htmlspecialchars($aset['type'] ?? '&nbsp;'),
        'sn'   => htmlspecialchars($aset['serial_number'] ?? '&nbsp;'),
        'host' => htmlspecialchars($aset['hostname'] ?? '&nbsp;'),
    ];
    for ($i = 2; $i <= 5; $i++) {
        $devices[$i] = ['type' => '&nbsp;', 'sn' => '&nbsp;', 'host' => '&nbsp;'];
    }

    $employee_name = !empty($aset['nama']) ? htmlspecialchars($aset['nama']) : '';
    $employee_id   = !empty($aset['nik']) ? htmlspecialchars($aset['nik']) : '';
    $div_dept      = !empty($aset['divisi']) ? htmlspecialchars($aset['divisi']) : '';
    $return_date   = date('d/m/Y');
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>IT HARDWARE RETURN FORM</title>
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
    .container { width: 100%; }
    .header h1 {
      font-size: 16pt;
      margin: 0 0 15px 0;
      font-family: 'Times New Roman', serif;
      font-weight: normal;
      color: #000;
    }

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
    .data-text { display: block; margin-top: 10px; font-size: 9pt; }
    table.info td:nth-child(2) { border-right: 0.5pt solid #888; }
    table.info tr:last-child td { border-bottom: none; }

    table.device-list {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 0;
    }
    table.device-list th, table.device-list td {
      padding: 10px 8px;
      border: 0.5pt solid #888;
      text-align: center;
      font-weight: normal;
    }

    .field-box {
      border: 0.5pt solid #888;
      border-top: none;
      padding: 18px 10px 10px 10px;
      position: relative;
    }

    .checkbox-spread {
      display: flex;
      justify-content: space-between;
      align-items: center;
      width: 100%;
      padding: 5px 0;
    }

    .checkbox-item { display: flex; align-items: center; font-size: 9pt; }
    input[type="checkbox"] { margin-right: 8px; }

    .comment-area { min-height: 45px; }

.signatures {
      display: flex;
      border: 0.5pt solid #888;
      border-top: none;
    }

    .signature-box {
      width: 50%;
      height: 140px; /* Tinggi kotak diperbesar agar ruang TTD luas */
      position: relative; /* Menjadi acuan untuk posisi absolute di dalamnya */
    }

    .signature-box:first-child { border-right: 0.5pt solid #888; }

    /* Label "Received by IT" / "Returned by User" di pojok kiri atas */
    .label-sig-top {
      position: absolute;
      top: 5px;
      left: 10px;
      font-size: 7.5pt;
      font-style: italic;
      color: #666;
    }

    /* Container untuk menaruh Signature di kiri bawah dan Date di kanan bawah */
    .sig-footer-container {
      position: absolute;
      bottom: 10px; /* Jarak dari dasar kotak */
      left: 10px;
      right: 10px;
      display: flex;
      justify-content: space-between; /* Signature di kiri, Date di kanan */
      align-items: flex-end;
    }

    .sig-text {
      font-size: 9pt;
      font-style: italic;
    }

    .date-fill {
      font-size: 8pt;
      color: #666;
      font-style: italic;
    }

    @media print {
      * { -webkit-print-color-adjust: exact; }
    }
  </style>
</head>
<body>

<div class="container">
  <div class="header">
    <h1>IT HARDWARE RETURN FORM</h1>
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
        <span class="label-top">Return Date</span>
        <span class="data-text"><?= $return_date ?></span>
      </td>
      <td style="width: 25%;"></td>
    </tr>
    <tr>
      <td><span class="label-top">Employee Name</span><span class="data-text"><?= $employee_name ?></span></td>
      <td></td>
      <td><span class="label-top">Employee ID</span><span class="data-text"><?= $employee_id ?></span></td>
      <td></td>
    </tr>
    <tr>
      <td><span class="label-top">Div./Dept.</span><span class="data-text"><?= $div_dept ?></span></td>
      <td></td>
      <td><span class="label-top">Cost Center</span><span class="data-text">&nbsp;</span></td>
      <td></td>
    </tr>
  </table>

  <div class="section-title">Device List</div>
  <table class="device-list">
    <thead>
      <tr>
        <th style="width: 8%;">No.</th>
        <th style="width: 35%;">Devices Name</th>
        <th style="width: 27%;">Serial Number</th>
        <th style="width: 30%;">Hostname / Asset No</th>
      </tr>
    </thead>
    <tbody>
      <?php for ($i = 1; $i <= 5; $i++): ?>
      <tr>
        <td><?= $i ?></td>
        <td><?= $devices[$i]['type'] ?></td>
        <td><?= $devices[$i]['sn'] ?></td>
        <td><?= $devices[$i]['host'] ?></td>
      </tr>
      <?php endfor; ?>
    </tbody>
  </table>

  <div class="field-box">
    <span class="label-top">Return Reason</span>
    <div class="checkbox-spread">
      <div class="checkbox-item"><input type="checkbox"> Mutation</div>
      <div class="checkbox-item"><input type="checkbox"> Resign</div>
      <div class="checkbox-item"><input type="checkbox"> Unit Replacement</div>
      <div class="checkbox-item"><input type="checkbox"> Other</div>
    </div>
  </div>

  <div class="field-box comment-area">
    <span class="label-top">Remarks</span>
  </div>

  <div class="section-title">Filled by IT who received asset</div>

  <div class="field-box">
    <span class="label-top">Asset Condition (while return)</span>
    <div class="checkbox-spread">
      <div class="checkbox-item"><input type="checkbox"> Good</div>
      <div class="checkbox-item"><input type="checkbox"> Fair</div>
      <div class="checkbox-item"><input type="checkbox"> Bad</div>
    </div>
  </div>

  <div class="field-box">
    <span class="label-top">Peripherals</span>
    <div class="checkbox-spread">
      <div class="checkbox-item"><input type="checkbox"> Power Adaptor</div>
      <div class="checkbox-item"><input type="checkbox"> LAN Adaptor</div>
      <div class="checkbox-item"><input type="checkbox"> VGA Adaptor</div>
      <div class="checkbox-item"><input type="checkbox"> Bag</div>
    </div>
  </div>

  <div class="field-box comment-area" style="margin-bottom: 15px;">
    <span class="label-top">Remarks</span>
  </div>

<div class="section-title">Returned by</div>
  <div class="signatures">
    <div class="signature-box">
      <span class="label-sig-top">Received by IT</span>
      <div class="sig-footer-container">
        <div class="sig-text">Signature . . . . . . . . . . . . .</div>
        <div class="date-fill">Date: . . . / . . . / . . . . .</div>
      </div>
    </div>

    <div class="signature-box">
      <span class="label-sig-top">Returned by User</span>
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