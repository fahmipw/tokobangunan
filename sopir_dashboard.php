<?php
date_default_timezone_set('Asia/Jakarta');
session_start();
if (!isset($_SESSION['sopir_id'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "toko_bangunan");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$sopir_id = $_SESSION['sopir_id'];

// Hapus foto
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['hapus_foto'])) {
    $surat_jalan_id = $_POST['surat_jalan_id'];

    $stmt = $conn->prepare("SELECT bukti_foto, status FROM surat_jalan WHERE id=? AND sopir_id=?");
    $stmt->bind_param("ii", $surat_jalan_id, $sopir_id);
    $stmt->execute();
    $row_foto = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($row_foto['status'] == 'Dibatalkan') {
        $_SESSION['error_msg'] = "Tidak bisa hapus foto, pengiriman dibatalkan oleh Admin.";
    } else {
        if (!empty($row_foto['bukti_foto']) && file_exists($row_foto['bukti_foto'])) unlink($row_foto['bukti_foto']);
        $stmt = $conn->prepare("UPDATE surat_jalan SET bukti_foto=NULL WHERE id=? AND sopir_id=?");
        $stmt->bind_param("ii", $surat_jalan_id, $sopir_id);
        $stmt->execute();
        $stmt->close();
        $_SESSION['success_msg'] = "Bukti foto berhasil dihapus.";
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Update status
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $surat_jalan_id = $_POST['surat_jalan_id'];
    $status = $_POST['status'];
    $alasan = $_POST['alasan'] ?? null;

    $stmt = $conn->prepare("SELECT status FROM surat_jalan WHERE id=? AND sopir_id=?");
    $stmt->bind_param("ii", $surat_jalan_id, $sopir_id);
    $stmt->execute();
    $current = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($current['status'] == 'Dibatalkan') {
        $_SESSION['error_msg'] = "Pengiriman dibatalkan oleh Admin, tidak bisa diupdate.";
    } elseif ($status == 'Ditolak' && empty(trim($alasan))) {
        $_SESSION['error_msg'] = "Alasan wajib diisi jika pengiriman ditolak.";
    } else {
        $tanggal_terkirim = null;
        $bukti_foto = null;

        if ($status == 'Terkirim') {
            $tanggal_terkirim = date('Y-m-d H:i:s');

            if (!empty($_FILES['bukti_foto']['name'])) {
                $target_dir = "uploads/";
                if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
                $ext = pathinfo($_FILES['bukti_foto']['name'], PATHINFO_EXTENSION);
                $filename = "bukti_{$surat_jalan_id}_" . time() . ".$ext";
                $target_file = $target_dir . $filename;
                if (move_uploaded_file($_FILES['bukti_foto']['tmp_name'], $target_file)) $bukti_foto = $target_file;
            }
        } else {
            if (!empty($_FILES['bukti_foto']['name'])) {
                $_SESSION['error_msg'] = "Tidak bisa upload foto karena status pengiriman bukan Terkirim.";
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            }
        }

        $stmt = $conn->prepare("UPDATE surat_jalan SET status=?, alasan=?, tanggal_terkirim=?, bukti_foto=? WHERE id=? AND sopir_id=?");
        $stmt->bind_param("ssssii", $status, $alasan, $tanggal_terkirim, $bukti_foto, $surat_jalan_id, $sopir_id);
        $stmt->execute();
        $stmt->close();

        $_SESSION['success_msg'] = "Status pengiriman berhasil diupdate.";
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Ambil data pengiriman
$sql = "SELECT 
    sj.id AS sj_id,
    sj.nomor,
    sj.tanggal,
    sj.status,
    sj.alasan,
    sj.tanggal_terkirim,
    sj.bukti_foto,
    t.nama AS toko_nama,
    k.nama AS kecamatan,
    GROUP_CONCAT(CONCAT(p.nama, ' (', pr.jumlah, ')') SEPARATOR '<br>') AS produk_list,
    SUM(pr.jumlah) AS total_jumlah
FROM surat_jalan sj
JOIN pengiriman pr ON pr.surat_jalan_id = sj.id
JOIN produk p ON pr.produk_id = p.id
JOIN toko t ON pr.toko_id = t.id
JOIN kecamatan k ON t.kecamatan_id = k.id
WHERE sj.sopir_id = ?
GROUP BY sj.id
ORDER BY sj.tanggal DESC, sj.id DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $sopir_id);
$stmt->execute();
$result = $stmt->get_result();

$success = $_SESSION['success_msg'] ?? '';
$error = $_SESSION['error_msg'] ?? '';
unset($_SESSION['success_msg'], $_SESSION['error_msg']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Dashboard Sopir</title>
<style>
body { font-family: 'Segoe UI', sans-serif; padding: 20px; background: #f5f5f5; }
h2 { text-align: center; color: #2c3e50; margin-bottom: 20px; }
table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 8px; overflow: hidden; }
th, td { padding: 12px; text-align: center; border-bottom: 1px solid #ddd; }
th { background: #2c3e50; color: #fff; }
tr:hover { background: #f0f7ff; }
.status-label { padding: 5px 10px; border-radius: 6px; font-weight: 600; display: inline-block; color: white; }
.status-pending { background-color: #f1c40f; }
.status-dalam-perjalanan { background-color: #3498db; }
.status-terkirim { background-color: #27ae60; }
.status-ditolak { background-color: #c0392b; }
.status-dibatalkan { background-color: #7f8c8d; }
.success { background-color: #d4edda; color: #155724; padding: 10px; border-radius: 6px; text-align:center; margin-bottom:15px; }
.error { background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 6px; text-align:center; margin-bottom:15px; }
.btn-logout {
    display: inline-block;
    padding: 10px 25px;
    background-color: #e74c3c; /* merah menarik */
    color: white;
    text-decoration: none;
    border-radius: 6px;
    font-weight: bold;
    transition: 0.3s;
}

.btn-logout:hover {
    background-color: #c0392b; /* lebih gelap saat hover */
    transform: scale(1.05);
}

form select, form input[type="text"], form input[type="file"], form button { padding:5px; margin:2px 0; font-size: 0.95rem; }
form button { cursor:pointer; background:#2980b9; color:#fff; border:none; border-radius:4px; transition:0.3s; }
form button:hover { background:#1c5fa0; }
</style>
</head>
<body>

<h2>Dashboard Sopir - <?= htmlspecialchars($_SESSION['sopir_nama']) ?></h2>

<?php if($success): ?>
<div class="success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<?php if($error): ?>
<div class="error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<p style="text-align:center; margin-bottom:20px;">
    <a href="rute_sopir.php?sopir_id=<?= $sopir_id ?>"
       style="padding:10px 20px; background:#3498db; color:white; 
              text-decoration:none; border-radius:6px; font-weight:bold;">
        🚚 Lihat Rute Pengiriman Saya
    </a>
</p>

<table>
<thead>
<tr>
<th>No</th>
<th>Nomor</th>
<th>Tanggal Pengiriman</th>
<th>Produk</th>
<th>Toko</th>
<th>Kecamatan</th>
<th>Jumlah</th>
<th>Status</th>
<th>Alasan</th>
<th>Tanggal Terkirim</th>
<th>Bukti Foto</th>
<th>Aksi</th>
</tr>
</thead>
<tbody>
<?php
$nomor = 1;
if ($result->num_rows > 0):
    while($row = $result->fetch_assoc()):
?>
<tr>
<td><?= $nomor++ ?></td>
<td><?= htmlspecialchars($row['nomor']) ?></td>
<td><?= date('d-m-Y', strtotime($row['tanggal'])) ?></td>
<td>
<?php
if ($row['status'] == 'Dibatalkan') {
    $produk = explode('<br>', $row['produk_list']);
    echo htmlspecialchars($produk[0]);
    if (count($produk) > 1) echo " <i>...</i>";
} else {
    echo $row['produk_list'];
}
?>
</td>
<td><?= htmlspecialchars($row['toko_nama']) ?></td>
<td><?= htmlspecialchars($row['kecamatan']) ?></td>
<td><?= (int)$row['total_jumlah'] ?></td>
<td>
<span class="status-label
<?php
switch($row['status']){
    case 'Pending': echo 'status-pending'; break;
    case 'Dalam Perjalanan': echo 'status-dalam-perjalanan'; break;
    case 'Terkirim': echo 'status-terkirim'; break;
    case 'Ditolak': echo 'status-ditolak'; break;
    case 'Dibatalkan': echo 'status-dibatalkan'; break;
}
?>"><?= htmlspecialchars($row['status']) ?></span>
</td>
<td><?= htmlspecialchars($row['alasan']) ?></td>
<td><?= $row['tanggal_terkirim'] ? date('d-m-Y H:i', strtotime($row['tanggal_terkirim'])) : '-' ?></td>

<td>
<?php if (trim(strtolower($row['status'])) === 'dibatalkan'): ?>
    <span style="color:#7f8c8d; font-style:italic;">Pengiriman dibatalkan oleh Admin</span>
<?php else: ?>
    <?php if($row['bukti_foto']): ?>
        <a href="<?= htmlspecialchars($row['bukti_foto']) ?>" target="_blank">Lihat</a> |
        <form method="post" style="display:inline;" onsubmit="return confirm('Yakin ingin menghapus foto?');">
            <input type="hidden" name="surat_jalan_id" value="<?= $row['sj_id'] ?>">
            <button type="submit" name="hapus_foto"
                style="background:#c0392b; color:#fff; border:none; padding:3px 6px; border-radius:4px; cursor:pointer;">
                Hapus Foto
            </button>
        </form>
    <?php else: ?>
        -
    <?php endif; ?>
<?php endif; ?>
</td>

<td>
<?php if (trim(strtolower($row['status'])) === 'dibatalkan'): ?>
    <span style="color:#7f8c8d; font-style:italic;">Pengiriman dibatalkan oleh Admin</span>
<?php else: ?>
    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="surat_jalan_id" value="<?= $row['sj_id'] ?>">

        <select name="status" required>
            <option value="">--Pilih Status--</option>
            <option value="Pending" <?= $row['status']=='Pending'?'selected':'' ?>>Pending</option>
            <option value="Dalam Perjalanan" <?= $row['status']=='Dalam Perjalanan'?'selected':'' ?>>Dalam Perjalanan</option>
            <option value="Terkirim" <?= $row['status']=='Terkirim'?'selected':'' ?>>Terkirim</option>
            <option value="Ditolak" <?= $row['status']=='Ditolak'?'selected':'' ?>>Ditolak</option>
        </select>

        <input type="text" name="alasan" placeholder="Alasan jika Ditolak" value="<?= htmlspecialchars($row['alasan']) ?>">
        <input type="file" name="bukti_foto" accept="image/*">

        <button type="submit" name="update_status">Update</button>
    </form>
<?php endif; ?>
</td>

</tr>
<?php
    endwhile;
else:
?>
<tr><td colspan="12" style="text-align:center;">Tidak ada pengiriman.</td></tr>
<?php endif; ?>
</tbody>
</table>

<p style="text-align:center; margin-top:20px;">
    <a href="logout.php" class="btn-logout">Logout</a>
</p>


</body>
</html>

<?php $conn->close(); ?>
