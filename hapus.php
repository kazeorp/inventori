<?php
include "koneksi.php";

$id = $_GET['id'];

$sql = "DELETE FROM inventori WHERE id='$id'";
if (mysqli_query($koneksi, $sql)) {
    echo "<script>alert('Data berhasil dihapus!'); window.location='tampil.php';</script>";
} else {
    echo "Error: " . mysqli_error($koneksi);
}
?>
