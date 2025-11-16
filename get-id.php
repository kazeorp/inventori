<?php
include "koneksi.php";
$hostname = mysqli_real_escape_string($koneksi, $_GET['hostname']);
$query = mysqli_query($koneksi, "SELECT id FROM inventori WHERE hostname = '$hostname'");
$data = mysqli_fetch_assoc($query);
echo json_encode($data ?: []);
?>