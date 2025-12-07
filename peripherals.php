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

$message = '';
$message_type = '';

// --- LOGIKA UPDATE STOK ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_stock'])) {
    $item_id = (int)$_POST['item_id'];
    $new_stock = (int)$_POST['stock_count'];

    if ($item_id > 0 && $new_stock >= 0) {
        $stmt = mysqli_prepare($koneksi, "UPDATE peripheral_stock SET stock_count = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "ii", $new_stock, $item_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $message = "Stok berhasil diperbarui!";
            $message_type = "success";
        } else {
            $message = "Gagal memperbarui stok: " . mysqli_error($koneksi);
            $message_type = "danger";
        }
        mysqli_stmt_close($stmt);
    } else {
        $message = "Input tidak valid.";
        $message_type = "warning";
    }
}

// --- QUERY DATA STOK ---
$sql_stock = "SELECT id, item_name, stock_count, unit, last_updated FROM peripheral_stock ORDER BY item_name";
$result_stock = mysqli_query($koneksi, $sql_stock);

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

    <main class="main-content">
        <h2 class="mb-4">📦 Stock Peripheral</h2>

        <?php if ($message): ?>
            <div class="alert alert-<?= $message_type ?> alert-dismissible fade show" role="alert">
                <?= e($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead class="table-primary">
                    <tr>
                        <th>Item Peripheral</th>
                        <th>Stock Saat Ini</th>
                        <th>Unit</th>
                        <th>Terakhir Diperbarui</th>
                        <th>Aksi</th>
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
                                    <button type="button" class="btn btn-sm btn-primary btn-edit-stock"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editStockModal"
                                        data-id="<?= e($row['id']) ?>"
                                        data-name="<?= e($row['item_name']) ?>"
                                        data-stock="<?= e($row['stock_count']) ?>">
                                        <i class="bi bi-pencil"></i> Edit Stok
                                    </button>
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

        <div class="modal fade" id="editStockModal" tabindex="-1" aria-labelledby="editStockModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" action="peripherals.php">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title" id="editStockModalLabel">Edit Stok: <span id="modal-item-name"></span></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="item_id" id="modal-item-id">
                            <div class="mb-3">
                                <label for="modal-stock-count" class="form-label">Jumlah Stok Baru</label>
                                <input type="number" class="form-control" name="stock_count" id="modal-stock-count" required min="0">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" name="update_stock" class="btn btn-primary">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </main>

    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const editStockModal = document.getElementById('editStockModal');
        editStockModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const itemId = button.getAttribute('data-id');
            const itemName = button.getAttribute('data-name');
            const itemStock = button.getAttribute('data-stock');

            const modalTitle = editStockModal.querySelector('#modal-item-name');
            const modalItemId = editStockModal.querySelector('#modal-item-id');
            const modalStockCount = editStockModal.querySelector('#modal-stock-count');

            modalTitle.textContent = itemName;
            modalItemId.value = itemId;
            modalStockCount.value = itemStock;
        });
    });
    </script>
</body>
</html>