// main.js - VERSI FINAL TERINTEGRASI PENUH (TERMASUK LOGIKA EDIT MODAL BARU)

// Fungsi global untuk Menggabungkan Tipe dan Ukuran Storage
window.updateStorageField = function (
	modalId,
	typeSelectId,
	sizeInputId,
	hiddenInputId,
) {
	const modal = document.getElementById(modalId);
	if (!modal) return;
	const typeSelect = modal.querySelector(`#${typeSelectId}`);
	const sizeInput = modal.querySelector(`#${sizeInputId}`);
	const hiddenInput = modal.querySelector(`#${hiddenInputId}`);

	if (!typeSelect || !sizeInput || !hiddenInput) return;

	function combineStorage() {
		const type = typeSelect.value;
		const size = sizeInput.value.trim();
		hiddenInput.value = type && size ? `${size} ${type}` : "";
	}

	typeSelect.addEventListener("change", combineStorage);
	sizeInput.addEventListener("input", combineStorage);
};

document.addEventListener("DOMContentLoaded", function () {
	console.log("JS DEBUG: main.js loaded. All system listeners registered.");

	// =======================================================
	// 1. DEFINISI MAPPING & FUNGSI PEMBANTU
	// =======================================================

	// Peta Standar Kelengkapan
	const standardMap = {
		"ADAPTOR,TAS": "TAS DAN ADAPTOR",
		"CONVERTERVGA,TAS": "TAS DAN CONVERTER VGA",
		"CONVERTERLAN,TAS": "TAS DAN CONVERTER LAN",
		"ADAPTOR,CONVERTERLAN,TAS": "TAS, ADAPTOR, CONVERTER LAN",
		"ADAPTOR,CONVERTERVGA,TAS": "TAS, ADAPTOR, CONVERTER VGA",
		"ADAPTOR,CONVERTERLAN,CONVERTERVGA,TAS":
			"TAS, ADAPTOR, CONVERTER LAN & VGA",
		"ADAPTOR,CONVERTERLAN,TAS,VGA": "TAS, ADAPTOR, CONVERTER LAN & VGA",
		ADAPTOR: "ADAPTOR",
		TAS: "TAS",
		"": "",
	};

	// Fungsi Pembantu 1: Membersihkan dan menormalkan string mentah (untuk kelengkapan)
	function cleanAndNormalize(str) {
		if (
			!str ||
			str === "0" ||
			str === "0000-00-00" ||
			str === "0000-00-00 00:00:00"
		)
			return "";

		let cleaned = str.toUpperCase().trim();
		cleaned = cleaned.replace(
			/,\s*|\s*,\s*|\s*&amp;\s*|\s*&\s*|\s*DAN\s*/g,
			",",
		);
		cleaned = cleaned
			.replace(/,,+/g, ",")
			.replace(/\s+/g, "")
			.replace(/^,|,$/g, "");

		return cleaned;
	}

	// Fungsi Pembantu 2: Mencari nilai standar Kelengkapan
	function normalizeKelengkapan(inputString) {
		if (!inputString) return "";

		let cleaned = cleanAndNormalize(inputString);
		let items = cleaned.split(",").sort();
		let sortedKey = items.join(",");

		const result = standardMap[sortedKey];
		return result || "";
	}

	// Fungsi Pembantu 3: Capitalize string (untuk Kategori Perangkat)
	function capitalize(str) {
		if (
			!str ||
			str === "0" ||
			str === "0000-00-00" ||
			str === "0000-00-00 00:00:00"
		)
			return "";
		return str
			.toLowerCase()
			.split(" ")
			.map((word) => {
				return word.charAt(0).toUpperCase() + word.slice(1);
			})
			.join(" ");
	}

	// Fungsi Pembantu 4: Mengubah format DATETIME menjadi DATE (YYYY-MM-DD)
	function normalizeDateTimeToDate(dateTimeStr) {
		if (
			!dateTimeStr ||
			dateTimeStr === "0000-00-00 00:00:00" ||
			dateTimeStr === "0000-00-00"
		) {
			return "";
		}
		return dateTimeStr.split(" ")[0];
	}

	// =======================================================
	// 2. INITIALIZATION
	// =======================================================
	if (document.getElementById("addModal")) {
		window.updateStorageField(
			"addModal",
			"add-storage-type",
			"add-storage-size",
			"add-storage-combined",
		);
	}
	if (document.getElementById("editModal")) {
		window.updateStorageField(
			"editModal",
			"edit-storage-type",
			"edit-storage-size",
			"edit-storage",
		);
	}
	// Logic for Edit Inventory Modal follows in the click listener below

	document.addEventListener("click", function (e) {
		const btn = e.target.closest(".edit-btn");
		if (!btn) {
			return;
		}

		// Guard: Only process if the button is intended for the main inventory edit modal
		if (btn.getAttribute("data-bs-target") !== "#editModal") {
			return;
		}

		e.preventDefault(); // Mencegah loncatan scroll
		const modal = document.getElementById("editModal");
		if (!modal) {
			console.error("JS ERROR: Edit modal with ID 'editModal' not found.");
			return;
		}

		// --- PENGAMBILAN DATA DARI DATASET ---
		const ds = btn.dataset;

		// Handle variations in naming (data-device-category becomes deviceCategory)
		const rawKelengkapan = ds.kelengkapan || "";
		const rawDeviceCategory = ds.deviceCategory || ds.device_category || "";

		const dataKelengkapan = normalizeKelengkapan(rawKelengkapan);
		const dataDeviceCategory = rawDeviceCategory
			? capitalize(rawDeviceCategory)
			: "";
		const dataTglMasuk = normalizeDateTimeToDate(
			ds.tanggal_masuk || ds.tanggalMasuk,
		);
		const dataTglKeluar = normalizeDateTimeToDate(
			ds.tanggal_keluar || ds.tanggalKeluar,
		);

		// Helper function to safely set values without crashing if an element is missing
		const fill = (idName, value, logMissing = true) => {
			// Ensure the ID name doesn't start with '#' to avoid double hashes in selector
			const cleanId = idName.startsWith("#") ? idName.slice(1) : idName;

			// Select element by ID, trying both hyphen and underscore versions
			const el =
				modal.querySelector(`#${cleanId}`) ||
				modal.querySelector(`#${cleanId.replace(/-/g, "_")}`);

			if (el) {
				const safeValue =
					value === undefined ||
					value === null ||
					String(value).toLowerCase() === "null"
						? ""
						: value;
				el.value = safeValue;
			} else if (logMissing) {
				console.warn(
					`JS DEBUG: Input element with ID '#${idName}' (or underscore version) not found in #editModal.`,
				);
			}
		};

		// --- PENGISIAN DATA KE MODAL ---
		fill("edit-id", ds.id);
		fill("edit-hostname", ds.hostname);
		fill("edit-warna", ds.warna);
		fill("edit-ram", ds.ram);

		// Handle Storage Field splitting (e.g., "SSD 256 GB" -> Type: SSD, Size: 256)
		// Updated to parse "SIZE TYPE" format (e.g., "256 SSD")
		const storageValue = ds.storage || "";
		fill("edit-storage", storageValue); // Hidden field

		// Regex to match "SIZE TYPE" (e.g., "256 SSD")
		const storageParts = storageValue.match(/^(\d+)\s*(HDD|SSD)$/i);

		if (storageParts) {
			// storageParts[1] is the size (e.g., "256")
			// storageParts[2] is the type (e.g., "SSD")
			fill("edit-storage-type", storageParts[2].toUpperCase(), false); // Fill type select
			fill("edit-storage-size", storageParts[1], false); // Fill size input
		} else {
			fill("edit-storage-type", "", false); // Clear type
			fill("edit-storage-size", "", false); // Clear size
		}

		fill("edit-win", ds.win);
		fill("edit-serial_number", ds.serial_number);
		fill("edit-keterangan", ds.keterangan);
		fill("edit-nik", ds.nik);
		fill("edit-nama", ds.nama);
		fill("edit-divisi", ds.divisi);

		// Select fields
		fill("edit-status", ds.status);
		fill("edit-type", ds.type);
		fill("edit-domain", ds.domain);
		fill("edit-kelengkapan", dataKelengkapan);
		fill("edit-device_category", dataDeviceCategory);

		// Tanggal
		fill("edit-tanggal_masuk", dataTglMasuk);
		fill("edit-tanggal_keluar", dataTglKeluar);

		// Link Tombol
		const detailBtn = modal.querySelector("#detailBtn");
		if (detailBtn) detailBtn.href = "detail-aset.php?id=" + ds.id;

		const deleteBtn = modal.querySelector("#deleteBtn");
		if (deleteBtn) deleteBtn.href = "hapus.php?id=" + ds.id;

		// Hak Akses (Gunakan userRole global)
		if (typeof userRole !== "undefined" && userRole === "normal") {
			modal.querySelectorAll("input, select, textarea").forEach((el) => {
				el.setAttribute("disabled", true);
			});
			if (modal.querySelector('button[type="submit"]'))
				modal.querySelector('button[type="submit"]').style.display = "none";
			if (modal.querySelector("#deleteBtn"))
				modal.querySelector("#deleteBtn").style.display = "none";
		} else {
			modal.querySelectorAll("input, select, textarea").forEach((el) => {
				el.removeAttribute("disabled");
				el.removeAttribute("readonly");
			});
			if (modal.querySelector('button[type="submit"]'))
				modal.querySelector('button[type="submit"]').style.display =
					"inline-block";
			if (modal.querySelector("#deleteBtn"))
				modal.querySelector("#deleteBtn").style.display = "inline-block";
		}
	});

	//  Konfirmasi hapus (Fungsi Global)
	window.confirmDelete = function () {
		return confirm(
			"Yakin ingin menghapus data ini? Tindakan ini tidak bisa dibatalkan.",
		);
	};

	// =======================================================
	// 4. LOGIKA REASSIGN SERVICE
	// =======================================================
	const reassignModalElement = document.getElementById("reassignModal");
	if (reassignModalElement) {
		reassignModalElement.addEventListener("show.bs.modal", function (event) {
			const button = event.relatedTarget;
			const serviceId = button.getAttribute("data-id");
			const currentAdminName = button.getAttribute("data-current-admin-name");

			document.getElementById("reassign-service-id").textContent = serviceId;
			document.getElementById("reassign-service-input").value = serviceId;
			document.getElementById("current-handler-name").textContent =
				currentAdminName;

			const formReassign = document.getElementById("form-reassign");
			if (formReassign) {
				formReassign.reset();
				const submitButton = formReassign.querySelector(
					'button[type="submit"]',
				);
				if (submitButton) {
					submitButton.disabled = false;
					submitButton.textContent = "Reassign";
				}
			}
		});
	}

	const formReassign = document.getElementById("form-reassign");
	if (formReassign) {
		formReassign.addEventListener("submit", function (e) {
			e.preventDefault();

			const newAdminId = document.getElementById("new_admin_id").value;
			const serviceId = document.getElementById("reassign-service-input").value;
			const currentAdminName = document.getElementById(
				"current-handler-name",
			).textContent;

			if (!newAdminId || newAdminId === "") {
				alert("Pilih Admin Baru terlebih dahulu.");
				document.getElementById("new_admin_id").focus();
				return;
			}

			if (
				confirm(
					`Yakin ingin me-reassign Service ID #${serviceId} (saat ini ditangani ${currentAdminName})?`,
				)
			) {
				const submitButton = formReassign.querySelector(
					'button[type="submit"]',
				);
				const originalText = submitButton.textContent;

				submitButton.disabled = true;
				submitButton.textContent = "Processing...";

				const formData = new URLSearchParams(new FormData(formReassign));

				fetch("ajax_reassign_service.php", {
					method: "POST",
					headers: { "Content-Type": "application/x-www-form-urlencoded" },
					body: formData,
				})
					.then((response) => {
						if (!response.ok) {
							throw new Error(`Server Error: HTTP Status ${response.status}`);
						}
						return response.json();
					})
					.then((data) => {
						const reassignModalElement =
							document.getElementById("reassignModal");
						if (reassignModalElement && typeof bootstrap !== "undefined") {
							bootstrap.Modal.getInstance(reassignModalElement).hide();
						}

						if (data.success) {
							alert(" Reassign berhasil: " + data.message);
							window.location.reload();
						} else {
							alert(
								" Reassign Gagal: " + (data.message || "Terjadi kesalahan."),
							);
							submitButton.disabled = false;
							submitButton.textContent = originalText;
						}
					})
					.catch((error) => {
						console.error("JS ERROR: Error Reassign:", error);
						alert(
							"Terjadi kesalahan jaringan atau server saat Reassign. Cek Console F12.",
						);

						const reassignModalElement =
							document.getElementById("reassignModal");
						if (reassignModalElement && typeof bootstrap !== "undefined") {
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
	const serviceListBody = document.getElementById("service-list-body");

	if (serviceListBody) {
		serviceListBody.addEventListener("click", function (event) {
			const claimButton = event.target.closest(".btn-claim");
			const selesaiButton = event.target.closest(".btn-selesai");

			// --- HANDLE CLAIM ---
			if (claimButton) {
				const serviceId = claimButton.dataset.id;
				const hostname = claimButton.dataset.hostname;

				if (!serviceId || !hostname) {
					showToastManual(
						"Gagal: Data service ID atau Hostname hilang.",
						"danger",
					);
					return;
				}

				// Ganti confirm native dengan pesan yang lebih simpel jika perlu,
				// atau tetap gunakan confirm jika ingin proteksi klik tidak sengaja.
				if (confirm(`Yakin ingin meng-claim Service Hostname: ${hostname}?`)) {
					// 1. Visual Feedback: Loading State
					claimButton.disabled = true;
					const originalContent = claimButton.innerHTML;
					claimButton.innerHTML =
						'<span class="spinner-border spinner-border-sm" role="status"></span>';

					fetch("ajax_claim_service.php", {
						method: "POST",
						headers: { "Content-Type": "application/x-www-form-urlencoded" },
						body: `id_service=${serviceId}&hostname=${hostname}`,
					})
						.then((response) => {
							if (!response.ok)
								throw new Error(`HTTP error! Status: ${response.status}`);
							return response.json();
						})
						.then((data) => {
							if (data.success) {
								// 2. Gunakan fungsi dari toast.php Anda
								showToastManual(data.message, "success");

								// 3. JANGAN redirect.
								// Jika WebSocket jalan, tabel akan update otomatis.
								// Jika tidak pakai WebSocket, kita panggil fungsi refresh manual di sini:
								if (typeof updateDashboard === "function") {
									updateDashboard();
								}
							} else {
								showToastManual("Gagal: " + data.message, "danger");
								// Kembalikan tombol jika gagal agar bisa dicoba lagi
								claimButton.disabled = false;
								claimButton.innerHTML = originalContent;
							}
						})
						.catch((error) => {
							console.error("JS ERROR:", error);
							showToastManual("Terjadi kesalahan jaringan.", "danger");
							claimButton.disabled = false;
							claimButton.innerHTML = originalContent;
						});
				}
			}

			// --- HANDLE SELESAIKAN (Revisi untuk membuka Modal) ---
			if (selesaiButton) {
				const serviceId = selesaiButton.dataset.id;
				const hostname = selesaiButton.dataset.hostname;
				const idInv = selesaiButton.dataset.idInv; // Tambahkan data-id-inv di HTML

				// Alih-alih Fetch, kita buka modal Bootstrap
				const modalElement = document.getElementById("modalSelesaiService");
				if (modalElement) {
					// Isi data ke dalam form modal
					document.getElementById("modal_id_service").value = serviceId;
					document.getElementById("modal_id_inventori").value = idInv;
					document.getElementById("modal_hostname_display").innerText =
						hostname;

					// Tampilkan modal
					const myModal = new bootstrap.Modal(modalElement);
					myModal.show();
				}
			}
		});
	}

	// =======================================================
	// 6. LOGIKA WEBSOCKET (REAL-TIME REFRESH - RELOAD PENUH)
	// =======================================================

	const NODE_SERVER_URL = "http://172.16.3.60:3000";

	// Periksa apakah Socket.IO library dimuat (pastikan <script src="/socket.io/...") ada di index2.php)
	if (typeof io !== "undefined") {
		const socket = io(NODE_SERVER_URL); // Gunakan const/let karena di dalam DOMContentLoaded

		socket.on("connect", () => {
			console.log(
				`[Client WS] Connected to Socket.IO server at ${NODE_SERVER_URL}`,
			);
		});

		socket.on("service_update", (data) => {
			const { id, action } = data;

			// Definisikan Aksi yang menyebabkan refresh di halaman ini
			let actionsToRefresh;

			//  LOGIKA BARU: Tentukan aksi berdasarkan halaman saat ini
			// Perhatikan: window.location.pathname akan mendapatkan '/tampil.php' atau '/index2.php'

			if (window.location.pathname.includes("tampil.php")) {
				// Untuk halaman Aset (tampil.php), kita peduli pada perubahan Aset (INSERT, UPDATE, DELETE)
				actionsToRefresh = [
					"asset_insert",
					"asset_update",
					"asset_delete",
					"asset_bulk_insert",
				];
			} else if (window.location.pathname.includes("index2.php")) {
				// Untuk halaman Service (index2.php), kita peduli pada perubahan Service
				actionsToRefresh = [
					"service_insert",
					"service_claim",
					"service_complete",
				];
			} else {
				// Halaman lain, misalnya landing page, tidak perlu refresh
				return;
			}

			// Cek apakah aksi yang diterima termasuk dalam daftar refresh
			if (actionsToRefresh.includes(action)) {
				console.log(
					`[Client WS] Received CRITICAL Update (${action}). Triggering page reload.`,
				);

				window.location.reload();
			} else {
				console.log(
					`[Client WS] Received non-monitored update (${action}). Ignored for this view.`,
				);
			}
		});

		socket.on("disconnect", () => {
			console.warn("[Client WS] Disconnected from Socket.IO.");
		});
	} else {
		console.error(
			"JS ERROR: Socket.IO library (io) tidak ditemukan. Cek <script> tag di HTML.",
		);
	}

	// Tombol Registrasi
	if (addModal) {
		addModal.addEventListener("show.bs.modal", function (event) {
			const button = event.relatedTarget;

			// Check apakah pemicu modal adalah tombol 'Registrasi Aset'
			if (button && button.classList.contains("btn-register-service")) {
				// Ambil data dari tombol
				const serviceId = button.getAttribute("data-service-id");
				const hostname = button.getAttribute("data-hostname");
				const namaUser = button.getAttribute("data-user");
				const divisi = button.getAttribute("data-divisi");

				// Isi Hidden Field Service ID (PENTING untuk proses di tambah.php)
				document.getElementById("service-id-to-update").value = serviceId;

				// Isi field Hostname, Nama, dan Divisi
				const hostnameInput = document.getElementById("add-hostname");
				document.getElementById("add-nama").value = namaUser;
				document.getElementById("add-divisi").value = divisi;

				// Set Hostname dan buat readonly (tidak bisa diubah)
				hostnameInput.value = hostname;
				hostnameInput.setAttribute("readonly", "readonly");

				// Ubah judul modal
				const modalTitle = addModal.querySelector(".modal-title");
				modalTitle.textContent =
					"Registrasi Aset dari Service Request #" + serviceId;
			} else {
				// Reset/bersihkan modal jika dibuka dari tombol "Tambah Inventori" biasa
				document.getElementById("service-id-to-update").value = "";
				document.getElementById("add-hostname").value = "";
				document.getElementById("add-nama").value = "";
				document.getElementById("add-divisi").value = "";
				document.getElementById("add-hostname").removeAttribute("readonly");

				const modalTitle = addModal.querySelector(".modal-title");
				modalTitle.textContent = "Tambah Inventori";
			}
		});
	}
}); // Penutup DOMContentLoaded

// Fungsi global untuk memanggil toast dari mana saja (Socket.io atau event lain)
window.showToast = function (message, res = "success") {
	if (typeof bootstrap === "undefined") {
		console.warn("Bootstrap belum siap untuk menampilkan toast.");
		return;
	}

	const toastElement = document.getElementById("liveToast");
	const toastBody = document.getElementById("toast-body");

	if (toastElement && toastBody) {
		// Reset class warna
		toastElement.classList.remove(
			"bg-success",
			"bg-danger",
			"bg-warning",
			"bg-info",
			"text-dark",
		);

		// Pilih warna
		let bgClass = "bg-primary";
		if (res === "success") bgClass = "bg-success";
		if (res === "danger") bgClass = "bg-danger";
		if (res === "warning") bgClass = "bg-warning text-dark";

		toastElement.classList.add(...bgClass.split(" "));
		toastBody.textContent = message;

		const toast = new bootstrap.Toast(toastElement);
		toast.show();
	}
};
