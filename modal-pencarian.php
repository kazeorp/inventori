<div class="modal fade" id="searchAssetModal" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow-lg">
            <div class="modal-header bg-dark text-white">
                <h6 class="modal-title"><i class="bi bi-box-seam me-2"></i>Pilih Aset Pengganti</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-3">
                    <div class="d-flex align-items-center gap-2">
                        <span>Tampilkan</span>
                        <select id="entriesSelect" class="form-select form-select-sm" style="width: 80px;">
                            <option value="5">5</option>
                            <option value="10" selected>10</option>
                            <option value="25">25</option>
                        </select>
                        <span>data</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="small fw-bold">Status:</span>
                        <select id="selectLoanStatus" class="form-select form-select-sm" style="width: 140px;">
                            <option value="">-- Semua --</option>
                            <?php
                            $loanable_statuses = ['Spare', 'Grace Period', 'MT', 'Pending Service'];
                            foreach ($loanable_statuses as $status) {
                                echo "<option value=\"$status\">$status</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="flex-grow-1" style="min-width: 200px; max-width: 300px;">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" id="searchTableInput" class="form-control border-start-0" placeholder="Cari hostname atau spek...">
                        </div>
                    </div>
                </div>

                <div class="table-responsive" style="min-height: 350px;">
                    <table class="table table-striped table-hover align-middle border">
                        <thead class="table-light border-bottom">
                            <tr>
                                <th style="width: 30%;" class="ps-3">Hostname</th>
                                <th style="width: 35%;">Spesifikasi</th>
                                <th style="width: 20%;">Status</th>
                                <th style="width: 15%;" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="assetTableBody">
                            </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3 border-top pt-3">
                    <div>
                        <small id="paginationInfo" class="text-muted fw-bold"></small>
                    </div>
                    <nav>
                        <ul class="pagination pagination-sm mb-0" id="paginationList">
                            </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>