<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "toko_bangunan");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Fungsi format tanggal Indonesia dengan nama hari
function formatTanggalIndonesia($tanggal) {
    $hari = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
    $bulan = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    
    $tgl = strtotime($tanggal);
    $namaHari = $hari[date('w', $tgl)];
    $tanggalAngka = date('d', $tgl);
    $namaBulan = $bulan[(int)date('m', $tgl)];
    $tahun = date('Y', $tgl);
    
    return "$namaHari, $tanggalAngka $namaBulan $tahun";
}

// Ambil filter
$status_filter = $_GET['status'] ?? '';
$tanggal_awal = $_GET['tanggal_awal'] ?? '';
$tanggal_akhir = $_GET['tanggal_akhir'] ?? '';
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;

$whereClauses = [];
$params = [];
$types = '';

// Filter status
if ($status_filter !== '') {
    $whereClauses[] = "sj.status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

// Filter tanggal
if ($tanggal_awal !== '') {
    $whereClauses[] = "sj.tanggal >= ?";
    $params[] = $tanggal_awal;
    $types .= 's';
}
if ($tanggal_akhir !== '') {
    $whereClauses[] = "sj.tanggal <= ?";
    $params[] = $tanggal_akhir;
    $types .= 's';
}

$whereSQL = '';
if (count($whereClauses) > 0) {
    $whereSQL = ' WHERE ' . implode(' AND ', $whereClauses);
}

// Pagination setup
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Total data
$count_sql = "SELECT COUNT(*) as total
             FROM surat_jalan sj
             JOIN sopir s ON sj.sopir_id = s.id
             JOIN pengiriman pr ON pr.surat_jalan_id = sj.id
             JOIN produk p ON pr.produk_id = p.id
             JOIN toko t ON pr.toko_id = t.id
             JOIN kecamatan k ON t.kecamatan_id = k.id
             $whereSQL";

$count_stmt = $conn->prepare($count_sql);
if ($params) $count_stmt->bind_param($types, ...$params);
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_rows = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $per_page);

// Query data
// ⚠️ PERUBAHAN: Tambahkan sj.id di SELECT
$sql = "SELECT sj.id AS surat_jalan_id, sj.nomor, sj.tanggal, sj.status, sj.alasan, s.nama AS sopir_nama, s.plat_nomor,
        p.nama AS produk_nama, t.nama AS toko_nama, k.nama AS kecamatan, pr.jumlah
        FROM surat_jalan sj
        JOIN sopir s ON sj.sopir_id = s.id
        JOIN pengiriman pr ON pr.surat_jalan_id = sj.id
        JOIN produk p ON pr.produk_id = p.id
        JOIN toko t ON pr.toko_id = t.id
        JOIN kecamatan k ON t.kecamatan_id = k.id
        $whereSQL
        ORDER BY sj.tanggal DESC, sj.nomor
        LIMIT ? OFFSET ?";

$types .= 'ii';
$params[] = $per_page;
$params[] = $offset;

$stmt = $conn->prepare($sql);
if (!$stmt) die("Prepare failed: " . $conn->error);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Pengiriman</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f4f7fa;
            margin: 0;
            padding: 30px 20px;
            color: #2c3e50;
            max-width: 1300px; /* Lebarkan sedikit untuk kolom baru */
            margin-left: auto;
            margin-right: auto;
        }
        h2 {
            text-align: center;
            margin-bottom: 30px;
            font-weight: 700;
            color: #34495e;
        }
        form {
            background: white;
            padding: 22px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 30px;
            align-items: flex-end;
        }
        form label {
            flex: 1 1 200px;
            display: flex;
            flex-direction: column;
            font-weight: 600;
        }
        select, input[type="date"] {
            margin-top: 6px;
            padding: 10px;
            border: 1.5px solid #ccc;
            border-radius: 10px;
            font-size: 14px;
        }
        .btn-group {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        button {
            cursor: pointer;
            padding: 10px 20px;
            border: none;
            font-weight: bold;
            border-radius: 10px;
            font-size: 14px;
            transition: 0.3s;
        }
        button[type="submit"] {
            background-color: #3498db;
            color: white;
        }
        button[type="reset"] {
            background-color: #95a5a6;
            color: white;
        }
        .print-btn {
            background-color: #2ecc71;
            color: white;
        }
        /* Style untuk tombol cetak di baris tabel */
        .btn-cetak-sj { 
            background-color: #f39c12; 
            color: white; 
            padding: 6px 10px; 
            font-size: 12px; 
            text-decoration: none;
            display: inline-block;
        }
        .btn-cetak-sj:hover {
            opacity: 0.8;
        }

        button:hover {
            opacity: 0.9;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0,0,0,0.05);
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            font-size: 14px;
        }
        thead {
            background: #3498db;
            color: white;
        }
        tbody tr:nth-child(even) {
            background: #f2f6f8;
        }
        .pagination {
            margin-top: 25px;
            text-align: center;
        }
        .pagination a {
            margin: 0 5px;
            padding: 8px 12px;
            text-decoration: none;
            border: 1px solid #ddd;
            border-radius: 6px;
            color: #3498db;
            font-weight: bold;
            transition: 0.2s;
        }
        .pagination a.active {
            background: #2980b9;
            color: white;
        }
        .button-link.kembali {
            display: inline-block;
            margin: 30px auto 0;
            padding: 12px 30px;
            background: #7f8c8d;
            color: white;
            border-radius: 10px;
            text-decoration: none;
            font-weight: bold;
            text-align: center;
        }

        .only-print {
            display: none;
        }

       @media print {
            form, .btn-group, .pagination, .button-link.kembali, .aksi-kolom, table:not(.only-print table) {
                display: none !important;
            }
            .only-print {
                display: block !important;
            }
        }

            .print-signature {
                margin-top: 50px;
                width: 100%;
                font-size: 12pt;
            }
            .print-signature td {
                text-align: center;
                padding: 40px 10px 10px 10px;
                border: none;
            }
           .report-date {
                text-align: center; /* Align the text to the left */
                margin-top: 30px;
                font-weight: 600;
            }
    </style>
</head>
<body>

<h2>Laporan Pengiriman</h2>

<form method="get" action="">
    <label>
        Status:
        <select name="status">
            <option value="">Semua</option>
            <option value="Pending" <?= $status_filter=='Pending' ? 'selected' : '' ?>>Pending</option>
            <option value="Dalam Perjalanan" <?= $status_filter=='Dalam Perjalanan' ? 'selected' : '' ?>>Dalam Perjalanan</option>
            <option value="Terkirim" <?= $status_filter=='Terkirim' ? 'selected' : '' ?>>Terkirim</option>
            <option value="Ditolak" <?= $status_filter=='Ditolak' ? 'selected' : '' ?>>Ditolak</option>
        </select>
    </label>
    <label>
        Tanggal Awal:
        <input type="date" name="tanggal_awal" value="<?= htmlspecialchars($tanggal_awal) ?>">
    </label>
    <label>
        Tanggal Akhir:
        <input type="date" name="tanggal_akhir" value="<?= htmlspecialchars($tanggal_akhir) ?>">
    </label>
    <div class="btn-group">
        <button type="submit">Filter</button>
        <button type="reset" onclick="window.location='?';return false;">Reset</button>
        <button type="button" class="print-btn" onclick="window.print()">Cetak Laporan</button>
    </div>
</form>

<table>
    <thead>
        <tr>
            <th>Nomor Surat Jalan</th>
            <th>Tanggal</th>
            <th>Status</th>
            <th>Alasan</th>
            <th>Nama Sopir</th>
            <th>Plat Nomor</th>
            <th>Nama Produk</th>
            <th>Nama Toko</th>
            <th>Kecamatan</th>
            <th>Jumlah</th>
            <th class="aksi-kolom">Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result->num_rows === 0): ?>
            <tr><td colspan="11" style="text-align:center; padding: 30px;">Tidak ada data</td></tr>
        <?php else: ?>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['nomor']) ?></td>
                <td><?= formatTanggalIndonesia($row['tanggal']) ?></td>
                <td><?= htmlspecialchars($row['status']) ?></td>
                <td><?= htmlspecialchars($row['alasan']) ?></td>
                <td><?= htmlspecialchars($row['sopir_nama']) ?></td>
                <td><?= htmlspecialchars($row['plat_nomor']) ?></td>
                <td><?= htmlspecialchars($row['produk_nama']) ?></td>
                <td><?= htmlspecialchars($row['toko_nama']) ?></td>
                <td><?= htmlspecialchars($row['kecamatan']) ?></td>
                <td><?= htmlspecialchars($row['jumlah']) ?></td>
                <td class="aksi-kolom">
                    <a href="cetak_surat_jalan.php?id=<?= htmlspecialchars($row['surat_jalan_id']) ?>" target="_blank" class="btn-cetak-sj">Cetak SJ</a>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php endif; ?>
    </tbody>
</table>

<div class="pagination">
    <?php if ($total_pages > 1): ?>
        <?php for ($p = 1; $p <= $total_pages; $p++): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $p])) ?>"
               class="<?= ($p == $page) ? 'active' : '' ?>"><?= $p ?></a>
        <?php endfor; ?>
    <?php endif; ?>
</div>

---

<div class="only-print">
    <div style="text-align: center; margin-bottom: 20px;">
        <h2 style="margin: 5px 0;">TOKO BANGUNAN SINAR TERANG BSD</h2>
        <p style="margin: 0;">Jl. Raya Rawa Buntu No.168, Rw. Buntu, Kec. Serpong, Kota Tangerang Selatan, Banten 15318</p>
        <p style="margin: 0;">Telp: (021) 75884388 | Email: sinarterang@gmail.com</p>
        <hr style="border: 2px solid black; margin-top: 10px;">
    </div>

    <?php
if ($tanggal_awal !== '' && $tanggal_akhir !== '') {
    echo '<p class="report-date">Laporan Periode: ' . formatTanggalIndonesia($tanggal_awal) . ' s/d ' . formatTanggalIndonesia($tanggal_akhir) . '</p>';
} elseif ($tanggal_awal !== '') {
    echo '<p class="report-date">Laporan Periode: ' . formatTanggalIndonesia($tanggal_awal) . '</p>';
} else {
    echo '<p class="report-date">' . formatTanggalIndonesia(date('Y-m-d')) . '</p>';
}
?>


    <table style="width:100%; border-collapse: collapse; margin-top: 20px;">
        <thead>
            <tr>
                <th style="border: 1px solid black; padding: 8px;">Nomor</th>
                <th style="border: 1px solid black; padding: 8px;">Tanggal</th>
                <th style="border: 1px solid black; padding: 8px;">Status</th>
                <th style="border: 1px solid black; padding: 8px;">Alasan</th>
                <th style="border: 1px solid black; padding: 8px;">Sopir</th>
                <th style="border: 1px solid black; padding: 8px;">Plat</th>
                <th style="border: 1px solid black; padding: 8px;">Produk</th>
                <th style="border: 1px solid black; padding: 8px;">Toko</th>
                <th style="border: 1px solid black; padding: 8px;">Kecamatan</th>
                <th style="border: 1px solid black; padding: 8px;">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Jalankan ulang query tanpa LIMIT dan OFFSET untuk cetak semua data
            $sql_print = str_replace("LIMIT ? OFFSET ?", "", $sql);
            $stmt_print = $conn->prepare($sql_print);

            // Salin ulang params dan types karena kita akan modifikasi
            $params_print = $params;
            $types_print = $types;

            // Hapus 2 parameter terakhir (limit dan offset) jika ada
            if (count($params_print) >= 2) {
                array_pop($params_print);
                array_pop($params_print);
                $types_print = substr($types_print, 0, -2); // Hapus dua karakter terakhir dari types
            }

            // Bind jika masih ada parameter
            if (!empty($params_print)) {
                // Perlu menghapus kolom 'i' yang terkait dengan sj.id agar bind_param tidak error karena tipe data yang tidak sesuai
                // Namun, karena query cetak ini HANYA untuk menampilkan data laporan (bukan surat jalan), kita bisa HAPUS sj.id
                // dan memastikan types_print sesuai dengan jumlah parameter yang tersisa.
                // Dalam kasus ini, sj.id tidak perlu dihapus dari SELECT karena dia tidak ada di parameter,
                // tapi kita harus memastikan bind_param hanya menerima parameter filter.
                
                // Jika Anda ingin memastikan keamanan, Anda harus membuat query terpisah untuk cetak yang TIDAK menyertakan kolom `sj.id` jika kolom tersebut tidak digunakan di sini.
                // Karena kita hanya menampilkan, kita anggap aman. Lanjut bind filter:
                
                $stmt_print->bind_param($types_print, ...$params_print);
            }

            $stmt_print->execute();
            $result_print = $stmt_print->get_result();


            if ($result_print->num_rows === 0) {
                echo '<tr><td colspan="10" style="border: 1px solid black; text-align:center; padding: 20px;">Tidak ada data</td></tr>';
            } else {
                while ($row = $result_print->fetch_assoc()) {
                    echo '<tr>';
                    echo '<td style="border: 1px solid black; padding: 6px;">' . htmlspecialchars($row['nomor']) . '</td>';
                    echo '<td style="border: 1px solid black; padding: 6px;">' . formatTanggalIndonesia($row['tanggal']) . '</td>';
                    echo '<td style="border: 1px solid black; padding: 6px;">' . htmlspecialchars($row['status']) . '</td>';
                    echo '<td style="border: 1px solid black; padding: 6px;">' . htmlspecialchars($row['alasan']) . '</td>';
                    echo '<td style="border: 1px solid black; padding: 6px;">' . htmlspecialchars($row['sopir_nama']) . '</td>';
                    echo '<td style="border: 1px solid black; padding: 6px;">' . htmlspecialchars($row['plat_nomor']) . '</td>';
                    echo '<td style="border: 1px solid black; padding: 6px;">' . htmlspecialchars($row['produk_nama']) . '</td>';
                    echo '<td style="border: 1px solid black; padding: 6px;">' . htmlspecialchars($row['toko_nama']) . '</td>';
                    echo '<td style="border: 1px solid black; padding: 6px;">' . htmlspecialchars($row['kecamatan']) . '</td>';
                    echo '<td style="border: 1px solid black; padding: 6px;">' . htmlspecialchars($row['jumlah']) . '</td>';
                    echo '</tr>';
                }
            }
            $stmt_print->close();
            ?>
        </tbody>
    </table>

    <table class="print-signature" style="width:100%; margin-top: 60px;">
        <tr>
            <td>Mengetahui<br><br><br><br>(__________________)</td>
            <td>Yang Membuat<br><br><br><br>(__________________)</td>
            <td>Penerima<br><br><br><br>(__________________)</td>
        </tr>
    </table>
</div>

<a href="dashboard.php" class="button-link kembali">🏠︎ Kembali ke Dashboard</a>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>