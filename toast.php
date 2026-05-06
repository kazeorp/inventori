<div class="toast-container position-fixed end-0 bottom-0 p-3" style="z-index: 9999; pointer-events: none;">
    <div id="liveToast" class="toast align-items-center border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true" style="pointer-events: auto; cursor: default;">
        <div class="d-flex">
            <div id="toast-body" class="toast-body fw-bold">
                </div>
            <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close" id="toast-close-btn"></button>
        </div>
    </div>
</div>

<script>
// Bungkus dalam fungsi agar bisa dipanggil kapan saja
function checkAndShowToast() {
    const urlParams = new URLSearchParams(window.location.search);
    const msg = urlParams.get('msg');
    const res = urlParams.get('res');

    if (msg) {
        // CEK: Jika bootstrap belum load, tunggu 100ms lalu coba lagi
        if (typeof bootstrap === 'undefined') {
            setTimeout(checkAndShowToast, 100);
            return;
        }

        const toastElement = document.getElementById('liveToast');
        const toastBody = document.getElementById('toast-body');
        const closeBtn = document.getElementById('toast-close-btn');

        if (!toastElement || !toastBody || !closeBtn) return;

        // Reset warna sebelumnya agar tidak tumpang tindih
        toastElement.classList.remove('bg-success', 'bg-danger', 'bg-warning', 'bg-info', 'bg-primary', 'text-dark', 'text-white');
        closeBtn.classList.remove('btn-close-white');

        let bgClass = 'bg-primary text-white';
        closeBtn.classList.add('btn-close-white');

        if (res === 'success') { bgClass = 'bg-success text-white'; }
        else if (res === 'danger') { bgClass = 'bg-danger text-white'; }
        else if (res === 'warning') { bgClass = 'bg-warning text-dark'; closeBtn.classList.remove('btn-close-white'); }
        else if (res === 'info') { bgClass = 'bg-info text-dark'; closeBtn.classList.remove('btn-close-white'); }

        toastElement.classList.add(...bgClass.split(' '));
        toastBody.textContent = decodeURIComponent(msg.replace(/\+/g, ' '));

        const toast = new bootstrap.Toast(toastElement, {
            delay: 4000,
            autohide: true
        });
        toast.show();

        if (window.history.replaceState) {
            const url = new URL(window.location);
            url.searchParams.delete('msg');
            url.searchParams.delete('res');
            window.history.replaceState(null, null, url);
        }
    }
}

// Jalankan saat DOM siap
document.addEventListener("DOMContentLoaded", checkAndShowToast);

// Tambahkan fungsi ini di dalam script toast.php Anda
function showToastManual(message, type = 'success') {
    const toastElement = document.getElementById('liveToast');
    const toastBody = document.getElementById('toast-body');
    const closeBtn = document.getElementById('toast-close-btn');
    if (!toastElement || !toastBody || !closeBtn) return;

    // Reset warna sebelumnya agar tidak tumpang tindih
    toastElement.classList.remove('bg-success', 'bg-danger', 'bg-warning', 'bg-info', 'bg-primary', 'text-dark', 'text-white');
    closeBtn.classList.remove('btn-close-white');

    let bgClass = 'bg-primary text-white';
    closeBtn.classList.add('btn-close-white');

    if (type === 'success') { bgClass = 'bg-success text-white'; }
    else if (type === 'danger') { bgClass = 'bg-danger text-white'; }
    else if (type === 'warning') { bgClass = 'bg-warning text-dark'; closeBtn.classList.remove('btn-close-white'); }
    else if (type === 'info') { bgClass = 'bg-info text-dark'; closeBtn.classList.remove('btn-close-white'); }

    toastElement.classList.add(...bgClass.split(' '));
    toastBody.textContent = message;

    const toast = new bootstrap.Toast(toastElement, { delay: 4000 });
    toast.show();
}
</script>