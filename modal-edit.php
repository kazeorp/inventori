<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form method="POST" action="update.php">
            <div class="modal-content border-0 shadow-lg" style="border-radius: var(--radius-lg, 12px);">
                <div class="modal-header bg-dark text-white py-3">
                    <h5 class="modal-title fw-bold" id="editModalLabel"><i class="bi bi-pencil-square me-2"></i> Update Asset Record</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="row g-3">
                        <input type="hidden" name="id" id="edit-id">

                        <div class="col-12 mb-2">
                            <h6 class="fw-bold text-muted small text-uppercase mb-0" style="letter-spacing: 1px;">Primary Specifications</h6>
                            <hr class="mt-2 mb-1 opacity-10">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small">Hostname</label>
                            <input type="text" name="hostname" id="edit-hostname" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small">Warna</label>
                            <select name="warna" id="edit-warna" class="form-select form-select-sm">
                                <option value="">- Pilih Warna -</option>
                                <option value="Merah">Merah</option>
                                <option value="Kuning">Kuning</option>
                                <option value="Hijau">Hijau</option>
                                <option value="Biru">Biru</option>
                                <option value="Hitam">Hitam</option>
                                <option value="Putih">Putih</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small">Current Status</label>
                            <select name="status" id="edit-status" class="form-select form-select-sm">
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
                            <label class="form-label fw-bold small">Domain</label>
                            <select name="domain" id="edit-domain" class="form-select form-select-sm" required>
                                <option value="APP">APP</option>
                                <option value="SMF">SMF</option>
                                <option value="CKP">CKP</option>
                                <option value="TGR">TGR</option>
                                <option value="KRW">KRW</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small">Kategori Perangkat</label>
                            <select name="device_category" id="edit-device_category" class="form-select form-select-sm" required>
                                <option value="">-- Pilih Kategori --</option>
                                <option value="Laptop">Laptop</option>
                                <option value="Desktop">Desktop</option>
                                <option value="Server">Server</option>
                                <option value="Monitor">Monitor</option>
                                <option value="Printer">Printer</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small">Tipe (Merk/Model)</label>
                            <select name="type" id="edit-type" class="form-select form-select-sm" required>
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
                            <label class="form-label fw-bold small">Serial Number</label>
                            <input type="text" name="serial_number" id="edit-serial_number" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold small">RAM</label>
                            <input type="text" name="ram" id="edit-ram" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold small">Storage</label>
                            <div class="input-group input-group-sm">
                                <select class="form-select" id="edit-storage-type">
                                    <option value="">-- Tipe --</option>
                                    <option value="HDD">HDD</option>
                                    <option value="SSD">SSD</option>
                                </select>
                                <input type="number" id="edit-storage-size" class="form-control" placeholder="Size">
                                <span class="input-group-text">GB</span>
                            </div>
                            <!-- Hidden input to store the combined value -->
                            <input type="hidden" name="storage" id="edit-storage">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold small">Windows</label>
                            <input type="text" name="win" id="edit-win" class="form-control form-control-sm">
                        </div>

                        <div class="col-md-8 mb-3">
                            <label class="form-label fw-bold small">Kelengkapan</label>
                            <select name="kelengkapan" id="edit-kelengkapan" class="form-select form-select-sm">
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

                        <div class="col-12 mt-2 mb-2">
                            <h6 class="fw-bold text-muted small text-uppercase mb-0" style="letter-spacing: 1px;">User Assignment</h6>
                            <hr class="mt-2 mb-1 opacity-10">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small">Tanggal Masuk</label>
                            <input type="date" name="tanggal_masuk" id="edit-tanggal_masuk" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small">Tanggal Keluar</label>
                            <input type="date" name="tanggal_keluar" id="edit-tanggal_keluar" class="form-control form-control-sm">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold small">NIK User</label>
                            <input type="text" name="nik" id="edit-nik" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold small">Nama User</label>
                            <input type="text" name="nama" id="edit-nama" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold small">Divisi</label>
                            <input type="text" name="divisi" id="edit-divisi" class="form-control form-control-sm">
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold small">Keterangan Internal</label>
                            <textarea name="keterangan" id="edit-keterangan" class="form-control form-control-sm" rows="2"></textarea>
                        </div>
                    </div> </div> <div class="modal-footer d-flex justify-content-between">
                    <a href="#" id="detailBtn" class="btn btn-outline-dark px-4"><i class="bi bi-box-arrow-up-right me-1"></i> Full Detail</a>
                    <div>
                        <button type="submit" class="btn btn-primary px-4">Save Changes</button>
                        <?php if (isset($role) && $role === 'superadmin'): ?>
                            <a href="#" id="deleteBtn" class="btn btn-danger px-4" onclick="return confirmDelete()">Delete</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>