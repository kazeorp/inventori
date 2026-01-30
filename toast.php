<div class="toast-container position-fixed end-0 p-3" style="top: 70px; z-index: 9999;">
    <div id="liveToast" class="toast align-items-center border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div id="toast-body" class="toast-body fw-bold text-white">
                </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
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

        if (!toastElement || !toastBody) return;

        let bgClass = 'bg-primary';
        if (res === 'success') bgClass = 'bg-success';
        if (res === 'danger') bgClass = 'bg-danger';
        if (res === 'warning') bgClass = 'bg-warning text-dark';
        if (res === 'info') bgClass = 'bg-info text-dark';

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
</script>