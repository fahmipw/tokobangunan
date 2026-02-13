<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: status_pengiriman.php");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_msg'] = "ID surat jalan tidak valid.";
    header("Location: status_pengiriman.php"); // Ganti dengan nama file list-mu
    exit();
}

$id = (int)$_GET['id'];

$conn = new mysqli("localhost", "root", "", "toko_bangunan");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$stmt = $conn->prepare("DELETE FROM surat_jalan WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    $_SESSION['success_msg'] = "Data pengiriman berhasil dihapus.";
} else {
    $_SESSION['error_msg'] = "Gagal menghapus data: " . $conn->error;
}

$stmt->close();
$conn->close();

header("Location: status_pengiriman.php"); // Ganti dengan nama file list-mu
exit();
