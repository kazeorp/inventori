<div class="modal fade" id="tipeLaptopModal" tabindex="-1" aria-labelledby="tipeLaptopModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title" id="tipeLaptopModalLabel"><i class="bi bi-laptop-fill"></i> Kelola Asset Inventori</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

                <h6 class="mb-3">Daftar Tipe Asset yang Tersedia:</h6>

                <div id="tipe-loading-indicator" class="text-center my-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Memuat data ...</p>
                </div>

                <div id="tipe-data-container">
                    </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editTipeModal" tabindex="-1" aria-labelledby="editTipeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form-edit-tipe" method="POST" action="proses-tipe-laptop.php">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="editTipeModalLabel">Edit Tipe Inventori</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
					<div class="modal-body">
						<input type="hidden" name="action" value="edit">
						<input type="hidden" name="id_tipe" id="edit-id-tipe">
						<div class="mb-3">
							<label for="edit-nama-tipe" class="form-label">Nama Tipe</label>
							<input type="text" class="form-control" id="edit-nama-tipe" name="nama_tipe" required>
						</div>
					</div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-info" name="submit_edit_tipe">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>