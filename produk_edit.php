<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "toko_bangunan");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

if (!isset($_GET['id'])) {
    die("ID produk tidak ditemukan.");
}

$id = intval($_GET['id']);

// Ambil data produk
$result = $conn->query("SELECT * FROM produk WHERE id = $id");
if ($result->num_rows == 0) {
    die("Produk tidak ditemukan.");
}
$produk = $result->fetch_assoc();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = $_POST['nama'];

    $stmt = $conn->prepare("UPDATE produk SET nama=? WHERE id=?");
    $stmt->bind_param("si", $nama, $id);
    if ($stmt->execute()) {
        $_SESSION['success_msg'] = "Produk berhasil diperbarui.";
        header("Location: produk_list.php");
        exit();
    } else {
        $error = "Error: " . $conn->error;
    }
    $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Edit Produk</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet" />
    <style>
        /* gaya sama seperti sebelumnya */
        * {
            box-sizing: border-box;
        }
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
            max-width: 480px;
            width: 100%;
            background: #ffffffdd;
            padding: 35px 40px;
            border-radius: 15px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
            backdrop-filter: saturate(180%) blur(15px);
            -webkit-backdrop-filter: saturate(180%) blur(15px);
        }
        h2 {
            text-align: center;
            margin-bottom: 35px;
            font-weight: 700;
            font-size: 2rem;
            color: #34495e;
            letter-spacing: 1px;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 22px;
        }
        label {
            font-weight: 600;
            margin-bottom: 8px;
            color: #2980b9;
        }
        input[type="text"] {
            padding: 12px 15px;
            font-size: 1rem;
            border-radius: 8px;
            border: 1.8px solid #ccc;
            transition: border-color 0.3s ease;
            outline-offset: 2px;
        }
        input[type="text"]:focus {
            border-color: #2980b9;
            outline: none;
            box-shadow: 0 0 8px #74b9ff88;
        }
        button {
            background-color: #27ae60;
            color: white;
            font-weight: 700;
            padding: 14px;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            cursor: pointer;
            box-shadow: 0 6px 18px rgba(39,174,96,0.5);
            transition: background-color 0.3s ease, transform 0.2s ease;
        }
        button:hover {
            background-color: #1e8449;
            transform: scale(1.05);
        }
        .notif-success, .notif-error {
            padding: 16px 24px;
            border-radius: 12px;
            font-weight: 600;
            margin-bottom: 25px;
            text-align: center;
            box-shadow: 0 6px 16px rgba(0,0,0,0.15);
        }
        .notif-success {
            background-color: #2ecc71;
            color: white;
            box-shadow: 0 6px 16px rgba(46,204,113,0.6);
        }
        .notif-error {
            background-color: #e74c3c;
            color: white;
            box-shadow: 0 6px 16px rgba(231,76,60,0.6);
        }
        a.back-link {
            display: block;
            margin-top: 25px;
            text-align: center;
            color: #2980b9;
            text-decoration: none;
            font-weight: 600;
            font-size: 1rem;
            transition: color 0.3s ease;
        }
        a.back-link:hover {
            color: #1f6391;
        }

        @media (max-width: 520px) {
            body {
                padding: 25px 10px;
            }
            .container {
                padding: 30px 25px;
            }
            h2 {
                font-size: 1.8rem;
                margin-bottom: 25px;
            }
            button {
                font-size: 1rem;
                padding: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Edit Produk</h2>

        <?php if ($success): ?>
            <div class="notif-success" id="successNotif"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="notif-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post" action="">
            <label for="nama">Nama Produk</label>
            <input type="text" id="nama" name="nama" value="<?= htmlspecialchars($produk['nama']) ?>" required />

            <button type="submit">Update</button>
        </form>

        <a href="produk_list.php" class="back-link" aria-label="Kembali ke Daftar Produk">← Kembali ke Daftar Produk</a>
    </div>

    <script>
        // Auto hide success notif after 3 seconds
        const notif = document.getElementById('successNotif');
        if (notif) {
            setTimeout(() => {
                notif.style.opacity = '0';
                notif.style.transition = 'opacity 0.5s ease';
                setTimeout(() => notif.remove(), 500);
            }, 3000);
        }
    </script>
</body>
</html>
