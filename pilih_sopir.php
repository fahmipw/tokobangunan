<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "toko_bangunan");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$sopir_list = $conn->query("SELECT id, nama FROM sopir ORDER BY nama");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Pilih Sopir</title>
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f9fafb; padding: 30px; color: #333; }
        h1 { text-align: center; margin-bottom: 30px; }
        ul { max-width: 400px; margin: auto; padding: 0; list-style: none; }
        li { background: white; margin: 10px 0; border-radius: 8px; box-shadow: 0 3px 8px rgba(0,0,0,0.1); }
        a.sopir-link { display: block; padding: 15px 20px; color: #2c3e50; text-decoration: none; font-weight: 600; transition: background 0.3s; border-radius: 8px; }
        a.sopir-link:hover { background: #3498db; color: white; }
        .all-route { text-align:center; margin-top: 40px; }
        .btn-back {
            display: inline-block;
            background-color: #2980b9;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            box-shadow: 0 6px 14px rgba(41, 128, 185, 0.5);
            transition: background-color 0.3s ease, transform 0.15s ease;
            user-select: none;
            margin-top: 30px;
        }
        .btn-back:hover, .btn-back:focus {
            background-color: #1c5fa0;
            transform: scale(1.05);
            outline: none;
        }
    </style>
</head>
<body>

    <h1>Pilih Sopir untuk Lihat Rute Pengiriman</h1>
    <ul>
        <?php while ($s = $sopir_list->fetch_assoc()): ?>
            <li><a href="rute_sopir.php?sopir_id=<?= $s['id'] ?>" class="sopir-link"><?= htmlspecialchars($s['nama']) ?></a></li>
        <?php endwhile; ?>
    </ul>

    <div class="all-route" style="text-align:center;">
        <a href="dashboard.php" class="btn-back" aria-label="Kembali ke Dashboard">← Kembali ke Dashboard</a>
    </div>

</body>
</html>
