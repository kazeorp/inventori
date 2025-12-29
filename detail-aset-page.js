// detail-aset-page.js (Hanya Logika Service/Claim/Reassign)

document.addEventListener('DOMContentLoaded', function() {
    console.log('JS DEBUG: detail-aset-page.js loaded (Service Logic Only).');

    // =======================================================
    // 1. SETUP REASSIGN MODAL & LISTENER
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
            document.getElementById('new_admin_id').value = ''; // Reset pilihan
            console.log(`JS DEBUG: Reassign Modal Opened for Service ID: ${serviceId}`);
        });
    }

    const serviceListBody = document.getElementById('service-list-body');

    // =======================================================
    // 2. HANDLE TOMBOL CLAIM (Event Delegation)
    // =======================================================
    if (serviceListBody) {
        serviceListBody.addEventListener('click', function(event) {
            const claimButton = event.target.closest('.btn-claim');

            if (claimButton) {
                const serviceId = claimButton.dataset.id;
                const hostname = claimButton.dataset.hostname;

                if (!serviceId || !hostname) {
                    console.error('JS ERROR: data-id atau data-hostname kosong!');
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
        });
    }


	// =======================================================
    // 3. HANDLE SUBMIT FORM REASSIGN (AJAX)
    // =======================================================
    const formReassign = document.getElementById('form-reassign');
    if (formReassign) {
        formReassign.addEventListener('submit', function(e) {
            e.preventDefault();

            const newAdminId = document.getElementById('new_admin_id').value;
            const serviceId = document.getElementById('reassign-service-input').value;
            const currentAdminName = document.getElementById('current-handler-name').textContent;

            // --- VALIDASI TAMBAHAN (PENTING) ---
            if (!newAdminId || newAdminId === "") {
                alert("Pilih Admin Baru terlebih dahulu.");
                // Mengembalikan fokus ke select box
                document.getElementById('new_admin_id').focus();
                return; // Berhenti di sini jika validasi gagal
            }
            // --- AKHIR VALIDASI TAMBAHAN ---

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
                        // Throw error jika respons HTTP gagal (e.g., 404, 500)
                        throw new Error(`Server Error: HTTP Status ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    const reassignModalElement = document.getElementById('reassignModal');
                    // Menyembunyikan modal
                    if (reassignModalElement && typeof bootstrap !== 'undefined') {
                       bootstrap.Modal.getInstance(reassignModalElement).hide();
                    }
                    console.log('JS DEBUG: Reassign Response:', data);

                    if (data.success) {
                        alert('✅ Reassign berhasil: ' + data.message);
                        window.location.reload();
                    } else {
                        alert('❌ Reassign Gagal: ' + (data.message || 'Terjadi kesalahan.'));
                        // Kembalikan tombol jika gagal dan tidak ada reload
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
    // 4. HANDLE TOMBOL SELESAIKAN (Event Delegation)
    // =======================================================
    if (serviceListBody) {
        serviceListBody.addEventListener('click', function(event) {
            const selesaiButton = event.target.closest('.btn-selesai');

            if (selesaiButton) {
                const serviceId = selesaiButton.dataset.id;
                const hostname = selesaiButton.dataset.hostname;

                // ... (Kode Selesaikan Logic Anda yang lain) ...
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
                        body: `id_service=${serviceId}&hostname=${hostname}`
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

}); // Penutup DOMContentLoaded