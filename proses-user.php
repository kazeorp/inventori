<?php
// Pastikan koneksi dan session sudah ter-include sebelum memulai proses
include 'session.php';
include 'koneksi.php';

// Cek hak akses (Hanya Superadmin yang dapat menjalankan file ini)
if (($_SESSION['role'] ?? 'normal') !== 'superadmin') {
  header("Location: kelola-user.php?status=error&msg=access_denied");
  exit;
}

// Ambil data admin yang sedang login untuk log
$current_admin_id = (int)($_SESSION['admin_id'] ?? 0);
$current_admin_nama_lengkap = mysqli_real_escape_string($koneksi, $_SESSION['nama_lengkap'] ?? 'System User');
$current_admin_role = $_SESSION['role'] ?? 'normal'; // BARIS BARU: Simpan role admin yang login

/**
 * Mencatat aksi Superadmin ke tabel admin_log.
 */
function logAdminAction($koneksi, $aksi, $detail) {
    global $current_admin_id, $current_admin_nama_lengkap; // Menggunakan nama_lengkap

    // Jika menggunakan TIMESTAMP DEFAULT CURRENT_TIMESTAMP di DDL,
    // Anda TIDAK perlu lagi mengirimkan variabel waktu ($timestamp) di INSERT.
    $aksi_safe = mysqli_real_escape_string($koneksi, $aksi);
    $detail_safe = mysqli_real_escape_string($koneksi, $detail);

    $query = "
        INSERT INTO admin_log (admin_id, admin_nama_lengkap, aksi, detail)
        VALUES ('$current_admin_id', '$current_admin_nama_lengkap', '$aksi_safe', '$detail_safe')
        /* Waktu (log_time) akan otomatis diisi oleh TIMESTAMP DEFAULT CURRENT_TIMESTAMP */
    ";

    // Non-fatal error jika logging gagal
    if (!mysqli_query($koneksi, $query)) {
        // error_log("Failed to insert into admin_log: " . mysqli_error($koneksi));
    }
}

// =======================================================
// 1. PROSES TAMBAH USER (Dari modal-tambah-user.php)
// =======================================================
if (isset($_POST['tambah_user'])) {
  // Sanitize input
  $username      = mysqli_real_escape_string($koneksi, $_POST['username']);
  $password_raw  = $_POST['password'];
  $role          = mysqli_real_escape_string($koneksi, $_POST['role']);
  $nama_lengkap  = mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']);

  // Hash Password
  $password_hash = password_hash($password_raw, PASSWORD_DEFAULT);

  // Proteksi 1: Mencegah pembuatan Superadmin baru
  if ($role === 'superadmin') {
    header("Location: kelola-user.php?status=error&msg=cannot_create_superadmin");
    exit;
  }

  // Proteksi 2: Cek duplikasi username
  $check_query = mysqli_query($koneksi, "SELECT id FROM admin WHERE username='$username'");
  if (mysqli_num_rows($check_query) > 0) {
    header("Location: kelola-user.php?status=error&msg=username_exists");
    exit;
  }

// Insert data user
    $insert_query = "INSERT INTO admin (username, password, role, nama_lengkap) VALUES ('$username', '$password_hash', '$role', '$nama_lengkap')";

    if (mysqli_query($koneksi, $insert_query)) {
        // --- LOGGING: Aksi Tambah User ---
        $detail = "User baru ditambahkan: Username={$username}, Role={$role}, Nama={$nama_lengkap}";
        logAdminAction($koneksi, 'ADD_USER', $detail);
        // ---------------------------------

        header("Location: kelola-user.php?status=success&msg=user_added");
    } else {
        header("Location: kelola-user.php?status=error&msg=insert_failed");
    }
    exit;
}

// =======================================================
// 2. PROSES EDIT USER (Dari modal-edit-user.php)
// =======================================================
if (isset($_POST['edit_user'])) {
    // Sanitize input
    $id             = (int)mysqli_real_escape_string($koneksi, $_POST['id']);
    $username       = mysqli_real_escape_string($koneksi, $_POST['username']);
    $nama_lengkap   = mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']);
    $role           = mysqli_real_escape_string($koneksi, $_POST['role']); // Role yang dikirim dari form
    $new_pass       = $_POST['new_password'];

    // Ambil data user target saat ini
    $cek_target = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT role, username FROM admin WHERE id='$id'"));
    $target_role = $cek_target['role'] ?? 'normal';
    $target_username_old = $cek_target['username'] ?? 'N/A';

    // --- Proteksi Server-Side: Kontrol Edit ---

    // 1. Jika user target adalah Superadmin LAIN
    if ($target_role === 'superadmin' && $id !== $current_admin_id) {
        header("Location: kelola-user.php?status=error&msg=superadmin_protected");
        exit;
    }

    // 2. Jika Superadmin mengedit dirinya sendiri (Self-Edit), JANGAN biarkan role-nya diubah!
    if ($target_role === 'superadmin' && $id === $current_admin_id && $role !== 'superadmin') {
        // Jika form mencoba mengubah role Superadmin yang login, kembalikan role ke Superadmin
        $role = 'superadmin';
    }

    // // 3. Mencegah user target diubah menjadi Superadmin (kecuali sudah Superadmin)
    // if ($target_role !== 'superadmin' && $role === 'superadmin') {
    //      header("Location: kelola-user.php?status=error&msg=cannot_set_superadmin");
    //      exit;
    // }

    // 4. Cek duplikasi username (baru ditambahkan)
    $check_duplicate_edit = mysqli_query($koneksi, "SELECT id FROM admin WHERE username='$username' AND id != '$id'");
    if (mysqli_num_rows($check_duplicate_edit) > 0) {
        header("Location: kelola-user.php?status=error&msg=username_exists");
        exit;
    }

    // --- Proses Update Data ---

    // Update data user (username, nama_lengkap, role)
    $update_query = "UPDATE admin SET username='$username', nama_lengkap='$nama_lengkap', role='$role' WHERE id='$id'";
    $success = mysqli_query($koneksi, $update_query);

    // Update password jika diisi
    if (!empty($new_pass)) {
        $hash = password_hash($new_pass, PASSWORD_DEFAULT);
        $pass_query = "UPDATE admin SET password='$hash' WHERE id='$id'";
        $success = mysqli_query($koneksi, $pass_query);
    }

    if ($success) {
        // --- LOGGING: Aksi Edit User ---
        $log_action = ($target_role !== 'superadmin' && $role === 'superadmin') ? 'PROMOTED_USER' : 'UPDATE_USER';
        $detail = "Data user ID={$id} ({$target_username_old} -> {$username}) diubah. Role: {$target_role} -> {$role}.";
        logAdminAction($koneksi, $log_action, $detail);
        // ---------------------------------

        // Jika admin mengubah role-nya sendiri dari Superadmin ke lainnya, redirect ke halaman login
        // (Walaupun sudah diproteksi, ini sebagai safety measure)
        if ($id === $current_admin_id && $role !== 'superadmin') {
            header("Location: logout.php");
            exit;
        }

        header("Location: kelola-user.php?status=success&msg=user_updated");
    } else {
        header("Location: kelola-user.php?status=error&msg=update_failed");
    }
    exit;
}

// =======================================================
// 3. PROSES HAPUS USER (Dari modal-edit-user.php)
// =======================================================
if (isset($_POST['hapus_user'])) {
    $id = (int)mysqli_real_escape_string($koneksi, $_POST['id']);

    // Ambil data user target saat ini
    $cek_target = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT role, username FROM admin WHERE id='$id'"));
    $target_role = $cek_target['role'] ?? 'normal';
    $target_username = $cek_target['username'] ?? 'N/A';

    // Proteksi 1: Mencegah penghapusan akun Superadmin lain
    if ($target_role === 'superadmin' && $id !== $current_admin_id) {
        header("Location: kelola-user.php?status=error&msg=superadmin_protected_delete");
        exit;
    }

    // Proteksi 2: Mencegah Superadmin menghapus dirinya sendiri
    if ($id === $current_admin_id) {
        header("Location: kelola-user.php?status=error&msg=superadmin_protected_delete");
        exit;
    }

    $delete_query = "DELETE FROM admin WHERE id='$id'";
    if (mysqli_query($koneksi, $delete_query)) {
        // --- LOGGING: Aksi Hapus User ---
        $detail = "User dihapus: ID={$id}, Username={$target_username}";
        logAdminAction($koneksi, 'DELETE_USER', $detail);
        // ---------------------------------

        header("Location: kelola-user.php?status=success&msg=user_deleted");
    } else {
        header("Location: kelola-user.php?status=error&msg=delete_failed");
    }
    exit;
}

// Jika tidak ada aksi yang dikenali, redirect
header("Location: kelola-user.php");
exit;

// Tag penutup tidak disertakan untuk mencegah spasi/baris kosong di akhir file (PHP Best Practice)