<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="update.php">
            <div class="modal-content" style="border-radius: var(--radius-md, 8px); box-shadow: var(--shadow-md, 0 4px 12px rgba(0,0,0,0.1));">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="editModalLabel" style="color: var(--app-blue, #0d6efd);">Edit Inventori</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="row">
                        <input type="hidden" name="id" id="edit-id">

                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Hostname</label>
                            <input type="text" name="hostname" id="edit-hostname" class="form-control" style="border-radius: var(--radius-md, 8px);">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Rak</label>
                            <input type="text" name="rak" id="edit-rak" class="form-control" style="border-radius: var(--radius-md, 8px);" readonly>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Status</label>
                            <select name="status" id="edit-status" class="form-select" style="border-radius: var(--radius-md, 8px);">
                                <?php
                                // Jika file status.php bermasalah, ini bisa bikin modal terpotong.
                                // Pastikan file status.php ada dan benar.
                                if (file_exists('status.php')) {
                                    include 'status.php';
                                } else {
                                    echo '<option value="">File status.php tidak ditemukan</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Domain</label>
                            <select name="domain" id="edit-domain" class="form-select" required style="border-radius: var(--radius-md, 8px);">
                                <option value="APP">APP</option>
                                <option value="SMF">SMF</option>
                                <option value="CKP">CKP</option>
                                <option value="TGR">TGR</option>
                                <option value="KRW">KRW</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Kategori Perangkat</label>
                            <select name="device_category" id="edit-device_category" class="form-select" required style="border-radius: var(--radius-md, 8px);">
                                <option value="">-- Pilih Kategori --</option>
                                <option value="Laptop">Laptop</option>
                                <option value="Desktop">Desktop</option>
                                <option value="Server">Server</option>
                                <option value="Monitor">Monitor</option>
                                <option value="Printer">Printer</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Tipe (Merk/Model)</label>
                            <select name="type" id="edit-type" class="form-select" required style="border-radius: var(--radius-md, 8px);">
                                <?php
                                if (file_exists('tipe-laptop.php')) {
                                    include 'tipe-laptop.php';
                                } else {
                                    echo '<option value="">File tipe-laptop.php tidak ditemukan</option>';
                                }
                                ?>
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="fw-bold">Serial Number</label>
                            <input type="text" name="serial_number" id="edit-serial_number" class="form-control" style="border-radius: var(--radius-md, 8px);">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="fw-bold">RAM</label>
                            <input type="text" name="ram" id="edit-ram" class="form-control" style="border-radius: var(--radius-md, 8px);">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="fw-bold">Storage</label>
                            <input type="text" name="storage" id="edit-storage" class="form-control" style="border-radius: var(--radius-md, 8px);">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="fw-bold">Windows</label>
                            <input type="text" name="win" id="edit-win" class="form-control" style="border-radius: var(--radius-md, 8px);">
                        </div>

                        <div class="col-md-8 mb-3">
                            <label class="fw-bold">Kelengkapan</label>
                            <select name="kelengkapan" id="edit-kelengkapan" class="form-select" style="border-radius: var(--radius-md, 8px);">
                                <option value="">-- Pilih Kelengkapan --</option>
                                <option value="TAS">TAS</option>
                                <option value="ADAPTOR">ADAPTOR</option>
                                <option value="TAS DAN ADAPTOR">TAS DAN ADAPTOR</option>
                                <option value="TAS DAN CONVERTER VGA">TAS DAN CONVERTER VGA</option>
                                <option value="TAS DAN CONVERTER LAN">TAS DAN CONVERTER LAN</option>
                                <option value="TAS, ADAPTOR, CONVERTER LAN">TAS, ADAPTOR, CONVERTER LAN</option>
                                <option value="TAS, ADAPTOR, CONVERTER VGA">TAS, ADAPTOR, CONVERTER VGA</option>
                                <option value="TAS, ADAPTOR, CONVERTER LAN & VGA">TAS, ADAPTOR, CONVERTER LAN & VGA</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Tanggal Masuk</label>
                            <input type="date" name="tanggal_masuk" id="edit-tanggal_masuk" class="form-control" style="border-radius: var(--radius-md, 8px);">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Tanggal Keluar</label>
                            <input type="date" name="tanggal_keluar" id="edit-tanggal_keluar" class="form-control" style="border-radius: var(--radius-md, 8px);">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="fw-bold">NIK</label>
                            <input type="text" name="nik" id="edit-nik" class="form-control" style="border-radius: var(--radius-md, 8px);">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="fw-bold">Nama</label>
                            <input type="text" name="nama" id="edit-nama" class="form-control" style="border-radius: var(--radius-md, 8px);">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="fw-bold">Divisi</label>
                            <input type="text" name="divisi" id="edit-divisi" class="form-control" style="border-radius: var(--radius-md, 8px);">
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="fw-bold">Keterangan</label>
                            <textarea name="keterangan" id="edit-keterangan" class="form-control" rows="2" style="border-radius: var(--radius-md, 8px);"></textarea>
                        </div>
                    </div> </div> <div class="modal-footer d-flex justify-content-between">
                    <a href="#" id="detailBtn" class="btn btn-secondary">Detail Aset</a>
                    <div>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                        <?php if (isset($role) && $role === 'superadmin'): ?>
                            <a href="#" id="deleteBtn" class="btn btn-danger" onclick="return confirmDelete()">Hapus</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div> </form>
    </div> </div> <script>
function confirmDelete() {
    return confirm("Apakah Anda yakin ingin menghapus data inventori ini?");
}
</script>