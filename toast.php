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
document.addEventListener("DOMContentLoaded", function() {
    const urlParams = new URLSearchParams(window.location.search);
    const msg = urlParams.get('msg');
    const res = urlParams.get('res');

    if (msg) {
        const toastElement = document.getElementById('liveToast');
        const toastBody = document.getElementById('toast-body');

        // Pilih warna berdasarkan parameter 'res'
        let bgClass = 'bg-primary'; // Default
        if (res === 'success') bgClass = 'bg-success';
        if (res === 'danger') bgClass = 'bg-danger';
        if (res === 'warning') bgClass = 'bg-warning text-dark';
        if (res === 'info') bgClass = 'bg-info text-dark';

        toastElement.classList.add(...bgClass.split(' '));

        // Bersihkan dan tampilkan pesan
        toastBody.textContent = decodeURIComponent(msg.replace(/\+/g, ' '));

        const toast = new bootstrap.Toast(toastElement, {
            delay: 4000,
            autohide: true
        });
        toast.show();

        // Bersihkan URL agar saat refresh pesan hilang
        if (window.history.replaceState) {
            const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname + window.location.search.replace(/[?&]res=[^&]+/, '').replace(/[?&]msg=[^&]+/, '').replace(/^[?&]/, '');
            window.history.replaceState(null, null, cleanUrl);
        }
    }
});
</script>