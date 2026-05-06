// Dapatkan elemen modal
const tipeLaptopModal = document.getElementById("tipeLaptopModal");

// Tambahkan event listener saat modal ditampilkan
tipeLaptopModal.addEventListener("show.bs.modal", function () {
	const loadingIndicator = document.getElementById("tipe-loading-indicator");
	const dataContainer = document.getElementById("tipe-data-container");

	// Tampilkan loading, kosongkan container sebelum memuat data baru
	loadingIndicator.style.display = "block";
	dataContainer.innerHTML = "";

	// Ambil template HTML lengkap (termasuk Form Tambah yang tersembunyi)
	const apiUrl = "ajax_tipe_content.php";

	fetch(apiUrl)
		.then((response) => {
			if (!response.ok) throw new Error("Gagal memuat data tipe aset.");
			return response.text();
		})
		.then((html) => {
			dataContainer.innerHTML = html;
		})
		.catch((error) => {
			dataContainer.innerHTML = `<div class="alert alert-danger">Gagal memuat data: ${error.message}</div>`;
		})
		.finally(() => {
			loadingIndicator.style.display = "none";
		});
});

// Event listener delegasi untuk Edit dan Hapus
tipeLaptopModal.addEventListener("click", function (event) {
	// Handle Edit
	const editBtn = event.target.closest(".btn-edit-tipe");
	if (editBtn) {
		const tr = editBtn.closest("tr");
		const id = tr.dataset.id;
		const nama = tr.dataset.tipe;

		document.getElementById("edit-id-tipe").value = id;
		document.getElementById("edit-nama-tipe").value = nama;
		return;
	}

	// Handle Delete
	const deleteBtn = event.target.closest(".btn-delete-tipe");
	if (deleteBtn) {
		const tr = deleteBtn.closest("tr");
		const id = tr.dataset.id;
		const nama = tr.dataset.tipe;

		if (confirm(`Apakah Anda yakin ingin menghapus tipe "${nama}"?`)) {
			window.location.href = `proses-tipe-laptop.php?action=hapus&id_tipe=${id}`;
		}
	}
});

// *Pastikan kode ini di-load setelah Bootstrap JS dan jQuery (jika digunakan)*
