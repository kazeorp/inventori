<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="proses-user.php" class="modal-content" style="border-radius: var(--radius-md); box-shadow: var(--shadow-md);">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" style="color: var(--app-blue);"> Tambah User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

                <div class="mb-3">
                    <label class="fw-bold">Username</label>
                    <input type="text" name="username" class="form-control" style="border-radius: var(--radius-md);" required>
                </div>

                <div class="mb-3">
                    <label class="fw-bold">Password</label>
                    <input type="password" name="password" class="form-control" style="border-radius: var(--radius-md);" required>
                </div>

                <div class="mb-3">
                    <label class="fw-bold">Nama</label>
                    <input type="text" name="nama_lengkap" class="form-control" style="border-radius: var(--radius-md);" required>
                </div>

                <div class="mb-3">
                    <label class="fw-bold">Role</label>
                    <select name="role" id="add_role_select" class="form-select" style="border-radius: var(--radius-md);" required>
                        <option value="admin">Admin</option>
                        <option value="normal">Normal</option>

                        <?php
                        // ASUMSI: $_SESSION['role'] tersedia dari file yang meng-include modal ini
                        if (isset($_SESSION['role']) && $_SESSION['role'] === 'superadmin'):
                            ?>
                            <option value="superadmin">Superadmin</option>
                        <?php endif; ?>
                    </select>
                </div>

            </div>
            <div class="modal-footer">
                <button type="submit" name="tambah_user" class="btn btn-primary">Simpan</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            </div>
        </form>
    </div>
</div>