<?php 
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "toko_bangunan");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = $_POST['nama'];
    $plat_nomor = $_POST['plat_nomor'];
    $username = $_POST['username'];
    $password = $_POST['password']; // plain text

    // Cek apakah username sudah ada
    $check = $conn->prepare("SELECT id FROM sopir WHERE username = ?");
    $check->bind_param("s", $username);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $error = "Username sudah digunakan, silakan pilih yang lain.";
    } else {
        $stmt = $conn->prepare("INSERT INTO sopir (nama, plat_nomor, username, password) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $nama, $plat_nomor, $username, $password);

        if ($stmt->execute()) {
            header("Refresh:2; url=sopir_list.php?message=" . urlencode("Sopir dan mobil berhasil ditambahkan."));
            $success = "Sopir dan login berhasil ditambahkan. Anda akan diarahkan...";
        } else {
            $error = "Error: " . $conn->error;
        }
        $stmt->close();
    }
    $check->close();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Tambah Sopir dan Login</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin:0; padding:30px 20px; background:#f9fafb; color:#333; }
        .container { max-width: 500px; background:white; margin:0 auto; padding:30px 35px; border-radius:12px; box-shadow:0 8px 24px rgba(44,62,80,0.1);}
        h2 { text-align:center; color:#2c3e50; margin-bottom:30px; font-weight:700;}
        form label { display:block; margin-bottom:8px; font-weight:600; color:#555;}
        form input[type="text"], form input[type="password"] { width:100%; padding:10px 12px; margin-bottom:20px; border:1px solid #ccc; border-radius:8px; font-size:1rem; transition:border-color 0.3s;}
        form input:focus { border-color:#2980b9; outline:none; }
        button { background-color:#2980b9; color:white; font-weight:700; border:none; padding:12px 20px; border-radius:8px; cursor:pointer; width:100%; font-size:1.1rem; transition:background-color 0.3s;}
        button:hover { background-color:#1c5fa0; }
        .notif-success { background:#d4edda; color:#155724; padding:15px; border-radius:8px; font-weight:600; margin-bottom:20px; text-align:center; box-shadow:0 4px 10px rgba(0,0,0,0.05);}
        .notif-error { background:#f8d7da; color:#721c24; padding:15px; border-radius:8px; font-weight:600; margin-bottom:20px; text-align:center; box-shadow:0 4px 10px rgba(0,0,0,0.05);}
        a.back-link { display:block; text-align:center; margin-top:25px; color:#2980b9; font-weight:600; text-decoration:none; transition:color 0.3s;}
        a.back-link:hover { color:#1c5fa0; text-decoration:underline;}
    </style>
</head>
<body>
    <div class="container">
        <h2>Tambah Sopir dan Login</h2>

        <?php if (isset($success)): ?>
            <div class="notif-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="notif-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if (!isset($success)): ?>
        <form method="post" action="">
            <label for="nama">Nama Sopir:</label>
            <input type="text" id="nama" name="nama" required autocomplete="off" />

            <label for="plat_nomor">Plat Nomor Mobil:</label>
            <input type="text" id="plat_nomor" name="plat_nomor" required autocomplete="off" />

            <label for="username">Username Login:</label>
            <input type="text" id="username" name="username" required autocomplete="off" />

            <label for="password">Password Login:</label>
            <input type="password" id="password" name="password" required autocomplete="off" />

            <button type="submit">Tambah</button>
        </form>
        <?php endif; ?>

        <a href="sopir_list.php" class="back-link">&larr; Kembali ke Daftar Sopir</a>
    </div>
</body>
</html>
