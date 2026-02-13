let groupCount = 0;

// Fungsi Utama: Tambah Group
function addGroup() {
	groupCount++;

	// Membangun opsi tipe dari variabel dataTipeBarang
	let tipeOptions = dataTipeBarang
		.map((t) => `<option value='${t}'>${t}</option>`)
		.join("");

	const groupHtml = `
    <div class="card mb-3 border-primary group-item" id="group_${groupCount}">
        <div class="card-header bg-white p-2 d-flex justify-content-between align-items-center">
            <span class="badge bg-primary">Group #${groupCount}</span>
            <button type="button" class="btn btn-sm btn-outline-danger border-0" onclick="removeGroup(${groupCount})"><i class="bi bi-trash"></i></button>
        </div>
        <div class="card-body p-3">
            <div class="row g-2 mb-3">
                <div class="col-md-3">
                    <label class="small fw-bold">Tipe</label>
                    <select class="form-select form-select-sm" required onchange="filterModelGroup(this, ${groupCount})">
                        <option value="">- Tipe -</option>
                        ${tipeOptions}
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="small fw-bold">Model</label>
                    <select name="group[${groupCount}][kode_barang]" class="form-select form-select-sm select-model" id="model_${groupCount}" required disabled>
                        <option value="">- Pilih Tipe -</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="small fw-bold">Peruntukan</label>
                    <select name="group[${groupCount}][peruntukan]" class="form-select form-select-sm" onchange="toggleUserGroup(this, ${groupCount})" required>
                        <option value="SSC">SSC</option>
                        <option value="APP">APP</option>
                        <option value="User">User</option> </select>
                </div>
                <div class="col-md-3 d-none" id="user_div_${groupCount}">
                    <label class="small fw-bold text-primary">Nama User</label>
                    <input type="text" name="group[${groupCount}][nama_user]" class="form-control form-control-sm text-uppercase">
                </div>
            </div>
            <div class="bg-light p-2 rounded">
                <label class="small fw-bold mb-1">Scan Serial Numbers:</label>
                <div id="sn_list_${groupCount}" class="d-flex flex-wrap gap-1 mb-2"></div>
                <input type="text" class="form-control form-control-sm sn-scanner" placeholder="Scan SN di sini..." onkeydown="handleScan(event, ${groupCount})">
            </div>
        </div>
    </div>`;

	document
		.getElementById("groupContainer")
		.insertAdjacentHTML("beforeend", groupHtml);
	document.querySelector(`#group_${groupCount} select`).focus();
}

// Logika Scan SN
async function handleScan(e, gId) {
	if (e.key === "Enter") {
		e.preventDefault();
		const input = e.target;
		const val = input.value.trim().toUpperCase();
		if (!val) return;

		const allScannedSN = Array.from(
			document.querySelectorAll('input[name*="[sn][]"]'),
		);
		const isDuplicateInUI = allScannedSN.some(
			(hiddenInput) => hiddenInput.value === val,
		);

		if (isDuplicateInUI) {
			showToast(`S/N ${val} sudah ada di daftar!`, "danger");
			input.value = "";
			return;
		}

		try {
			const response = await fetch(
				`peripherals.php?cek_sn=${encodeURIComponent(val)}`,
			);
			const data = await response.json();
			if (data.exists) {
				showToast(`S/N ${val} sudah terdaftar di Database!`, "danger");
				input.value = "";
				return;
			}

			const snList = document.getElementById(`sn_list_${gId}`);
			const badge = `
                <span class="badge bg-dark d-flex align-items-center p-2 shadow-sm" style="font-family: monospace;">
                    ${val}
                    <input type="hidden" name="group[${gId}][sn][]" value="${val}">
                    <i class="bi bi-x-circle ms-2 text-danger btn-remove-sn" style="cursor:pointer;" onclick="this.parentElement.remove()"></i>
                </span>`;
			snList.insertAdjacentHTML("beforeend", badge);
			input.value = "";
		} catch (error) {
			showToast("Gagal validasi SN", "danger");
		}
	}
}

// Global Toast Function
function showToast(m, t = "danger") {
	const toastEl = document.getElementById("liveToast");
	if (!toastEl) return;
	document.getElementById("toast-body").innerText = m;
	toastEl.className = `toast align-items-center text-white bg-${t} border-0`;
	bootstrap.Toast.getOrCreateInstance(toastEl).show();
}

// Initialization & Event Listeners
document.addEventListener("DOMContentLoaded", function () {
	// Tampilkan pesan dari PHP
	if (typeof msg !== "undefined" && msg) {
		showToast(msg, typeof res !== "undefined" ? res : "primary");
		window.history.replaceState({}, document.title, window.location.pathname);
	}

	// Modal Listener
	const modalMasuk = document.getElementById("modalMasuk");
	if (modalMasuk) {
		modalMasuk.addEventListener("shown.bs.modal", function () {
			if (document.querySelectorAll(".group-item").length === 0) addGroup();
		});
	}
});

// Helper Functions
function removeGroup(id) {
	if (confirm("Hapus group ini?"))
		document.getElementById(`group_${id}`).remove();
}
function toggleUserGroup(sel, id) {
	document
		.getElementById(`user_div_${id}`)
		.classList.toggle("d-none", sel.value !== "User");
}

function filterModelGroup(selectElement, gId) {
	const tipe = selectElement.value;
	const modelSelect = document.getElementById(`model_${gId}`);
	if (!tipe) {
		modelSelect.disabled = true;
		return;
	}

	const models = dataRelasi.filter((item) => item.tipe === tipe);
	let options = '<option value="">- Pilih Model -</option>';
	models.forEach((item) => {
		options += `<option value="${item.kode}">${item.model} [${item.kode}]</option>`;
	});
	modelSelect.innerHTML = options;
	modelSelect.disabled = false;
}
