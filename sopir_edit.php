<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "toko_bangunan");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

if (!isset($_GET['id'])) die("ID sopir tidak ditemukan.");

$id = intval($_GET['id']);

// Ambil data sopir
$result = $conn->query("SELECT * FROM sopir WHERE id = $id");
if ($result->num_rows == 0) die("Sopir tidak ditemukan.");
$sopir = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = $_POST['nama'];
    $plat_nomor = $_POST['plat_nomor'];

    $stmt = $conn->prepare("UPDATE sopir SET nama=?, plat_nomor=? WHERE id=?");
    $stmt->bind_param("ssi", $nama, $plat_nomor, $id);

    if ($stmt->execute()) {
        $success = "Data sopir berhasil diperbarui.";
        // Refresh data
        $result = $conn->query("SELECT * FROM sopir WHERE id = $id");
        $sopir = $result->fetch_assoc();
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
    <title>Edit Sopir dan Mobil</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
            margin: 0;
            padding: 40px 20px;
            color: #2c3e50;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
        }
        .container {
            background: white;
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(44,62,80,0.1);
            max-width: 420px;
            width: 100%;
            box-sizing: border-box;
        }
        h2 {
            text-align: center;
            margin-bottom: 30px;
            font-weight: 700;
            color: #34495e;
            letter-spacing: 0.5px;
        }
        label {
            display: block;
            font-weight: 600;
            margin-bottom: 6px;
            font-size: 1rem;
            color: #34495e;
            user-select: text;
        }
        input[type="text"] {
            width: 100%;
            padding: 12px 14px;
            border-radius: 6px;
            border: 1.8px solid #bdc3c7;
            font-size: 1rem;
            margin-bottom: 24px;
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
        .success {
            background-color: #d4edda;
            color: #155724;
            box-shadow: 0 4px 12px rgba(21, 87, 36, 0.25);
        }
        .error {
            background-color: #fce4e4;
            color: #e74c3c;
            box-shadow: 0 4px 12px rgba(231, 76, 60, 0.25);
        }
        .back-link {
            display: block;
            margin-top: 28px;
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
    <main class="container" role="main" aria-label="Form edit sopir dan mobil">
        <h2>Edit Sopir dan Mobil</h2>

        <?php if (isset($success)): ?>
            <div class="message success" role="alert"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="message error" role="alert"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post" action="" novalidate>
            <label for="nama">Nama Sopir:</label>
            <input type="text" id="nama" name="nama" required value="<?= htmlspecialchars($sopir['nama']) ?>" autocomplete="off" />

            <label for="plat_nomor">Plat Nomor Mobil:</label>
            <input type="text" id="plat_nomor" name="plat_nomor" required value="<?= htmlspecialchars($sopir['plat_nomor']) ?>" autocomplete="off" />

            <button type="submit">Update</button>
        </form>

        <a href="sopir_list.php" class="back-link" aria-label="Kembali ke daftar sopir">&larr; Kembali ke Daftar Sopir</a>
    </main>
</body>
</html>
