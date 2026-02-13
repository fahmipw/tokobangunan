<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "toko_bangunan");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$surat_jalan_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $conn->prepare("SELECT nomor, foto_bukti, timestamp_bukti FROM surat_jalan WHERE id = ?");
$stmt->bind_param("i", $surat_jalan_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Bukti Pengiriman</title>
</head>
<body>
<h2>Bukti Pengiriman</h2>

<?php if ($row): ?>
    <p>Nomor Surat Jalan: <?= htmlspecialchars($row['nomor']) ?></p>
    <?php if ($row['foto_bukti']): ?>
        <img src="<?= htmlspecialchars($row['foto_bukti']) ?>" alt="Bukti Pengiriman" style="max-width:100%;"><br>
        <p>Diambil: <?= date('d-m-Y H:i', strtotime($row['timestamp_bukti'])) ?></p>
    <?php else: ?>
        <p>Bukti pengiriman belum tersedia.</p>
    <?php endif; ?>
<?php else: ?>
    <p>Data tidak ditemukan.</p>
<?php endif; ?>

<a href="status_pengiriman.php">Kembali</a>
</body>
</html>
