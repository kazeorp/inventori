<?php
// cetak-return-form.php
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
    $return_date   = !empty($_GET['tanggal']) ? date('d/m/Y', strtotime($_GET['tanggal'])) : date('d/m/Y');
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
      margin-bottom: 10px;
    }
    table.device-list th, table.device-list td {
      padding: 12px 8px;
      border: 0.5pt solid #888;
      text-align: center;
      font-weight: normal;
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
      height: 60px;
    }

    .signatures {
      display: flex;
      border: 0.5pt solid #888;
      border-top: none;
    }
    .signature-box {
      width: 33.33%; /* Dibagi 3 untuk Return Form */
      height: 120px;
      padding: 10px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }
    .signature-box { border-right: 0.5pt solid #888; }
    .signature-box:last-child { border-right: none; }
    .sig-title { font-style: italic; font-weight: bold; font-size: 8pt; margin-bottom: 5px;}

    .sig-footer-container {
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      font-style: italic;
    }
    .sig-name { margin-top: 10px; font-weight: bold; text-decoration: underline; }
    .date-fill { font-size: 8pt; color: #666; margin-top: 5px; }

    @media print {
      * { -webkit-print-color-adjust: exact; }
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
        <th style="width: 10%;">No.</th>
        <th style="width: 35%;">Devices Name</th>
        <th style="width: 25%;">Serial Number</th>
        <th style="width: 30%;">Hostname / Asset No</th>
      </tr>
    </thead>
    <tbody>
      <tr><td>1</td><td><?= $d1_type ?></td><td><?= $d1_sn?></td><td><?= $d1_host ?></td></tr>
      <tr><td>2</td><td><?= $d2_type ?></td><td><?= $d2_sn?></td><td><?= $d2_host ?></td></tr>
      <tr><td>3</td><td><?= $d3_type ?></td><td><?= $d3_sn?></td><td><?= $d3_host ?></td></tr>
    </tbody>
  </table>

  <div class="section-title">Return Details & Condition</div>
  <div class="checkbox-section">
    <div class="column">
      <h5 style="font-size: 9pt; margin: 0 0 6px 0; font-weight: bold;">Return Reason:</h5>
      <div class="checkbox-item"><input type="checkbox"> Resigned</div>
      <div class="checkbox-item"><input type="checkbox"> Unit Replacement</div>
      <div class="checkbox-item"><input type="checkbox"> Mutation / Transfer</div>
      <div class="checkbox-item"><input type="checkbox"> Project Ended</div>
      <div class="checkbox-item"><input type="checkbox"> Other</div>
    </div>
    <div class="column">
      <h5 style="font-size: 9pt; margin: 0 0 6px 0; font-weight: bold;">Device Condition:</h5>
      <div class="checkbox-item"><input type="checkbox"> Good / Normal</div>
      <div class="checkbox-item"><input type="checkbox"> Minor Damage</div>
      <div class="checkbox-item"><input type="checkbox"> Major Damage / Broken</div>
    </div>
    <div class="column">
      <h5 style="font-size: 9pt; margin: 0 0 6px 0; font-weight: bold;">Peripherals Returned:</h5>
      <div class="checkbox-item"><input type="checkbox"> Power Adaptor</div>
      <div class="checkbox-item"><input type="checkbox"> Bag</div>
      <div class="checkbox-item"><input type="checkbox"> Mouse</div>
      <div class="checkbox-item"><input type="checkbox"> LAN / VGA Adaptor</div>
    </div>
  </div>
  <div class="comment-box">
    Remarks / IT Notes:
  </div>

  <div class="section-title" style="margin-top: 12px;">Validation</div>
  <div class="signatures">
    <div class="signature-box">
      <div class="sig-title">Returned by (User)</div>
      <div class="sig-footer-container">
        <div class="sig-name">( <?= $employee_name ?: '________________' ?> )</div>
        <div class="date-fill">Date: . . . / . . . / . . . . .</div>
      </div>
    </div>
    <div class="signature-box">
      <div class="sig-title">Approved by (Superior)</div>
      <div class="sig-footer-container">
        <div class="sig-name">( ________________ )</div>
        <div class="date-fill">Date: . . . / . . . / . . . . .</div>
      </div>
    </div>
    <div class="signature-box">
      <div class="sig-title">Received by (IT Technical Support)</div>
      <div class="sig-footer-container">
        <div class="sig-name">( ________________ )</div>
        <div class="date-fill">Date: . . . / . . . / . . . . .</div>
      </div>
    </div>
  </div>

</div>

<script>window.onload = function() { window.print(); }</script>
</body>
</html>