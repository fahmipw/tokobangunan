<?php
// cek_koneksi.php
$host = "localhost";
$user = "root";
$password = "";
$database = "toko_bangunan";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// opsional: atur charset supaya kompatibel utf8mb4
$conn->set_charset("utf8mb4");
?>
