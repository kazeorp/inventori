<?php
// peripherals.php
include 'session.php';
include 'koneksi.php';

// Fungsi untuk keamanan
if (!function_exists('e')) {
    function e($text) {
        return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
    }
}

// Proteksi: Hanya admin/superadmin yang bisa akses
$user_role_login = $_SESSION['role'] ?? 'normal';
if ($user_role_login !== 'admin' && $user_role_login !== 'superadmin') {
    header("Location: index.php");
    exit;
}

$user_logged_in = $_SESSION['user_name'] ?? 'System';

// 💡 IMPLEMENTASI POST-REDIRECT-GET (PRG): Ambil dan hapus pesan dari session
$message = $_SESSION['message'] ?? '';
$message_type = $_SESSION['message_type'] ?? '';

unset($_SESSION['message']);
unset($_SESSION['message_type']);

// --- LOGIKA MENAMBAH ITEM PERIPHERAL BARU ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_new_item'])) {
    $item_name = mysqli_real_escape_string($koneksi, $_POST['item_name']);
    $unit = mysqli_real_escape_string($koneksi, $_POST['unit']);
    $initial_stock = (int)$_POST['stock_count'];

    if (!empty($item_name)) {
        $check = mysqli_query($koneksi, "SELECT id FROM peripheral_stock WHERE item_name = '$item_name'");
        if (mysqli_num_rows($check) > 0) {
            $_SESSION['message'] = "Gagal: Item '$item_name' sudah ada dalam stok.";
            $_SESSION['message_type'] = "danger";
        } else {
            $stmt = mysqli_prepare($koneksi, "INSERT INTO peripheral_stock (item_name, unit, stock_count) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "ssi", $item_name, $unit, $initial_stock);

            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['message'] = "Item '$item_name' berhasil ditambahkan!";
                $_SESSION['message_type'] = "success";
            } else {
                $_SESSION['message'] = "Gagal menambahkan item: " . mysqli_error($koneksi);
                $_SESSION['message_type'] = "danger";
            }
            mysqli_stmt_close($stmt);
        }
    }
    // 💡 REDIRECT SETELAH POST
    header("Location: peripherals.php");
    exit;
}

// --- LOGIKA TRANSAKSI STOK MASUK (IN) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_transaction'])) {
    $peripheral_id = (int)$_POST['peripheral_id'];
    $quantity = (int)$_POST['quantity'];
    $po_number = mysqli_real_escape_string($koneksi, $_POST['po_number'] ?? '');
    $notes = mysqli_real_escape_string($koneksi, $_POST['notes'] ?? '');

    if ($peripheral_id > 0 && $quantity > 0) {
        $q_item = mysqli_query($koneksi, "SELECT item_name FROM peripheral_stock WHERE id = $peripheral_id");
        $d_item = mysqli_fetch_assoc($q_item);
        $item_name = $d_item['item_name'] ?? 'N/A';

        $action = 'IN';
        $stmt_history = mysqli_prepare($koneksi, "INSERT INTO peripheral_history (peripheral_id, item_name, action, quantity, po_number, notes, action_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt_history, "isdisss", $peripheral_id, $item_name, $action, $quantity, $po_number, $notes, $user_logged_in);
        mysqli_stmt_execute($stmt_history);

        $stmt_update = mysqli_prepare($koneksi, "UPDATE peripheral_stock SET stock_count = stock_count + ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt_update, "ii", $quantity, $peripheral_id);

        if (mysqli_stmt_execute($stmt_update)) {
            $_SESSION['message'] = "Stok '$item_name' berhasil ditambahkan sebanyak $quantity unit. PO/ID: " . e($po_number);
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Gagal memperbarui stok: " . mysqli_error($koneksi);
            $_SESSION['message_type'] = "danger";
        }
        mysqli_stmt_close($stmt_update);
        mysqli_stmt_close($stmt_history);
    } else {
        $_SESSION['message'] = "Input transaksi tidak valid.";
        $_SESSION['message_type'] = "warning";
    }
    // 💡 REDIRECT SETELAH POST
    header("Location: peripherals.php");
    exit;
}

// --- LOGIKA TRANSAKSI STOK KELUAR (OUT) DENGAN PO SPESIFIK ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['take_transaction'])) {
    $peripheral_id = (int)$_POST['peripheral_id'];
    $quantity = (int)$_POST['quantity'];
    $recipient = mysqli_real_escape_string($koneksi, $_POST['recipient'] ?? '');
    $notes = mysqli_real_escape_string($koneksi, $_POST['notes'] ?? '');
    $used_po_number = mysqli_real_escape_string($koneksi, $_POST['used_po_number'] ?? '');

    if ($peripheral_id > 0 && $quantity > 0 && !empty($recipient) && !empty($used_po_number)) {
        $q_item = mysqli_query($koneksi, "SELECT item_name, stock_count FROM peripheral_stock WHERE id = $peripheral_id");
        $d_item = mysqli_fetch_assoc($q_item);
        $item_name = $d_item['item_name'] ?? 'N/A';
        $current_stock = $d_item['stock_count'];

        $sql_check_po = "SELECT SUM(CASE WHEN action = 'IN' THEN quantity ELSE -quantity END) as saldo_po
                         FROM peripheral_history
                         WHERE peripheral_id = $peripheral_id AND po_number = '$used_po_number'";
        $q_check_po = mysqli_query($koneksi, $sql_check_po);
        $d_check_po = mysqli_fetch_assoc($q_check_po);
        $saldo_po = $d_check_po['saldo_po'] ?? 0;

        if ($quantity > $saldo_po) {
            $_SESSION['message'] = "Gagal mengeluarkan stok: Jumlah yang diminta ($quantity) melebihi stok yang tersedia ($saldo_po) untuk PO/ID: " . e($used_po_number);
            $_SESSION['message_type'] = "danger";
        } else {
            $stmt_update = mysqli_prepare($koneksi, "UPDATE peripheral_stock SET stock_count = stock_count - ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt_update, "ii", $quantity, $peripheral_id);

            if (mysqli_stmt_execute($stmt_update)) {
                $action = 'OUT';
                $stmt_history = mysqli_prepare($koneksi, "INSERT INTO peripheral_history (peripheral_id, item_name, action, quantity, po_number, notes, action_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
                mysqli_stmt_bind_param($stmt_history, "isdisss", $peripheral_id, $item_name, $action, $quantity, $used_po_number, $notes, $user_logged_in);
                mysqli_stmt_execute($stmt_history);

                $_SESSION['message'] = "Stok '$item_name' berhasil dikeluarkan sebanyak $quantity unit dari PO/ID: " . e($used_po_number) . ". Diberikan kepada: " . e($recipient);
                $_SESSION['message_type'] = "success";
            } else {
                $_SESSION['message'] = "Gagal memperbarui stok (OUT): " . mysqli_error($koneksi);
                $_SESSION['message_type'] = "danger";
            }
            mysqli_stmt_close($stmt_update);
            mysqli_stmt_close($stmt_history);
        }
    } else {
        $_SESSION['message'] = "Input transaksi keluar tidak valid. Pastikan NIK/Nama penerima dan Nomor PO sudah dipilih/diisi.";
        $_SESSION['message_type'] = "warning";
    }
    // 💡 REDIRECT SETELAH POST
    header("Location: peripherals.php");
    exit;
}


// --- QUERY DATA STOK UTAMA ---
$result_stock = false;
$sql_stock = "SELECT id, item_name, stock_count, unit, last_updated FROM peripheral_stock ORDER BY item_name";
if ($koneksi) {
    $result_stock = mysqli_query($koneksi, $sql_stock);
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Stok Peripheral</title>
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <?php include 'header.php'; ?>
    <?php include 'sidebar.php'; ?>

    <div aria-live="polite" aria-atomic="true" class="position-fixed top-0 end-0 p-3" style="z-index: 1050;">
        <?php if ($message):
            $header_color = $message_type === 'success' ? 'text-success' : ($message_type === 'danger' ? 'text-danger' : 'text-warning');
        ?>
        <div id="statusToast" class="toast align-items-center text-white bg-<?= e($message_type) ?> border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="5000">
            <div class="d-flex">
                <div class="toast-body fw-bold">
                    <?= e($message) ?>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <main class="main-content">
        <h2 class="mb-4">📦 Manajemen Stok Peripheral</h2>

        <button type="button" class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addItemModal">
            ➕ Tambah Item Peripheral Baru
        </button>

        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead class="table-primary">
                    <tr>
                        <th>Item Peripheral</th>
                        <th>Stok Saat Ini</th>
                        <th>Unit</th>
                        <th>Terakhir Diperbarui</th>
                        <th style="width: 250px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result_stock && mysqli_num_rows($result_stock) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($result_stock)): ?>
                            <tr>
                                <td><?= e($row['item_name']) ?></td>
                                <td>
                                    <span class="badge bg-success fs-6"><?= e($row['stock_count']) ?></span>
                                </td>
                                <td><?= e($row['unit']) ?></td>
                                <td><?= date('d-m-Y H:i', strtotime($row['last_updated'])) ?></td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-success btn-add-stock"
                                        data-bs-toggle="modal"
                                        data-bs-target="#addStockModal"
                                        data-id="<?= e($row['id']) ?>"
                                        data-name="<?= e($row['item_name']) ?>">
                                        <i class="bi bi-plus-circle"></i> Tambah Stok
                                    </button>

                                    <?php if (($row['stock_count'] ?? 0) > 0): ?>
                                    <button type="button" class="btn btn-sm btn-danger btn-take-stock mt-1"
                                        data-bs-toggle="modal"
                                        data-bs-target="#takeStockModal"
                                        data-id="<?= e($row['id']) ?>"
                                        data-name="<?= e($row['item_name']) ?>"
                                        data-max-stock="<?= e($row['stock_count']) ?>">
                                        <i class="bi bi-dash-circle"></i> Gunakan Stok
                                    </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">Tidak ada data peripheral dalam stok.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php
        include 'modal-peripherals.php';
        ?>

    </main>

    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {

        // 💡 SCRIPT UNTUK MENAMPILKAN TOAST OTOMATIS
        const toastEl = document.getElementById('statusToast');
        if (toastEl) {
            const toast = new bootstrap.Toast(toastEl, {
                autohide: true,
                delay: 5000 // 5 detik
            });
            toast.show();
        }

        // SCRIPT UNTUK MODAL TAMBAH STOK (IN)
        const addStockModal = document.getElementById('addStockModal');
        if (addStockModal) {
            addStockModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const peripheralId = button.getAttribute('data-id');
                const itemName = button.getAttribute('data-name');

                addStockModal.querySelector('#trans-item-name').textContent = itemName;
                addStockModal.querySelector('#trans-peripheral-id').value = peripheralId;
                addStockModal.querySelector('#trans-quantity').value = 1;
                addStockModal.querySelector('#trans-po-number').value = '';
                addStockModal.querySelector('#trans-notes').value = '';
            });
        }


        // SCRIPT UNTUK MODAL GUNAKAN STOK (OUT) DENGAN PEMILIHAN PO
        const takeStockModal = document.getElementById('takeStockModal');
        if (takeStockModal) {
            takeStockModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const peripheralId = button.getAttribute('data-id');
                const itemName = button.getAttribute('data-name');
                const maxStock = button.getAttribute('data-max-stock');

                takeStockModal.querySelector('#take-item-name').textContent = itemName;
                takeStockModal.querySelector('#take-peripheral-id').value = peripheralId;
                takeStockModal.querySelector('#take-max-stock-display').textContent = maxStock;

                const poSelect = takeStockModal.querySelector('#take-po-select');
                const poSaldoDisplay = takeStockModal.querySelector('#po-saldo-display');
                const quantityInput = takeStockModal.querySelector('#take-quantity');

                poSelect.innerHTML = '<option value="">Memuat...</option>';
                poSaldoDisplay.textContent = '';
                quantityInput.value = 1;

                // Memanggil API untuk mengambil daftar PO yang masih memiliki saldo
                fetch(`get_available_pos.php?peripheral_id=${peripheralId}`)
                    .then(response => response.json())
                    .then(data => {
                        poSelect.innerHTML = '';
                        if (data.status === 'success' && data.data.length > 0) {
                            poSelect.innerHTML = '<option value="">-- Pilih PO / ID Unit --</option>';
                            data.data.forEach(po => {
                                const option = document.createElement('option');
                                option.value = po.po_number;
                                option.textContent = `${po.po_number} (Stok: ${po.saldo})`;
                                option.dataset.saldo = po.saldo;
                                poSelect.appendChild(option);
                            });
                        } else {
                             poSelect.innerHTML = '<option value="">Tidak ada PO tersedia</option>';
                             quantityInput.setAttribute('max', 0);
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching POs:', error);
                        poSelect.innerHTML = '<option value="">Gagal memuat PO</option>';
                    });

                // Event listener untuk membatasi input quantity berdasarkan saldo PO yang dipilih
                poSelect.onchange = function() {
                    const selectedOption = this.options[this.selectedIndex];
                    const saldo = selectedOption && selectedOption.dataset.saldo ? parseInt(selectedOption.dataset.saldo) : 0;

                    if (saldo > 0) {
                        poSaldoDisplay.textContent = `Saldo PO terpilih: ${saldo} Unit`;
                        quantityInput.setAttribute('max', saldo);
                    } else {
                        poSaldoDisplay.textContent = 'Pilih PO yang valid.';
                        quantityInput.removeAttribute('max');
                    }
                };
            });
        }
    });
    </script>
</body>
</html>