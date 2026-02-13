<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// =================================================
// 1. KONFIGURASI & FUNGSI
// =================================================
$conn = new mysqli("localhost", "root", "", "toko_bangunan");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

function formatTanggal($tanggal) {
    $bulan = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    $tgl = strtotime($tanggal);
    return date('d', $tgl) . ' ' . $bulan[(int)date('m', $tgl)] . ' ' . date('Y', $tgl);
}

function terbilang($x) {
    $angka = ["", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas"];
    if ($x < 12) return " " . $angka[$x];
    elseif ($x < 20) return terbilang($x - 10) . " belas";
    elseif ($x < 100) return terbilang($x / 10) . " puluh" . terbilang($x % 10);
    elseif ($x < 200) return " seratus" . terbilang($x - 100);
    elseif ($x < 1000) return terbilang($x / 100) . " ratus" . terbilang($x % 100);
    elseif ($x < 2000) return " seribu" . terbilang($x - 1000);
    elseif ($x < 1000000) return terbilang($x / 1000) . " ribu" . terbilang($x % 1000);
    return "angka terlalu besar"; 
}

$nama_perusahaan = "PT. PILAR RAYA UTAMA";
$alamat_perusahaan = "Ruko Paramount Gaze A/21 Gading Serpong";
$telp_fax_perusahaan = "Telp : 021 - 7568 4388 / 4386 4588 Fax: 021 - 7568 4587";

// =================================================
// 2. AMBIL DATA
// =================================================
$surat_jalan_id = $_GET['id'] ?? null;
if (!$surat_jalan_id || !is_numeric($surat_jalan_id)) die("ID Surat Jalan tidak valid.");

// Header
$sql_header = "SELECT sj.nomor, sj.tanggal, s.nama AS sopir_nama, t.nama AS toko_nama, t.alamat AS toko_alamat, t.no_telp AS toko_telp
               FROM surat_jalan sj
               JOIN sopir s ON sj.sopir_id = s.id
               JOIN pengiriman pr ON pr.surat_jalan_id = sj.id
               JOIN toko t ON pr.toko_id = t.id
               WHERE sj.id = ?
               GROUP BY sj.id"; 

$stmt_header = $conn->prepare($sql_header);
$stmt_header->bind_param('i', $surat_jalan_id);
$stmt_header->execute();
$result_header = $stmt_header->get_result();
if ($result_header->num_rows === 0) die("Surat Jalan tidak ditemukan.");
$header = $result_header->fetch_assoc();
$stmt_header->close();

// Detail
$sql_items = "SELECT p.id, p.nama AS produk_nama, pr.jumlah
              FROM pengiriman pr
              JOIN produk p ON pr.produk_id = p.id
              WHERE pr.surat_jalan_id = ?";

$stmt_items = $conn->prepare($sql_items);
$stmt_items->bind_param('i', $surat_jalan_id);
$stmt_items->execute();
$items_result = $stmt_items->get_result();
$items = [];
while ($row = $items_result->fetch_assoc()) {
    $items[] = $row;
}
$stmt_items->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Jalan No. <?= htmlspecialchars($header['nomor']) ?></title>
    <style>
        body { font-family: 'Arial', sans-serif; margin: 0; padding: 0; background-color: #fff; }

        .surat-jalan {
            width: 210mm; 
            min-height: 297mm; 
            margin: 0 auto;
            padding: 15mm; 
            box-sizing: border-box;
            font-size: 12pt;
            color: #000;
        }

        .header-kop { width: 100%; border-collapse: collapse; }
        .header-kop td { padding: 2px 0; }
        .header-kop h2 { margin: 0; font-size: 14pt; font-weight: bold; }
        .sj-title { font-size: 20pt; font-weight: bold; color: #333; text-align: right; }

        .line-divider {
            border: 0;
            border-top: 1px solid #000;
            margin-top: 5px;
            margin-bottom: 10px;
        }

        .header-sj { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .header-sj td { padding: 4px; vertical-align: top; border: 1px solid #000; font-size: 10pt; }
        .header-sj .info-content td {
            border: none;
            padding: 0;
            white-space: nowrap;
        }
        .header-sj .info-content { width: 100%; border-collapse: collapse; }
        .header-sj .label { width: 100px; font-weight: bold; }

        .table-barang { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .table-barang th, .table-barang td {
            border: 1px solid #000;
            padding: 6px 8px;
            text-align: left;
        }
        .table-barang th {
            background-color: #f0f0f0;
            text-align: center;
            font-weight: bold;
        }
        .table-barang .no { width: 30px; text-align: center; }
        .table-barang .kode { width: 100px; text-align: center; }
        .table-barang .quantity { text-align: left; width: 250px; }

        .signature-area {
            width: 100%;
            margin-top: 50px;
            text-align: center;
            font-size: 12pt;
        }

        .signature-area td {
            width: 33%;
            vertical-align: top;
            padding-top: 10px;
        }

        .ttd-line {
            border-top: 1px solid #000;
            width: 80%;
            margin: 0 auto 5px;
            height: 1px;
        }

        .signature-area span {
            display: inline-block;
            margin-top: 5px;
        }

        @media print {
            .surat-jalan { width: 100%; min-height: 0; padding: 0; margin: 0; }
            @page { size: A4; margin: 15mm; }
            body { margin: 0; }
        }
    </style>
</head>
<body onload="window.print()">

<div class="surat-jalan">
    <table class="header-kop">
        <tr>
            <td style="width: 70%;">
                <h2><?= htmlspecialchars($nama_perusahaan) ?></h2>
                <p style="margin: 0;"><?= htmlspecialchars($alamat_perusahaan) ?></p>
                <p style="margin: 0;"><?= htmlspecialchars($telp_fax_perusahaan) ?></p>
            </td>
            <td style="width: 30%; text-align: right;">
                <span class="sj-title">SURAT JALAN</span>
            </td>
        </tr>
    </table>

    <hr class="line-divider">

    <table class="header-sj">
        <tr>
            <td style="width: 50%;">
                <table class="info-content">
                    <tr><td class="label">Nama Toko</td><td>:</td><td><?= htmlspecialchars($header['toko_nama']) ?></td></tr>
                    <tr><td class="label">Alamat</td><td>:</td><td><?= htmlspecialchars($header['toko_alamat']) ?></td></tr>
                    <tr><td class="label">No Telpon</td><td>:</td><td><?= htmlspecialchars($header['toko_telp'] ?? '-') ?></td></tr>
                </table>
            </td>
            <td style="width: 50%;">
                <table class="info-content">
                    <tr><td class="label">No Surat Jalan</td><td>:</td><td><?= htmlspecialchars($header['nomor']) ?></td></tr>
                    <tr><td class="label">Tanggal</td><td>:</td><td><?= formatTanggal($header['tanggal']) ?></td></tr>
                    <tr><td class="label">Pengiriman</td><td>:</td><td><?= htmlspecialchars($header['sopir_nama']) ?></td></tr>
                    <tr><td class="label">Sales</td><td>:</td><td>-</td></tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="table-barang">
        <thead>
            <tr>
                <th class="no">No</th>
                <th class="kode">KODE</th>
                <th>NAMA BARANG</th>
                <th class="quantity">QUANTITY</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; foreach ($items as $item): ?>
            <tr>
                <td class="no"><?= $no++ ?></td>
                <td class="kode"><?= htmlspecialchars($item['id']) ?></td>
                <td><?= htmlspecialchars($item['produk_nama']) ?></td>
                <td class="quantity">
                    <?= htmlspecialchars($item['jumlah']) ?> 
                    (<?= ucwords(trim(terbilang($item['jumlah']))) ?> Sak)
                </td>
            </tr>
            <?php endforeach; ?>
            <?php for ($i = $no; $i <= 5; $i++): ?>
            <tr>
                <td class="no">&nbsp;</td>
                <td class="kode"></td>
                <td></td>
                <td class="quantity"></td>
            </tr>
            <?php endfor; ?>
        </tbody>
    </table>

    <!-- Signature Area -->
    <table class="signature-area">
        <tr>
            <td>Penerima</td>
            <td>Pengirim / Sopir</td>
            <td>Hormat Kami</td>
        </tr>
        <tr style="height: 80px;">
            <td></td><td></td><td></td>
        </tr>
        <tr>
            <td>
                <div class="ttd-line"></div>
                <span>(_______________________)</span>
            </td>
            <td>
                <div class="ttd-line"></div>
                <span>(<?= htmlspecialchars($header['sopir_nama']) ?>)</span>
            </td>
            <td>
                <div class="ttd-line"></div>
                <span>(_______________________)</span><br>
                <span style="font-size: 8pt;"><?= htmlspecialchars($nama_perusahaan) ?></span>
            </td>
        </tr>
    </table>

    <p style="text-align: center; margin-top: 10px; font-size: 8pt; color: #555;">**Barang sudah diterima dalam keadaan baik**</p>
</div>

</body>
</html>
