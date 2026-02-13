<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: toko_list.php");
    exit();
}

$id = intval($_GET['id']);

$conn = new mysqli("localhost", "root", "", "toko_bangunan");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Hapus pengiriman terkait
$stmt2 = $conn->prepare("DELETE FROM pengiriman WHERE toko_id=?");
if (!$stmt2) {
    die("Prepare failed (hapus pengiriman): " . $conn->error);
}
$stmt2->bind_param("i", $id);
if (!$stmt2->execute()) {
    die("Execute failed (hapus pengiriman): " . $stmt2->error);
}
$stmt2->close();

// Hapus toko
$stmt = $conn->prepare("DELETE FROM toko WHERE id=?");
if (!$stmt) {
    die("Prepare failed (hapus toko): " . $conn->error);
}
$stmt->bind_param("i", $id);
if (!$stmt->execute()) {
    die("Execute failed (hapus toko): " . $stmt->error);
}
$stmt->close();

header("Location: toko_list.php");
exit();
?>
