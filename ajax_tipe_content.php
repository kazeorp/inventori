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
<div class="card mb-4 bg-light shadow-sm d-none" id="card-tambah-tipe" style="border: 1px solid rgba(0,0,0,0.05);">
    <div class="card-body">
        <h6 class="card-title text-primary fw-bold mb-3 small text-uppercase"><i class="bi bi-plus-circle me-1"></i> Input Tipe Baru</h6>
        <form id="form-tambah-tipe" method="POST" action="proses-tipe-laptop.php">
            <input type="hidden" name="action" value="tambah">
            <div class="row g-2">
                <div class="col-md-8">
                    <input type="text" class="form-control form-control-sm uppercase-input" name="nama_tipe" placeholder="Nama Tipe (Contoh: Laptop HP)" required oninput="this.value = this.value.toUpperCase()">
                </div>
                <div class="col-md-4 text-end">
                    <button type="submit" class="btn btn-sm btn-primary px-3" name="submit_tambah_tipe">Simpan</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="document.getElementById('card-tambah-tipe').classList.add('d-none')">Batal</button>
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
<h6 class="fw-bold text-muted mb-3 small text-uppercase" style="letter-spacing: 1px;">Daftar Tipe Tersedia</h6>
<div class="table-responsive">
    <table class="table table-sm table-hover border">
        <thead class="table-light border-bottom">
            <tr>
                <th class="ps-3">Nama Tipe / Merk</th>
                <th style="width: 150px;" class="text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($result_tipe)): ?>
            <tr data-id="<?= e($row['id']) ?>" data-tipe="<?= e($row['type_name']) ?>" data-desc="">
                <td><?= e($row['type_name']) ?></td>
                <td>
                    <button type="button" class="btn btn-sm btn-outline-info btn-edit-tipe me-1"
                        data-bs-toggle="modal" data-bs-target="#editTipeModal">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger btn-delete-tipe">
                        <i class="bi bi-trash"></i>
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