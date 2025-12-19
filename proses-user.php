<?php
include 'session.php';
include 'koneksi.php';

// Cek hak akses awal: Hanya Superadmin dan Admin yang boleh mengakses file ini
$role_login = $_SESSION['role'] ?? 'normal';
if ($role_login !== 'superadmin' && $role_login !== 'admin') {
    header("Location: index.php");
    exit;
}

$current_admin_id = (int)($_SESSION['admin_id'] ?? 0);
$current_admin_nama_lengkap = mysqli_real_escape_string($koneksi, $_SESSION['nama_lengkap'] ?? 'System User');

/**
 * Mencatat aksi ke tabel admin_log.
 */
function logAdminAction($koneksi, $aksi, $detail) {
    global $current_admin_id, $current_admin_nama_lengkap;
    $aksi_safe = mysqli_real_escape_string($koneksi, $aksi);
    $detail_safe = mysqli_real_escape_string($koneksi, $detail);

    $query = "INSERT INTO admin_log (admin_id, admin_nama_lengkap, aksi, detail)
              VALUES ('$current_admin_id', '$current_admin_nama_lengkap', '$aksi_safe', '$detail_safe')";
    mysqli_query($koneksi, $query);
}

// =======================================================
// 1. PROSES TAMBAH USER (Hanya Superadmin)
// =======================================================
if (isset($_POST['tambah_user'])) {
    // Proteksi: Hanya Superadmin yang boleh tambah user
    if ($role_login !== 'superadmin') {
        header("Location: kelola-user.php?status=error&msg=access_denied");
        exit;
    }

    $username      = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password_raw  = $_POST['password'];
    $role          = mysqli_real_escape_string($koneksi, $_POST['role']);
    $nama_lengkap  = mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']);
    $password_hash = password_hash($password_raw, PASSWORD_DEFAULT);

    // Cek duplikasi username
    $check_query = mysqli_query($koneksi, "SELECT id FROM admin WHERE username='$username'");
    if (mysqli_num_rows($check_query) > 0) {
        header("Location: kelola-user.php?status=error&msg=username_exists");
        exit;
    }

    $insert_query = "INSERT INTO admin (username, password, role, nama_lengkap) VALUES ('$username', '$password_hash', '$role', '$nama_lengkap')";

    if (mysqli_query($koneksi, $insert_query)) {
        logAdminAction($koneksi, 'ADD_USER', "Menambah user baru: {$username} sebagai {$role}");
        header("Location: kelola-user.php?status=success&msg=user_added");
    } else {
        header("Location: kelola-user.php?status=error&msg=insert_failed");
    }
    exit;
}

// =======================================================
// 2. PROSES EDIT USER
// =======================================================
if (isset($_POST['edit_user'])) {
    $id            = (int)$_POST['id'];
    $username      = mysqli_real_escape_string($koneksi, $_POST['username']);
    $nama_lengkap  = mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']);
    $role_input    = mysqli_real_escape_string($koneksi, $_POST['role']); // Role dari form
    $new_pass      = $_POST['new_password'];

    // Ambil data asli target dari database untuk validasi
    $cek_target = mysqli_query($koneksi, "SELECT role, username FROM admin WHERE id='$id'");
    $target_data = mysqli_fetch_assoc($cek_target);

    if (!$target_data) {
        header("Location: kelola-user.php?status=error&msg=user_not_found");
        exit;
    }

    $target_role_old = $target_data['role'];

    // --- LOGIKA PROTEKSI SERVER-SIDE ---

    if ($role_login === 'admin') {
        // ADMIN: Hanya boleh edit dirinya sendiri
        if ($id !== $current_admin_id) {
            header("Location: kelola-user.php?status=error&msg=access_denied");
            exit;
        }
        // ADMIN: Tidak boleh mengubah role-nya sendiri (pakai role lama)
        $role_final = $target_role_old;
    } else {
        // SUPERADMIN:
        // 1. Tidak boleh edit Superadmin LAIN
        if ($target_role_old === 'superadmin' && $id !== $current_admin_id) {
            header("Location: kelola-user.php?status=error&msg=superadmin_protected");
            exit;
        }
        // 2. Tidak boleh mengubah role-nya sendiri
        if ($id === $current_admin_id) {
            $role_final = 'superadmin';
        } else {
            // Boleh ubah role orang lain (Admin/Normal)
            $role_final = $role_input;
        }
    }

    // Cek duplikasi username
    $check_dup = mysqli_query($koneksi, "SELECT id FROM admin WHERE username='$username' AND id != '$id'");
    if (mysqli_num_rows($check_dup) > 0) {
        header("Location: kelola-user.php?status=error&msg=username_exists");
        exit;
    }

    // Jalankan Update
    $update_query = "UPDATE admin SET username='$username', nama_lengkap='$nama_lengkap', role='$role_final' WHERE id='$id'";
    $success = mysqli_query($koneksi, $update_query);

    if (!empty($new_pass)) {
        $hash = password_hash($new_pass, PASSWORD_DEFAULT);
        mysqli_query($koneksi, "UPDATE admin SET password='$hash' WHERE id='$id'");
    }

    if ($success) {
        logAdminAction($koneksi, 'UPDATE_USER', "Mengubah data user ID: {$id} ({$username})");
        header("Location: kelola-user.php?status=success&msg=user_updated");
    } else {
        header("Location: kelola-user.php?status=error&msg=update_failed");
    }
    exit;
}

// =======================================================
// 3. PROSES HAPUS USER (Hanya Superadmin)
// =======================================================
if (isset($_POST['hapus_user'])) {
    if ($role_login !== 'superadmin') {
        header("Location: kelola-user.php?status=error&msg=access_denied");
        exit;
    }

    $id = (int)$_POST['id'];

    $cek_target = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT role, username FROM admin WHERE id='$id'"));

    // Proteksi: Tidak boleh hapus Superadmin (baik diri sendiri maupun orang lain)
    if ($cek_target['role'] === 'superadmin') {
        header("Location: kelola-user.php?status=error&msg=superadmin_protected_delete");
        exit;
    }

    if (mysqli_query($koneksi, "DELETE FROM admin WHERE id='$id'")) {
        logAdminAction($koneksi, 'DELETE_USER', "Menghapus user: {$cek_target['username']}");
        header("Location: kelola-user.php?status=success&msg=user_deleted");
    } else {
        header("Location: kelola-user.php?status=error&msg=delete_failed");
    }
    exit;
}

header("Location: kelola-user.php");
exit;