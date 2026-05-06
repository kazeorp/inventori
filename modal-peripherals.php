<?php
// modal-peripherals.php

// PENTING: File ini harus di-include di peripherals.php
// Pastikan fungsi e() sudah didefinisikan di file induk (peripherals.php)
if (!function_exists('e')) {
    function e($text)
    {
        return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
    }
}
?>

<div class="modal fade" id="addItemModal" tabindex="-1" aria-labelledby="addItemModalLabel">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="peripherals.php">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="addItemModalLabel">Tambah Item Peripheral Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="add_new_item" value="1">
                    <div class="mb-3">
                        <label for="new-item-name" class="form-label">Nama Item</label>
                        <input type="text" class="form-control" name="item_name" id="new-item-name" required>
                    </div>
                    <div class="mb-3">
                        <label for="new-item-unit" class="form-label">Satuan Unit</label>
                        <input type="text" class="form-control" name="unit" id="new-item-unit" value="Unit" required>
                    </div>
                    <div class="mb-3">
                        <label for="initial-stock" class="form-label">Stok Awal</label>
                        <input type="number" class="form-control" name="stock_count" id="initial-stock" required min="0" value="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Simpan Item</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="addStockModal" tabindex="-1" aria-labelledby="addStockModalLabel">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="peripherals.php">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="addStockModalLabel">Tambah Stok (IN) Item: <span id="trans-item-name"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="add_transaction" value="1">
                    <input type="hidden" name="peripheral_id" id="trans-peripheral-id">

                    <div class="mb-3">
                        <label for="trans-po-number" class="form-label">Nomor PO / ID Unik (Opsional)</label>
                        <input type="text" class="form-control" name="po_number" id="trans-po-number">
                    </div>
                    <div class="mb-3">
                        <label for="trans-quantity" class="form-label">Jumlah Unit Masuk</label>
                        <input type="number" class="form-control" name="quantity" id="trans-quantity" required min="1">
                    </div>
                     <div class="mb-3">
                        <label for="trans-notes" class="form-label">Catatan</label>
                        <textarea class="form-control" name="notes" id="trans-notes"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Proses Transaksi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="takeStockModal" tabindex="-1" aria-labelledby="takeStockModalLabel">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="peripherals.php">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="takeStockModalLabel">Gunakan Stok (OUT) Item: <span id="take-item-name"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
<div class="modal-body">
                    <input type="hidden" name="take_transaction" value="1">
                    <input type="hidden" name="peripheral_id" id="take-peripheral-id">

                    <div class="alert alert-warning p-2">Stok Tersedia (Total): <strong id="take-max-stock-display"></strong> Unit</div>

                    <div class="mb-3">
                        <label for="take-po-select" class="form-label">Pilih Nomor PO / ID Unit</label>
                        <select class="form-select" name="used_po_number" id="take-po-select" required>
                            <option value="">Memuat...</option>
                        </select>
                        <small class="text-danger" id="po-saldo-display"></small>
                    </div>
                    <div class="mb-3">
                        <label for="take-recipient" class="form-label">NIK / Nama Penerima (Wajib)</label>
                        <input type="text" class="form-control" name="recipient" id="take-recipient" required>
                    </div>
                    <div class="mb-3">
                        <label for="take-quantity" class="form-label">Jumlah Unit Keluar</label>
                        <input type="number" class="form-control" name="quantity" id="take-quantity" required min="1">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Keluarkan Stok</button>
                </div>
            </form>
        </div>
    </div>
</div>