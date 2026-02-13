<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "toko_bangunan");
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_produk = $_POST['id_produk'] ?? '';
    $stok_baru = intval($_POST['stok_baru'] ?? -1);

    if ($id_produk === '' || $stok_baru < 0) {
        $error = 'Data tidak valid. Mohon pilih produk dan isi stok dengan benar.';
    } else {
        // Ambil stok lama
        $stmt0 = $conn->prepare("SELECT stok FROM produk WHERE id = ?");
        $stmt0->bind_param("s", $id_produk);
        $stmt0->execute();
        $stmt0->bind_result($stok_lama);
        if (!$stmt0->fetch()) {
            $error = "Produk tidak ditemukan.";
        } else {
            $stmt0->close();

            // Update stok
            $stmt1 = $conn->prepare("UPDATE produk SET stok = ? WHERE id = ?");
            $stmt1->bind_param("is", $stok_baru, $id_produk);
            if (!$stmt1->execute()) {
                $error = "Gagal memperbarui stok: " . $stmt1->error;
            } else {
                $stmt1->close();

                // Catat riwayat opname
                $stmt2 = $conn->prepare(
                    "INSERT INTO riwayat_stok (produk_id, waktu, stok_lama, stok_baru, jenis, keterangan, admin_id)
                     VALUES (?, NOW(), ?, ?, 'opname', ?, ?)"
                );
                $keterangan = "Stok opname/manual update";
                $admin_id = $_SESSION['admin_id'];
                $stmt2->bind_param("iisi i", $id_produk, $stok_lama, $stok_baru, $keterangan, $admin_id);
                $stmt2->execute();
                $stmt2->close();

                $success = "Stok produk berhasil diperbarui.";
            }
        }
    }
}


// Ambil data semua produk untuk dropdown
$produk_result = $conn->query("SELECT id, nama, stok FROM produk ORDER BY nama");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Stok Opname - Toko Bangunan</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap');
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #74ebd5 0%, #ACB6E5 100%);
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding-top: 60px;
            color: #2c3e50;
        }
        .container {
            background: #fff;
            width: 420px;
            padding: 30px 35px;
            border-radius: 10px;
            box-shadow: 0 6px 18px rgba(44, 62, 80, 0.15);
        }
        h2 {
            text-align: center;
            font-weight: 700;
            font-size: 1.8rem;
            margin-bottom: 25px;
        }
        label {
            display: block;
            font-weight: 600;
            margin-bottom: 10px;
            margin-top: 15px;
        }
        select, input[type="number"] {
            width: 100%;
            padding: 12px 14px;
            font-size: 1rem;
            border-radius: 6px;
            border: 1.8px solid #bdc3c7;
            margin-bottom: 15px;
            box-sizing: border-box;
        }
        input[type="number"]:focus, select:focus {
            border-color: #2980b9;
            outline: none;
        }
        button {
            width: 100%;
            background-color: #27ae60;
            border: none;
            padding: 14px 0;
            border-radius: 8px;
            color: white;
            font-weight: 700;
            font-size: 1.1rem;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        button:hover {
            background-color: #1e8449;
        }
        .message {
            text-align: center;
            padding: 12px;
            border-radius: 8px;
            font-weight: 600;
            margin-bottom: 20px;
        }
        .success {
            background-color: #d4efdf;
            color: #27ae60;
        }
        .error {
            background-color: #fce4e4;
            color: #e74c3c;
        }
        .back-link {
            display: block;
            margin-top: 20px;
            text-align: center;
            color: #2980b9;
            font-weight: 600;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Stok Opname</h2>

        <?php if ($success): ?>
            <div class="message success"><?= htmlspecialchars($success) ?></div>
        <?php elseif ($error): ?>
            <div class="message error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post" action="">
            <label for="id_produk">Pilih Produk:</label>
            <select name="id_produk" id="id_produk" required>
                <option value="">-- Pilih Produk --</option>
                <?php while ($row = $produk_result->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>">
                        <?= htmlspecialchars($row['nama']) ?> (Stok: <?= $row['stok'] ?>)
                    </option>
                <?php endwhile; ?>
            </select>

            <label for="stok_baru">Stok Baru:</label>
            <input type="number" name="stok_baru" id="stok_baru" min="0" required>

            <button type="submit">Perbarui Stok</button>
        </form>

        <a href="produk_list.php" class="back-link">&larr; Kembali ke Daftar Produk</a>
    </div>
</body>
</html>
