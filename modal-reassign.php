<?php
if (!isset($koneksi)) {
    require_once "koneksi.php";
}
if (!function_exists('e')) {
    require_once "helpers.php";
}
?>
<div class="modal fade" id="reassignModal" tabindex="-1" aria-labelledby="reassignModalLabel">
    <div class="modal-dialog">
        <form id="form-reassign">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="reassignModalLabel">Reassign Servis ID: <span id="reassign-service-id"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Servis saat ini ditugaskan ke: <strong id="current-handler-name"></strong>.</p>
                    <input type="hidden" name="service_id" id="reassign-service-input">
                    <div class="mb-3">
                        <label for="new_admin_id" class="form-label">Pilih Admin Baru:</label>
                            <select name="new_admin_id" id="new_admin_id" class="form-select" required>
                                <option value="">-- Pilih Admin Baru --</option>
                                <?php
                                $query_users = mysqli_query($koneksi, "SELECT id, nama_lengkap, role FROM admin WHERE role IN ('admin', 'superadmin') ORDER BY nama_lengkap ASC");

if ($query_users):
    while ($u = mysqli_fetch_assoc($query_users)): ?>
                                        <option value="<?= e($u['id']) ?>" data-username="<?= e($u['nama_lengkap']) ?>">
                                            <?= e($u['nama_lengkap']) ?> (<?= e($u['role']) ?>)
                                        </option>
                                    <?php endwhile;
endif; ?>
                            </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Reassign</button>
                </div>
            </div>
        </form>
    </div>
</div>