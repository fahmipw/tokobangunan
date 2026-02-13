<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "toko_bangunan");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$result = $conn->query("SELECT * FROM sopir ORDER BY nama");

$message = $_GET['message'] ?? '';
$error = $_GET['error'] ?? '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Daftar Sopir dan Mobil</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 30px 20px;
            color: #333;
        }

        h2 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 30px;
            font-weight: 700;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 25px 35px;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(44,62,80,0.1);
        }

        .notif-success, .notif-error {
            max-width: 900px;
            margin: 20px auto;
            padding: 15px;
            border-radius: 8px;
            font-weight: 600;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            text-align: center;
        }

        .notif-success {
            background-color: #d4edda;
            color: #155724;
        }

        .notif-error {
            background-color: #f8d7da;
            color: #721c24;
        }

        .add-link {
            display: inline-block;
            background-color: #2980b9;
            color: white;
            padding: 10px 18px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            margin-bottom: 20px;
            transition: background-color 0.3s ease;
        }

        .add-link:hover {
            background-color: #1c5fa0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            border-radius: 8px;
            overflow: hidden;
        }

        th, td {
            padding: 14px 18px;
            text-align: center;
            border-bottom: 1px solid #e0e6ed;
            font-size: 1rem;
            vertical-align: middle;
        }

        th {
            background-color: #2c3e50;
            color: white;
            font-weight: 700;
        }

        tr:hover {
            background-color: #f1f6fb;
        }

        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: bold;
            font-size: 0.95rem;
            text-decoration: none;
            color: white;
            transition: background-color 0.3s ease;
        }

        .btn i {
            font-size: 0.95rem;
        }

        .btn-edit {
            background-color: #3498db;
        }

        .btn-edit:hover {
            background-color: #217dbb;
        }

        .btn-delete {
            background-color: #e74c3c;
        }

        .btn-delete:hover {
            background-color: #c0392b;
        }

        .back-link {
            display: block;
            max-width: 900px;
            margin: 30px auto 0;
            text-align: center;
            text-decoration: none;
            color: #2980b9;
            font-weight: 600;
            font-size: 1.1rem;
            padding: 12px 0;
        }

        .back-link:hover {
            color: #1c5fa0;
            text-decoration: underline;
        }
    </style>
</head>
<body>

<?php if ($message): ?>
    <div role="alert" aria-live="polite" class="notif-success">
        <?= htmlspecialchars($message) ?>
    </div>
<?php elseif ($error): ?>
    <div role="alert" aria-live="assertive" class="notif-error">
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<div class="container" role="main" aria-label="Daftar sopir dan mobil">
    <h2>Daftar Sopir dan Mobil</h2>
    <a href="sopir_add.php" class="add-link" aria-label="Tambah sopir dan mobil">Tambah Sopir dan Mobil</a>

    <table>
        <thead>
            <tr>
                <th>Nama Sopir</th>
                <th>Plat Nomor Mobil</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['nama']) ?></td>
                        <td><?= htmlspecialchars($row['plat_nomor']) ?></td>
                        <td>
                            <div class="action-buttons">
                                <a href="sopir_edit.php?id=<?= $row['id'] ?>" class="btn btn-edit" aria-label="Edit sopir <?= htmlspecialchars($row['nama']) ?>">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="sopir_delete.php?id=<?= $row['id'] ?>" class="btn btn-delete" onclick="return confirm('Yakin ingin menghapus?')" aria-label="Hapus sopir <?= htmlspecialchars($row['nama']) ?>">
                                    <i class="fas fa-trash-alt"></i> Hapus
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="3" style="text-align:center; padding: 20px;">Data tidak ditemukan</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <a href="dashboard.php" class="back-link" aria-label="Kembali ke dashboard">&larr; Kembali ke Dashboard</a>
</div>

</body>
</html>
