<?php
include 'session.php';
include 'koneksi.php';

if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'superadmin')) {
    header("Location: index.php");
    exit;
}

$query_types = mysqli_query($koneksi, "SELECT * FROM device_types ORDER BY type_name ASC");
$device_types = [];
while ($row = mysqli_fetch_assoc($query_types)) {
    $device_types[] = $row;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Form Manual</title>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        .main-content { margin-left: 240px; margin-top: 100px; padding: 30px; }
        .card { border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); border: none; }
        .form-label { font-weight: bold; color: #555; font-size: 0.9rem; }
        hr { border-top: 2px solid #eee; margin: 25px 0; }
        .section-header { color: #0d6efd; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        .asset-row { background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 15px; border-left: 4px solid #0d6efd; position: relative; }
        .btn-remove { position: absolute; top: 10px; right: 10px; color: #dc3545; cursor: pointer; }
        .uppercase-input { text-transform: uppercase; }
        input::-webkit-calendar-picker-indicator { display: none !important; }
        .spinner-search { width: 1rem; height: 1rem; display: none; }

        /* Style untuk Toast Container */
        .toast-container { position: fixed; top: 20px; right: 20px; z-index: 9999; }
    </style>
</head>
<body class="bg-light">
<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>

<div class="toast-container" id="toastPlacement">
    <div id="liveToast" class="toast align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body" id="toastMessage">
                </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<main class="main-content">
    <div class="container-fluid">
        <h3 class="mb-4"><i class="bi bi-printer-fill"></i> Cetak Form Manual</h3>

        <div class="card">
            <div class="card-body p-4">
                <form action="" method="GET" id="mainForm" target="_blank">
                    <input type="hidden" name="manual" value="true">

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label text-primary">Jenis Formulir</label>
                            <select name="jenis_form" id="jenis_form" class="form-select form-select-lg border-primary" required onchange="validateRowLimit()">
                                <option value="accepted">ACCEPTED FORM (Maks. 3 Asset)</option>
                                <option value="return">IT HARDWARE RETURN (Maks. 5 Asset)</option>
                            </select>
                        </div>
                    </div>

                    <div class="section-header justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-laptop fs-4"></i>
                            <h5 class="mb-0">Informasi Asset</h5>
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="addAssetRow()">
                            <i class="bi bi-plus-circle"></i> Tambah Asset
                        </button>
                    </div>

                    <datalist id="list_asset_types">
                        <?php foreach ($device_types as $type): ?>
                            <option value="<?= htmlspecialchars($type['type_name']) ?>">
                        <?php endforeach; ?>
                    </datalist>

                    <div id="asset-container"></div>

                    <hr>

                    <div class="section-header">
                        <i class="bi bi-person-circle fs-4"></i>
                        <h5 class="mb-0">Informasi Karyawan</h5>
                    </div>

                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">NIK / Employee ID</label>
                            <input type="text" name="nik" id="nik_user" class="form-control" placeholder="NIK">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" name="nama" id="nama_user" class="form-control uppercase-input" oninput="makeUppercase(this)" placeholder="NAMA">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Divisi / Departemen</label>
                            <input type="text" name="divisi" id="divisi_user" class="form-control uppercase-input" oninput="makeUppercase(this)" placeholder="DIVISI">
                        </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Tanggal Form</label>
                                <div class="input-group">
                                    <input type="date" name="tanggal" id="tgl_form" class="form-control">
                                    <button class="btn btn-outline-primary" type="button" title="Set Hari Ini" onclick="setToday()">
                                        <i class="bi bi-calendar-check"></i>
                                    </button>
                                    <button class="btn btn-outline-danger" type="button" title="Kosongkan" onclick="clearDate()">
                                        <i class="bi bi-x-circle"></i>
                                    </button>
                                </div>
                                <small class="text-muted" style="font-size: 0.75rem;">Kosong untuk format ..../..../.......</small>
                            </div>
                    </div>

                    <div class="mt-4 pt-3 border-top d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-danger" onclick="clearForm()">
                            <i class="bi bi-trash"></i> Clear Form
                        </button>
                        <button type="submit" class="btn btn-primary btn-lg px-5 shadow-sm" onclick="setFormAction()">
                            <i class="bi bi-file-earmark-pdf"></i> Generate & Cetak Form
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<script>
    let rowCount = 0;

        // Fungsi untuk set tanggal ke hari ini
    function setToday() {
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('tgl_form').value = today;
    }

    // Fungsi untuk mengosongkan tanggal
    function clearDate() {
        document.getElementById('tgl_form').value = '';
    }

    // Fungsi menampilkan Toast
    function showToast(message, type = 'success') {
        const toastEl = document.getElementById('liveToast');
        const toastMsg = document.getElementById('toastMessage');

        toastEl.classList.remove('bg-success', 'bg-danger', 'bg-warning');

        if (type === 'success') toastEl.classList.add('bg-success');
        else if (type === 'error') toastEl.classList.add('bg-danger');
        else toastEl.classList.add('bg-warning');

        toastMsg.innerText = message;

        const toast = new bootstrap.Toast(toastEl, { delay: 5000 });
        toast.show();
    }

    function makeUppercase(input) {
        input.value = input.value.toUpperCase();
    }

    function addAssetRow() {
        const jenisForm = document.getElementById('jenis_form').value;
        const maxRows = (jenisForm === 'accepted') ? 3 : 5;

        if (rowCount >= maxRows) {
            showToast(`Maksimal ${maxRows} asset untuk formulir ini.`, 'warning');
            return;
        }

        rowCount++;
        const container = document.getElementById('asset-container');
        const rowDiv = document.createElement('div');
        rowDiv.className = 'asset-row';
        rowDiv.id = `row-${rowCount}`;
        rowDiv.innerHTML = `
            ${rowCount > 1 ? `<i class="bi bi-x-circle-fill btn-remove" onclick="removeAssetRow(${rowCount})"></i>` : ''}
            <span class="device-number fw-bold text-primary mb-2 d-block">Asset #${rowCount}</span>
            <div class="row">
                <div class="col-md-4 mb-2">
                    <label class="small text-muted">Hostname / Asset No</label>
                    <div class="input-group input-group-sm">
                        <input type="text" name="host${rowCount}" id="host${rowCount}" class="form-control uppercase-input" oninput="makeUppercase(this)" placeholder="CARI HOSTNAME...">
                        <button class="btn btn-primary" type="button" onclick="searchAsset(${rowCount})">
                            <i class="bi bi-search" id="icon-${rowCount}"></i>
                            <span class="spinner-border spinner-border-sm spinner-search d-none" id="load-${rowCount}"></span>
                        </button>
                    </div>
                </div>
                <div class="col-md-4 mb-2">
                    <label class="small text-muted">Nama Asset / Type</label>
                    <input type="text" name="type${rowCount}" id="type${rowCount}" class="form-control form-control-sm uppercase-input" list="list_asset_types" oninput="makeUppercase(this)" placeholder="TYPE">
                </div>
                <div class="col-md-4 mb-2">
                    <label class="small text-muted">Serial Number</label>
                    <input type="text" name="sn${rowCount}" id="sn${rowCount}" class="form-control form-control-sm uppercase-input" oninput="makeUppercase(this)" placeholder="S/N">
                </div>
            </div>
        `;
        container.appendChild(rowDiv);
    }

    async function searchAsset(index) {
        const hostname = document.getElementById(`host${index}`).value;
        if (!hostname) {
            showToast("Masukkan Hostname terlebih dahulu!", "warning");
            return;
        }

        document.getElementById(`icon-${index}`).style.display = 'none';
        document.getElementById(`load-${index}`).style.display = 'inline-block';

        try {
            const response = await fetch(`get-asset-info.php?hostname=${hostname}`);
            const data = await response.json();

            if (data.status === 'success') {
                document.getElementById(`type${index}`).value = data.type;
                document.getElementById(`sn${index}`).value = data.sn;

                // Selalu perbarui data karyawan
                document.getElementById('nik_user').value = data.nik;
                document.getElementById('nama_user').value = data.nama;
                document.getElementById('divisi_user').value = data.divisi;

                showToast(`Data ditemukan untuk Hostname: ${hostname}`, "success");
            } else {
                showToast("Hostname tidak ditemukan.", "error");
            }
        } catch (error) {
            showToast("Gagal menghubungi server.", "error");
        } finally {
            document.getElementById(`icon-${index}`).style.display = 'inline-block';
            document.getElementById(`load-${index}`).style.display = 'none';
        }
    }

    function removeAssetRow(id) {
        document.getElementById(`row-${id}`).remove();
        reindexRows();
    }

    function reindexRows() {
        const rows = document.querySelectorAll('.asset-row');
        rowCount = 0;
        rows.forEach((row) => {
            rowCount++;
            row.id = `row-${rowCount}`;
            row.querySelector('.device-number').innerText = `Asset #${rowCount}`;
            row.querySelector('[id^="host"]').id = `host${rowCount}`;
            row.querySelector('[id^="type"]').id = `type${rowCount}`;
            row.querySelector('[id^="sn"]').id = `sn${rowCount}`;
            row.querySelector('button').setAttribute('onclick', `searchAsset(${rowCount})`);
        });
    }

    function validateRowLimit() {
        const jenisForm = document.getElementById('jenis_form').value;
        if (jenisForm === 'accepted' && rowCount > 3) {
            showToast('Maksimal 3 asset untuk Accepted Form. Baris berlebih dihapus.', 'warning');
            while (rowCount > 3) { document.getElementById(`row-${rowCount}`).remove(); rowCount--; }
        }
    }

    function clearForm() {
        if(confirm("Apakah Anda yakin ingin menghapus semua inputan?")) {
            document.getElementById('mainForm').reset();
            const container = document.getElementById('asset-container');
            container.innerHTML = '';
            rowCount = 0;
            addAssetRow(); // Kembali ke baris pertama
            showToast("Form berhasil dibersihkan.", "success");
        }
    }

    function setFormAction() {
        const form = document.getElementById('mainForm');
        const jenisForm = document.getElementById('jenis_form').value;

        if (jenisForm === 'accepted') {
            form.action = 'cetak-form.php';
        } else {
            form.action = 'cetak-return-form.php';
        }
    }

    window.onload = addAssetRow;
</script>
<script src="bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>