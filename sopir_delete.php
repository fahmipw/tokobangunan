<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: sopir_list.php?error=" . urlencode("ID sopir tidak ditemukan."));
    exit();
}

$id = intval($_GET['id']);

$conn = new mysqli("localhost", "root", "", "toko_bangunan");
if ($conn->connect_error) {
    header("Location: sopir_list.php?error=" . urlencode("Koneksi gagal: " . $conn->connect_error));
    exit();
}

// Cek apakah ada pengiriman yang belum berstatus "Terkirim"
$query = "
    SELECT COUNT(*) as count
    FROM surat_jalan
    WHERE sopir_id = ? AND status <> 'Terkirim'
";

$stmt_check = $conn->prepare($query);
$stmt_check->bind_param("i", $id);
$stmt_check->execute();
$stmt_check->bind_result($count_pending);
$stmt_check->fetch();
$stmt_check->close();

if ($count_pending > 0) {
    header("Location: sopir_list.php?error=" . urlencode("Sopir tidak dapat dihapus karena masih memiliki $count_pending pengiriman yang belum selesai (Pending, Dalam Perjalanan, atau Ditolak)."));
    exit();
}
// Hapus surat jalan yang sudah terkirim
$stmt_delete_sj = $conn->prepare("DELETE FROM surat_jalan WHERE sopir_id = ? AND status = 'Terkirim'");
$stmt_delete_sj->bind_param("i", $id);
$stmt_delete_sj->execute();
$stmt_delete_sj->close();
// Hapus sopir
$stmt_delete = $conn->prepare("DELETE FROM sopir WHERE id = ?");
$stmt_delete->bind_param("i", $id);

if ($stmt_delete->execute()) {
    $stmt_delete->close();
    $conn->close();
    header("Location: sopir_list.php?message=" . urlencode("Sopir berhasil dihapus."));
    exit();
} else {
    $error = "Gagal menghapus sopir: " . $conn->error;
    $stmt_delete->close();
    $conn->close();
    header("Location: sopir_list.php?error=" . urlencode($error));
    exit();
}
?>
