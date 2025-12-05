// main.js - VERSI FINAL TERINTEGRASI PENUH (TERMASUK LOGIKA EDIT MODAL BARU)

document.addEventListener('DOMContentLoaded', function() {
    console.log('JS DEBUG: main.js loaded. All system listeners registered.');

    // =======================================================
    // 1. DEFINISI MAPPING & FUNGSI PEMBANTU
    // =======================================================

    // Definisi Mapping Status ke Rak
    const statusToRak = {
        "Spare": "GD-R11",
        "Pending Service": "GD-R12",
        "Grace Period": "GD-R13",
        "Scrap": "GD-R4",
        "MT": "GD-R8",
        "Ready to Assign": "GD-R9",
        "Assign": "Assign",
        "Loan": "Loan"
    };

    // Peta Standar Kelengkapan
    const standardMap = {
        "ADAPTOR,TAS": "TAS DAN ADAPTOR",
        "CONVERTERVGA,TAS": "TAS DAN CONVERTER VGA",
        "CONVERTERLAN,TAS": "TAS DAN CONVERTER LAN",
        "ADAPTOR,CONVERTERLAN,TAS": "TAS, ADAPTOR, CONVERTER LAN",
        "ADAPTOR,CONVERTERVGA,TAS": "TAS, ADAPTOR, CONVERTER VGA",
        "ADAPTOR,CONVERTERLAN,CONVERTERVGA,TAS": "TAS, ADAPTOR, CONVERTER LAN & VGA",
        "ADAPTOR,CONVERTERLAN,TAS,VGA": "TAS, ADAPTOR, CONVERTER LAN & VGA",
        "ADAPTOR": "ADAPTOR",
        "TAS": "TAS",
        "": ""
    };

    // Fungsi Pembantu 1: Membersihkan dan menormalkan string mentah (untuk kelengkapan)
    function cleanAndNormalize(str) {
        if (!str || str === '0' || str === '0000-00-00' || str === '0000-00-00 00:00:00') return '';

        let cleaned = str.toUpperCase().trim();
        cleaned = cleaned.replace(/,\s*|\s*,\s*|\s*&amp;\s*|\s*&\s*|\s*DAN\s*/g, ',');
        cleaned = cleaned.replace(/,,+/g, ',').replace(/\s+/g, '').replace(/^,|,$/g, '');

        return cleaned;
    }

    // Fungsi Pembantu 2: Mencari nilai standar Kelengkapan
    function normalizeKelengkapan(inputString) {
        if (!inputString) return '';

        let cleaned = cleanAndNormalize(inputString);
        let items = cleaned.split(',').sort();
        let sortedKey = items.join(',');

        const result = standardMap[sortedKey];
        return result || '';
    }

    // Fungsi Pembantu 3: Capitalize string (untuk Kategori Perangkat)
    function capitalize(str) {
        if (!str || str === '0' || str === '0000-00-00' || str === '0000-00-00 00:00:00') return '';
        return str.toLowerCase().split(' ').map(word => {
            return word.charAt(0).toUpperCase() + word.slice(1);
        }).join(' ');
    }

    // Fungsi Pembantu 4: Mengubah format DATETIME menjadi DATE (YYYY-MM-DD)
    function normalizeDateTimeToDate(dateTimeStr) {
        if (!dateTimeStr || dateTimeStr === '0000-00-00 00:00:00' || dateTimeStr === '0000-00-00') {
            return '';
        }
        return dateTimeStr.split(' ')[0];
    }

    // =======================================================
    // 2. LOGIKA OTOMATISASI RAK (Modal Tambah & Edit)
    // =======================================================
    const addModal = document.getElementById('addModal');
    const statusSelectAdd = document.getElementById('add-status');
    const rakInputAdd = document.getElementById('add-rak');
    if (statusSelectAdd && rakInputAdd) {
        statusSelectAdd.addEventListener('change', function () {
            rakInputAdd.value = statusToRak[this.value] || "";
        });
        console.log("JS DEBUG: Rak Automation Listener Registered for Add Modal.");
    }

    // Mendapatkan elemen di awal untuk digunakan di Bagian 3
    const statusSelectEdit = document.getElementById('edit-status');
    const rakInputEdit = document.getElementById('edit-rak');

    if (statusSelectEdit && rakInputEdit) {
        statusSelectEdit.addEventListener('change', function () {
            rakInputEdit.value = statusToRak[this.value] || "";
        });
        console.log("JS DEBUG: Rak Automation Listener Registered for Edit Modal.");
    }

    // =======================================================
    // 3. LOGIKA PENGISIAN MODAL EDIT INVENTORI (BARU & LENGKAP)
    // =======================================================

    // Asumsi: userRole didefinisikan secara global di luar script ini, misal: <script>const userRole = 'admin';</script>
    const editButtons = document.querySelectorAll('.edit-btn');
    editButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            const modal = document.getElementById('editModal');
            if (!modal) return;

            // --- PENGAMBILAN DATA DENGAN NORMALISASI ---

            // Kelengkapan
            const kelengkapanRaw = btn.getAttribute('data-kelengkapan');
            const dataKelengkapan = normalizeKelengkapan(kelengkapanRaw);

            // Kategori Perangkat
            const categoryRaw = btn.getAttribute('data-device_category');
            const dataDeviceCategory = categoryRaw ? capitalize(categoryRaw) : '';

            // Tanggal
            const tglMasukRaw = btn.getAttribute('data-tanggal_masuk');
            const dataTglMasuk = normalizeDateTimeToDate(tglMasukRaw);

            const tglKeluarRaw = btn.getAttribute('data-tanggal_keluar');
            const dataTglKeluar = normalizeDateTimeToDate(tglKeluarRaw);


            // --- PENGISIAN DATA KE MODAL ---

            // Input fields (menggunakan this.dataset)
            modal.querySelector('#edit-id').value = this.dataset.id;
            modal.querySelector('#edit-hostname').value = this.dataset.hostname;
            // Rak diisi dari dataset, akan di-override jika status diubah (oleh Bagian 2)
            modal.querySelector('#edit-rak').value = this.dataset.rak;
            modal.querySelector('#edit-ram').value = this.dataset.ram;
            modal.querySelector('#edit-storage').value = this.dataset.storage;
            modal.querySelector('#edit-win').value = this.dataset.win;
            modal.querySelector('#edit-keterangan').value = this.dataset.keterangan;
            modal.querySelector('#edit-nik').value = this.dataset.nik;
            modal.querySelector('#edit-nama').value = this.dataset.nama;
            modal.querySelector('#edit-divisi').value = this.dataset.divisi;

            // Select fields
            modal.querySelector('#edit-status').value = this.dataset.status;
            modal.querySelector('#edit-type').value = this.dataset.type;
            modal.querySelector('#edit-domain').value = this.dataset.domain;

            // Pengisian Select Field (Kelengkapan & Kategori)
            modal.querySelector('#edit-kelengkapan').value = dataKelengkapan;
            modal.querySelector('#edit-device_category').value = dataDeviceCategory;

            // Pengisian Tanggal
            modal.querySelector('#edit-tanggal_masuk').value = dataTglMasuk;
            modal.querySelector('#edit-tanggal_keluar').value = dataTglKeluar;


            // Isi link tombol aksi
            modal.querySelector('#detailBtn').href = 'detail-aset.php?id=' + this.dataset.id;
            modal.querySelector('#deleteBtn').href = 'hapus.php?id=' + this.dataset.id;

            //  Cek Hak Akses
            const role = typeof userRole !== 'undefined' ? userRole : 'normal';

            const elementsToDisable = modal.querySelectorAll('input, select, textarea');
            const submitBtn = modal.querySelector('button[type="submit"]');
            const deleteBtn = modal.querySelector('#deleteBtn');
            const detailBtn = modal.querySelector('#detailBtn');

            if (role === 'normal') {
                elementsToDisable.forEach(el => {
                    el.setAttribute('readonly', true);
                    el.setAttribute('disabled', true);
                });

                if (submitBtn) submitBtn.style.display = 'none';
                if (deleteBtn) deleteBtn.style.display = 'none';
                if (detailBtn) detailBtn.style.display = 'none';
            } else {
                elementsToDisable.forEach(el => {
                    // Hanya rak yang tetap readonly
                    if (el.id !== 'edit-rak') {
                        el.removeAttribute('readonly');
                        el.removeAttribute('disabled');
                    }
                });
                if (rakInputEdit) rakInputEdit.setAttribute('readonly', true); // Pastikan Rak tetap readonly

                if (submitBtn) submitBtn.style.display = 'inline-block';
                // Kontrol tampilan tombol Delete dan Detail (asumsi tombol Detail selalu visible untuk admin)
                if (deleteBtn) deleteBtn.style.display = 'inline-block';
                if (detailBtn) detailBtn.style.display = 'inline-block';
            }
        });
    });

    //  Konfirmasi hapus (Fungsi Global)
    window.confirmDelete = function () {
        return confirm("Yakin ingin menghapus data ini? Tindakan ini tidak bisa dibatalkan.");
    };

    // =======================================================
    // 4. LOGIKA REASSIGN SERVICE
    // =======================================================
    const reassignModalElement = document.getElementById('reassignModal');
    if (reassignModalElement) {
        reassignModalElement.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const serviceId = button.getAttribute('data-id');
            const currentAdminName = button.getAttribute('data-current-admin-name');

            document.getElementById('reassign-service-id').textContent = serviceId;
            document.getElementById('reassign-service-input').value = serviceId;
            document.getElementById('current-handler-name').textContent = currentAdminName;

            const formReassign = document.getElementById('form-reassign');
            if (formReassign) {
                formReassign.reset();
                const submitButton = formReassign.querySelector('button[type="submit"]');
                if(submitButton) {
                    submitButton.disabled = false;
                    submitButton.textContent = 'Reassign';
                }
            }
            console.log(`JS DEBUG: Reassign Modal Opened for Service ID: ${serviceId}`);
        });
    }

    const formReassign = document.getElementById('form-reassign');
    if (formReassign) {
        formReassign.addEventListener('submit', function(e) {
            e.preventDefault();

            const newAdminId = document.getElementById('new_admin_id').value;
            const serviceId = document.getElementById('reassign-service-input').value;
            const currentAdminName = document.getElementById('current-handler-name').textContent;

            if (!newAdminId || newAdminId === "") {
                alert("Pilih Admin Baru terlebih dahulu.");
                document.getElementById('new_admin_id').focus();
                return;
            }

            if (confirm(`Yakin ingin me-reassign Service ID #${serviceId} (saat ini ditangani ${currentAdminName})?`)) {

                const submitButton = formReassign.querySelector('button[type="submit"]');
                const originalText = submitButton.textContent;

                submitButton.disabled = true;
                submitButton.textContent = 'Processing...';

                const formData = new URLSearchParams(new FormData(formReassign));

                fetch('ajax_reassign_service.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`Server Error: HTTP Status ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    const reassignModalElement = document.getElementById('reassignModal');
                    if (reassignModalElement && typeof bootstrap !== 'undefined') {
                       bootstrap.Modal.getInstance(reassignModalElement).hide();
                    }
                    console.log('JS DEBUG: Reassign Response:', data);

                    if (data.success) {
                        alert(' Reassign berhasil: ' + data.message);
                        window.location.reload();
                    } else {
                        alert(' Reassign Gagal: ' + (data.message || 'Terjadi kesalahan.'));
                        submitButton.disabled = false;
                        submitButton.textContent = originalText;
                    }
                })
                .catch(error => {
                    console.error('JS ERROR: Error Reassign:', error);
                    alert('Terjadi kesalahan jaringan atau server saat Reassign. Cek Console F12.');

                    const reassignModalElement = document.getElementById('reassignModal');
                    if (reassignModalElement && typeof bootstrap !== 'undefined') {
                        bootstrap.Modal.getInstance(reassignModalElement).hide();
                    }
                    submitButton.disabled = false;
                    submitButton.textContent = originalText;
                });
            }
        });
    }


    // =======================================================
    // 5. LOGIKA CLAIM & SELESAIKAN SERVICE (Event Delegation)
    // =======================================================
    const serviceListBody = document.getElementById('service-list-body');

    if (serviceListBody) {
        serviceListBody.addEventListener('click', function(event) {
            const claimButton = event.target.closest('.btn-claim');
            const selesaiButton = event.target.closest('.btn-selesai');

            // --- HANDLE CLAIM ---
            if (claimButton) {
                const serviceId = claimButton.dataset.id;
                const hostname = claimButton.dataset.hostname;

                if (!serviceId || !hostname) {
                    alert('Gagal: Data service ID atau Hostname hilang.');
                    return;
                }

                if (confirm(`Yakin ingin meng-claim Service ID #${serviceId} (Hostname: ${hostname}) dan melanjutkan ke halaman input aktivitas?`)) {

                    claimButton.disabled = true;
                    claimButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Claiming...';

                    fetch('ajax_claim_service.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `id_service=${serviceId}&hostname=${hostname}`
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! Status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            alert(data.message);
                            if (data.redirect_url) {
                                window.location.href = data.redirect_url;
                            } else {
                                alert('Service berhasil di-claim, tetapi gagal mengarahkan. Harap refresh.');
                                window.location.reload();
                            }
                        } else {
                            alert('Gagal meng-claim service: ' + (data.message || 'Terjadi kesalahan.'));
                            window.location.reload();
                        }
                    })
                    .catch(error => {
                        console.error('JS ERROR: Error Claim:', error);
                        alert('Terjadi kesalahan jaringan atau server saat meng-claim.');
                        claimButton.disabled = false;
                        claimButton.innerHTML = '<i class="bi bi-person-fill-up"></i> Claim';
                    });
                }
            }

            // --- HANDLE SELESAIKAN ---
            if (selesaiButton) {
                const serviceId = selesaiButton.dataset.id;
                const hostname = selesaiButton.dataset.hostname;

                if (!serviceId || !hostname) {
                    alert('Gagal: Data service ID atau Hostname hilang untuk Selesaikan.');
                    return;
                }

                if (confirm(`PERHATIAN! Yakin service ID #${serviceId} (Hostname: ${hostname}) sudah selesai? Status aset di Inventori TIDAK akan diubah secara otomatis.`)) {

                    selesaiButton.disabled = true;
                    selesaiButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Selesai...';

                    fetch('ajax_selesaikan_service.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        // Cukup kirim data minimum yang dibutuhkan PHP
                        body: `id_service=${serviceId}`
                        // Anda bisa menghapus &hostname=${hostname} karena tidak digunakan di PHP
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! Status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        alert(data.message);

                        if (data.success) {
                            window.location.reload();
                        } else {
                            selesaiButton.disabled = false;
                            selesaiButton.innerHTML = '<i class="bi bi-check-circle"></i> Selesaikan';
                        }
                    })
                    .catch(error => {
                        console.error('JS ERROR: Error Selesaikan:', error);
                        alert('Terjadi kesalahan jaringan atau server saat menyelesaikan service.');
                        selesaiButton.disabled = false;
                        selesaiButton.innerHTML = '<i class="bi bi-check-circle"></i> Selesaikan';
                    });
                }
            }
        });
    }

// =======================================================
    // 6. LOGIKA WEBSOCKET (REAL-TIME REFRESH - RELOAD PENUH)
    // =======================================================

    const NODE_SERVER_URL = 'http://172.16.3.60:3000';

    // Periksa apakah Socket.IO library dimuat (pastikan <script src="/socket.io/...") ada di index2.php)
    if (typeof io !== 'undefined') {
        const socket = io(NODE_SERVER_URL); // Gunakan const/let karena di dalam DOMContentLoaded

        socket.on('connect', () => {
            console.log(`[Client WS] Connected to Socket.IO server at ${NODE_SERVER_URL}`);
        });

        socket.on('service_update', (data) => {
            const { id, action } = data;

            // Definisikan Aksi yang menyebabkan refresh di halaman ini
            let actionsToRefresh;

            //  LOGIKA BARU: Tentukan aksi berdasarkan halaman saat ini
            // Perhatikan: window.location.pathname akan mendapatkan '/tampil.php' atau '/index2.php'

            if (window.location.pathname.includes('tampil.php')) {
                // Untuk halaman Aset (tampil.php), kita peduli pada perubahan Aset (INSERT, UPDATE, DELETE)
                actionsToRefresh = ['asset_insert', 'asset_update', 'asset_delete', 'asset_bulk_insert'];

            } else if (window.location.pathname.includes('index2.php')) {
                // Untuk halaman Service (index2.php), kita peduli pada perubahan Service
                actionsToRefresh = ['service_insert', 'service_claim', 'service_complete'];

            } else {
                // Halaman lain, misalnya landing page, tidak perlu refresh
                return;
            }

            // Cek apakah aksi yang diterima termasuk dalam daftar refresh
            if (actionsToRefresh.includes(action)) {

                console.log(`[Client WS] Received CRITICAL Update (${action}). Triggering page reload.`);

                window.location.reload();
            } else {
                 console.log(`[Client WS] Received non-monitored update (${action}). Ignored for this view.`);
            }
        });

        socket.on('disconnect', () => {
            console.warn('[Client WS] Disconnected from Socket.IO.');
        });
    } else {
        console.error("JS ERROR: Socket.IO library (io) tidak ditemukan. Cek <script> tag di HTML.");
    }


    // Tombol Registrasi
    if (addModal) {
        addModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;

            // Check apakah pemicu modal adalah tombol 'Registrasi Aset'
            if (button && button.classList.contains('btn-register-service')) {

                // Ambil data dari tombol
                const serviceId = button.getAttribute('data-service-id');
                const hostname = button.getAttribute('data-hostname');
                const namaUser = button.getAttribute('data-user');
                const divisi = button.getAttribute('data-divisi');

                // Isi Hidden Field Service ID (PENTING untuk proses di tambah.php)
                document.getElementById('service-id-to-update').value = serviceId;

                // Isi field Hostname, Nama, dan Divisi
                const hostnameInput = document.getElementById('add-hostname');
                document.getElementById('add-nama').value = namaUser;
                document.getElementById('add-divisi').value = divisi;

                // Set Hostname dan buat readonly (tidak bisa diubah)
                hostnameInput.value = hostname;
                hostnameInput.setAttribute('readonly', 'readonly');

                // Ubah judul modal
                const modalTitle = addModal.querySelector('.modal-title');
                modalTitle.textContent = 'Registrasi Aset dari Service Request #' + serviceId;
            } else {
                // Reset/bersihkan modal jika dibuka dari tombol "Tambah Inventori" biasa
                document.getElementById('service-id-to-update').value = '';
                document.getElementById('add-hostname').value = '';
                document.getElementById('add-nama').value = '';
                document.getElementById('add-divisi').value = '';
                document.getElementById('add-hostname').removeAttribute('readonly');

                const modalTitle = addModal.querySelector('.modal-title');
                modalTitle.textContent = 'Tambah Inventori';
            }
        });
    }
}); // Penutup DOMContentLoaded