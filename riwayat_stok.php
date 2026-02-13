<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "toko_bangunan");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Query riwayat stok dengan join produk, tanpa admin (karena tidak ada kolom admin_id)
$sql = "SELECT rs.id, rs.waktu, p.nama AS nama_produk, rs.stok_lama, rs.stok_baru, rs.jenis, rs.keterangan
        FROM riwayat_stok rs
        LEFT JOIN produk p ON rs.produk_id = p.id
        ORDER BY rs.waktu DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Riwayat Stok Produk</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet" />
<style>
body {
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg, #74ebd5 0%, #ACB6E5 100%);
    margin: 0;
    padding: 40px 15px;
    color: #2c3e50;
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: flex-start;
}
.container {
    max-width: 960px;
    width: 100%;
    background: #ffffffdd;
    padding: 35px 40px;
    border-radius: 15px;
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
}
h2 {
    text-align: center;
    margin-bottom: 35px;
    font-weight: 700;
    font-size: 2.5rem;
}
table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
}
th, td {
    padding: 14px 10px;
    text-align: left;
    border-bottom: 1px solid #ccc;
}
th {
    background: #2980b9;
    color: white;
}
.actions a {
    text-decoration: none;
    background-color: #2980b9;
    color: white;
    padding: 12px 22px;
    margin: 10px 10px 20px 0;
    border-radius: 8px;
    font-weight: 600;
    display: inline-block;
}
.actions a:hover {
    background-color: #1f6391;
}
</style>
</head>
<body>
<div class="container">
    <h2>Riwayat Stok Produk</h2>

    <div class="actions">
        <a href="produk_list.php">⏎ Kembali ke Daftar Produk</a>
        <a href="dashboard.php">⏎ Kembali ke Dashboard</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Waktu</th>
                <th>Produk</th>
                <th>Stok Lama</th>
                <th>Stok Baru</th>
                <th>Jenis</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['id']) ?></td>
                    <td><?= htmlspecialchars($row['waktu']) ?></td>
                    <td><?= htmlspecialchars($row['nama_produk'] ?? 'Produk tidak ditemukan') ?></td>
                    <td><?= (int)$row['stok_lama'] ?></td>
                    <td><?= (int)$row['stok_baru'] ?></td>
                    <td><?= htmlspecialchars($row['jenis']) ?></td>
                    <td><?= htmlspecialchars($row['keterangan']) ?></td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="7" style="text-align: center;">Belum ada riwayat stok.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>
