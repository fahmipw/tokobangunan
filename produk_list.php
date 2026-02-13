<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "toko_bangunan");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$admin_id = $_SESSION['admin_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn->begin_transaction();
    try {
        $result = $conn->query("SELECT id, stok FROM produk ORDER BY id");
        if (!$result) throw new Exception("Gagal ambil data produk");

        while ($row = $result->fetch_assoc()) {
            $id = (int)$row['id'];
            $stok_lama = (int)$row['stok'];
            $stok_baru = $stok_lama;

            // Stok opname
            $stok_opname = isset($_POST['stok'][$id]) ? intval($_POST['stok'][$id]) : $stok_baru;
            if ($stok_opname != $stok_baru) {
                $stok_baru = $stok_opname;
                $jenis = 'stok opname';
                $keterangan = "Stok opname produk ID $id";
            }

            // Barang masuk
            $barang_masuk = isset($_POST['barang_masuk'][$id]) ? intval($_POST['barang_masuk'][$id]) : 0;
            if ($barang_masuk > 0) {
                $stok_baru += $barang_masuk;
                $jenis = 'barang masuk';
                $keterangan = "Barang masuk produk ID $id, qty $barang_masuk";
            }

            // Barang keluar
            $barang_keluar = isset($_POST['barang_keluar'][$id]) ? intval($_POST['barang_keluar'][$id]) : 0;
            if ($barang_keluar > 0) {
                if ($barang_keluar > $stok_baru) {
                    throw new Exception("Stok produk ID $id tidak cukup untuk barang keluar.");
                }
                $stok_baru -= $barang_keluar;
                $jenis = 'barang keluar';
                $keterangan = "Barang keluar produk ID $id, qty $barang_keluar";
            }

            if ($stok_baru != $stok_lama) {
                $stmt1 = $conn->prepare("UPDATE produk SET stok = ? WHERE id = ?");
                $stmt1->bind_param("ii", $stok_baru, $id);
                if (!$stmt1->execute()) throw new Exception("Gagal update stok produk ID $id");
                $stmt1->close();

                $stmt2 = $conn->prepare("INSERT INTO riwayat_stok (produk_id, waktu, stok_lama, stok_baru, jenis, keterangan) VALUES (?, NOW(), ?, ?, ?, ?)");
                if (!$stmt2) throw new Exception("Prepare failed: " . $conn->error);
                $stmt2->bind_param("iisss", $id, $stok_lama, $stok_baru, $jenis, $keterangan);
                if (!$stmt2->execute()) throw new Exception("Gagal simpan riwayat stok");
                $stmt2->close();
            }
        }

        $conn->commit();
        $_SESSION['success_msg'] = "Stok berhasil diperbarui.";
        header("Location: produk_list.php");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error_msg'] = $e->getMessage();
        header("Location: produk_list.php");
        exit();
    }
}

$result = $conn->query("SELECT * FROM produk ORDER BY id");

$success_msg = $_SESSION['success_msg'] ?? '';
unset($_SESSION['success_msg']);
$error_msg = $_SESSION['error_msg'] ?? '';
unset($_SESSION['error_msg']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Daftar Produk & Stok Opname</title>
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
.notif.success {
    background-color: #2ecc71;
    padding: 16px;
    border-radius: 10px;
    color: white;
    text-align: center;
    font-weight: 600;
    margin-bottom: 20px;
}
.notif.error {
    background-color: #e74c3c;
    padding: 16px;
    border-radius: 10px;
    color: white;
    text-align: center;
    font-weight: 600;
    margin-bottom: 20px;
}
.table-wrapper {
    overflow-x: auto;
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
input[type="number"] {
    width: 80px;
    padding: 5px 8px;
    font-size: 1rem;
}
.btn-save {
    background-color: #27ae60;
    color: white;
    padding: 12px 24px;
    font-weight: bold;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 1rem;
}
.btn-save:hover {
    background-color: #1e8449;
}

/* Tombol navigasi */
.actions {
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
    margin-bottom: 25px;
    gap: 10px;
}
.actions a {
    text-decoration: none;
    color: white;
    padding: 12px 22px;
    margin: 10px 10px 20px 0;
    border-radius: 8px;
    font-weight: 600;
    display: inline-block;
}

.actions a:first-child {
    background-color: #2980b9; /* Tambah Produk - Biru */
}
.actions a:first-child:hover {
    background-color: #1f6391;
}

.actions a:nth-child(2) {
    background-color: #2980b9; /* Riwayat Stok - Ungu */
}
.actions a:nth-child(2):hover {
    background-color: #1f6391;
}

.actions a:nth-child(3) {
    background-color: gray; /* Kembali ke Dashboard - Oranye */
}

.actions a:hover {
    background-color: #6c7378ff;
}
</style>
</head>
<body>
<div class="container">
    <h2>Daftar Produk & Stok Opname</h2>

    <?php if ($success_msg): ?>
        <div class="notif success"><?= htmlspecialchars($success_msg) ?></div>
    <?php endif; ?>
    <?php if ($error_msg): ?>
        <div class="notif error"><?= htmlspecialchars($error_msg) ?></div>
    <?php endif; ?>

    <div class="actions">
        <a href="produk_add.php">+ Tambah Produk</a>
        <a href="riwayat_stok.php">📄 Riwayat Stok</a>
        <a href="dashboard.php">🏠︎ Kembali ke Dashboard</a>
    </div>

    <form method="post" action="">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Produk</th>
                        <th>Stok Sekarang</th>
                        <th>Stok Opname</th>
                        <th>Barang Masuk</th>
                        <th>Barang Keluar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['id']) ?></td>
                            <td><?= htmlspecialchars($row['nama']) ?></td>
                            <td><?= (int)$row['stok'] ?></td>
                            <td>
                                <input type="number" name="stok[<?= $row['id'] ?>]" value="<?= (int)$row['stok'] ?>" min="0">
                            </td>
                            <td>
                                <input type="number" name="barang_masuk[<?= $row['id'] ?>]" value="0" min="0">
                            </td>
                            <td>
                                <input type="number" name="barang_keluar[<?= $row['id'] ?>]" value="0" min="0">
                            </td>
                            <td>
                            <a href="produk_edit.php?id=<?= $row['id'] ?>" style="color: #2980b9; text-decoration: none;">
                                <i class="fas fa-edit"></i> Edit
                            </a> |
                            <a href="produk_delete.php?id=<?= $row['id'] ?>" onclick="return confirm('Yakin ingin menghapus produk ini?')" style="color: #e74c3c; text-decoration: none;">
                                <i class="fas fa-trash-alt"></i> Hapus
                            </a>
                        </td>

                        </tr>
                    <?php endwhile; ?>
                    <tr>
                        <td colspan="7" style="text-align: center;">
                            <button type="submit" class="btn-save">Simpan Perubahan Stok</button>
                        </td>
                    </tr>
                <?php else: ?>
                    <tr><td colspan="7" style="text-align: center;">Belum ada produk tersedia.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </form>
</div>
</body>
</html>
