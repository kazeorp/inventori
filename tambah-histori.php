<?php

// Pastikan tidak ada spasi/baris kosong di atas tag <?php
// =======================================================
// 1. PENGATURAN AWAL & VALIDASI METHOD
// =======================================================

include 'session.php';
include 'koneksi.php';

// --- Definisi Mapping Status ke Rak (Sama dengan JS) ---
$statusToRak = [
    "Spare" => "GD-R11",
    "Pending Service" => "GD-R12",
    "Grace Period" => "GD-R13",
    "Scrap" => "GD-R4",
    "MT" => "GD-R8",
    "Ready to Assign" => "GD-R9",
    "Assign" => "Assign",
    "Loan" => "Loan"
];

date_default_timezone_set('Asia/Jakarta');

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: tampil.php");
    exit;
}

if (($_SESSION['role'] ?? 'normal') !== 'admin' && ($_SESSION['role'] ?? 'normal') !== 'superadmin') {
    header("Location: index.php");
    exit;
}


// =======================================================
// 2. AMBIL DATA FORM & VALIDASI DASAR
// =======================================================

// --- A. PENGAMBILAN DATA (Gunakan nama input yang benar) ---

$inventori_id = (int)($_POST['inventori_id'] ?? 0);
$hostname = $_POST['hostname'] ?? '';

// KOREKSI: Ambil dari 'new_status' BUKAN 'aksi'
$aksi = $_POST['new_status'] ?? 'Diservis';

$ticket = $_POST['ticket'] ?? '';
$catatan_aktivitas = $_POST['catatan'] ?? '';
$oleh = $_POST['oleh'] ?? 'Admin';
$tanggal = date("Y-m-d H:i:s");

// 💡 ADMIN YANG SEDANG LOGIN UNTUK LAST_ADMIN
$admin_name = $_SESSION['username'] ?? 'System';
$admin_name_safe = mysqli_real_escape_string($koneksi, $admin_name);


// --- B. DATA LOAN & SERVICE ---

$id_service = (int)($_POST['id_service'] ?? 0);

// Logika Loan: Didasarkan pada keberadaan toggle
$is_loan_active = isset($_POST['toggle_loan']);

$loan_hostname_val = $_POST['loan_hostname'] ?? ''; // Hostname aset pengganti
// Ambil data user dari aset A untuk dicopy ke aset B
$loan_nik = $_POST['loan_nik'] ?? '';
$loan_nama = $_POST['loan_nama'] ?? '';
$loan_divisi = $_POST['loan_divisi'] ?? '';
$loan_catatan = $_POST['loan_catatan'] ?? '';


// =======================================================
// VALIDASI KONTINUASI
// =======================================================

if ($inventori_id <= 0 || empty($hostname) || empty($aksi) || empty($ticket) || empty($catatan_aktivitas)) {

    $pesan_error = "Error: Data Aktivitas (Aset Utama) tidak lengkap.";

    // Check validasi jika Loan diaktifkan
    if ($is_loan_active && empty($loan_hostname_val)) {
        $pesan_error = "Error: Aset Pengganti wajib dipilih karena Loan diaktifkan.";
    }

    // Hanya lakukan redirect jika ada error kritis
    if (empty($hostname) || empty($aksi) || empty($ticket) || ($is_loan_active && empty($loan_hostname_val))) {
        $_SESSION['notifikasi'] = ['status' => 'error', 'pesan' => $pesan_error];
        // Redirect ke detail aset, bukan dashboard
        header("Location: detail_aset.php?hostname=" . urlencode($hostname));
        exit;
    }
}

// =======================================================
// 3. MULAI TRANSAKSI & LOGIKA UTAMA
// =======================================================

mysqli_begin_transaction($koneksi);

try {
    // ----------------------------------------------------
    // A. PEMROSESAN ASET A (Aset yang Discanned)
    // ----------------------------------------------------

    if ($is_loan_active) {
    // Aset A akan memiliki status: 'Pending Service' atau 'Scrap' ($aksi)

    // 1. Tentukan Nilai Rak Baru dari Mapping
    $rak_baru = $statusToRak[$aksi] ?? ''; // Mengambil rak dari mapping

    // 2. Buat string SET RAK
    $set_rak = "";
    if (!empty($rak_baru) && $rak_baru !== 'Assign' && $rak_baru !== 'Loan') {
        // Hanya update jika rak adalah lokasi fisik (bukan Assign/Loan)
        $set_rak = ", rak = '{$rak_baru}'";
    }

    // 3. Update Status, Rak, dan Admin Terakhir Aset A
    // 💡 Perubahan 1: Tambahkan last_admin ke UPDATE Aset A
    $query_update_a = "
        UPDATE inventori
        SET status = '$aksi' {$set_rak},
        tanggal_masuk = NOW(),
        last_admin = '{$admin_name_safe}'
        WHERE id = '$inventori_id'
    ";

    if (!mysqli_query($koneksi, $query_update_a)) {
        throw new Exception("Gagal update status Aset {$hostname} ke {$aksi}: " . mysqli_error($koneksi));
    }

        // Perbarui $aksi untuk log agar lebih jelas
        $aksi_log = "Loan aset {$loan_hostname_val} dibuat & Status Aset {$hostname} diubah menjadi {$aksi}";

    } else {
        // JIKA LOAN TIDAK AKTIF: JANGAN UPDATE STATUS INVENTORI
        // Aksi log menggunakan nilai dari form ('Diservis')
        $aksi_log = $aksi;
    }

    // 2. Logika Histori Aset A (WAJIB)
    // Catatan: Histori ini adalah histori aktivitas user, bukan perubahan data.
    $query_insert_histori_a = "
        INSERT INTO histori_aset (inventori_id, tanggal, aksi, ticket, oleh, catatan)
        VALUES ('$inventori_id', '$tanggal','$aksi_log', '$ticket', '$oleh', '$catatan_aktivitas')
    ";
    if (!mysqli_query($koneksi, $query_insert_histori_a)) {
        throw new Exception("Gagal insert histori Aset A: " . mysqli_error($koneksi));
    }


// ----------------------------------------------------
// B. PEMROSESAN ASET B (Jika Loan Aktif)
// ----------------------------------------------------
if ($is_loan_active) {
    // 1. Ambil ID Aset B berdasarkan hostname
    $stmt_get_loan_id = mysqli_prepare($koneksi, "SELECT id FROM inventori WHERE hostname = ?");

    mysqli_stmt_bind_param($stmt_get_loan_id, 's', $loan_hostname_val);
    mysqli_stmt_execute($stmt_get_loan_id);
    $result_loan_id = mysqli_stmt_get_result($stmt_get_loan_id);
    $row_loan_id = mysqli_fetch_assoc($result_loan_id);
    mysqli_stmt_close($stmt_get_loan_id);

    if (!$row_loan_id) {
        throw new Exception("Hostname Aset Loan '{$loan_hostname_val}' tidak ditemukan di inventori.");
    }
    $id_loan_aset = (int)$row_loan_id['id'];

    // 2. Update status Aset B ke 'Loan' dan salin data user Aset A
    // 💡 Perubahan 2: Tambahkan last_admin ke UPDATE Aset B
    $stmt_update_b = mysqli_prepare($koneksi, "
        UPDATE inventori
        SET status = 'Loan', nik = ?, nama = ?, divisi = ?, tanggal_keluar = NOW(),
        last_admin = ?
        WHERE id = ?
    ");

    // Binding: s (nik), s (nama), s (divisi), s (admin_name), i (id_loan_aset)
    // 💡 Perubahan 3: Tambahkan $admin_name di parameter binding
    mysqli_stmt_bind_param($stmt_update_b, 'ssssi',
        $loan_nik,
        $loan_nama,
        $loan_divisi,
        $admin_name, // <-- Tambahan Admin Name
        $id_loan_aset // Gunakan ID Aset B
    );
    if (!mysqli_stmt_execute($stmt_update_b)) {
        throw new Exception("Gagal update status Aset B (Loan): " . mysqli_stmt_error($stmt_update_b));
    }
    mysqli_stmt_close($stmt_update_b);

    // 3. Insert Histori Aset B - KOREKSI ID DARI $inventori_id MENJADI $id_loan_aset
    $catatan_loan_lengkap = "LOAN kepada user '{$loan_nama}' ({$loan_nik}, Divisi: {$loan_divisi}). Tiket: {$ticket}. Catatan Tambahan: {$loan_catatan}";

    $stmt_insert_histori_b = mysqli_prepare($koneksi, "
        INSERT INTO histori_aset (inventori_id, tanggal, aksi, ticket, oleh, catatan)
        VALUES (?, ?, 'Loan', ?, ?, ?)
    ");

    // Binding: i (inventori_id), s (tanggal), s (ticket), s (oleh), s (catatan)
    mysqli_stmt_bind_param($stmt_insert_histori_b, 'issss',
        $id_loan_aset, // <-- GUNAKAN ID ASET LOAN (ASET B)
        $tanggal,
        $ticket,
        $oleh,
        $catatan_loan_lengkap
    );
    if (!mysqli_stmt_execute($stmt_insert_histori_b)) {
        throw new Exception("Gagal insert histori Aset B: " . mysqli_stmt_error($stmt_insert_histori_b));
    }
    mysqli_stmt_close($stmt_insert_histori_b);
}

// ----------------------------------------------------
// C. PEMROSESAN SERVICE_LIST (Mencatat Klaim)
// ----------------------------------------------------
$pesan_sukses = "Aktivitas untuk aset {$hostname} berhasil dicatat.";
$claim_performed = false;

// Aksi ini harus dijalankan HANYA JIKA ada ID Service yang dikirim
if ($id_service > 0) {

    $current_admin_name = mysqli_real_escape_string($koneksi, $_SESSION['username'] ?? 'Admin');
    $current_admin_id = (int)($_SESSION['admin_id'] ?? 0);

    $query_update_service = "
        UPDATE service_list
        SET claim_status = 'On Service',
            current_admin_id = '$current_admin_id',
            current_admin_name = '$current_admin_name',
            loan_hostname = " . ($is_loan_active ? "'$loan_hostname_val'" : "NULL") . ",
            admin_claim_id = COALESCE(admin_claim_id, '$current_admin_id'),
            admin_claim_name = COALESCE(admin_claim_name, '$current_admin_name')
        WHERE id_service = '$id_service' AND finish_status IS NULL AND claim_status IS NULL
    ";

    if (!mysqli_query($koneksi, $query_update_service)) {
        throw new Exception("Gagal mengupdate status klaim servis: " . mysqli_error($koneksi));
    }

    // Cek apakah ada baris yang terpengaruh (servis berhasil diklaim)
    if (mysqli_affected_rows($koneksi) > 0) {
        $claim_performed = true;
        $loan_message = $is_loan_active ? " (Loan/Status Berat Tercatat)" : " (Servis Ringan)";
        $pesan_sukses = "Servis #{$id_service} berhasil diklaim{$loan_message} & Aktivitas dicatat.";
    } else {
        // Servis mungkin sudah diklaim, tetap lanjut ke commit jika tidak ada error lain
        $pesan_sukses = "Aktivitas dicatat, tetapi Servis #{$id_service} mungkin sudah diklaim sebelumnya.";
    }
}

    // =======================================================
    // 4. COMMIT & REDIRECTION SUKSES
    // =======================================================
    mysqli_commit($koneksi);

    $loan_message = $is_loan_active ? " Aset pengganti juga berhasil dipinjamkan." : "";

    $pesan_final = "Aktivitas untuk aset {$hostname} berhasil dicatat.{$loan_message}";
    $redirect_url = "detail-aset.php?hostname=" . urlencode($hostname);

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => $pesan_final, 'redirect' => $redirect_url]);
    exit;

} catch (Exception $e) {
    // =======================================================
    // 5. ROLLBACK & REDIRECTION GAGAL
    // =======================================================
    mysqli_rollback($koneksi);

    $target_redirect = !empty($hostname) ? "detail-aset.php?hostname=" . urlencode($hostname) : "dashboard.php";
    $pesan_error = "Aktivitas GAGAL diproses! " . $e->getMessage();

    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $pesan_error, 'redirect' => $target_redirect]);
    exit;
}
?>