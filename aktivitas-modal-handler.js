// aktivitas-modal-handler.js (Final dengan Fitur Search Modal)

document.addEventListener('DOMContentLoaded', function() {

    // --- Tambahkan state pagination di bagian atas DOMContentLoaded ---
    let currentPage = 1;
    let rowsPerPage = 10;
    let filteredData = [];

    // --- 1. REFERENSI ELEMEN DOM ---
    const toggleLoan = document.getElementById('toggleLoan');
    const loanSection = document.getElementById('loanSection');
    const selectLoanStatus = document.getElementById('selectLoanStatus');

    // Elemen Baru untuk Search Modal
    const btnCariAsetLoan = document.getElementById('btnCariAsetLoan');
    const displayLoanAset = document.getElementById('displayLoanAset');
    const loanIdInput = document.getElementById('loan_id_selected');
    const searchAssetModalEl = document.getElementById('searchAssetModal');
    const assetTableBody = document.getElementById('assetTableBody');
    const searchTableInput = document.getElementById('searchTableInput');

    const loanCatatan = document.getElementById('loanCatatan');

    // Elemen Aksi Tunggal
    const inputAksiSelect = document.getElementById('inputAksiSelect');
    const optionPendingService = document.getElementById('optionPendingService');
    const optionScrap = document.getElementById('optionScrap');
    const aksiHelpText = document.getElementById('aksiHelpText');

    const activityModalElement = document.getElementById('aktivitasModal');

    // Data aset yang valid untuk dipinjamkan
    const loanAssetsData = window.loanAssetsData || [];
    const priority = ['Spare', 'Grace Period', 'MT', 'Pending Service'];

    // Validasi elemen-elemen penting
    if (!activityModalElement || !toggleLoan || !selectLoanStatus || !inputAksiSelect) {
        console.error("Salah satu elemen modal aktivitas tidak ditemukan.");
        return;
    }

    // --- 2. FUNGSI RENDER TABEL DI DALAM MODAL PENCARIAN ---
function renderAssetTable() {
    if (!assetTableBody) return;

    const filterStatus = selectLoanStatus.value;
    const searchTerm = searchTableInput ? searchTableInput.value.toLowerCase() : '';

    // 1. Filter Data
    filteredData = window.loanAssetsData.filter(asset => {
        const matchStatus = (filterStatus === '' || filterStatus === 'available') ? true : asset.status === filterStatus;

        // Gabungkan semua spek untuk pencarian yang akurat
        const spekStr = `${asset.ram} ${asset.storage} ${asset.os}`.toLowerCase();
        const matchSearch = asset.hostname.toLowerCase().includes(searchTerm) || spekStr.includes(searchTerm);

        return matchStatus && matchSearch;
    });

    // 2. Sorting
    filteredData.sort((a, b) => {
        const priorityArr = ['Spare', 'Grace Period', 'MT', 'Pending Service'];
        const indexA = priorityArr.indexOf(a.status);
        const indexB = priorityArr.indexOf(b.status);
        if (indexA !== indexB) return indexA - indexB;
        return a.hostname.localeCompare(b.hostname);
    });

    // 3. Pagination Logic
    const totalItems = filteredData.length;
    const totalPages = Math.ceil(totalItems / rowsPerPage);
    if (currentPage > totalPages) currentPage = totalPages || 1;

    const startIndex = (currentPage - 1) * rowsPerPage;
    const paginatedItems = filteredData.slice(startIndex, startIndex + rowsPerPage);

    // 4. Render Rows
    assetTableBody.innerHTML = '';

    if (paginatedItems.length === 0) {
        assetTableBody.innerHTML = `<tr><td colspan="4" class="text-center py-5 text-muted italic">Tidak ada aset tersedia yang cocok.</td></tr>`;
    } else {
        paginatedItems.forEach(asset => {
            const tr = document.createElement('tr');

            // Format Spesifikasi agar rapi (RAM / Storage / Windows)
            const spekFormatted = `
                <div class="d-flex flex-column">
                    <span class="small text-dark fw-semibold">${asset.ram || '-'} GB RAM</span>
                    <span class="text-muted" style="font-size: 0.75rem;">${asset.storage || '-'} | Win ${asset.os || '-'}</span>
                </div>
            `;

            tr.innerHTML = `
                <td class="ps-3 fw-bold text-primary">${asset.hostname}</td>
                <td>${spekFormatted}</td>
                <td><span class="badge rounded-pill bg-info text-dark" style="font-size: 0.7rem;">${asset.status}</span></td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-primary select-asset-btn px-3"
                        data-id="${asset.id}" data-hostname="${asset.hostname}">
                        Pilih
                    </button>
                </td>
            `;
            assetTableBody.appendChild(tr);
        });

        // --- BAGIAN PENTING: Event Listener untuk tombol yang baru dibuat ---
document.querySelectorAll('.select-asset-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.getAttribute('data-id');
        const hostname = this.getAttribute('data-hostname');

        // 1. Isi input di modal utama
        if (displayLoanAset) displayLoanAset.value = hostname;
        if (loanIdInput) loanIdInput.value = id;

        // 2. Tutup modal pencarian
        const modalInstance = bootstrap.Modal.getInstance(searchAssetModalEl);
        if (modalInstance) {
            modalInstance.hide();
        }

        console.log("Aset dipilih:", hostname, "ID:", id); // Untuk debugging
    });
});
    }

    updatePaginationUI(totalItems, totalPages);
}

    function updatePaginationUI(totalItems, totalPages) {
        const info = document.getElementById('paginationInfo');
        const list = document.getElementById('paginationList');

        const start = totalItems === 0 ? 0 : (currentPage - 1) * rowsPerPage + 1;
        const end = Math.min(currentPage * rowsPerPage, totalItems);
        info.innerText = `Showing ${start} to ${end} of ${totalItems} entries`;

        list.innerHTML = '';
        // Tombol Previous
        list.innerHTML += `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="changePage(${currentPage - 1})">Previous</a>
        </li>`;

        // Tombol Angka (Sederhana)
        for (let i = 1; i <= totalPages; i++) {
            if (i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
                list.innerHTML += `<li class="page-item ${i === currentPage ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="changePage(${i})">${i}</a>
                </li>`;
            }
        }

        // Tombol Next
        list.innerHTML += `<li class="page-item ${currentPage === totalPages || totalPages === 0 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="changePage(${currentPage + 1})">Next</a>
        </li>`;
    }

    // Global function agar bisa dipanggil dari onclick HTML
    window.changePage = function(page) {
        event.preventDefault();
        currentPage = page;
        renderAssetTable();
    }

    // Tambahkan Listener untuk Show Entries
    document.getElementById('entriesSelect').addEventListener('change', function() {
        rowsPerPage = parseInt(this.value);
        currentPage = 1;
        renderAssetTable();
    });

    // --- 3. FUNGSI TOGGLE LOAN UTAMA ---
    function handleLoanToggle() {
        if (this.checked) {
            loanSection.style.display = 'block';

            document.querySelector('#inputAksiSelect option[value="Diservis"]').style.display = 'none';
            optionPendingService.style.display = 'block';
            optionScrap.style.display = 'block';

            inputAksiSelect.value = 'Pending Service';
            inputAksiSelect.required = true;
            aksiHelpText.textContent = 'Pilih Status Aset Utama (WAJIB)';

            // Aktifkan input & tombol Loan
            selectLoanStatus.disabled = false;
            btnCariAsetLoan.disabled = false;
            displayLoanAset.disabled = false;
            displayLoanAset.required = true;
            loanCatatan.disabled = false;

        } else {
            loanSection.style.display = 'none';

            document.querySelector('#inputAksiSelect option[value="Diservis"]').style.display = 'block';
            optionPendingService.style.display = 'none';
            optionScrap.style.display = 'none';

            inputAksiSelect.value = 'Diservis';
            aksiHelpText.textContent = "Aksi akan default ke 'Mencatat Servis' jika tanpa Loan.";

            // Reset & Nonaktifkan input Loan
            if (displayLoanAset) displayLoanAset.value = '';
            if (loanIdInput) loanIdInput.value = '';
            loanCatatan.value = '';

            selectLoanStatus.disabled = true;
            btnCariAsetLoan.disabled = true;
            displayLoanAset.disabled = true;
            displayLoanAset.required = false;
            loanCatatan.disabled = true;
            selectLoanStatus.value = '';
        }
    }

    // --- 4. EVENT LISTENERS ---

    toggleLoan.addEventListener('change', handleLoanToggle);

    // Filter Status berubah, reset pilihan aset yang sedang tampil di input
    selectLoanStatus.addEventListener('change', function() {
        if (displayLoanAset) displayLoanAset.value = '';
        if (loanIdInput) loanIdInput.value = '';
    });

    // Buka Search Modal
    if (btnCariAsetLoan && searchAssetModalEl) {
        const searchAssetModal = new bootstrap.Modal(searchAssetModalEl);
        btnCariAsetLoan.addEventListener('click', function() {
            renderAssetTable();
            searchAssetModal.show();
        });
    }

    // Live Search di dalam modal
    if (searchTableInput) {
        searchTableInput.addEventListener('input', renderAssetTable);
    }

    // --- 5. LOGIKA POP-UP MODAL DARI SERVICE CLAIM REDIRECT ---
    if (window.isServiceClaimRedirect === true) {
        const activityModal = new bootstrap.Modal(activityModalElement);
        toggleLoan.checked = false;
        handleLoanToggle.call(toggleLoan);
        activityModal.show();

        if (window.history.replaceState) {
            const url = new URL(window.location.href);
            url.searchParams.delete('action');
            window.history.replaceState({path: url.href}, '', url.href);
        }
    }

    // --- 6. FIX BACKDROP MODAL ---
    activityModalElement.addEventListener('hidden.bs.modal', function () {
        window.location.reload();
    });

    // --- 7. LOGIKA SUBMIT FORM AJAX ---
    const formAktivitas = document.getElementById('formAktivitas');
    if (formAktivitas) {
        formAktivitas.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const submitButton = this.querySelector('button[type="submit"]');
            submitButton.disabled = true;

            fetch('tambah-histori.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(errorData => {
                        throw new Error(errorData.message || 'Server Error. Cek kode PHP.');
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    alert('SUCCESS: ' + data.message);
                } else {
                    alert('ERROR: ' + data.message);
                }

                if (data.redirect) {
                    window.location.href = data.redirect;
                }
            })
            .catch(error => {
                console.error('AJAX Submit Error:', error);
                alert('Proses Gagal Total: ' + error.message);
            })
            .finally(() => {
                submitButton.disabled = false;
            });
        });
    }
});