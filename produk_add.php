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

$error = '';

function generateProductID($nama, $conn) {
    // Ambil 3 huruf pertama nama produk, uppercase
    $prefix = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $nama), 0, 3));
    if (strlen($prefix) < 3) {
        // Kalau kurang dari 3 huruf, tambahkan X sampai 3 huruf
        $prefix = str_pad($prefix, 3, 'X');
    }

    // Loop untuk pastikan ID unik
    do {
        $randomNumber = rand(100, 999); // angka 3 digit random
        $newID = $prefix . $randomNumber;

        // Cek apakah ID sudah ada di DB
        $stmt = $conn->prepare("SELECT id FROM produk WHERE id = ?");
        $stmt->bind_param("s", $newID);
        $stmt->execute();
        $stmt->store_result();
        $exists = $stmt->num_rows > 0;
        $stmt->close();
    } while ($exists);

    return $newID;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = trim($_POST['nama'] ?? '');

    if ($nama === '') {
        $error = 'Data tidak valid. Mohon isi nama produk dengan benar.';
    } else {
        // Generate ID produk otomatis
        $id= generateProductID($nama, $conn);

        $stmt = $conn->prepare("INSERT INTO produk (id, nama) VALUES (?, ?)");
        $stmt->bind_param("ss", $id_produk, $nama);
        if ($stmt->execute()) {
            $_SESSION['success_msg'] = "Produk berhasil ditambahkan dengan ID: $id";
            $stmt->close();
            header("Location: produk_list.php");
            exit();
        } else {
            $error = 'Gagal menambahkan produk: ' . $conn->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8" />
<title>Tambah Produk - Toko Bangunan</title>
<style>
    /* Sesuai tema sidebar */
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
        width: 400px;
        padding: 30px 35px;
        border-radius: 10px;
        box-shadow: 0 6px 18px rgba(44, 62, 80, 0.15);
        user-select: none;
    }
    h2 {
        text-align: center;
        font-weight: 700;
        font-size: 1.8rem;
        margin-bottom: 30px;
        color: #2c3e50;
        letter-spacing: 0.5px;
    }
    label {
        display: block;
        font-weight: 600;
        margin-bottom: 8px;
        font-size: 1rem;
        color: #34495e;
        user-select: text;
    }
    input[type="text"] {
        width: 100%;
        padding: 12px 14px;
        font-size: 1rem;
        border-radius: 6px;
        border: 1.8px solid #bdc3c7;
        margin-bottom: 22px;
        box-sizing: border-box;
        transition: border-color 0.3s ease, box-shadow 0.3s ease;
        font-family: inherit;
    }
    input[type="text"]:focus {
        border-color: #2980b9;
        outline: none;
        box-shadow: 0 0 10px rgba(41, 128, 185, 0.4);
    }
    button {
        width: 100%;
        background-color: #2980b9;
        border: none;
        padding: 14px 0;
        border-radius: 8px;
        color: white;
        font-weight: 700;
        font-size: 1.1rem;
        cursor: pointer;
        transition: background-color 0.3s ease, transform 0.15s ease;
        box-shadow: 0 6px 14px rgba(41, 128, 185, 0.5);
        user-select: none;
    }
    button:hover {
        background-color: #1c5fa0;
        transform: scale(1.05);
    }
    .message {
        text-align: center;
        font-weight: 700;
        margin-bottom: 25px;
        font-size: 1rem;
        padding: 12px 18px;
        border-radius: 8px;
        user-select: none;
    }
    .error {
        background-color: #fce4e4;
        color: #e74c3c;
        box-shadow: 0 4px 12px rgba(231, 76, 60, 0.25);
    }
    .back-link {
        display: block;
        margin-top: 22px;
        text-align: center;
        color: #2980b9;
        font-weight: 600;
        font-size: 1rem;
        text-decoration: none;
        user-select: none;
        transition: color 0.3s ease;
    }
    .back-link:hover {
        color: #1c5fa0;
        text-decoration: underline;
    }
</style>
</head>
<body>
    <div class="container" role="main" aria-label="Form tambah produk">
        <h2>Tambah Produk</h2>

        <?php if ($error): ?>
            <div class="message error" role="alert"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post" action="" novalidate>
            <label for="nama">Nama Produk:</label>
            <input type="text" id="nama" name="nama" required value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>" autocomplete="off" />

            <button type="submit">Simpan</button>
        </form>

        <a href="produk_list.php" class="back-link">&larr; Kembali ke Daftar Produk</a>
    </div>
</body>
</html>