<?php
include 'session.php';
include 'koneksi.php';

// Pastikan user adalah superadmin
if ($_SESSION['role'] !== 'superadmin') {
  echo "<!DOCTYPE html><html lang='id'><head><meta charset='UTF-8'><title>Akses Ditolak</title><link href='bootstrap/css/bootstrap.min.css' rel='stylesheet'><link rel='stylesheet' href='style.css'></head><body class='bg-light'>";
  include 'header.php';
  include 'sidebar.php';
  echo "<div class='main-content container py-4'><div class='alert alert-danger'>Akses ditolak! Hanya Superadmin yang dapat mengakses halaman ini.</div></div>";
  echo "<script src='bootstrap/js/bootstrap.bundle.min.js'></script></body></html>";
  exit;
}

$users = mysqli_query($koneksi, "SELECT * FROM admin");
// Dapatkan ID Superadmin yang sedang login
$current_user_id = (int)($_SESSION['admin_id'] ?? 0);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Kelola User</title>
  <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <!-- Memuat style.css global untuk konsistensi layout -->
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body>

  <?php include 'header.php'; ?>
  <?php include 'sidebar.php'; ?>

  <main class="main-content">
    <h2 class="mb-4"> Kelola User</h2>

    <!-- NOTIFIKASI STATUS -->
    <?php
    if (isset($_GET['status']) && isset($_GET['msg'])) {
        $status = $_GET['status'];
        $msg = $_GET['msg'];
        $alert_class = ($status === 'success') ? 'alert-success' : 'alert-danger';
        $pesan = '';

        switch ($msg) {
            case 'user_added':
                $pesan = ' User baru berhasil ditambahkan.';
                break;
            case 'user_updated':
                $pesan = ' Data user berhasil diperbarui.';
                break;
            case 'user_deleted':
                $pesan = ' User berhasil dihapus.';
                break;
            case 'cannot_create_superadmin':
                $pesan = ' Gagal: Tidak dapat membuat akun superadmin.';
                break;
            case 'username_exists':
                $pesan = ' Gagal: Username sudah digunakan.';
                break;
            case 'superadmin_protected':
            case 'superadmin_protected_delete':
                $pesan = ' Akses Ditolak: Akun Superadmin tidak dapat dimodifikasi atau dihapus.';
                break;
            default:
                $pesan = ' Terjadi kesalahan.';
                break;
        }

        echo "<div class='alert {$alert_class} alert-dismissible fade show' role='alert'>
                {$pesan}
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
              </div>";
    }
    ?>
    <!-- AKHIR NOTIFIKASI STATUS -->

    <!-- Tombol Tambah -->
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addUserModal"> Tambah User</button>

    <div class="table-responsive">
      <table class="table table-bordered table-striped">
        <thead class="table-dark">
          <tr>
            <th>Username</th>
            <th>Role</th>
            <th>Nama</th>
            <!-- Kolom 'Aksi' dihapus -->
          </tr>
        </thead>
<tbody>
          <?php
          while ($user = mysqli_fetch_assoc($users)):
              // Cek apakah user yang ditampilkan adalah user yang sedang login
              $is_current_user = ($user['id'] == $current_user_id);
          ?>
            <tr>
              <td>
                <?php

                // 1. Jika User adalah Superadmin yang sedang login
                if ($user['role'] === 'superadmin' && $is_current_user):
                ?>
                    <a href="#" class="text-decoration-none text-success edit-user-btn"
                      data-bs-toggle="modal"
                      data-bs-target="#editUserModal"
                      data-id="<?= htmlspecialchars($user['id'] ?? '') ?>"
                      data-username="<?= htmlspecialchars($user['username'] ?? '') ?>"
                      data-role="<?= htmlspecialchars($user['role'] ?? '') ?>"
                      data-nama="<?= htmlspecialchars($user['nama_lengkap'] ?? '') ?>">
                      <?= htmlspecialchars($user['username'] ?? '') ?>
                    </a>
                    <span class="badge bg-success">Anda</span>
                <?php

                // 2. Jika User adalah Superadmin lain
                elseif ($user['role'] === 'superadmin' && !$is_current_user):
                ?>
                    <?= htmlspecialchars($user['username'] ?? '') ?>
                    <span class="badge bg-danger">Protected</span>
                <?php

                // 3. Jika User adalah Admin/Normal
                else:
                ?>
                    <a href="#" class="text-decoration-none text-primary edit-user-btn"
                      data-bs-toggle="modal"
                      data-bs-target="#editUserModal"
                      data-id="<?= htmlspecialchars($user['id'] ?? '') ?>"
                      data-username="<?= htmlspecialchars($user['username'] ?? '') ?>"
                      data-role="<?= htmlspecialchars($user['role'] ?? '') ?>"
                      data-nama="<?= htmlspecialchars($user['nama_lengkap'] ?? '') ?>">
                      <?= htmlspecialchars($user['username'] ?? '') ?>
                    </a>
                <?php endif; ?>
              </td>
              <td><?= ucfirst(htmlspecialchars($user['role'] ?? '')) ?></td>
              <td><?= htmlspecialchars($user['nama_lengkap'] ?? '') ?></td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>

    <!-- Include Modal Tambah -->
    <?php include 'modal-tambah-user.php'; ?>

    <!-- MODAL EDIT USER TUNGGAL (HARUS DI SINI) -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="proses-user.php" method="POST" class="modal-content" style="border-radius: var(--radius-md); box-shadow: var(--shadow-md);">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="editUserModalLabel" style="color: var(--app-blue);"> Ubah User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="edit_id">

                <div class="mb-3">
                    <label for="edit_username" class="form-label fw-bold">Username</label>
                    <input type="text" name="username" id="edit_username" class="form-control" style="border-radius: var(--radius-md);" required>
                </div>

                <div class="mb-3">
                    <label for="edit_nama_lengkap" class="form-label fw-bold">Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" id="edit_nama_lengkap" class="form-control" style="border-radius: var(--radius-md);" required>
                </div>

                <div class="mb-3">
                    <label for="edit_password" class="form-label fw-bold">Password Baru (opsional)</label>
                    <input type="password" name="new_password" id="edit_password" class="form-control" style="border-radius: var(--radius-md);" placeholder="Kosongkan jika tidak ingin mengubah">
                </div>

                <div class="mb-3">
                    <label for="edit_role" class="form-label fw-bold">Role</label>
                    <select name="role" id="edit_role" class="form-select" style="border-radius: var(--radius-md);" required>
                        <option value="admin">Admin</option>
                        <option value="normal">Normal</option>
                    </select>
                    <small id="role_warning" class="text-danger fw-bold" style="display:none;"> Role Superadmin tidak dapat diubah.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" name="edit_user" class="btn btn-primary">Simpan Perubahan</button>
                <button type="submit" name="hapus_user" id="hapus_user_btn" class="btn btn-danger" onclick="return confirm('Yakin ingin menghapus user ini?')">Hapus User</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            </div>
        </form>
    </div>
</div>

  </main>

  <script src="bootstrap/js/bootstrap.bundle.min.js"></script>

<script>
  // PASANG ID USER YANG SEDANG LOGIN DARI PHP
    const currentUserId = <?= json_encode($current_user_id) ?>;

  // Script untuk mengisi data user ke dalam modal edit tunggal
  document.addEventListener('DOMContentLoaded', function () {
    const editButtons = document.querySelectorAll('.edit-user-btn');
    const editModal = document.getElementById('editUserModal');

    if (editModal) {
      editButtons.forEach(button => {
        button.addEventListener('click', function (e) {
          e.preventDefault();

          const userId = this.getAttribute('data-id');
          const username = this.getAttribute('data-username');
          const role = this.getAttribute('data-role');
          const nama = this.getAttribute('data-nama');

          // Dapatkan elemen-elemen di modal
          const editRoleSelect = editModal.querySelector('#edit_role');
          const hapusBtn = editModal.querySelector('#hapus_user_btn');
          const roleWarning = editModal.querySelector('#role_warning');

          // Set judul modal
          editModal.querySelector('.modal-title').textContent = 'Ubah User: ' + username;

          // Set nilai form
          editModal.querySelector('#edit_id').value = userId;
          editModal.querySelector('#edit_username').value = username;
          editModal.querySelector('#edit_nama_lengkap').value = nama;
          editRoleSelect.value = role; // Set role awal

          // Kosongkan password field
          editModal.querySelector('#edit_password').value = '';

          // Logika Proteksi Superadmin
          if (role === 'superadmin') {
            // Nonaktifkan field Role dan tampilkan peringatan
            editRoleSelect.setAttribute('disabled', 'disabled');
            roleWarning.style.display = 'block';

            // Sembunyikan tombol Hapus untuk akun Superadmin
            hapusBtn.style.display = 'none';
          } else {
            // Aktifkan field Role
            editRoleSelect.removeAttribute('disabled');
            roleWarning.style.display = 'none';

            // Tampilkan kembali tombol Hapus untuk user non-superadmin
            hapusBtn.style.display = 'inline-block';
          }
        });
      });
    } else {
      console.error("Modal element with ID 'editUserModal' not found.");
    }
  });
</script>
</body>
</html>
