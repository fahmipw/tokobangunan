<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "toko_bangunan");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = trim($_POST['nama']);
    $alamat = trim($_POST['alamat']);
    $no_tlp = trim($_POST['no_tlp']);
    $kecamatan_nama = trim($_POST['kecamatan']);  
    $latitude = $_POST['latitude'];   // ambil langsung tanpa floatval()
    $longitude = $_POST['longitude'];

    if (empty($nama) || empty($kecamatan_nama)) {
        $error = "Nama toko dan kecamatan wajib diisi.";
    } else {
        $stmt = $conn->prepare("SELECT id FROM kecamatan WHERE nama = ?");
        if ($stmt === false) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("s", $kecamatan_nama);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($kecamatan_id);
            $stmt->fetch();
            $stmt->close();
        } else {
            $stmt->close();

            $stmt = $conn->prepare("INSERT INTO kecamatan (nama) VALUES (?)");
            if ($stmt === false) {
                die("Prepare failed (insert kecamatan): " . $conn->error);
            }
            $stmt->bind_param("s", $kecamatan_nama);
            $stmt->execute();
            $kecamatan_id = $stmt->insert_id;
            $stmt->close();
        } 

        $stmt = $conn->prepare("INSERT INTO toko (nama, alamat, no_telp, kecamatan_id, latitude, longitude) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt === false) {
            die("Prepare failed (insert toko): " . $conn->error);
        }
        $stmt->bind_param("sssidd", $nama, $alamat, $no_tlp, $kecamatan_id, $latitude, $longitude);
       if ($stmt->execute()) {
    $success = "Toko berhasil ditambahkan. Anda akan diarahkan dalam 3 detik...";
    // jangan pakai header() langsung
} else {
    $error = "Gagal menambahkan toko: " . $conn->error;
}

        $stmt->close();
    } 
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Tambah Toko - TB Sinar Terang BSD</title>
<style>
  /* Reset & base */
  * {
    box-sizing: border-box;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  }
  body, html {
    margin: 0; padding: 0; height: 100%;
    background: #f5f7fa;
    color: #34495e;
  }

  /* Container setup to center form vertically and horizontally */
  .form-wrapper {
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 40px 15px;
  }

  .form-container {
    background: white;
    padding: 40px 50px;
    border-radius: 15px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.05);
    max-width: 480px;
    width: 100%;
  }

  h2 {
    color: #2c3e50;
    font-weight: 700;
    font-size: 2rem;
    text-align: center;
    margin-bottom: 30px;
    letter-spacing: 1px;
  }

  form {
    display: flex;
    flex-direction: column;
  }

  label {
    font-weight: 600;
    margin-bottom: 8px;
    color: #34495e;
    font-size: 1rem;
  }

  input[type="text"],
  textarea {
    padding: 12px 15px;
    border: 2px solid #ccc;
    border-radius: 12px;
    margin-bottom: 22px;
    font-size: 1rem;
    color: #2c3e50;
    transition: border-color 0.3s ease;
    resize: vertical;
  }

  input[type="text"]:focus,
  textarea:focus {
    border-color: #2980b9;
    outline: none;
  }

  textarea {
    min-height: 80px;
  }

  button {
    background-color: #2980b9;
    color: white;
    font-weight: 700;
    border: none;
    border-radius: 15px;
    padding: 16px 0;
    font-size: 1.2rem;
    cursor: pointer;
    transition: background-color 0.3s ease, transform 0.2s ease;
  }

  button:hover {
    background-color: #1f5f8b;
    transform: scale(1.05);
  }

  .message {
    margin-bottom: 20px;
    padding: 14px 18px;
    border-radius: 15px;
    font-weight: 600;
    text-align: center;
  }

  .error {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
  }

  .success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
  }

  a.back-link {
    display: inline-block;
    margin-top: 25px;
    text-decoration: none;
    color: #2980b9;
    font-weight: 600;
    transition: color 0.3s ease;
    font-size: 1rem;
  }

  a.back-link:hover {
    color: #1f5f8b;
  }
</style>
</head>
<body>

<div class="form-wrapper">
  <div class="form-container">
      <h2>Tambah Toko</h2>

      <?php if (isset($error)): ?>
          <div class="message error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <?php if (isset($success)): ?>
    <div class="message success"><?= htmlspecialchars($success) ?></div>
    <script>
        setTimeout(function() {
            window.location.href = 'toko_list.php';
        }, 3000); // 3000 ms = 3 detik
    </script>
<?php endif; ?>


      <form method="post" action="">
          <label for="nama">Nama Toko:</label>
          <input type="text" id="nama" name="nama" required>

          <label for="alamat">Alamat:</label>
          <textarea id="alamat" name="alamat" rows="3"></textarea>

          <label for="no_tlp">No. Telepon:</label>
          <input type="text" id="no_tlp" name="no_tlp">

          <label for="kecamatan">Kecamatan:</label>
          <input type="text" id="kecamatan" name="kecamatan" required>

          <label for="latitude">Latitude:</label>
          <input type="text" id="latitude" name="latitude" placeholder="Contoh: -6.300000" required>

          <label for="longitude">Longitude:</label>
          <input type="text" id="longitude" name="longitude" placeholder="Contoh: 106.700000" required>

          <button type="submit">Simpan</button>
      </form>

      <a href="dashboard.php" class="back-link">← Kembali ke Dashboard</a>
  </div>
</div>

</body>
</html>
