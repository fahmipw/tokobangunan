<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "toko_bangunan");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$start = [
    'id' => 0,
    'nama' => 'TB Sinar Terang BSD',
    'latitude' => -6.320875521426852,
    'longitude' => 106.68210307885205
];
// Ambil toko beserta kecamatan
$result = $conn->query("
    SELECT toko.id, toko.nama, toko.latitude, toko.longitude, kecamatan.nama AS kecamatan 
    FROM toko 
    JOIN kecamatan ON toko.kecamatan_id = kecamatan.id
");

// Simpan toko ke array
$toko_list = [];
while ($row = $result->fetch_assoc()) {
    if ($row['latitude'] !== null && $row['longitude'] !== null) {
        $toko_list[] = $row;
    }
}

function haversine($lat1, $lon1, $lat2, $lon2) {
    $R = 6371;
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat/2) * sin($dLat/2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLon/2) * sin($dLon/2);
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    return $R * $c;
}

function nearest_neighbor($start, $points) {
    $route = [];
    $current = $start;
    $unvisited = $points;

    while (count($unvisited) > 0) {
        $nearest = null;
        $nearest_distance = INF;
        foreach ($unvisited as $key => $point) {
            $dist = haversine($current['latitude'], $current['longitude'], $point['latitude'], $point['longitude']);
            if ($dist < $nearest_distance) {
                $nearest_distance = $dist;
                $nearest = $key;
            }
        }
        $route[] = $unvisited[$nearest];
        $current = $unvisited[$nearest];
        unset($unvisited[$nearest]);
    }
    return $route;
}

$rute = nearest_neighbor($start, $toko_list);

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Rute Pengiriman Heuristik</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #74ebd5 0%, #ACB6E5 100%);
            margin: 0;
            padding: 40px 20px;
            color: #2c3e50;
            display: flex;
            justify-content: center;
            min-height: 100vh;
        }
        .container {
            background: white;
            border-radius: 12px;
            padding: 30px 40px;
            max-width: 600px;
            width: 100%;
            box-shadow: 0 6px 18px rgba(44, 62, 80, 0.15);
        }
        h2 {
            text-align: center;
            font-weight: 600;
            font-size: 1.8rem;
            margin-bottom: 20px;
            color: #34495e;
        }
        .info-box {
            background-color: #eaf4fc;
            border-left: 5px solid #2980b9;
            padding: 20px 25px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
            color: #2c3e50;
            font-size: 1rem;
            line-height: 1.5;
        }
        .info-box svg {
            flex-shrink: 0;
            width: 32px;
            height: 32px;
            fill: #2980b9;
        }
        ol {
            padding-left: 20px;
            margin-bottom: 30px;
            color: #34495e;
            font-weight: 500;
            line-height: 1.5;
        }
        ol li {
            margin-bottom: 10px;
            font-size: 1.1rem;
            transition: color 0.3s ease;
        }
        ol li:hover {
            color: #2980b9;
            cursor: default;
        }
        .button-group {
    display: flex;
    gap: 15px;
    justify-content: center;
    flex-wrap: wrap;
    margin-top: 30px;
}

.btn {
    display: inline-block;
    padding: 12px 28px;
    font-weight: 600;
    border-radius: 8px;
    text-decoration: none;
    user-select: none;
    box-shadow: 0 6px 14px rgba(0,0,0,0.15);
    transition: background-color 0.3s ease, transform 0.15s ease, box-shadow 0.25s ease;
    font-family: 'Poppins', sans-serif;
    font-size: 1rem;
    min-width: 180px;
    text-align: center;
}

/* Tombol utama */
.btn-primary {
    background-color: #2980b9;
    color: white;
    border: none;
}

.btn-primary:hover,
.btn-primary:focus {
    background-color: #1c5fa0;
    box-shadow: 0 8px 20px rgba(29, 93, 150, 0.6);
    transform: scale(1.05);
    outline: none;
}

/* Tombol sekunder */
.btn-secondary {
    background-color: #bdc3c7;
    color: #2c3e50;
    border: none;
}

.btn-secondary:hover,
.btn-secondary:focus {
    background-color: #95a5a6;
    box-shadow: 0 8px 20px rgba(130, 140, 145, 0.6);
    transform: scale(1.05);
    outline: none;
}

/* Responsive */
@media (max-width: 640px) {
    .btn {
        min-width: 100%;
        padding: 14px 0;
    }
        a.back-link {
            display: inline-block;
            background-color: #2980b9;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            box-shadow: 0 6px 14px rgba(41, 128, 185, 0.5);
            transition: background-color 0.3s ease, transform 0.15s ease;
            user-select: none;
        }
        a.back-link:hover {
            background-color: #1c5fa0;
            transform: scale(1.05);
        }
        @media (max-width: 640px) {
            .container {
                padding: 20px 25px;
            }
            h2 {
                font-size: 1.5rem;
            }
            .info-box {
                font-size: 0.9rem;
            }
            ol li {
                font-size: 1rem;
            }
            a.back-link {
                width: 100%;
                text-align: center;
                padding: 14px 0;
            }
        }
    </style>
</head>
<body>
    <div class="container" role="main" aria-label="Daftar rute pengiriman">
        <h2>Rute Pengiriman<br>(Heuristik Nearest Neighbor)</h2>
        
        <div class="info-box" role="note" aria-live="polite">
            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10
                10-4.48 10-10S17.52 2 12 2zm0 15h-1v-6h2v4h-1v-4h1v6z"/>
            </svg>
            <div>
                Halaman ini menampilkan urutan rute pengiriman toko berdasarkan metode <strong>Heuristik Nearest Neighbor</strong>.
                Titik awal pengiriman adalah <em>TB Sinar Terang BSD</em>, dan algoritma akan mencari toko terdekat berikutnya secara berurutan.
                Dengan cara ini, rute pengiriman dapat dioptimalkan untuk meminimalkan jarak perjalanan dan waktu tempuh.
            </div>
        </div>

        <ol>
    <?php foreach ($rute as $toko): ?>
        <li>
            <?= htmlspecialchars($toko['nama']); ?> 
            (Kecamatan: <?= htmlspecialchars($toko['kecamatan']); ?>)
            <br>
            Lat: <?= $toko['latitude']; ?>, Lon: <?= $toko['longitude']; ?>
        </li>
    <?php endforeach; ?>
</ol>
     <div class="button-group">
    <a href="pilih_sopir.php" class="btn btn-primary" aria-label="Pilih Sopir">Pilih Sopir</a>
    <a href="dashboard.php" class="btn btn-secondary" aria-label="Kembali ke Dashboard">← Kembali ke Dashboard</a>
</div>

    </div>
</body>
</html>
