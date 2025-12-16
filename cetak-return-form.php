<?php
// cetak-return-form.php - IT HARDWARE RETURN FORM

include 'koneksi.php'; // Pastikan koneksi.php sudah tersedia

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
$devices_name = htmlspecialchars($aset['type'] ?? 'N/A');
$serial_number = 'Lihat Keterangan'; // Asumsi Serial Number ada di Keterangan
$asset_no = htmlspecialchars($aset['hostname'] ?? 'N/A');

// Data Karyawan
$employee_name = htmlspecialchars($aset['nama'] ?? '____________________');
$nik = htmlspecialchars($aset['nik'] ?? '____________________');
$div_dept = htmlspecialchars($aset['divisi'] ?? '____________________');

// Tanggal
$return_date = date('d/m/Y');
$employee_id = $nik;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>IT HARDWARE RETURN - ID: <?= htmlspecialchars($id) ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            font-size: 9pt;
        }
        .container {
            width: 95%;
            max-width: 700px; /* Ukuran maksimal aman untuk A4 */
            margin: 10px auto;
            border: 2px solid #000;
            padding: 15px;
            box-sizing: border-box;
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
            border: 1px solid #000000e8;
            text-align: center;
        }
        table.device-list th {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        .section-title {
            font-weight: bold;
            padding: 5px;
            border: 1px solid #000;
            background-color: #000000d6;
            color: white;
            padding-left: 5px;
            margin-top: 10px;
        }

        .checkbox-item {
            margin-bottom: 3px;
            display: inline-block;
            margin-right: 15px;
        }

        input[type="checkbox"] {
            accent-color: black;
            margin-right: 5px;
            border: 1px solid black !important;
            background-color: white !important;
            width: 12px;
            height: 12px;
            vertical-align: middle;
        }

        .signature-box {
            width: 33%;
            text-align: center;
            padding: 5px;
            border-right: 1px solid #000;
        }
        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 0;
            margin-bottom: 0;
            border: 1px solid #000;
            border-top: none;
        }
        .signatures .signature-box:last-child {
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
        .signature-box p {
            margin: 5px 0 0 0;
            color: #666;
            font-weight: normal;
        }
        .signature-box p:first-of-type {
            margin-top: 10px;
            font-weight: bold;
        }

        /* CSS Print Optimization */
        @media print {
            html, body {
                margin: 0 !important;
                padding: 0 !important;
                height: auto;
                overflow: hidden;
            }
            .container {
                border: none;
                padding: 5px !important;
                width: 100%;
                margin: 0;
            }
            body, p, td, th {
                font-size: 8.5pt;
                line-height: 1.2;
            }
            .signatures, .checkbox-section {
                page-break-inside: avoid !important;
            }
            .section-title {
                margin-top: 5px !important;
                margin-bottom: 0 !important;
            }
            * {
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }
        }
    </style>
</head>
<body>

<div class="container">

    <div class="header">
        <h1>IT HARDWARE RETURN</h1>
    </div>

    <div class="section-title">Employee's Information</div>
    <table class="info" style="border: none;">
        <tr>
            <td style="width: 20%; border: none; padding-left: 0;">Request Number</td>
            <td style="width: 40%; border: none;">: </td>
            <td style="width: 20%; border: none;">Return Date</td>
            <td style="width: 40%; border: none;">: <?= $return_date ?></td>
        </tr>
        <tr>
            <td style="border: none; padding-left: 0;">Employee Name</td>
            <td style="border: none;">: <?= $employee_name ?></td>
            <td style="border: none;">Employee ID</td>
            <td style="border: none;">: <?= $employee_id ?></td>
        </tr>
        <tr>
            <td style="border: none; padding-left: 0;">Div./Dept.</td>
            <td style="border: none;">: <?= $div_dept ?></td>
            <td style="border: none;">Cost Center</td>
            <td style="border: none;">: </td>
        </tr>
    </table>

    <div class="section-title">Device List</div>
    <table class="device-list">
        <thead>
            <tr>
                <th style="width: 5%;">No.</th>
                <th style="width: 40%;">Devices Name</th>
                <th style="width: 30%;">Serial Number</th>
                <th style="width: 25%;">Asset No</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td><?= $devices_name ?></td>
                <td><?= $serial_number ?></td>
                <td><?= $asset_no ?></td>
            </tr>
            <?php for ($i = 2; $i <= 5; $i++): ?>
            <tr>
                <td><?= $i ?></td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
            <?php endfor; ?>
        </tbody>
    </table>

    <div style="padding: 5px; border: 1px solid #000; border-top: none;">
        <p style="margin: 0 0 5px 0; font-weight: bold;">Return Reason:</p>
        <div class="checkbox-item"><input type="checkbox"> Mutation</div>
        <div class="checkbox-item"><input type="checkbox"> Resign</div>
        <div class="checkbox-item"><input type="checkbox"> Unit Replacement</div>
        <div class="checkbox-item"><input type="checkbox"> Other</div>
        <p style="margin: 10px 0 0 0; font-weight: bold;">Remarks:
            <pre>
            <pre>
        </p>
    </div>

    <div class="section-title" style="margin-top: 15px;">Filled by IT who received asset</div>
    <div style="padding: 5px; border: 1px solid #000; border-top: none;">
        <p style="margin: 0 0 5px 0; font-weight: bold;">Asset Condition (while Return):</p>
        <div class="checkbox-item"><input type="checkbox"> Good</div>
        <div class="checkbox-item"><input type="checkbox"> Fair</div>
        <div class="checkbox-item"><input type="checkbox"> Bad</div>

        <p style="margin: 10px 0 5px 0; font-weight: bold;">Peripherals:</p>
        <div class="checkbox-item"><input type="checkbox"> Power Adaptor</div>
        <div class="checkbox-item"><input type="checkbox"> LAN Adaptor</div>
        <div class="checkbox-item"><input type="checkbox"> VGA Adaptor</div>
        <div class="checkbox-item"><input type="checkbox"> Bag</div>

        <p style="margin: 10px 0 0 0; font-weight: bold;">Remarks:
            <pre>
            <pre>
        </p>
    </div>


    <div class="section-title" style="margin-top: 15px;">Returned by</div>
    <div class="signatures">
        <div class="signature-box">
            <div class="signature-title">Returned by (User)</div>
            <div class="signature-area"></div>
            <p style="margin-top: 10px;">Signature . . . . . . . . . . . . .</p>
            <p>(User)</p>
        </div>

        <div class="signature-box">
            <div class="signature-title">Approved by (Superior)</div>
            <div class="signature-area"></div>
            <p style="margin-top: 10px;">Signature . . . . . . . . . . . . .</p>
            <p>(Superior)</p>
        </div>

        <div class="signature-box">
            <div class="signature-title">Received by (IT)</div>
            <div class="signature-area"></div>
            <p style="margin-top: 10px;">Signature . . . . . . . . . . . . .</p>
            <p>(IT)</p>
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