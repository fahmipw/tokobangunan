<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "toko_bangunan");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$sql = "SELECT sj.nomor, sj.tanggal, sj.tanggal_terkirim, sj.status, sj.alasan, sj.bukti_foto,
        s.nama AS sopir_nama, s.plat_nomor,
        p.nama AS produk_nama, t.nama AS toko_nama, k.nama AS kecamatan, pr.jumlah
        FROM surat_jalan sj
        JOIN sopir s ON sj.sopir_id = s.id
        JOIN pengiriman pr ON pr.surat_jalan_id = sj.id
        JOIN produk p ON pr.produk_id = p.id
        JOIN toko t ON pr.toko_id = t.id
        JOIN kecamatan k ON t.kecamatan_id = k.id
        ORDER BY sj.tanggal DESC, sj.id DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Cetak Data Pengiriman</title>
<style>
body { font-family: 'Segoe UI', sans-serif; padding: 20px; }
table { width: 100%; border-collapse: collapse; }
th, td { border: 1px solid #333; padding: 8px; text-align: center; }
th { background: #2c3e50; color: white; }
img { max-width: 100px; height: auto; }
@media print {
    button { display: none; }
}
</style>
</head>
<body>

<h2>Data Pengiriman</h2>
<button onclick="window.print()">🖨️ Cetak</button>
<br><br>

<table>
<thead>
<tr>
<th>No</th>
<th>Nomor</th>
<th>Tanggal</th>
<th>Sopir</th>
<th>Produk</th>
<th>Toko</th>
<th>Kecamatan</th>
<th>Jumlah</th>
<th>Status</th>
<th>Tanggal Terkirim</th>
<th>Alasan</th>
<th>Bukti Foto</th>
</tr>
</thead>
<tbody>
<?php
$no = 1;
if ($result && $result->num_rows > 0):
    while ($row = $result->fetch_assoc()):
?>
<tr>
<td><?= $no++ ?></td>
<td><?= htmlspecialchars($row['nomor']) ?></td>
<td><?= date('d-m-Y', strtotime($row['tanggal'])) ?></td>
<td><?= htmlspecialchars($row['sopir_nama']) ?><br><small><?= htmlspecialchars($row['plat_nomor']) ?></small></td>
<td><?= htmlspecialchars($row['produk_nama']) ?></td>
<td><?= htmlspecialchars($row['toko_nama']) ?></td>
<td><?= htmlspecialchars($row['kecamatan']) ?></td>
<td><?= (int)$row['jumlah'] ?></td>
<td><?= htmlspecialchars($row['status']) ?></td>
<td><?= $row['tanggal_terkirim'] ? date('d-m-Y H:i', strtotime($row['tanggal_terkirim'])) : '-' ?></td>
<td><?= htmlspecialchars($row['alasan']) ?></td>
<td>
<?php if (!empty($row['bukti_foto'])): ?>
    <img src="<?= htmlspecialchars($row['bukti_foto']) ?>" alt="Bukti Foto">
<?php else: ?>
    -
<?php endif; ?>
</td>
</tr>
<?php
    endwhile;
else:
?>
<tr><td colspan="12">Tidak ada data pengiriman.</td></tr>
<?php endif; ?>
</tbody>
</table>

</body>
</html>

<?php $conn->close(); ?>
