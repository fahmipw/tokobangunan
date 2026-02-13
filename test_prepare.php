<?php
$conn = new mysqli("localhost", "root", "", "toko_bangunan");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$kecamatan_nama = "Pamulang";

$stmt = $conn->prepare("SELECT id FROM kecamatan WHERE nama = ?");
if ($stmt === false) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("s", $kecamatan_nama);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->bind_result($kecamatan_id);
    $stmt->fetch();
    echo "ID Kecamatan untuk $kecamatan_nama adalah: $kecamatan_id";
} else {
    echo "Kecamatan tidak ditemukan.";
}

$stmt->close();
$conn->close();
