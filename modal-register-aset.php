<div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="registerForm" method="POST" action="register_service.php" autocomplete="off">
                <div class="modal-header bg-light text-dark">
                    <h5 class="modal-title" id="registerModalLabel"><i class="fas fa-plus-circle"></i> Registrasi Aset Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="closeRegisterModal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="regHostname" class="form-label fw-bold">Hostname Aset</label>
                        <input type="text" class="form-control" id="regHostname" name="hostname" required>
                    </div>
                    <div class="mb-3">
                        <label for="regNamaUser" class="form-label fw-bold">Nama Pengguna</label>
                        <input type="text" class="form-control" id="regNamaUser" name="nama_user" required>
                    </div>
                    <div class="mb-3">
                        <label for="regDivisi" class="form-label fw-bold">Divisi / Departemen</label>
                        <input type="text" class="form-control" id="regDivisi" name="divisi" required>
                    </div>
                    <input type="hidden" name="service_type" value="Register Aset Baru">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning"><i class="fas fa-paper-plane"></i> Submit Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
// Ambil parameter status dan pesan dari URL
$status_pesan = $_GET['status'] ?? '';
$pesan_raw = $_GET['msg'] ?? '';

if ($status_pesan && $pesan_raw) {
    // Bersihkan pesan dari underscore dan URL decode
    $pesan_bersih = htmlspecialchars(str_replace('_', ' ', urldecode($pesan_raw)));

    // Tentukan kelas alert berdasarkan status
    $alert_class = ($status_pesan === 'success') ? 'alert-success' : 'alert-danger';
    $alert_icon = ($status_pesan === 'success') ? '✅ Sukses' : '❌ Gagal';

    // Tampilkan Alert/Popup (Bootstrap Alert)
    echo '
        <div id="statusAlert" class="alert ' . $alert_class . ' alert-dismissible fade show fixed-top-alert" role="alert">
            <strong>' . $alert_icon . '!</strong> ' . $pesan_bersih . '
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <style>
            .fixed-top-alert {
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 1050;
                max-width: 400px;
            }
        </style>
        <script>
            // 1. Clean URL segera
            if (window.history.replaceState) {
                const url = new URL(window.location);
                url.searchParams.delete("status");
                url.searchParams.delete("msg");
                window.history.replaceState({path: url.href}, "", url.href);
            }

            // 2. Auto-hide Alert
            // ⚠️ Pastikan file JS Bootstrap sudah di-load sebelum kode ini dieksekusi!
            if (document.getElementById("statusAlert")) {
                const alertElement = document.getElementById("statusAlert");

                // Gunakan API Bootstrap untuk penutupan yang benar
                const bsAlert = new bootstrap.Alert(alertElement);

                setTimeout(function() {
                    bsAlert.close(); // Panggil metode close()
                }, 5000); // 5000ms = 5 detik
            }
        </script>
    ';
}
?>