<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    $_SESSION['error_msg'] = "ID produk tidak ditemukan.";
    header("Location: produk_list.php");
    exit();
}

$id = intval($_GET['id']);

$conn = new mysqli("localhost", "root", "", "toko_bangunan");
if ($conn->connect_error) {
    $_SESSION['error_msg'] = "Koneksi gagal: " . $conn->connect_error;
    header("Location: produk_list.php");
    exit();
}

// Periksa apakah produk terkait dengan pengiriman yang belum selesai
$stmt_check = $conn->prepare("SELECT COUNT(*) FROM pengiriman pr JOIN surat_jalan sj ON pr.surat_jalan_id = sj.id WHERE pr.produk_id = ? AND sj.status <> 'Terkirim'");
$stmt_check->bind_param("i", $id);
$stmt_check->execute();
$stmt_check->bind_result($count_pending);
$stmt_check->fetch();
$stmt_check->close();

if ($count_pending > 0) {
    $_SESSION['error_msg'] = "Produk tidak dapat dihapus karena masih ada " . $count_pending . " pengiriman yang belum selesai (Pending/Dalam Perjalanan/Ditolak).";
    header("Location: produk_list.php");
    exit();
}

// Hapus dulu pengiriman yang terkait produk (yang sudah Terkirim)
$stmt_del_pengiriman = $conn->prepare("DELETE FROM pengiriman WHERE produk_id = ?");
$stmt_del_pengiriman->bind_param("i", $id);
$stmt_del_pengiriman->execute();
$stmt_del_pengiriman->close();

// Hapus produk
$stmt = $conn->prepare("DELETE FROM produk WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        $_SESSION['success_msg'] = "Produk berhasil dihapus.";
    } else {
        $_SESSION['error_msg'] = "Gagal menghapus produk. Produk tidak ditemukan.";
    }
} else {
    $_SESSION['error_msg'] = "Gagal menghapus produk: " . $conn->error;
}

$stmt->close();
$conn->close();

header("Location: produk_list.php");
exit();
?>
