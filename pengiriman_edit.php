<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "toko_bangunan");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_GET['sj_id'])) {
    die("ID surat jalan tidak ditemukan.");
}

$surat_jalan_id = intval($_GET['sj_id']);

// Ambil data surat_jalan
$stmt = $conn->prepare("SELECT * FROM surat_jalan WHERE id = ?");
$stmt->bind_param("i", $surat_jalan_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) {
    die("Surat jalan tidak ditemukan.");
}
$surat = $result->fetch_assoc();
$stmt->close();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $status = $_POST['status'];
    $alasan = isset($_POST['alasan']) ? trim($_POST['alasan']) : null;

    // Validasi status: jika status “Ditolak”, alasan wajib
    if ($status === 'Ditolak' && empty($alasan)) {
        $error = "Alasan wajib diisi jika status Ditolak.";
    } else {
        $stmt2 = $conn->prepare("UPDATE surat_jalan SET status = ?, alasan = ? WHERE id = ?");
        $stmt2->bind_param("ssi", $status, $alasan, $surat_jalan_id);
        if ($stmt2->execute()) {
            $success = "Status pengiriman berhasil diperbarui.";
            // Refresh data surat setelah update
            $stmt2->close();

            $stmt3 = $conn->prepare("SELECT * FROM surat_jalan WHERE id = ?");
            $stmt3->bind_param("i", $surat_jalan_id);
            $stmt3->execute();
            $res2 = $stmt3->get_result();
            $surat = $res2->fetch_assoc();
            $stmt3->close();
        } else {
            $error = "Gagal memperbarui status: " . $conn->error;
            $stmt2->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Pengiriman</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .container { max-width: 600px; margin: auto; background: #f9f9f9; padding: 25px; border-radius: 8px; }
        label { display: block; margin-top: 15px; font-weight: bold; }
        select, textarea, input[type="text"] { width: 100%; padding: 10px; margin-top: 5px; border-radius: 4px; border: 1px solid #ccc; }
        button { margin-top: 20px; padding: 10px 20px; background: #2980b9; color: #fff; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #1f5f8a; }
        .notif-success { background-color: #2ecc71; color: white; padding: 12px; border-radius: 4px; margin-bottom: 15px; }
        .notif-error { background-color: #e74c3c; color: white; padding: 12px; border-radius: 4px; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Edit Pengiriman (Surat Jalan #<?= htmlspecialchars($surat['nomor']) ?>)</h2>

        <?php if ($success): ?>
            <div class="notif-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="notif-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post" action="">
            <label>Status</label>
            <select name="status" required>
                <option value="">-- Pilih Status --</option>
                <option value="Pending" <?= $surat['status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                <option value="Dalam Perjalanan" <?= $surat['status'] == 'Dalam Perjalanan' ? 'selected' : '' ?>>Dalam Perjalanan</option>
                <option value="Terkirim" <?= $surat['status'] == 'Terkirim' ? 'selected' : '' ?>>Terkirim</option>
                <option value="Ditolak" <?= $surat['status'] == 'Ditolak' ? 'selected' : '' ?>>Ditolak</option>
            </select>

            <label for="alasan">Alasan / Keterangan (jika diperlukan)</label>
            <textarea name="alasan" id="alasan" rows="3"><?= htmlspecialchars($surat['alasan']) ?></textarea>

            <button type="submit">Update Pengiriman</button>
        </form>

        <p><a href="status_pengiriman.php">← Kembali ke Daftar Pengiriman</a></p>
    </div>
</body>
</html>

<?php
$conn->close();
?>
