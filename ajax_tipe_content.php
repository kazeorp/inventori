<?php
include 'session.php';
include 'koneksi.php';

// Pastikan hanya Superadmin yang bisa mengakses
if (($_SESSION['role'] ?? 'normal') !== 'superadmin') {
    http_response_code(403);
    die("Akses ditolak.");
}

function e($text)
{
    return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
}

// 1. Ambil data tipe
// KOREKSI NAMA KOLOM: Menggunakan type_name
$query_tipe = "SELECT id, type_name FROM device_types ORDER BY type_name ASC";
$result_tipe = mysqli_query($koneksi, $query_tipe);

// PEMERIKSAAN ERROR
if (!$result_tipe) {
    http_response_code(500);
    echo '<div class="alert alert-danger"> Query Gagal. Periksa tabel di database Anda. Error: ' . e(mysqli_error($koneksi)) . '</div>';
    exit;
}

// 2. Tampilkan Form Tambah
?>
<div class="card mb-4 bg-light shadow-sm">
    <div class="card-body">
        <h6 class="card-title text-primary"> Tambah Tipe Baru</h6>
        <form id="form-tambah-tipe" method="POST" action="proses-tipe-laptop.php">
            <input type="hidden" name="action" value="tambah">
            <div class="row g-2">
                <div class="col-md-9">
                    <input type="text" class="form-control" name="nama_tipe" placeholder="Nama Tipe (Contoh: Laptop HP)" required>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100" name="submit_tambah_tipe">Tambah</button>
                </div>
                <input type="hidden" name="deskripsi" value="">
            </div>
        </form>
    </div>
</div>

<?php
// 3. Tampilkan Tabel Tipe yang Sudah Ada
if (mysqli_num_rows($result_tipe) > 0):
    ?>
<div class="table-responsive">
    <table class="table table-sm table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>Nama Tipe</th>
                <th style="width: 150px;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($result_tipe)): ?>
            <tr data-id="<?= e($row['id']) ?>" data-tipe="<?= e($row['type_name']) ?>" data-desc="">
                <td><?= e($row['type_name']) ?></td>
                <td>
                    <button type="button" class="btn btn-sm btn-info text-white btn-edit-tipe me-1"
                        data-bs-toggle="modal" data-bs-target="#editTipeModal">
                        Edit
                    </button>
                    <button type="button" class="btn btn-sm btn-danger btn-delete-tipe">
                        Hapus
                    </button>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
<?php else: ?>
<div class="alert alert-info text-center">Belum ada tipe inventori yang tersimpan.</div>
<?php endif; ?>