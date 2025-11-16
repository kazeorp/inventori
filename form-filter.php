<form method="GET" class="row mb-3">
  <div class="col-md-3">
    <input type="text" name="cari" class="form-control" placeholder="Cari Hostname / Nama / NIK" value="<?= isset($_GET['cari']) ? $_GET['cari'] : ''; ?>">
  </div>
  <div class="col-md-2">
    <select name="rak" class="form-select">
      <option value="">-- Semua Rak --</option>
      <option value="GD-R11">GD-R11</option>
      <option value="GD-R12">GD-R12</option>
      <!-- Tambahkan rak lainnya -->
    </select>
  </div>
  <div class="col-md-2">
    <select name="type" class="form-select">
      <option value="">-- Semua Type --</option>
      <option value="Laptop">Laptop</option>
      <option value="PC">PC</option>
      <!-- Tambahkan type lainnya -->
    </select>
  </div>
  <div class="col-md-2">
    <button type="submit" class="btn btn-primary">Filter</button>
    <a href="tampil.php" class="btn btn-secondary">Reset</a>
  </div>
</form>
