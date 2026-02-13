<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: status_pengiriman.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "toko_bangunan");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_msg'] = "ID surat jalan tidak valid.";
    header("Location: status_pengiriman.php"); // Ganti dengan nama file list-mu
    exit();
}

$id = (int)$_GET['id'];

// Proses update ketika form disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $status = $_POST['status'] ?? '';
    $alasan = trim($_POST['alasan'] ?? '');

    if (empty($status)) {
        $error = "Status harus diisi.";
    } else {
        $stmt = $conn->prepare("UPDATE surat_jalan SET status = ?, alasan = ? WHERE id = ?");
        $stmt->bind_param("ssi", $status, $alasan, $id);
        if ($stmt->execute()) {
            $_SESSION['success_msg'] = "Status pengiriman berhasil diupdate.";
            $stmt->close();
            $conn->close();
            header("Location: status_pengiriman.php"); // Ganti dengan nama file list-mu
            exit();
        } else {
            $error = "Gagal mengupdate status: " . $conn->error;
        }
    }
}

// Ambil data surat jalan untuk ditampilkan di form
$stmt = $conn->prepare("SELECT nomor, tanggal, status, alasan FROM surat_jalan WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) {
    $_SESSION['error_msg'] = "Data surat jalan tidak ditemukan.";
    header("Location: status_pengiriman.php"); // Ganti dengan nama file list-mu
    exit();
}
$row = $result->fetch_assoc();

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Edit Status Pengiriman</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet" />
    <style>
        body { font-family: 'Poppins', sans-serif; padding: 20px; background: #f0f4f8; color: #333; }
        form { max-width: 500px; margin: 30px auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 3px 10px rgba(0,0,0,0.1); }
        label { display: block; margin-bottom: 8px; font-weight: 600; }
        input[type=text], select, textarea { width: 100%; padding: 10px; margin-bottom: 15px; border-radius: 6px; border: 1px solid #ccc; font-size: 14px; }
        textarea { resize: vertical; min-height: 80px; }
        button { background: #3498db; color: white; padding: 10px 20px; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; }
        button:hover { background: #2980b9; }
        .error { background: #e74c3c; color: white; padding: 10px; border-radius: 6px; margin-bottom: 15px; }
        .back-link { display: block; margin-top: 20px; text-align: center; color: #2980b9; text-decoration: none; }
        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body>

    <h2>Edit Status Pengiriman</h2>

    <?php if (!empty($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <label>No Surat Jalan:</label>
        <input type="text" value="<?= htmlspecialchars($row['nomor']) ?>" disabled>

        <label>Tanggal:</label>
        <input type="text" value="<?= htmlspecialchars($row['tanggal']) ?>" disabled>

        <label for="status">Status:</label>
        <select name="status" id="status" required>
            <option value="">-- Pilih Status --</option>
            <?php
            $statuses = ['pending', 'dalam perjalanan', 'terkirim', 'ditolak'];
            foreach ($statuses as $s) {
                $selected = (strtolower($row['status']) === $s) ? 'selected' : '';
                echo "<option value=\"$s\" $selected>" . ucfirst($s) . "</option>";
            }
            ?>
        </select>

        <label for="alasan">Alasan/Keterangan (opsional):</label>
        <textarea name="alasan" id="alasan"><?= htmlspecialchars($row['alasan']) ?></textarea>

        <button type="submit">Update Status</button>
    </form>

    <a href="status_list.php" class="back-link">&laquo; Kembali ke daftar pengiriman</a>

</body>
</html>
