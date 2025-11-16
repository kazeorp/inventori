<?php
// modal-aktivitas.php (Di-include dari detail_aset.php)
// Pastikan $koneksi dan $aset (dari detail_aset.php) tersedia di scope ini

$id_aset_saat_ini = (int)($aset['id'] ?? 0);
$hostname_saat_ini = e($aset['hostname'] ?? 'N/A');

// DAFTAR STATUS YANG VALID UNTUK DI-LOAN (sebagai aset pengganti)
$loanable_statuses = ['Spare', 'Grace Period', 'MT', 'Pending Service'];
$status_list = "'" . implode("','", $loanable_statuses) . "'";

// QUERY ASET YANG VALID UNTUK DI-LOAN
$all_loan_query = mysqli_query($koneksi, "
    SELECT id, hostname, status
    FROM inventori
    WHERE status IN ($status_list)
      AND id != {$id_aset_saat_ini}
    ORDER BY hostname ASC
");

$all_loan_assets = [];
if ($all_loan_query) {
    while ($row = mysqli_fetch_assoc($all_loan_query)) {
        $all_loan_assets[] = $row;
    }
}
?>

<div class="modal fade" id="aktivitasModal" tabindex="-1" aria-labelledby="aktivitasModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="formAktivitas" action="tambah-histori.php" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="aktivitasModalLabel">Input Aktivitas Aset: <span id="modalAsetHostname"><?= $hostname_saat_ini ?></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="inventori_id" value="<?= e($aset['id'] ?? '') ?>">
                    <input type="hidden" name="hostname" value="<?= e($aset['hostname'] ?? '') ?>">
                    <input type="hidden" name="oleh" value="<?= e($_SESSION['nama_lengkap'] ?? $_SESSION['username'] ?? 'Admin') ?>">

                    <input type="hidden" name="id_service" value="<?= e($id_service_terkait ?? '0') ?>">

                    <div class="row">
                        <div class="col-md-6 border-end">
                            <h6>Aktivitas Utama (Aset yang Discanned)</h6>

                            <div class="mb-3">
                                <label for="inputAdminDisplay" class="form-label">PIC / Admin</label>
                                <input type="text" class="form-control" value="<?= e($_SESSION['nama_lengkap'] ?? $_SESSION['username'] ?? 'Admin') ?>" disabled>
                            </div>

                            <div class="mb-3">
                                <label for="inputAksiSelect" class="form-label">Aksi Status Aset A</label>
                                <select class="form-select" id="inputAksiSelect" name="new_status" required>
                                    <option value="Diservis">Normal Service</option>
                                    <option value="Pending Service" id="optionPendingService" style="display:none;">Pending Service</option>
                                    <option value="Scrap" id="optionScrap" style="display:none;">Scrap (Rusak Total)</option>
                                </select>
                                <div class="form-text" id="aksiHelpText">Aksi akan default ke 'Normal Service' jika tanpa Loan.</div>
                            </div>

                            <div class="mb-3">
                                <label for="inputTiket" class="form-label">Nomor Tiket / WO</label>
                                <input type="text" class="form-control" id="inputTiket" name="ticket" required>
                            </div>

                            <div class="mb-3">
                                <label for="inputCatatan" class="form-label">Catatan Aktivitas</label>
                                <textarea class="form-control" id="inputCatatan" name="catatan" rows="2" required></textarea>
                            </div>

                            <hr class="mt-4 mb-3">

                            <div class="form-check form-switch mb-4">
                                <input class="form-check-input" type="checkbox" id="toggleLoan" name="toggle_loan">
                                <label class="form-check-label text-danger fw-bold" for="toggleLoan">Aset Ini Membutuhkan Aset Pengganti (LOAN)</label>
                                <div class="form-text">Jika diaktifkan, status aset ini akan segera diperbarui.</div>
                            </div>
                        </div>

                        <div class="col-md-6" id="loanSection" style="display: none;">
                            <h6>Pilih Aset Pengganti (Aset B - Status: LOAN)</h6>

                            <input type="hidden" name="loan_nik" value="<?= e($aset['nik'] ?? '') ?>">
                            <input type="hidden" name="loan_nama" value="<?= e($aset['nama'] ?? '') ?>">
                            <input type="hidden" name="loan_divisi" value="<?= e($aset['divisi'] ?? '') ?>">

                            <div class="alert alert-info py-2">
                                <small>Dipinjamkan ke: <?= e($aset['nama'] ?? 'N/A') ?> (<?= e($aset['nik'] ?? 'N/A') ?>)</small>
                            </div>

                            <div class="mb-3">
                                <label for="selectLoanStatus" class="form-label">Filter Status Aset Pengganti</label>
                                <select class="form-select" id="selectLoanStatus" name="loan_filter_status" required disabled>
                                    <option value="">-- Pilih Status --</option>
                                    <option value="available" class="fw-bold">Aset Tersedia (Semua Status Valid)</option>

                                    <?php
                                    foreach ($loanable_statuses as $status) {
                                        echo "<option value=\"$status\">$status</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="selectLoanAset" class="form-label">Pilih Aset Pengganti (Hostname)</label>
                                <select class="form-select" id="selectLoanAset" name="loan_hostname" required disabled>
                                    <option value="">-- Pilih Aset setelah Filter --</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="loanCatatan" class="form-label">Catatan Loan (Opsional)</label>
                                <textarea class="form-control" id="loanCatatan" name="loan_catatan" rows="1" disabled></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="btnBatalAktivitas">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnSubmitAktivitas">Simpan Aktivitas</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    window.loanAssetsData = <?= json_encode($all_loan_assets); ?>;
</script>