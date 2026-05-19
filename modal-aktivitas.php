<?php
// modal-aktivitas.php (Di-include dari detail_aset.php)
require_once 'koneksi.php';

$id_aset_saat_ini = (int) ($aset['id'] ?? 0);
$hostname_saat_ini = e($aset['hostname'] ?? 'N/A');

// DAFTAR STATUS YANG VALID UNTUK DI-LOAN (sebagai aset pengganti)
$loanable_statuses = ['Spare', 'Grace Period', 'MT', 'Pending Service'];
$status_list = "'" . implode("','", $loanable_statuses) . "'";

// QUERY ASET YANG VALID UNTUK DI-LOAN
$all_loan_query = mysqli_query($koneksi, "
    SELECT id, hostname, status, ram, storage, win AS os
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
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: var(--radius-md, 8px);">
            <form id="formAktivitas" action="tambah-histori.php" method="POST">
                <div class="modal-header bg-dark text-white px-4 py-3">
                    <h5 class="modal-title fw-bold" id="aktivitasModalLabel"><i class="bi bi-activity me-2"></i> Log Aktivitas: <?= $hostname_saat_ini ?></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 bg-light">
                    <input type="hidden" name="inventori_id" value="<?= e($aset['id'] ?? '') ?>">
                    <input type="hidden" name="hostname" value="<?= e($aset['hostname'] ?? '') ?>">
                    <input type="hidden" name="oleh" value="<?= e($_SESSION['nama_lengkap'] ?? $_SESSION['username'] ?? 'Admin') ?>">
                    <input type="hidden" name="id_service" id="modal_id_service" value="<?= e($_GET['id_service'] ?? '0') ?>">

                    <div class="row g-4">
                        <!-- Column 1: Scanned Asset Activity -->
                        <div class="col-md-6 border-md-end border-bottom-0 pb-md-0 pb-4">
                            <div class="d-flex align-items-center mb-3">
                                <span class="badge bg-primary-soft text-primary p-2 rounded-circle me-2"><i class="bi bi-pc-display fs-5"></i></span>
                                <h6 class="fw-bold m-0 text-uppercase small text-muted" style="letter-spacing: 0.5px;">Aktivitas Utama</h6>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold small">TECHNICIAN / PIC</label>
                                <input type="text" class="form-control form-control-sm bg-white" value="<?= e($_SESSION['nama_lengkap'] ?? $_SESSION['username'] ?? 'Admin') ?>" disabled>
                            </div>

                            <div class="mb-3">
                                <label for="inputAksiSelect" class="form-label fw-bold small">PEMBARUAN STATUS</label>
                                <select class="form-select form-select-sm shadow-sm" id="inputAksiSelect" name="new_status" required>
                                    <option value="Diservis">Service Maintenance (Default)</option>
                                    <option value="Pending Service" id="optionPendingService" style="display:none;">Pending Service (Masuk Gudang)</option>
                                    <option value="Scrap" id="optionScrap" style="display:none;">Scrap (Aset Dirusak)</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="inputTiket" class="form-label fw-bold small">NOMOR TIKET</label>
                                <input type="text" class="form-control form-control-sm shadow-sm text-uppercase" id="inputTiket" name="ticket" placeholder="Contoh: SRV-202312001" required>
                            </div>

                            <div class="mb-0">
                                <label for="inputCatatan" class="form-label fw-bold small">CATATAN TINDAKAN</label>
                                <textarea class="form-control form-control-sm shadow-sm" id="inputCatatan" name="catatan" rows="3" placeholder="Apa yang dilakukan pada aset ini?" required></textarea>
                            </div>

                            <div class="mt-4 p-3 rounded border border-danger-subtle bg-white shadow-sm">
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input" type="checkbox" id="toggleLoan" name="toggle_loan">
                                    <label class="form-check-label text-danger fw-bold small" for="toggleLoan">AKTIFKAN PROSES LOAN (PINJAMAN)</label>
                                </div>
                            </div>
                        </div>

                        <!-- Column 2: Loan/Replacement Asset (Hidden by default) -->
                        <div class="col-md-6" id="loanSection" style="display: none; animation: fadeIn 0.3s ease;">
                            <div class="d-flex align-items-center mb-3">
                                <span class="badge bg-info-soft text-info p-2 rounded-circle me-2"><i class="bi bi-arrow-repeat fs-5"></i></span>
                                <h6 class="fw-bold m-0 text-uppercase small text-muted" style="letter-spacing: 0.5px;">Aset Pengganti (Loan)</h6>
                            </div>

                            <input type="hidden" name="loan_nik" value="<?= e($aset['nik'] ?? '') ?>">
                            <input type="hidden" name="loan_nama" value="<?= e($aset['nama'] ?? '') ?>">
                            <input type="hidden" name="loan_divisi" value="<?= e($aset['divisi'] ?? '') ?>">

                            <div class="alert bg-info-soft border-info-subtle p-2 mb-3">
                                <small class="text-info fw-bold"><i class="bi bi-info-circle me-1"></i> Dipinjamkan ke:</small>
                                <div class="small text-dark mt-1"><?= e($aset['nama'] ?? 'N/A') ?> (<?= e($aset['nik'] ?? 'N/A') ?>)</div>
                            </div>

                            <div class="mb-3">
                                <label for="selectLoanAset" class="form-label fw-bold small">PILIH UNIT PENGGANTI</label>
                                <div class="input-group input-group-sm">
                                    <input type="hidden" id="loan_id_selected" name="loan_id">
                                    <input type="text" class="form-control bg-white shadow-sm" id="displayLoanAset" name="loan_hostname" placeholder="Pilih unit pengganti..." readonly required>
                                    <button class="btn btn-dark" type="button" id="btnCariAsetLoan" title="Cari di master data">
                                        <i class="bi bi-search"></i> Cari
                                    </button>
                                </div>
                            </div>

                            <div class="mb-0">
                                <label for="loanCatatan" class="form-label fw-bold small">INFO TAMBAHAN LOAN</label>
                                <textarea class="form-control form-control-sm shadow-sm" id="loanCatatan" name="loan_catatan" rows="2" placeholder="Catatan khusus untuk unit pengganti..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light px-4 py-3">
                    <button type="button" class="btn btn-outline-secondary px-4 fw-bold shadow-sm" data-bs-dismiss="modal" id="btnBatalAktivitas">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm" id="btnSubmitAktivitas">
                        <i class="bi bi-check-circle me-1"></i> Simpan Aktivitas
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
.bg-primary-soft { background-color: rgba(13, 110, 253, 0.1); }
.bg-info-soft { background-color: rgba(13, 202, 240, 0.1); }
</style>

<script>
    window.loanAssetsData = <?= json_encode($all_loan_assets); ?>;

    document.addEventListener('DOMContentLoaded', function() {
        const toggleLoan = document.getElementById('toggleLoan');
        const loanSection = document.getElementById('loanSection');
        const inputAksi = document.getElementById('inputAksiSelect');

        // Options to toggle
        const optPending = document.getElementById('optionPendingService');
        const optScrap = document.getElementById('optionScrap');

        // Loan related inputs
        const loanInputs = loanSection.querySelectorAll('input, select, textarea, button');

        function updateAktivitasUI() {
            const isLoan = toggleLoan.checked;

            // 1. Handle Section Visibility and Disabled state
            loanSection.style.display = isLoan ? 'block' : 'none';
            loanInputs.forEach(el => {
                el.disabled = !isLoan;
            });

            // 2. Handle Status Options based on Loan toggle
            if (isLoan) {
                optPending.style.display = 'block';
                optScrap.style.display = 'block';
                // If current selection is default, switch to Pending Service for better flow
                if (inputAksi.value === 'Diservis') {
                    inputAksi.value = 'Pending Service';
                }
            } else {
                optPending.style.display = 'none';
                optScrap.style.display = 'none';
                inputAksi.value = 'Diservis';
            }
        }

        if (toggleLoan) {
            toggleLoan.addEventListener('change', updateAktivitasUI);
            // Initial check
            updateAktivitasUI();
        }
    });
</script>