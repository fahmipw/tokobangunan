<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "toko_bangunan");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$total_result = $conn->query("SELECT COUNT(*) AS total FROM toko");
$total_row = $total_result->fetch_assoc();
$total_data = $total_row['total'];
$total_pages = ceil($total_data / $limit);

$sql = "SELECT toko.*, kecamatan.nama AS kecamatan FROM toko JOIN kecamatan ON toko.kecamatan_id = kecamatan.id ORDER BY toko.id LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Daftar Toko</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        *, *::before, *::after {
            box-sizing: border-box;
        }
        body {
            font-family: 'Poppins', sans-serif;
            background: #f6f8fa;
            padding: 20px;
            color: #333;
            margin: 0;
        }
        h2 {
            margin-bottom: 20px;
            color: #222;
            text-align: center;
            font-weight: 700;
            letter-spacing: 0.05em;
        }

        /* Tombol tambah & kembali */
        .button-group {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }
        .button-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            font-size: 15px;
            font-weight: 600;
            border-radius: 6px;
            text-decoration: none;
            color: white;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transition: background-color 0.3s, transform 0.2s;
            white-space: nowrap;
        }
        .button-link.primary {
            background-color: #2980b9;
        }
        .button-link.primary:hover {
            background-color: #1f6391;
            transform: translateY(-2px);
        }
        .button-link.secondary {
            background-color: #7f8c8d;
        }
        .button-link.secondary:hover {
            background-color: #5d6d6d;
            transform: translateY(-2px);
        }

        @media (max-width: 600px) {
            .button-group {
                flex-direction: column;
                align-items: stretch;
            }
            .button-link {
                justify-content: center;
                width: 100%;
            }
        }

        /* Table */
        table {
            border-collapse: collapse;
            width: 100%;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            font-size: 14px;
        }
        thead {
            background: #2c3e50;
            color: white;
        }
        th, td {
            padding: 12px 15px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }
        tbody tr:hover {
            background: #f0f7ff;
        }

        /* Aksi Edit & Hapus */
        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 8px;
        }
        .action-link {
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: 600;
            text-decoration: none;
            font-size: 13px;
            color: white;
        }
        .action-link.edit {
            background-color: #3498db;
        }
        .action-link.edit:hover {
            background-color: #1f6391;
        }
        .action-link.delete {
            background-color: #e74c3c;
        }
        .action-link.delete:hover {
            background-color: #c0392b;
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
            gap: 5px;
            flex-wrap: wrap;
        }
        .pagination a, .pagination span {
            padding: 8px 14px;
            border: 1px solid #2980b9;
            color: #2980b9;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        .pagination a:hover {
            background-color: #2980b9;
            color: white;
        }
        .pagination .active {
            background-color: #2980b9;
            color: white;
            pointer-events: none;
        }
    </style>
</head>
<body>

    <h2>Daftar Toko</h2>

    <div class="button-group">
        <a href="toko_add.php" class="button-link primary"><i class="fas fa-plus"></i> Tambah Toko</a>
        <a href="dashboard.php" class="button-link secondary"></i> 🏠︎ Kembali ke Dashboard</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>Alamat</th>
                <th>No Telepon</th>
                <th>Kecamatan</th>
                <th>Latitude</th>
                <th>Longitude</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php 
                $no = $offset + 1;
                while ($row = $result->fetch_assoc()): 
                ?>
                <tr>
                    <td><?= $no ?></td>
                    <td><?= htmlspecialchars($row['nama']) ?></td>
                    <td><?= htmlspecialchars($row['alamat']) ?></td>
                    <td><?= htmlspecialchars($row['no_telp']) ?></td>
                    <td><?= htmlspecialchars($row['kecamatan']) ?></td>
                    <td><?= $row['latitude'] ?></td>
                    <td><?= $row['longitude'] ?></td>
                    <td>
                        <div class="action-buttons">
                            <a href="toko_edit.php?id=<?= $row['id'] ?>" class="action-link edit"><i class="fas fa-edit"></i> Edit</a>
                            <a href="toko_delete.php?id=<?= $row['id'] ?>" class="action-link delete" onclick="return confirm('Yakin ingin hapus toko ini?')"><i class="fas fa-trash-alt"></i> Hapus</a>
                        </div>
                    </td>
                </tr>
                <?php 
                $no++;
                endwhile; 
                ?>
            <?php else: ?>
                <tr><td colspan="8" style="text-align:center; padding: 15px;">Data tidak ditemukan</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?page=1">First</a>
            <a href="?page=<?= $page - 1 ?>">Prev</a>
        <?php endif; ?>

        <?php
        $start = max(1, $page - 2);
        $end = min($total_pages, $page + 2);
        if ($start > 1) echo '<span>...</span>';
        for ($i = $start; $i <= $end; $i++): ?>
            <?php if ($i == $page): ?>
                <span class="active"><?= $i ?></span>
            <?php else: ?>
                <a href="?page=<?= $i ?>"><?= $i ?></a>
            <?php endif; ?>
        <?php endfor; ?>
        <?php if ($end < $total_pages) echo '<span>...</span>'; ?>

        <?php if ($page < $total_pages): ?>
            <a href="?page=<?= $page + 1 ?>">Next</a>
            <a href="?page=<?= $total_pages ?>">Last</a>
        <?php endif; ?>
    </div>

</body>
</html>
