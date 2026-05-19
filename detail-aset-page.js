document.addEventListener("DOMContentLoaded", function () {
    const reassignModal = document.getElementById("reassignModal");
    if (reassignModal) {
        reassignModal.addEventListener("show.bs.modal", e => {
            const ds = e.relatedTarget.dataset;
            document.getElementById("reassign-service-id").textContent = ds.id;
            document.getElementById("reassign-service-input").value = ds.id;
            document.getElementById("current-handler-name").textContent = ds.currentAdminName;
            document.getElementById("new_admin_id").value = "";
        });
    }

    const serviceList = document.getElementById("service-list-body");
    if (serviceList) {
        serviceList.addEventListener("click", async e => {
            const claimBtn = e.target.closest(".btn-claim"), selesaiBtn = e.target.closest(".btn-selesai");
            if (!claimBtn && !selesaiBtn) return;

            const { id, hostname } = (claimBtn || selesaiBtn).dataset;
            if (claimBtn && confirm(`Claim Service ID #${id} (${hostname})?`)) {
                await handleServiceAction(claimBtn, 'ajax_claim_service.php', `id_service=${id}&hostname=${hostname}`);
            } else if (selesaiBtn && confirm(`Selesaikan service ID #${id}?`)) {
                await handleServiceAction(selesaiBtn, 'ajax_selesaikan_service.php', `id_service=${id}&hostname=${hostname}`);
            }
        });
    }

    async function handleServiceAction(btn, url, body) {
        const original = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
        try {
            const res = await fetch(url, { method: "POST", headers: { "Content-Type": "application/x-www-form-urlencoded" }, body });
            const data = await res.json();
            alert(data.message);
            if (data.success) location.reload();
            else btn.disabled = false, btn.innerHTML = original;
        } catch (err) {
            alert("Gagal menghubungi server.");
            btn.disabled = false, btn.innerHTML = original;
        }
    }

    const formReassign = document.getElementById("form-reassign");
    if (formReassign) {
        formReassign.onsubmit = async e => {
            e.preventDefault();
            if (!document.getElementById("new_admin_id").value) return alert("Pilih Admin Baru.");
            if (!confirm("Yakin ingin reassign service ini?")) return;

            const submitBtn = formReassign.querySelector('button[type="submit"]'), originalText = submitBtn.textContent;
            submitBtn.disabled = true; submitBtn.textContent = "Processing...";
            try {
                const res = await fetch("ajax_reassign_service.php", { method: "POST", body: new URLSearchParams(new FormData(formReassign)) });
                const data = await res.json();
                if (reassignModal) bootstrap.Modal.getInstance(reassignModal).hide();
                alert(data.message);
                if (data.success) location.reload();
                else submitBtn.disabled = false, submitBtn.textContent = originalText;
            } catch (err) {
                alert("Terjadi kesalahan jaringan.");
                submitBtn.disabled = false; submitBtn.textContent = originalText;
            }
        };
    }
});
