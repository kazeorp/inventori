<div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form method="POST" action="tambah.php">
      <input type="hidden" name="service_id_to_update" id="service-id-to-update" value="">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Tambah Inventori</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body row">

          <div class="col-md-6 mb-3">
            <label class="fw-bold">Hostname</label>
            <input type="text" name="hostname" id="add-hostname" class="form-control" required>
          </div>
          <div class="col-md-6 mb-3">
            <label class="fw-bold">Rak</label>
            <input type="text" name="rak" id="add-rak" class="form-control" readonly>
          </div>

          <div class="col-md-6 mb-3">
            <label class="fw-bold">Status</label>
            <select name="status" id="add-status" class="form-control" required>
              <?php include 'status.php'; ?>
            </select>
          </div>
          <div class="col-md-6 mb-3">
            <label class="fw-bold">Domain</label>
            <select name="domain" id="add-domain" class="form-control" required>
              <option value="APP">APP</option>
              <option value="SMF">SMF</option>
              <option value="CKP">CKP</option>
              <option value="TGR">TGR</option>
              <option value="KRW">KRW</option>
            </select>
          </div>

          <div class="col-md-6 mb-3">
            <label class="fw-bold">Kategori Perangkat</label>
            <select name="device_category" id="add-device_category" class="form-control" required>
              <option value="">-- Pilih Kategori --</option>
              <option value="Laptop">Laptop</option>
              <option value="Desktop">Desktop</option>
              <option value="Server">Server</option>
              <option value="Printer">Printer</option>
            </select>
          </div>
          <div class="col-md-6 mb-3">
            <label class="fw-bold">Tipe (Merk/Model)</label>
            <select name="type" id="add-type" class="form-control" required>
              <?php include 'tipe-laptop.php'; ?>
            </select>
          </div>

          <div class="col-md-4 mb-3">
            <label class="fw-bold">Serial Number</label>
            <input type="text" name="serial_number" id="add-serial_number" class="form-control">
          </div>
          <div class="col-md-4 mb-3">
            <label class="fw-bold">RAM</label>
            <input type="text" name="ram" id="add-ram" class="form-control">
          </div>
          <div class="col-md-4 mb-3">
            <label class="fw-bold">Storage</label>
            <input type="text" name="storage" id="add-storage" class="form-control">
          </div>
          <div class="col-md-4 mb-3">
            <label class="fw-bold">Windows</label>
            <input type="text" name="win" id="add-win" class="form-control">
          </div>

          <div class="col-md-6 mb-3">
            <label class="fw-bold">Keterangan</label>
            <textarea name="keterangan" id="add-keterangan" class="form-control"></textarea>
          </div>
          <div class="col-md-6 mb-3">
            <label class="fw-bold">Kelengkapan</label>
            <select name="kelengkapan" id="add-kelengkapan" class="form-control">
              <option value="">-- Pilih Kelengkapan --</option>
              <option value="TAS">TAS</option>
              <option value="ADAPTOR">ADAPTOR</option>
              <option value="TAS DAN ADAPTOR">TAS DAN ADAPTOR</option>
              <option value="TAS DAN CONVERTER VGA">TAS DAN CONVERTER VGA</option>
              <option value="TAS DAN CONVERTER LAN">TAS DAN CONVERTER LAN</option>
              <option value="TAS, ADAPTOR, CONVERTER LAN">TAS, ADAPTOR, CONVERTER LAN</option>
              <option value="TAS, ADAPTOR, CONVERTER VGA">TAS, ADAPTOR, CONVERTER VGA</option>
              <option value="TAS, ADAPTOR, CONVERTER LAN & VGA">TAS, ADAPTOR, CONVERTER LAN & VGA</option>
            </select>
          </div>

		  <div class="col-md-6 mb-3">
            <label class="fw-bold">Tanggal Masuk</label>
            <input type="date" name="tanggal_masuk" id="add-tanggal_masuk" class="form-control" style="border-radius: var(--radius-md);" >
          </div>
          <div class="col-md-6 mb-3">
            <label class="fw-bold">Tanggal Keluar</label>
            <input type="date" name="tanggal_keluar" id="add-tanggal_keluar" class="form-control">
          </div>
          <div class="col-md-6 mb-3">
            <label class="fw-bold">NIK</label>
            <input type="text" name="nik" id="add-nik" class="form-control">
          </div>
          <div class="col-md-6 mb-3">
            <label class="fw-bold">Nama</label>
            <input type="text" name="nama" id="add-nama" class="form-control">
          </div>
          <div class="col-md-6 mb-3">
            <label class="fw-bold">Divisi</label>
            <input type="text" name="divisi" id="add-divisi" class="form-control">
          </div>

        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Simpan</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        </div>
      </div>
    </form>
  </div>
</div>