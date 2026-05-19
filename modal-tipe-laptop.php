<div class="modal fade" id="tipeLaptopModal" tabindex="-1" aria-labelledby="tipeLaptopModalLabel" style="z-index: 1060;">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white px-4 py-3">
                <div class="d-flex align-items-center">
                    <h5 class="modal-title fw-bold" id="tipeLaptopModalLabel"><i class="bi bi-laptop me-2"></i> Master Data Aset</h5>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <button type="button" class="btn btn-sm btn-primary shadow-sm px-3 fw-bold" id="btn-show-add-form">
                        <i class="bi bi-plus-lg"></i> Tambah Tipe
                    </button>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body p-4">
                <div id="tipe-loading-indicator" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Memuat data master...</p>
                </div>

                <div id="tipe-data-container">
                </div>
            </div>
            <div class="modal-footer bg-light px-4">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editTipeModal" tabindex="-1" aria-labelledby="editTipeModalLabel" style="z-index: 1070;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <form id="form-edit-tipe" method="POST" action="proses-tipe-laptop.php">
                <div class="modal-header bg-primary text-white px-4 py-3">
                    <h5 class="modal-title fw-bold" id="editTipeModalLabel"><i class="bi bi-pencil-square me-2"></i> Edit Tipe</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id_tipe" id="edit-id-tipe">
                    <div class="mb-0">
                        <label for="edit-nama-tipe" class="form-label fw-bold small text-uppercase" style="letter-spacing: 0.5px;">Nama Tipe / Merk</label>
                        <input type="text" class="form-control shadow-sm uppercase-input" id="edit-nama-tipe" name="nama_tipe" required oninput="this.value = this.value.toUpperCase()">
                    </div>
                </div>
                <div class="modal-footer bg-light px-4">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm" name="submit_edit_tipe">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Toggle visibility of the add form within the dynamic container
document.addEventListener('click', function(e) {
    const showAddBtn = e.target.closest('#btn-show-add-form');
    if (showAddBtn) {
        const addFormContainer = document.getElementById('card-tambah-tipe');
        if (addFormContainer) {
            addFormContainer.classList.toggle('d-none');
            if (!addFormContainer.classList.contains('d-none')) {
                addFormContainer.querySelector('input[name="nama_tipe"]').focus();
            }
        }
    }
});
</script>