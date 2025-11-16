// aktivitas-modal-handler.js (Final)

document.addEventListener('DOMContentLoaded', function() {

    // --- 1. REFERENSI ELEMEN DOM ---
    const toggleLoan = document.getElementById('toggleLoan');
    const loanSection = document.getElementById('loanSection');
    const selectLoanStatus = document.getElementById('selectLoanStatus');
    const selectLoanAset = document.getElementById('selectLoanAset');
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
    if (!activityModalElement || !toggleLoan || !selectLoanStatus || !selectLoanAset || !inputAksiSelect) {
        console.error("Salah satu elemen modal aktivitas tidak ditemukan.");
        return;
    }
// --------------------------------------------------------------------------------------

    // --- 2. FUNGSI FILTER DROPDOWN HOSTNAME (TIDAK BERUBAH) ---
    function updateLoanAsetDropdown(statusFilter) {
        selectLoanAset.innerHTML = '<option value="">-- Pilih Aset setelah Filter --</option>';

        if (loanAssetsData.length === 0) {
            selectLoanAset.innerHTML = '<option value="" disabled>-- Tidak ada aset valid tersedia. --</option>';
            return;
        }

        let filteredAssets = [];
        if (statusFilter === '' || statusFilter === 'available') {
            filteredAssets = [...loanAssetsData];
        } else {
            filteredAssets = loanAssetsData.filter(asset => asset.status === statusFilter);
        }

        if (filteredAssets.length > 0) {
            filteredAssets.sort((a, b) => {
                const indexA = priority.indexOf(a.status);
                const indexB = priority.indexOf(b.status);
                if (indexA !== indexB) { return indexA - indexB; }
                return a.hostname.localeCompare(b.hostname);
            });

            let currentGroup = null;
            filteredAssets.forEach(asset => {
                const status = asset.status;
                if (status !== currentGroup) {
                    if (currentGroup !== null) {
                        const separator = document.createElement('option');
                        separator.textContent = `--- STATUS: ${status.toUpperCase()} ---`;
                        separator.disabled = true;
                        selectLoanAset.appendChild(separator);
                    }
                    currentGroup = status;
                }
                const option = document.createElement('option');
                option.value = asset.hostname;
                option.textContent = `${asset.hostname} (${status})`;
                selectLoanAset.appendChild(option);
            });
        } else {
            selectLoanAset.innerHTML = `<option value="" disabled>-- Tidak ada aset yang cocok. --</option>`;
        }
    }
// --------------------------------------------------------------------------------------

    // --- 3. FUNGSI TOGGLE LOAN UTAMA ---
    function handleLoanToggle() {
        if (this.checked) {
            // Aksi Loan dipilih: TAMPILKAN OPSI LOAN & KUNCI SELECT
            loanSection.style.display = 'block';

            // Tampilkan opsi Loan & sembunyikan opsi Diservis
            document.querySelector('#inputAksiSelect option[value="Diservis"]').style.display = 'none';
            optionPendingService.style.display = 'block';
            optionScrap.style.display = 'block';

            // Set default Aksi ke Pending Service (atau Scrap, jika Anda ingin Scrap sebagai default)
            inputAksiSelect.value = 'Pending Service';
            inputAksiSelect.required = true;
            aksiHelpText.textContent = 'Pilih Status Aset Utama (WAJIB)';

            // Aktifkan input Loan
            selectLoanStatus.disabled = false;
            selectLoanAset.disabled = false;
            selectLoanAset.required = true;
            loanCatatan.disabled = false;

            // Isi dropdown saat loan diaktifkan
            updateLoanAsetDropdown(selectLoanStatus.value || 'available');

        } else {
            // Aksi Loan dibatalkan: TAMPILKAN OPSI SERVIS BIASA & BUKA KUNCI
            loanSection.style.display = 'none';

            // Sembunyikan opsi Loan & tampilkan opsi Diservis
            document.querySelector('#inputAksiSelect option[value="Diservis"]').style.display = 'block';
            optionPendingService.style.display = 'none';
            optionScrap.style.display = 'none';

            // Reset Aksi ke Diservis (default)
            inputAksiSelect.value = 'Diservis';
            // inputAksiSelect.required = true; // Biarkan tetap true jika Aksi selalu wajib
            aksiHelpText.textContent = "Aksi akan default ke 'Mencatat Servis' jika tanpa Loan.";

            // --- TAMBAHKAN INI UNTUK PEMBERSIHAN DATA ---
            selectLoanAset.value = ''; // Pastikan nilai dikosongkan
            loanCatatan.value = '';   // Pastikan catatan loan dikosongkan
            // --------------------------------------------

            // Nonaktifkan input Loan
            selectLoanStatus.disabled = true;
            selectLoanAset.disabled = true;
            selectLoanAset.required = false;
            loanCatatan.disabled = true;

            // Reset pilihan Aset B
            selectLoanStatus.value = '';
            selectLoanAset.innerHTML = '<option value="">-- Pilih Aset setelah Filter --</option>';
        }
    }
// --------------------------------------------------------------------------------------

    // --- 4. EVENT LISTENERS UTAMA ---

    toggleLoan.addEventListener('change', handleLoanToggle);

    selectLoanStatus.addEventListener('change', function() {
        updateLoanAsetDropdown(this.value);
    });
// --------------------------------------------------------------------------------------

    // --- 5. LOGIKA POP-UP MODAL DARI SERVICE CLAIM REDIRECT ---

    if (window.isServiceClaimRedirect === true) {

        const activityModal = new bootstrap.Modal(activityModalElement);

        // Atur default untuk skenario redirect: Loan dinonaktifkan
        toggleLoan.checked = false;

        // Panggil handler untuk menampilkan semua field Loan dan status Aset A
        handleLoanToggle.call(toggleLoan);

        // Tampilkan modal
        activityModal.show();

        // Bersihkan URL dari parameter 'action'
        if (window.history.replaceState) {
            const url = new URL(window.location.href);
            url.searchParams.delete('action');
            window.history.replaceState({path: url.href}, '', url.href);
        }
    }
// --------------------------------------------------------------------------------------

    // =================================================================
    // --- 6. FIX BACKDROP MODAL MACET & SCROLL TERKUNCI ---
    // =================================================================

    activityModalElement.addEventListener('hidden.bs.modal', function () {
        // Paksa reload halaman
        window.location.reload();
    });

    // --- 7. LOGIKA SUBMIT FORM AJAX ---
const formAktivitas = document.getElementById('formAktivitas'); 

if (formAktivitas) {
    formAktivitas.addEventListener('submit', function(e) {
        e.preventDefault(); // Mencegah submit tradisional
        
        const formData = new FormData(this);
        const submitButton = this.querySelector('button[type="submit"]');

        submitButton.disabled = true; // Nonaktifkan tombol saat loading

        fetch('tambah-histori.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            // Cek jika response OK (200), jika tidak lempar error
            if (!response.ok) {
                return response.json().then(errorData => {
                    throw new Error(errorData.message || 'Server Error. Cek kode PHP.');
                });
            }
            return response.json();
        })
        .then(data => {
            // Sukses atau Gagal (berdasarkan JSON)
            if (data.success) {
                alert('SUCCESS: ' + data.message);
            } else {
                alert('ERROR: ' + data.message);
            }
            
            // Lakukan Redirect setelah notifikasi
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