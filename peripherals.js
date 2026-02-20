let groupCount = 0;

function addGroup() {
	groupCount++;
	const container = document.getElementById("groupContainer");
	if (!container) return;

	const groupHtml = `
        <div class="card mb-3 shadow-sm border-start border-primary border-4" id="group_${groupCount}">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0 text-primary fw-bold"><i class="bi bi-box-seam me-2"></i>GROUP ITEM #${groupCount}</h6>
                    <button type="button" class="btn btn-sm btn-outline-danger border-0" onclick="removeGroup(${groupCount})">
                        <i class="bi bi-trash"></i> Hapus Group
                    </button>
                </div>

                <div class="row g-3 mb-3">
					<div class="col-md-4">
							<label class="small fw-bold">TIPE BARANG</label>
							<select name="group[${groupCount}][tipe]" class="form-select form-select-sm" onchange="loadModels(this, ${groupCount})" required>
								<option value="">-- Pilih Tipe --</option>
								<option value="ADAPTOR">ADAPTOR</option>
								<option value="BATTERY">BATTERY</option>
								<option value="CABLE HDMI">CABLE HDMI</option>
								<option value="CABLE UTP">CABLE UTP</option>
								<option value="CONNECTOR">CONNECTOR</option>
								<option value="HARDDISK">HARDDISK</option>
								<option value="KEYBOARD">KEYBOARD</option>
								<option value="MEMORY">MEMORY</option>
								<option value="MOUSE">MOUSE</option>
								<option value="PATCH CORD">PATCH CORD</option>
								<option value="PRINTER">PRINTER</option>
								<option value="ROLLER">ROLLER</option>
								<option value="SCANNER">SCANNER</option>
								<option value="SWITCH">SWITCH</option>
								<option value="WIRELESS">WIRELESS</option>
								<option value="PROJECTOR">PROJECTOR</option>
							</select>
						</div>
						<div class="col-md-4">
							<label class="small fw-bold">MODEL (KODE)</label>
							<select name="group[${groupCount}][kode_barang]" id="model_${groupCount}" class="form-select form-select-sm" required>
								<option value="">-- Pilih Tipe Dulu --</option>
							</select>
						</div>
                    <div class="col-md-4">
                        <label class="small fw-bold">PERUNTUKAN</label>
                        <select name="group[${groupCount}][peruntukan]" class="form-select form-select-sm" onchange="toggleUserField(this, ${groupCount})">
                            <option value="Spare">SPARE IT</option>
                            <option value="User">USER (NAMA)</option>
                        </select>
                        <input type="text" name="group[${groupCount}][nama_user]" id="user_field_${groupCount}" class="form-control form-control-sm mt-2 d-none" placeholder="Nama User...">
                    </div>
                </div>

                <div class="bg-white p-3 rounded border">
                    <label class="small fw-bold mb-2">INPUT SERIAL NUMBER</label>
                    <div class="sn-tag-container d-flex flex-wrap gap-2 p-2 border rounded bg-light" id="sn_container_${groupCount}" onclick="focusSNInput(${groupCount})">
                        <input type="text" class="sn-input border-0 bg-transparent" style="outline: none; min-width: 150px;"
                               onkeydown="handleSNInput(event, ${groupCount})" placeholder="Scan atau Masukkan SN">
                    </div>
                    <input type="hidden" name="group[${groupCount}][sn]" id="sn_hidden_${groupCount}">
                </div>
            </div>
        </div>
    `;

	container.insertAdjacentHTML("beforeend", groupHtml);
}

// FUNGSI LOGIKA TAG INPUT
const snData = {}; // Object untuk menampung array SN per group

function handleSNInput(event, id) {
	const input = event.target;
	const value = input.value.trim().toUpperCase();

	// Jalankan jika tekan Enter (13) atau Koma (188)
	if (event.keyCode === 13 || event.keyCode === 188) {
		event.preventDefault();

		if (value && (!snData[id] || !snData[id].includes(value))) {
			if (!snData[id]) snData[id] = [];
			snData[id].push(value);
			renderTags(id);
			input.value = "";
		}
	}
	// Hapus tag terakhir jika tekan Backspace pada input kosong
	else if (
		event.keyCode === 8 &&
		input.value === "" &&
		snData[id] &&
		snData[id].length > 0
	) {
		snData[id].pop();
		renderTags(id);
	}
}

function renderTags(id) {
	const container = document.getElementById(`sn_container_${id}`);
	const hiddenInput = document.getElementById(`sn_hidden_${id}`);
	const inputField = container.querySelector(".sn-input");

	// Bersihkan tag lama kecuali input field
	container.querySelectorAll(".badge").forEach((tag) => tag.remove());

	// Buat tag baru
	snData[id].forEach((sn, index) => {
		const tag = document.createElement("span");
		tag.className =
			"badge bg-primary d-flex align-items-center gap-2 py-2 px-3";
		tag.innerHTML = `${sn} <i class="bi bi-x-circle-fill cursor-pointer" onclick="removeTag(${id}, ${index})" style="cursor:pointer"></i>`;
		container.insertBefore(tag, inputField);
	});

	// Masukkan ke hidden input untuk dikirim ke PHP (dipisahkan koma)
	hiddenInput.value = snData[id].join(",");
}

function removeTag(id, index) {
	snData[id].splice(index, 1);
	renderTags(id);
}

function focusSNInput(id) {
	document.querySelector(`#sn_container_${id} .sn-input`).focus();
}

// Fungsi pendukung lainnya (removeGroup, toggleUserField, loadModels) tetap sama seperti sebelumnya...

function removeGroup(id) {
	const el = document.getElementById("group_" + id);
	if (el) el.remove();
}

function toggleUserField(select, id) {
	const field = document.getElementById("user_field_" + id);
	if (select.value === "User") {
		field.classList.remove("d-none");
		field.required = true;
	} else {
		field.classList.add("d-none");
		field.required = false;
		field.value = "";
	}
}

async function loadModels(selectEl, id) {
	const tipe = selectEl.value;
	const modelSelect = document.getElementById("model_" + id);

	if (!tipe) {
		modelSelect.innerHTML = '<option value="">-- Pilih Tipe Dulu --</option>';
		return;
	}

	modelSelect.innerHTML = '<option value="">Loading...</option>';

	try {
		const response = await fetch(
			`peripherals.php?get_models_by_tipe=${encodeURIComponent(tipe)}`,
		);
		const data = await response.json();

		modelSelect.innerHTML = '<option value="">-- Pilih Model --</option>';
		data.forEach((item) => {
			const option = document.createElement("option");
			option.value = item.kode_barang;
			option.textContent = item.model;
			modelSelect.appendChild(option);
		});
	} catch (error) {
		modelSelect.innerHTML = '<option value="">Gagal Memuat</option>';
	}
}

document.addEventListener("DOMContentLoaded", () => {
	const modalMasuk = document.getElementById("modalMasuk");
	if (modalMasuk) {
		modalMasuk.addEventListener("shown.bs.modal", () => {
			const container = document.getElementById("groupContainer");
			if (container && container.innerHTML.trim() === "") {
				addGroup();
			}
		});
	}
});

// Menggunakan Event Delegation agar tombol tetap berfungsi meski data di-refresh
document.addEventListener("click", function (event) {
	// Cek apakah yang diklik adalah tombol edit atau elemen di dalamnya
	const btn = event.target.closest(".btn-edit-tipe");

	if (btn) {
		// Ambil data dari atribut data-*
		const id = btn.getAttribute("data-id");
		const kode = btn.getAttribute("data-kode");
		const tipe = btn.getAttribute("data-tipe");
		const model = btn.getAttribute("data-model");
		const desc = btn.getAttribute("data-desc");

		// Isi form di dalam Modal Edit
		document.getElementById("edit_id").value = id;
		document.getElementById("edit_kode").value = kode;
		document.getElementById("edit_tipe").value = tipe;
		document.getElementById("edit_model").value = model;
		document.getElementById("edit_desc").value = desc;

		// Tampilkan Modal secara manual menggunakan Bootstrap API
		const modalEl = document.getElementById("modalEditTipe");
		const editModal = new bootstrap.Modal(modalEl);
		editModal.show();
	}
});
