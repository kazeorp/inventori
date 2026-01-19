<?php
// cetak-return-form.php - Versi Pas 1 Halaman & Tanpa Border Luar

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

    $employee_name = htmlspecialchars($aset['nama'] ?? '');
    $employee_id   = htmlspecialchars($aset['nik'] ?? '');
    $div_dept      = htmlspecialchars($aset['divisi'] ?? '');
    $return_date   = date('d/m/Y');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>IT HARDWARE RETURN FORM</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            font-size: 10pt; /* Ukuran font sedikit diperbesar agar pas halaman */
        }
        .container {
            width: 95%;
            max-width: 700px;
            margin: 0 auto;
            /* Border luar dihapus sesuai request */
        }

        .header h1 {
            font-size: 18pt;
            margin: 0;
            font-family: 'Times New Roman', serif;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table.info td {
            padding: 8px 5px; /* Padding diperbesar agar lebih lega */
            border: none;
        }
        table.device-list th, table.device-list td {
            padding: 12px 5px; /* Baris tabel lebih tinggi */
            border: 1px solid #000;
            text-align: center;
        }
        table.device-list th {
            background-color: #f0f0f0;
        }

        .section-title {
            padding: 8px;
            border: 1px solid #000000eb;
            background-color: #333;
            color: white;
            margin-top: 20px;
        }

        .checkbox-group {
            padding: 15px;
            border: 1px solid #000;
            border-top: none;
        }
        .checkbox-item {
            margin-bottom: 8px;
            display: inline-block;
            margin-right: 25px;
        }

        input[type="checkbox"] {
            transform: scale(1.2);
            margin-right: 8px;
            vertical-align: middle;
        }

        .signatures {
            display: flex;
            justify-content: space-between;
            border: 1px solid #000;
            border-top: none;
        }
        .signature-box {
            width: 33.33%;
            text-align: center;
            border-right: 1px solid #000;
            display: flex;
            flex-direction: column;
        }
        .signature-box:last-child {
            border-right: none;
        }
        .signature-title {
            background-color: #e0e0e0;
            font-weight: bold;
            padding: 8px;
            border-bottom: 1px solid #000;
        }
        .signature-area {
            height: 80px; /* Area tanda tangan lebih tinggi */
        }
        .signature-box p {
            margin: 5px 0;
            font-size: 9pt;
        }

        @media print {
            @page {
                size: A4;
                margin: 1cm;
            }
            body { padding: 0; }
            * { -webkit-print-color-adjust: exact; color-adjust: exact; }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>IT HARDWARE RETURN FORM</h1>
    </div>

    <div class="section-title">Employee's Information</div>
    <table class="info">
        <tr>
            <td style="width: 20%;">Request Number</td>
            <td style="width: 30%;">: </td>
            <td style="width: 20%;">Return Date</td>
            <td style="width: 30%;">: <?= $return_date ?></td>
        </tr>
        <tr>
            <td>Employee Name</td>
            <td>: <?= $employee_name ?></td>
            <td>Employee ID</td>
            <td>: <?= $employee_id ?></td>
        </tr>
        <tr>
            <td>Div./Dept.</td>
            <td>: <?= $div_dept ?></td>
            <td>Cost Center</td>
            <td>: </td>
        </tr>
    </table>

    <div class="section-title">Device List</div>
    <table class="device-list">
        <thead>
            <tr>
                <th style="width: 8%;">No.</th>
                <th style="width: 42%;">Devices Name</th>
                <th style="width: 25%;">Serial Number</th>
                <th style="width: 25%;">Asset No</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td><?= $d1_type ?></td>
                <td><?= $d1_sn ?></td>
                <td><?= $d1_host ?></td>
            </tr>
            <tr>
                <td>2</td>
                <td><?= $d2_type ?></td>
                <td><?= $d2_sn ?></td>
                <td><?= $d2_host ?></td>
            </tr>
            <tr>
                <td>3</td>
                <td><?= $d3_type ?></td>
                <td><?= $d3_sn ?></td>
                <td><?= $d3_host ?></td>
            </tr>
        </tbody>
    </table>

    <div class="section-title">Return Reason & Remarks</div>
    <div class="checkbox-group">
        <div class="checkbox-item"><input type="checkbox"> Mutation</div>
        <div class="checkbox-item"><input type="checkbox"> Resign</div>
        <div class="checkbox-item"><input type="checkbox"> Unit Replacement</div>
        <div class="checkbox-item"><input type="checkbox"> Other</div>
        <div style="margin-top: 15px; border-top: 1px dashed #ccc; padding-top: 10px; min-height: 60px;">
            <strong>Remarks:</strong>
        </div>
    </div>

    <div class="section-title">IT Receipt Confirmation</div>
    <div class="checkbox-group">
        <strong>Condition:</strong> &nbsp;
        <div class="checkbox-item"><input type="checkbox"> Good</div>
        <div class="checkbox-item"><input type="checkbox"> Fair</div>
        <div class="checkbox-item"><input type="checkbox"> Bad</div>

        <div style="margin-top: 10px;">
            <strong>Peripherals:</strong> &nbsp;
            <div class="checkbox-item"><input type="checkbox"> Power Adaptor</div>
            <div class="checkbox-item"><input type="checkbox"> Bag</div>
            <div class="checkbox-item"><input type="checkbox"> Mouse</div>
        </div>
        <div style="margin-top: 15px; border-top: 1px dashed #ccc; padding-top: 10px; min-height: 60px;">
            <strong>IT Remarks:</strong>
        </div>
    </div>

    <div class="section-title">Validation</div>
    <div class="signatures">
        <div class="signature-box">
            <div class="signature-title">Returned by (User)</div>
            <div class="signature-area"></div>
            <p><strong>( <?= $employee_name ?: '________________' ?> )</strong></p>
            <p>Date: ____/____/_____</p>
        </div>
        <div class="signature-box">
            <div class="signature-title">Approved by (Superior)</div>
            <div class="signature-area"></div>
            <p><strong>( ________________ )</strong></p>
            <p>Date: ____/____/_____</p>
        </div>
        <div class="signature-box">
            <div class="signature-title">Received by (IT)</div>
            <div class="signature-area"></div>
            <p><strong>( ________________ )</strong></p>
            <p>Date: ____/____/_____</p>
        </div>
    </div>
</div>

<script>
    window.onload = function() { window.print(); }
</script>
</body>
</html>