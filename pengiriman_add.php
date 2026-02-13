<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "toko_bangunan");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Tambah kolom harga jika belum ada
$cek_kolom = $conn->query("SHOW COLUMNS FROM pengiriman LIKE 'harga'");
if ($cek_kolom->num_rows == 0) {
    $conn->query("ALTER TABLE pengiriman ADD COLUMN harga DECIMAL(10,2) NULL");
}

// Dropdown toko & produk
$toko_result = $conn->query("SELECT toko.id, toko.nama, kecamatan.nama AS kecamatan FROM toko JOIN kecamatan ON toko.kecamatan_id = kecamatan.id ORDER BY kecamatan.nama, toko.nama");
$produk_result = $conn->query("SELECT id, nama, stok FROM produk");

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Data utama
    $toko_id  = intval($_POST['toko_id']);
    $tanggal  = $_POST['tanggal'];
    $produk_ids = $_POST['produk_id'];
    $jumlahs   = $_POST['jumlah'];
    $hargas    = $_POST['harga'];

    // Validasi dasar
    if ($toko_id <= 0 || !$tanggal || empty($produk_ids)) {
        $error = "Semua field wajib diisi.";
    } else if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
        $error = "Format tanggal tidak valid.";
    } else {

        try {
            $conn->begin_transaction();

            // Ambil koordinat toko tujuan
            $stmtToko = $conn->prepare("SELECT latitude, longitude FROM toko WHERE id = ?");
            $stmtToko->bind_param("i", $toko_id);
            $stmtToko->execute();
            $stmtToko->bind_result($toko_lat, $toko_lon);
            if (!$stmtToko->fetch()) throw new Exception("Toko tidak ditemukan.");
            $stmtToko->close();

            // Rute sopir (SAMA seperti kode lama)
            $today = date('Y-m-d');
            $surat_result = $conn->query("
                SELECT sj.id, sj.sopir_id, t.latitude, t.longitude
                FROM surat_jalan sj
                JOIN pengiriman p ON sj.id = p.surat_jalan_id
                JOIN toko t ON p.toko_id = t.id
                WHERE sj.tanggal = '$today'
            ");

            $sopir_rute = [];
            foreach ($surat_result as $row) {
                $sid = $row['sopir_id'];
                if (!isset($sopir_rute[$sid])) $sopir_rute[$sid] = [];
                $sopir_rute[$sid][] = [
                    'latitude' => $row['latitude'],
                    'longitude' => $row['longitude']
                ];
            }

            // Ambil titik terakhir
            $last_point = [];
            foreach ($sopir_rute as $sid => $rute) {
                $last_point[$sid] = end($rute);
            }

            // Fungsi jarak
            function jarak($lat1, $lon1, $lat2, $lon2) {
                $R = 6371;
                $dLat = deg2rad($lat2 - $lat1);
                $dLon = deg2rad($lon2 - $lon1);
                $a = sin($dLat/2)**2 + cos(deg2rad($lat1))*cos(deg2rad($lat2))*sin($dLon/2)**2;
                return 2 * $R * atan2(sqrt($a), sqrt(1-$a));
            }

            // Cari sopir terdekat
            $terdekat_sopir_id = null;
            $min_jarak = INF;

            foreach ($last_point as $sid => $poin) {
                $dist = jarak($poin['latitude'], $poin['longitude'], $toko_lat, $toko_lon);
                if ($dist < $min_jarak && $dist <= 1.5) {
                    $min_jarak = $dist;
                    $terdekat_sopir_id = $sid;
                }
            }

            if ($terdekat_sopir_id !== null) {
                $sopir_id = $terdekat_sopir_id;
            } else {
                $used_sopir_ids = array_keys($sopir_rute);
                $where_not_in = "";
                if (!empty($used_sopir_ids)) {
                    $where_not_in = "WHERE id NOT IN (" . implode(',', $used_sopir_ids) . ")";
                }
                $sopir_result = $conn->query("SELECT id FROM sopir $where_not_in");

                $sopir_ids = [];
                while ($row = $sopir_result->fetch_assoc()) $sopir_ids[] = $row['id'];

                if (empty($sopir_ids)) {
                    $all = $conn->query("SELECT id FROM sopir");
                    while ($r = $all->fetch_assoc()) $sopir_ids[] = $r['id'];
                }
                if (empty($sopir_ids)) throw new Exception("Tidak ada sopir tersedia.");

                $sopir_id = $sopir_ids[array_rand($sopir_ids)];
            }

            // Buat 1 surat jalan
            $nomor_sj = "SJ" . str_replace('-', '', $tanggal) . rand(1000, 9999);
            $stmtSJ = $conn->prepare("INSERT INTO surat_jalan (nomor, tanggal, sopir_id, status) VALUES (?, ?, ?, 'Pending')");
            $stmtSJ->bind_param("ssi", $nomor_sj, $tanggal, $sopir_id);
            $stmtSJ->execute();
            $surat_jalan_id = $stmtSJ->insert_id;
            $stmtSJ->close();

            // LOOP untuk semua produk
            for ($i = 0; $i < count($produk_ids); $i++) {

                $produk_id = intval($produk_ids[$i]);
                $jumlah    = intval($jumlahs[$i]);
                $harga     = floatval($hargas[$i]);

                if ($produk_id <= 0 || $jumlah <= 0 || $harga < 0) {
                    throw new Exception("Data produk tidak valid.");
                }

                // Ambil stok lama
                $stmt0 = $conn->prepare("SELECT stok FROM produk WHERE id = ?");
                $stmt0->bind_param("i", $produk_id);
                $stmt0->execute();
                $stmt0->bind_result($stok_lama);
                if (!$stmt0->fetch()) throw new Exception("Produk tidak ditemukan.");
                $stmt0->close();

                if ($stok_lama < $jumlah) {
                    throw new Exception("Stok produk ID $produk_id tidak cukup (stok: $stok_lama).");
                }

                $stok_baru = $stok_lama - $jumlah;

                // Update stok
                $stmt1 = $conn->prepare("UPDATE produk SET stok = ? WHERE id = ?");
                $stmt1->bind_param("ii", $stok_baru, $produk_id);
                $stmt1->execute();
                $stmt1->close();

                // Riwayat stok
                $ket = "Pengiriman ke toko ID $toko_id, jumlah $jumlah";
                $stmt2 = $conn->prepare(
                    "INSERT INTO riwayat_stok (produk_id, waktu, stok_lama, stok_baru, jenis, keterangan)
                     VALUES (?, NOW(), ?, ?, 'pengiriman', ?)"
                );
                $stmt2->bind_param("iiis", $produk_id, $stok_lama, $stok_baru, $ket);
                $stmt2->execute();
                $stmt2->close();

                // Detail pengiriman (multiproduk)
                $stmt4 = $conn->prepare("INSERT INTO pengiriman (surat_jalan_id, produk_id, toko_id, jumlah, harga)
                                         VALUES (?, ?, ?, ?, ?)");
                $stmt4->bind_param("iiiid", $surat_jalan_id, $produk_id, $toko_id, $jumlah, $harga);
                $stmt4->execute();
                $stmt4->close();
            }

            $conn->commit();
            $_SESSION['success_msg'] = "Pengiriman berhasil dibuat dengan nomor $nomor_sj (multi produk). Sopir ID: $sopir_id.";
            header("Location: status_pengiriman.php");
            exit();

        } catch (Exception $e) {
            $conn->rollback();
            $error = $e->getMessage();
        }
    }
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Tambah Pengiriman</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet" />
<style>
/* CSS TETAP SAMA PERSIS DENGAN YANG ANDA KIRIM */
</style>
</head>
<body>
<div class="container">

<h2>Tambah Pengiriman</h2>

<?php if ($error): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="post" action="">

    <!-- MULTI PRODUK -->
    <div id="produk-container">
        <div class="produk-item">

            <label>Produk:</label>
            <select name="produk_id[]" required>
                <option value="">-- Pilih Produk --</option>
                <?php
                $produk_result->data_seek(0);
                while ($row = $produk_result->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>">
                        <?= htmlspecialchars($row['nama']) ?> (Stok: <?= $row['stok'] ?>)
                    </option>
                <?php endwhile; ?>
            </select>

            <label>Jumlah:</label>
            <input type="number" name="jumlah[]" min="1" required>

            <label>Harga:</label>
            <input type="number" name="harga[]" min="0" step="0.01" required>

            <hr>
        </div>
    </div>

    <button type="button" onclick="addProduk()">+ Tambah Produk</button>

    <label for="toko_id">Toko (Tujuan Pengiriman):</label>
    <select id="toko_id" name="toko_id" required>
        <option value="">-- Pilih Toko --</option>
        <?php
        $toko_result->data_seek(0);
        while ($row = $toko_result->fetch_assoc()): ?>
            <option value="<?= $row['id'] ?>">
                <?= htmlspecialchars($row['nama']) ?> (<?= htmlspecialchars($row['kecamatan']) ?>)
            </option>
        <?php endwhile; ?>
    </select>

    <label for="tanggal">Tanggal Pengiriman:</label>
    <input type="date" id="tanggal" name="tanggal" required value="<?= date('Y-m-d') ?>" />

    <button type="submit">Buat Pengiriman</button>

</form>

<a href="dashboard.php" class="back-link">← Kembali ke Dashboard</a>

</div>

<script>
// Tambah baris produk baru
function addProduk() {
    let c = document.getElementById('produk-container');
    let item = document.querySelector('.produk-item');
    let clone = item.cloneNode(true);
    clone.querySelectorAll("input").forEach(i => i.value = "");
    clone.querySelector("select").selectedIndex = 0;
    c.appendChild(clone);
}
</script>

</body>
</html>

<style>* {
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
    max-width: 600px;
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
    font-size: 2.5rem;
    color: #34495e;
    letter-spacing: 1px;
}
label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #2980b9;
}
select, input[type="number"], input[type="date"] {
    width: 100%;
    padding: 12px 15px;
    margin-bottom: 20px;
    border-radius: 10px;
    border: 1.8px solid #2980b9;
    font-size: 1rem;
    transition: border-color 0.3s ease;
}
select:focus, input[type="number"]:focus, input[type="date"]:focus {
    outline: none;
    border-color: #1f6391;
}
button {
    background-color: #2980b9;
    color: white;
    font-weight: 700;
    font-size: 1.1rem;
    padding: 14px 30px;
    border: none;
    border-radius: 12px;
    cursor: pointer;
    box-shadow: 0 8px 20px rgba(41, 128, 185, 0.5);
    transition: background-color 0.3s ease, transform 0.2s ease;
    display: block;
    width: 100%;
    margin-bottom: 15px;
}
button:hover {
    background-color: #1f6391;
    transform: scale(1.05);
}
.notif {
    background-color: #2ecc71;
    color: white;
    padding: 16px 24px;
    border-radius: 12px;
    font-weight: 600;
    margin-bottom: 25px;
    box-shadow: 0 6px 16px rgba(46,204,113,0.6);
    text-align: center;
    animation: fadeInUp 0.7s ease forwards;
}
.error {
    background-color: #e74c3c;
    color: white;
    padding: 16px 24px;
    border-radius: 12px;
    font-weight: 600;
    margin-bottom: 25px;
    box-shadow: 0 6px 16px rgba(231,76,60,0.6);
    text-align: center;
    animation: fadeInUp 0.7s ease forwards;
}
@keyframes fadeInUp {
    from {opacity: 0; transform: translateY(20px);}
    to {opacity: 1; transform: translateY(0);}
}
a.back-link {
    display: inline-block;
    margin-top: 20px;
    color: #2980b9;
    font-weight: 600;
    text-decoration: none;
    transition: color 0.3s ease;
}
a.back-link:hover {
    color: #1f6391;
    text-decoration: underline;
}
@media (max-width: 600px) {
    body {
        padding: 25px 10px;
        display: block;
    }
    .container {
        padding: 25px 20px;
    }
    h2 {
        font-size: 2rem;
        margin-bottom: 25px;
    }
}
</style>