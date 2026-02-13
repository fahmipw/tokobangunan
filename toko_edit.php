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
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Ambil data toko
$stmt = $conn->prepare("SELECT * FROM toko WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo "Toko tidak ditemukan";
    exit();
}
$toko = $result->fetch_assoc();
$stmt->close();

// Ambil kecamatan untuk dropdown
$kecamatan_result = $conn->query("SELECT * FROM kecamatan ORDER BY nama");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'];
    $alamat = $_POST['alamat'];
    $no_telp = $_POST['no_telp'];
    $kecamatan_id = intval($_POST['kecamatan_id']);
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];

    $stmt = $conn->prepare("UPDATE toko SET nama=?, alamat=?, no_telp=?, kecamatan_id=?, latitude=?, longitude=? WHERE id=?");
    $stmt->bind_param("sssiddi", $nama, $alamat, $no_telp, $kecamatan_id, $latitude, $longitude, $id);

    if ($stmt->execute()) {
        $success = "Data toko berhasil diupdate.";
        // Refresh data toko setelah update
        $stmt->close();
        $stmt2 = $conn->prepare("SELECT * FROM toko WHERE id=?");
        $stmt2->bind_param("i", $id);
        $stmt2->execute();
        $toko = $stmt2->get_result()->fetch_assoc();
        $stmt2->close();
    } else {
        $error = "Gagal update data: " . $conn->error;
    }
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Edit Toko - TB Sinar Terang BSD</title>
<style>
  /* Reset dasar */
  * {
    box-sizing: border-box;
  }
  body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #f4f7fb;
    margin: 0;
    padding: 30px 15px;
    color: #34495e;
  }
  h2 {
    text-align: center;
    font-weight: 700;
    margin-bottom: 25px;
    color: #2c3e50;
    letter-spacing: 1px;
  }
  .form-wrapper {
    max-width: 480px;
    margin: 0 auto;
    background: #fff;
    padding: 30px 35px;
    border-radius: 12px;
    box-shadow: 0 12px 30px rgba(0,0,0,0.1);
  }
  form {
    display: flex;
    flex-direction: column;
  }
  label {
    font-weight: 600;
    margin-bottom: 6px;
    font-size: 14px;
  }
  input[type="text"],
  select,
  textarea {
    padding: 12px 15px;
    border: 2px solid #ccc;
    border-radius: 10px;
    font-size: 14px;
    margin-bottom: 20px;
    color: #2c3e50;
    transition: border-color 0.3s ease;
  }
  input[type="text"]:focus,
  select:focus,
  textarea:focus {
    border-color: #2980b9;
    outline: none;
  }
  textarea {
    resize: vertical;
    min-height: 80px;
  }
  button {
    background-color: #2980b9;
    color: white;
    border: none;
    border-radius: 15px;
    padding: 14px 0;
    font-weight: 700;
    font-size: 16px;
    cursor: pointer;
    transition: background-color 0.3s ease, transform 0.2s ease;
  }
  button:hover {
    background-color: #1f6391;
    transform: translateY(-2px);
  }
  .message {
    padding: 14px 18px;
    border-radius: 15px;
    font-weight: 600;
    text-align: center;
    margin-bottom: 20px;
  }
  .success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
  }
  .error {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
  }
  a.back-link {
    display: block;
    max-width: 480px;
    margin: 25px auto 0;
    text-align: center;
    font-weight: 600;
    text-decoration: none;
    color: #2980b9;
    font-size: 14px;
    transition: color 0.3s ease;
  }
  a.back-link:hover {
    color: #1f6391;
  }
  @media (max-width: 540px) {
    .form-wrapper {
      padding: 25px 20px;
      width: 100%;
    }
  }
</style>
</head>
<body>

<h2>Edit Toko</h2>

<div class="form-wrapper">
    <?php if (isset($success)): ?>
        <div class="message success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if (isset($error)): ?>
        <div class="message error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" action="" autocomplete="off" novalidate>
        <label for="nama">Nama:</label>
        <input type="text" id="nama" name="nama" required value="<?= htmlspecialchars($toko['nama']) ?>">

        <label for="alamat">Alamat:</label>
        <textarea id="alamat" name="alamat" required><?= htmlspecialchars($toko['alamat']) ?></textarea>

        <label for="no_telp">No Telepon:</label>
        <input type="text" id="no_telp" name="no_telp" value="<?= htmlspecialchars($toko['no_telp']) ?>">

        <label for="kecamatan_id">Kecamatan:</label>
        <select id="kecamatan_id" name="kecamatan_id" required>
            <?php while($kec = $kecamatan_result->fetch_assoc()): ?>
                <option value="<?= $kec['id'] ?>" <?= ($kec['id'] == $toko['kecamatan_id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($kec['nama']) ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label for="latitude">Latitude:</label>
        <input type="text" id="latitude" name="latitude" value="<?= htmlspecialchars($toko['latitude']) ?>">

        <label for="longitude">Longitude:</label>
        <input type="text" id="longitude" name="longitude" value="<?= htmlspecialchars($toko['longitude']) ?>">

        <button type="submit">Update</button>
    </form>
</div>

<a href="toko_list.php" class="back-link">← Kembali ke Daftar Toko</a>

</body>
</html>
