<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "toko_bangunan");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Admin membatalkan pengiriman
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['batalkan_surat_jalan_id'])) {

    $sj_id = $_POST['batalkan_surat_jalan_id'];
    $conn->begin_transaction();

    try {
        // Ambil list produk
        $stmt = $conn->prepare("SELECT produk_id, jumlah FROM pengiriman WHERE surat_jalan_id = ?");
        $stmt->bind_param("i", $sj_id);
        $stmt->execute();
        $res = $stmt->get_result();

        while ($item = $res->fetch_assoc()) {
            $pid = $item['produk_id'];
            $jml = $item['jumlah'];

            // Kembalikan stok
            $conn->query("UPDATE produk SET stok = stok + $jml WHERE id = $pid");

            // Riwayat stok
            $conn->query("
                INSERT INTO riwayat_stok (produk_id, waktu, stok_lama, stok_baru, jenis, keterangan)
                VALUES (
                    $pid,
                    NOW(),
                    (SELECT stok - $jml FROM produk WHERE id = $pid),
                    (SELECT stok FROM produk WHERE id = $pid),
                    'barang masuk',
                    'Pembatalan oleh admin - Surat Jalan ID $sj_id'
                )
            ");
        }

        // Update status surat jalan
        $stmt = $conn->prepare("UPDATE surat_jalan SET status='Dibatalkan', alasan='Dibatalkan oleh admin' WHERE id=?");
        $stmt->bind_param("i", $sj_id);
        $stmt->execute();

        $conn->commit();
        $_SESSION['success_msg'] = "Pengiriman berhasil dibatalkan.";

    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['success_msg'] = "Gagal membatalkan pengiriman.";
    }

    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}


// Hapus surat jalan jika ada request hapus
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_surat_jalan_id'])) {
    $delete_id = $_POST['delete_surat_jalan_id'];

    // Hapus data pengiriman terkait dulu
    $stmt = $conn->prepare("DELETE FROM pengiriman WHERE surat_jalan_id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();

    // Hapus surat jalan
    $stmt = $conn->prepare("DELETE FROM surat_jalan WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();

    $_SESSION['success_msg'] = "Pengiriman berhasil dihapus.";
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $surat_jalan_id = $_POST['surat_jalan_id'];
    $status = $_POST['status'];
    $alasan = $_POST['alasan'] ?? null;

    $conn->begin_transaction();
    try {
        // Ambil data pengiriman terkait
        $stmt = $conn->prepare("SELECT produk_id, jumlah FROM pengiriman WHERE surat_jalan_id = ?");
        $stmt->bind_param("i", $surat_jalan_id);
        $stmt->execute();
        $result = $stmt->get_result();

        // Jika status ditolak, lakukan penambahan stok
        if ($status === 'Ditolak') {
            while ($row = $result->fetch_assoc()) {
                $produk_id = $row['produk_id'];
                $jumlah_retur = (int)$row['jumlah'];

                // Ambil stok lama
                $stmt_stok = $conn->prepare("SELECT stok FROM produk WHERE id = ?");
                $stmt_stok->bind_param("i", $produk_id);
                $stmt_stok->execute();
                $stok_result = $stmt_stok->get_result()->fetch_assoc();
                $stok_lama = (int)$stok_result['stok'];
                $stok_baru = $stok_lama + $jumlah_retur;
                $stmt_stok->close();

                // Update stok produk
                $stmt_update = $conn->prepare("UPDATE produk SET stok = ? WHERE id = ?");
                $stmt_update->bind_param("ii", $stok_baru, $produk_id);
                $stmt_update->execute();
                $stmt_update->close();

                // Tambahkan ke riwayat stok
                $jenis = 'barang masuk';
                $keterangan = "Retur karena pengiriman ditolak (Surat Jalan ID: $surat_jalan_id)";
                $stmt_riwayat = $conn->prepare("INSERT INTO riwayat_stok (produk_id, waktu, stok_lama, stok_baru, jenis, keterangan) VALUES (?, NOW(), ?, ?, ?, ?)");
                $stmt_riwayat->bind_param("iiiss", $produk_id, $stok_lama, $stok_baru, $jenis, $keterangan);
                $stmt_riwayat->execute();
                $stmt_riwayat->close();
            }
        }
        $stmt->close();

        // Update status surat jalan
        $stmt = $conn->prepare("UPDATE surat_jalan SET status = ?, alasan = ? WHERE id = ?");
        $stmt->bind_param("ssi", $status, $alasan, $surat_jalan_id);
        $stmt->execute();
        $stmt->close();

        $conn->commit();
        $_SESSION['success_msg'] = "Status pengiriman berhasil diupdate.";
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['success_msg'] = "Gagal update status: " . $e->getMessage();
    }
    

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}


$success = '';
if (isset($_SESSION['success_msg'])) {
    $success = $_SESSION['success_msg'];
    unset($_SESSION['success_msg']);
}

$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Hitung total data untuk pagination
$total_result = $conn->query("SELECT COUNT(DISTINCT sj.id) as total FROM surat_jalan sj");
$total_row = $total_result->fetch_assoc();
$total_data = $total_row['total'];
$total_pages = ceil($total_data / $limit);

$nomor = $offset + 1;

$sql = "SELECT 
    sj.id AS sj_id,
    sj.nomor,
    sj.tanggal,
    sj.tanggal_terkirim,
    sj.status,
    sj.alasan,
    sj.bukti_foto,
    s.nama AS sopir_nama,
    s.plat_nomor,
    t.nama AS toko_nama,
    k.nama AS kecamatan,

    -- Gabungkan produk + jumlah
    GROUP_CONCAT(CONCAT(p.nama, ' (', pr.jumlah, ')') SEPARATOR '<br>') AS produk_list

FROM surat_jalan sj
JOIN sopir s ON sj.sopir_id = s.id
JOIN pengiriman pr ON pr.surat_jalan_id = sj.id
JOIN produk p ON pr.produk_id = p.id
JOIN toko t ON pr.toko_id = t.id
JOIN kecamatan k ON t.kecamatan_id = k.id

GROUP BY sj.id
ORDER BY sj.tanggal DESC, sj.id DESC
LIMIT $limit OFFSET $offset
";




$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Status Pengiriman</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        *, *::before, *::after {
            box-sizing: border-box;
        }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #ffffff);
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
        h3 {
            margin-top: 40px;
            color: #2c3e50;
            font-weight: 600;
            letter-spacing: 0.03em;
        }
        .notif {
            background-color: #27ae60;
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 600;
            box-shadow: 0 2px 8px rgb(39 174 96 / 0.3);
            animation: fadeInDown 0.4s ease forwards;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
            text-align: center;
        }
        @keyframes fadeInDown {
            from {opacity: 0; transform: translateY(-20px);}
            to {opacity: 1; transform: translateY(0);}
        }
        .button-link {
            display: inline-block;
            padding: 12px 28px;
            background: #2980b9;
            color: white;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 700;
            font-size: 16px;
            box-shadow: 0 4px 8px rgba(41, 128, 185, 0.3);
            transition: background-color 0.3s ease, box-shadow 0.3s ease, transform 0.2s ease;
            user-select: none;
        }
        .button-link i {
            margin-right: 8px;
            font-size: 16px;
        }
        .button-link:hover {
            background: #1f6391;
            box-shadow: 0 6px 15px rgba(31, 99, 145, 0.6);
            transform: translateY(-2px);
        }
        .button-link:active {
            transform: translateY(0);
            box-shadow: 0 3px 6px rgba(31, 99, 145, 0.4);
        }
        .button-group {
        display: flex;
        justify-content: center; /* supaya tombol berada di tengah */
        gap: 15px; /* jarak antar tombol */
        margin-bottom: 25px;
        padding: 0 20px;
        }
        .button-group {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding: 0 20px;
        }
        .button-delete {
    background-color: #e74c3c; /* merah */
    color: white;
    border: none;
    padding: 6px 12px;
    border-radius: 5px;
    cursor: pointer;
    font-weight: 600;
    font-size: 14px;
    transition: background-color 0.3s ease;
    width: auto; /* agar tidak melebar */
    display: inline-block;
    min-width: 70px; /* atau sesuai kebutuhan */
    text-align: center;
}

.button-delete:hover {
    background-color: #c0392b; /* merah gelap saat hover */
}

        table {
            border-collapse: collapse;
            width: 100%;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgb(0 0 0 / 0.1);
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
            vertical-align: middle;
        }
        tbody tr:hover {
            background: #f0f7ff;
        }
        .action-form {
            max-width: 180px;
        }
        select, input[type="text"], button {
            padding: 7px 10px;
            font-size: 14px;
            border: 1px solid #ccc;
            border-radius: 5px;
            outline: none;
            transition: border-color 0.3s ease;
            width: 100%;
            box-sizing: border-box;
            margin-bottom: 6px;
        }
        select:focus, input[type="text"]:focus {
            border-color: #2980b9;
        }
        /* Tombol Kembali */
        .button-link.kembali {
            background: #7f8c8d; /* abu-abu gelap */
        }
        .button-link.kembali:hover {
            background: #636e72;
        }
        button {
            background-color: #2980b9;
            color: white;
            cursor: pointer;
            border: none;
            font-weight: 600;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }
        button:hover {
            background-color: #1f6391;
        }
        .status-terkirim {
            color: #27ae60;
            font-weight: 700;
            font-size: 18px;
            text-align: center;
        }
        table.sopir-table {
            margin-top: 10px;
            max-width: 400px;
            font-size: 14px;
        }
        table.sopir-table th, table.sopir-table td {
            padding: 10px 12px;
            border: 1px solid #ddd;
            background: white;
        }
        table.sopir-table thead {
            background: #34495e;
            color: white;
        }
        table.sopir-table tbody tr:hover {
            background: #eaf3ff;
        }
        table.sopir-table a {
            background: #27ae60;
            color: white;
            padding: 6px 12px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            transition: background 0.3s ease;
            display: inline-block;
        }
        table.sopir-table a:hover {
            background: #1e8449;
        }
        @media (max-width: 900px) {
            table, thead, tbody, th, td, tr {
                display: block;
            }
            thead tr {
                display: none;
            }
            tbody tr {
                margin-bottom: 20px;
                background: white;
                padding: 15px;
                border-radius: 10px;
                box-shadow: 0 3px 10px rgb(0 0 0 / 0.1);
            }
            tbody td {
                padding-left: 50%;
                text-align: right;
                position: relative;
                border-bottom: 1px solid #eee;
            }
            tbody td::before {
                content: attr(data-label);
                position: absolute;
                left: 15px;
                width: 45%;
                padding-left: 10px;
                font-weight: 600;
                text-align: left;
                color: #555;
            }
            .action-form {
                max-width: 100%;
            }
        }

        /* Status color classes */
        .status-label {
            padding: 5px 10px;
            border-radius: 6px;
            font-weight: 600;
            display: inline-block;
            text-align: center;
            min-width: 100px;
            color: white;
            font-size: 13px;
        }
        .status-pending { background-color: #f39c12; } /* orange */
        .status-dalam-perjalanan { background-color: #3498db; } /* blue */
        .status-terkirim { background-color: #27ae60; } /* green */
        .status-ditolak { background-color: #c0392b; } /* red */

    </style>
</head>
<body>

<h2>Status Pengiriman</h2>

<div class="button-group">
    <a href="pengiriman_add.php" class="button-link tambah"><i class="fas fa-plus"></i> Tambah Pengiriman</a>
    <a href="dashboard.php" class="button-link kembali">🏠︎ Kembali ke Dashboard</a>
</div>

<?php if ($success): ?>
    <div class="notif"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Nomor</th>
            <th>Tanggal Pengiriman</th>
            <th>Sopir</th>
            <th>Produk</th>
            <th>Toko</th>
            <th>Kecamatan</th>
            <th>Jumlah</th>
            <th>Status</th>
            <th>Tanggal Terkirim</th>
            <th>Alasan</th>
            <th>Aksi</th>
        </tr>
    </thead>
   <tbody>
<?php if ($result && $result->num_rows > 0): ?>
    <?php 
    $last_sj = null;
    while ($row = $result->fetch_assoc()): 
    ?>

        <tr>
            <td data-label="No"><?= $nomor++ ?></td>
            <td data-label="Nomor"><?= htmlspecialchars($row['nomor']) ?></td>
            <td data-label="Tanggal"><?= date('d-m-Y', strtotime($row['tanggal'])) ?></td>

            <td data-label="Sopir">
                <?= htmlspecialchars($row['sopir_nama']) ?><br />
                <small><?= htmlspecialchars($row['plat_nomor']) ?></small>
            </td>

       <td data-label="Produk">
    <?= $row['produk_list'] ?>
</td>

            <td data-label="Toko"><?= htmlspecialchars($row['toko_nama']) ?></td>
            <td data-label="Kecamatan"><?= htmlspecialchars($row['kecamatan']) ?></td>


<td data-label="Jumlah">
    <!-- Tidak perlu jumlah per produk karena sudah di dalam list -->
    -
</td>

            <td data-label="Status">
                <span class="status-label
                    <?php
                        switch ($row['status']) {
                            case 'Pending': echo 'status-pending'; break;
                            case 'Dalam Perjalanan': echo 'status-dalam-perjalanan'; break;
                            case 'Terkirim': echo 'status-terkirim'; break;
                            case 'Ditolak': echo 'status-ditolak'; break;
                        }
                    ?>
                ">
                    <?= htmlspecialchars($row['status']) ?>
                </span>
            </td>
            <td data-label="Tanggal Terkirim">
    <?= $row['tanggal_terkirim'] ? date('d-m-Y H:i', strtotime($row['tanggal_terkirim'])) : '-' ?>
</td>


            <td data-label="Alasan">
                <?= htmlspecialchars($row['alasan']) ?>
            </td>

          <td data-label="Aksi" style="min-width:180px;">
    <?php if (!empty($row['bukti_foto'])): ?>
        <a href="<?= htmlspecialchars($row['bukti_foto']) ?>" 
           class="button-link" 
           style="display:block;text-align:center;" target="_blank">
            Lihat Bukti Pengiriman
        </a>
    <?php else: ?>
        <span style="color:#999; font-size:14px;">Belum ada bukti</span>
    <?php endif; ?>

    <!-- Tombol Cetak Surat Jalan -->
    <a href="cetak_surat_jalan.php?id=<?= $row['sj_id'] ?>" 
       class="button-link" 
       style="display:block; text-align:center; margin-top:5px;" 
       target="_blank">
        🖨️ Cetak Surat Jalan
    </a>
    <!-- Tombol Batalkan Pengiriman (Admin) -->
<?php if ($row['status'] != 'Terkirim' && $row['status'] != 'Dibatalkan'): ?>
    <form method="post" onsubmit="return confirm('Yakin membatalkan pengiriman ini?');" style="margin-top:5px;">
        <input type="hidden" name="batalkan_surat_jalan_id" value="<?= $row['sj_id'] ?>">
        <button type="submit" class="button-delete" style="width:100%; background:#e74c3c;">
            Batalkan Pengiriman
        </button>
    </form>
<?php endif; ?>

</td>



        </tr>

    <?php endwhile; ?>
<?php else: ?>
    <tr><td colspan="11" style="text-align:center;">Tidak ada data pengiriman.</td></tr>
<?php endif; ?>
</tbody>

</table>

<!-- Pagination -->
<div style="margin-top:20px; text-align:center;">
    <?php if ($total_pages > 1): ?>
        <?php for ($i=1; $i <= $total_pages; $i++): ?>
            <a href="?page=<?= $i ?>" style="
                display:inline-block;
                margin: 0 5px;
                padding: 8px 15px;
                background: <?= $i==$page ? '#2980b9' : '#ddd' ?>;
                color: <?= $i==$page ? 'white' : '#333' ?>;
                border-radius: 6px;
                text-decoration: none;
                font-weight: 600;
                transition: background-color 0.3s ease;
            "><?= $i ?></a>
        <?php endfor; ?>
    <?php endif; ?>
</div>

</body>
</html>
<a href="cetak_pengiriman.php" target="_blank" class="button-link">
    <i class="fas fa-print"></i> Cetak Semua Pengiriman
</a>


<?php
$conn->close();
?>
