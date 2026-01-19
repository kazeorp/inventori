<?php
include 'session.php';
include 'koneksi.php';

$role_login = $_SESSION['role'];
$current_user_id = (int) ($_SESSION['admin_id'] ?? 0);

// Izinkan Superadmin DAN Admin masuk
if ($role_login !== 'superadmin' && $role_login !== 'admin') {
    header("Location: index.php");
    exit;
}

// Ambil data user
if ($role_login === 'superadmin') {
    // Superadmin melihat semua user
    $query_users = mysqli_query($koneksi, "SELECT * FROM admin ORDER BY role DESC");
} else {
    // Admin hanya melihat dirinya sendiri
    $query_users = mysqli_query($koneksi, "SELECT * FROM admin WHERE id = $current_user_id");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manage Admin</title>
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <?php include 'header.php'; ?>
    <?php include 'sidebar.php'; ?>

    <main class="main-content">
        <div class="container-fluid">
            <h2 class="mb-4"><i class="bi bi-people-fill"></i> Manage Admin Account</h2>

            <?php if ($role_login === 'admin'):
                $me = mysqli_fetch_assoc($query_users);
                ?>
                <div class="row">
                    <div class="col-md-6">
                        <div class="card shadow-sm">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">My Profile</h5>
                            </div>
                            <div class="card-body">
                                <form action="proses-user.php" method="POST">
                                    <input type="hidden" name="id" value="<?= $me['id'] ?>">
                                    <div class="mb-3">
                                        <label class="form-label">Username</label>
                                        <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($me['username']) ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Full Name</label>
                                        <input type="text" name="nama_lengkap" class="form-control" value="<?= htmlspecialchars($me['nama_lengkap']) ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">New Password (Kosongkan jika tidak ganti)</label>
                                        <input type="password" name="new_password" class="form-control">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Role</label>
                                        <input type="text" class="form-control bg-light" value="Admin" readonly>
                                        <input type="hidden" name="role" value="admin">
                                    </div>
                                    <button type="submit" name="edit_user" class="btn btn-primary w-100">Simpan Perubahan</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            <?php else: ?>
                <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="bi bi-person-plus"></i> Add Admin
                </button>

                <div class="table-responsive bg-white p-3 rounded shadow-sm">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Username</th>
                                <th>Full Name</th>
                                <th>Role</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                          <tbody>
                              <?php while ($user = mysqli_fetch_assoc($query_users)):
                                  $is_me = ($user['id'] == $current_user_id);
                                  $is_other_sa = ($user['role'] === 'superadmin' && !$is_me);
                                  $is_normal = ($user['role'] === 'normal'); // Cek apakah role normal
                                  ?>
                                  <tr>
                                      <td class="fw-bold"><?= htmlspecialchars($user['username']) ?></td>
                                      <td><?= htmlspecialchars($user['nama_lengkap']) ?></td>
                                      <td>
                                          <span class="badge <?= $user['role'] === 'superadmin' ? 'bg-danger' : ($user['role'] === 'normal' ? 'bg-secondary' : 'bg-info') ?>">
                                              <?= ucfirst($user['role']) ?>
                                          </span>
                                      </td>
                                      <td>
                                          <?php if ($is_other_sa): ?>
                                              <span class="badge bg-secondary"><i class="bi bi-lock-fill"></i> Protected (SA)</span>
                                          <?php elseif ($is_normal): ?>
                                              <span class="badge bg-dark"><i class="bi bi-shield-lock"></i> System Role</span>
                                          <?php else: ?>
                                              <button class="btn btn-sm btn-outline-primary edit-user-btn"
                                                  data-bs-toggle="modal" data-bs-target="#editUserModal"
                                                  data-id="<?= $user['id'] ?>"
                                                  data-username="<?= $user['username'] ?>"
                                                  data-nama="<?= $user['nama_lengkap'] ?>"
                                                  data-role="<?= $user['role'] ?>">
                                                  <i class="bi bi-pencil-square"></i> Edit
                                              </button>
                                          <?php endif; ?>
                                      </td>
                                  </tr>
                              <?php endwhile; ?>
                          </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <div class="modal fade" id="editUserModal" tabindex="-1">
        <div class="modal-dialog">
            <form action="proses-user.php" method="POST" class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Ubah User</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" id="edit_username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="nama_lengkap" id="edit_nama_lengkap" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password Baru</label>
                        <input type="password" name="new_password" class="form-control" placeholder="Kosongkan jika tidak ingin mengubah">
                    </div>
                    <div class="mb-3" id="role_field_container">
                        <label class="form-label">Role</label>
                        <select name="role" id="edit_role" class="form-select">
                            <option value="superadmin">Superadmin</option>
                            <option value="admin">Admin</option>
                            <option value="normal">Normal</option>
                        </select>
                        <small id="sa_warning" class="text-danger d-none">Anda tidak bisa mengubah role Anda sendiri.</small>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="submit" name="hapus_user" id="hapus_user_btn" class="btn btn-danger" onclick="return confirm('Yakin hapus user ini?')">Hapus User</button>
                    <div>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="edit_user" class="btn btn-primary">Simpan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php if ($role_login === 'superadmin') {
        include 'modal-tambah-user.php';
    } ?>

    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('.edit-user-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                const role = this.dataset.role;
                const is_me = (id == "<?= $current_user_id ?>");

                document.getElementById('edit_id').value = id;
                document.getElementById('edit_username').value = this.dataset.username;
                document.getElementById('edit_nama_lengkap').value = this.dataset.nama;
                document.getElementById('edit_role').value = role;

                // Proteksi Role Superadmin saat edit diri sendiri
                if (is_me) {
                    document.getElementById('edit_role').disabled = true;
                    document.getElementById('sa_warning').classList.remove('d-none');
                    document.getElementById('hapus_user_btn').classList.add('d-none');
                } else {
                    document.getElementById('edit_role').disabled = false;
                    document.getElementById('sa_warning').classList.add('d-none');
                    document.getElementById('hapus_user_btn').classList.remove('d-none');
                }
            });
        });
    </script>
</body>
</html>