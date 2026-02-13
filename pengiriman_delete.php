<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: pengiriman_list.php");
    exit();
}

$id = intval($_GET['id']);

$conn = new mysqli("localhost", "root", "", "toko_bangunan");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$stmt = $conn->prepare("DELETE FROM pengiriman WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    $stmt->close();
    header("Location: pengiriman_list.php?msg=deleted");
} else {
    echo "Gagal menghapus data: " . $stmt->error;
}
?>
