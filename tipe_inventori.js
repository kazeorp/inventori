// Dapatkan elemen modal
const tipeLaptopModal = document.getElementById("tipeLaptopModal");

// Tambahkan event listener saat modal ditampilkan
tipeLaptopModal.addEventListener("show.bs.modal", function () {
	const loadingIndicator = document.getElementById("tipe-loading-indicator");
	const dataContainer = document.getElementById("tipe-data-container");

	// Tampilkan loading, kosongkan container sebelum memuat data baru
	loadingIndicator.style.display = "block";
	dataContainer.innerHTML = "";

	// URL untuk memanggil endpoint API yang baru kita buat
	const apiUrl = "proses-tipe-laptop.php?action=get_all_tipe";

	fetch(apiUrl)
		.then((response) => {
			// Cek apakah respons sukses (status 200)
			if (!response.ok) {
				throw new Error(
					"Gagal mengambil data tipe. Status: " + response.status,
				);
			}
			return response.json(); // Mengubah respons menjadi objek JavaScript/JSON
		})
		.then((data) => {
			let htmlContent = "";

			if (data.length > 0) {
				// Mulai membangun tabel
				htmlContent += '<div class="table-responsive">';
				htmlContent += '<table class="table table-sm table-hover">';
				htmlContent +=
					'<thead class="table-light"><tr><th>ID</th><th>Nama Tipe</th><th class="text-center">Aksi</th></tr></thead>';
				htmlContent += "<tbody>";

				// Loop melalui data JSON
				data.forEach((tipe) => {
					htmlContent += `<tr>
                                        <td>${tipe.id_tipe}</td>
                                        <td>${tipe.nama_tipe}</td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-info edit-btn"
                                                    data-id="${tipe.id_tipe}"
                                                    data-nama="${tipe.nama_tipe}"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editTipeModal">
                                                <i class="bi bi-pencil"></i> Edit
                                            </button>
                                            <button class="btn btn-sm btn-danger delete-btn"
                                                    data-id="${tipe.id_tipe}"
                                                    data-nama="${tipe.nama_tipe}">
                                                <i class="bi bi-trash"></i> Hapus
                                            </button>
                                        </td>
                                    </tr>`;
				});

				htmlContent += "</tbody></table>";
				htmlContent += "</div>";
			} else {
				htmlContent =
					'<p class="text-muted text-center mt-4">Belum ada tipe inventori yang ditambahkan.</p>';
			}

			dataContainer.innerHTML = htmlContent; // Tampilkan HTML ke container
		})
		.catch((error) => {
			// Tangani error, tampilkan pesan di container
			console.error("Error saat memuat tipe inventori:", error);
			dataContainer.innerHTML = `<div class="alert alert-danger" role="alert">
                                        Gagal memuat data tipe. (${error.message}). Cek Konsol Browser.
                                    </div>`;
		})
		.finally(() => {
			// Selalu sembunyikan loading indicator
			loadingIndicator.style.display = "none";
		});
});

// Event listener untuk tombol edit agar modal edit terisi
tipeLaptopModal.addEventListener("click", function (event) {
	if (event.target.classList.contains("edit-btn")) {
		const button = event.target;
		const id_tipe = button.getAttribute("data-id");
		const nama_tipe = button.getAttribute("data-nama");

		// Isi form edit
		document.getElementById("edit-id-tipe").value = id_tipe;
		document.getElementById("edit-nama-tipe").value = nama_tipe;
	}
});

// *Pastikan kode ini di-load setelah Bootstrap JS dan jQuery (jika digunakan)*
