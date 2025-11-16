<?php
include "koneksi.php";

// Ambil perangkat Grace Period > 3 bulan
$notifikasi_grace = [];
$cek = mysqli_query($koneksi, "SELECT * FROM inventori WHERE status='Grace Period'");

while ($row = mysqli_fetch_assoc($cek)) {
    if (!empty($row['tanggal_keluar'])) {
        $tanggal_keluar = new DateTime($row['tanggal_keluar']);
        $batas_grace = clone $tanggal_keluar;
        $batas_grace->add(new DateInterval('P3M'));
        $hari_ini = new DateTime();

        if ($hari_ini >= $batas_grace) {
            $notifikasi_grace[] = $row;
        }
    }
}
?>

<!-- Tombol Notifikasi -->
<div class="d-flex justify-content-end mb-3">
  <button class="btn btn-outline-danger position-relative" data-bs-toggle="modal" data-bs-target="#notifikasiModal">
    🔔 Notifikasi
    <?php if (count($notifikasi_grace) > 0): ?>
      <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
        <?= count($notifikasi_grace) ?>
      </span>
    <?php endif; ?>
  </button>
</div>

<!-- Modal Notifikasi -->
<div class="modal fade" id="notifikasiModal" tabindex="-1" aria-labelledby="notifikasiModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title">Notifikasi Grace Period</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <?php if (count($notifikasi_grace) > 0): ?>
          <p class="mb-3">Berikut perangkat dengan status <strong>Grace Period</strong> lebih dari 3 bulan:</p>
          <ul class="list-group">
            <?php foreach ($notifikasi_grace as $item): ?>
              <li class="list-group-item d-flex justify-content-between align-items-center">
                <a href="tampil.php?status=Grace%20Period&cari=<?= urlencode($item['hostname']) ?>" class="text-danger fw-bold">
                  <?= $item['hostname'] ?>
                </a>
                <span class="badge bg-secondary">Tanggal Diterima: <?= date('d M Y', strtotime($item['tanggal_keluar'])) ?></span>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <p class="text-success">Tidak ada perangkat yang melewati batas Grace Period.</p>
        <?php endif; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>
