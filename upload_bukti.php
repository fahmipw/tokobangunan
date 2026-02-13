<?php
session_start();
$conn = new mysqli("localhost", "root", "", "toko_bangunan");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['surat_jalan_id'])) {
    $surat_jalan_id = $_POST['surat_jalan_id'];

    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $filename = "bukti_sj_" . $surat_jalan_id . "_" . time() . "." . $ext;
        $target_file = $target_dir . $filename;

        if (move_uploaded_file($_FILES['foto']['tmp_name'], $target_file)) {
            // Simpan path + timestamp ke database
            $stmt = $conn->prepare("UPDATE surat_jalan SET foto_bukti = ?, timestamp_bukti = NOW() WHERE id = ?");
            $stmt->bind_param("si", $target_file, $surat_jalan_id);
            $stmt->execute();
            $stmt->close();

            $success = "Foto bukti berhasil di-upload!";
        } else {
            $error = "Gagal meng-upload foto.";
        }
    } else {
        $error = "Tidak ada file yang di-upload.";
    }
}

// Ambil daftar surat jalan sopir ini (contoh ID sopir = 1)
$result = $conn->query("SELECT id, nomor FROM surat_jalan WHERE sopir_id = 1 ORDER BY tanggal DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Upload Bukti Pengiriman</title>
</head>
<body>
<h2>Upload Bukti Pengiriman</h2>

<?php if (!empty($success)) echo "<p style='color:green;'>$success</p>"; ?>
<?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>

<form method="POST" enctype="multipart/form-data">
    <label for="surat_jalan_id">Pilih Surat Jalan:</label>
    <select name="surat_jalan_id" required>
        <option value="">--Pilih--</option>
        <?php while ($row = $result->fetch_assoc()): ?>
            <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['nomor']) ?></option>
        <?php endwhile; ?>
    </select>
    <br><br>
    <label for="foto">Upload Foto Bukti:</label>
    <input type="file" name="foto" accept="image/*" required>
    <br><br>
    <button type="submit">Upload</button>
</form>
</body>
</html>
