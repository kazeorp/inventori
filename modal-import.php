<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="importModalLabel"><i class="bi bi-file-earmark-arrow-up"></i> Import Data Inventori</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="import_inventori.php" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="alert alert-warning">
                        Pastikan urutan kolom pada file Excel Anda sesuai dengan urutan yang digunakan saat Export: Hostname, Status, Rak,
						Tipe, RAM, Storage, OS, Keterangan, Kelengkapan,
						Tanggal Masuk, Tanggal Keluar, NIK User, Nama User, Divisi.
                    </div>
                    <div class="mb-3">
                        <label for="fileExcel" class="form-label">Pilih File Excel (.xlsx)</label>
                        <input class="form-control" type="file" id="fileExcel" name="fileExcel" accept=".xlsx" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-info text-white" name="import_submit">Import Data</button>
                </div>
            </form>
        </div>
    </div>
</div>