<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "toko_bangunan");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

function haversine_distance($lat1, $lon1, $lat2, $lon2) {
    $earth_radius = 6371;

    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);

    $a = sin($dLat/2) * sin($dLat/2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLon/2) * sin($dLon/2);

    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    return $earth_radius * $c;
}

function nearest_neighbor_route($start_lat, $start_lon, $toko_tujuan) {
    $route = [];
    $current_lat = $start_lat;
    $current_lon = $start_lon;
    $remaining = $toko_tujuan;

    while (count($remaining) > 0) {
        $nearest_index = null;
        $nearest_distance = PHP_INT_MAX;

        foreach ($remaining as $index => $toko) {
            $dist = haversine_distance($current_lat, $current_lon, $toko['latitude'], $toko['longitude']);
            if ($dist < $nearest_distance) {
                $nearest_distance = $dist;
                $nearest_index = $index;
            }
        }

        $nearest_toko = $remaining[$nearest_index];
        $route[] = $nearest_toko;

        $current_lat = $nearest_toko['latitude'];
        $current_lon = $nearest_toko['longitude'];

        array_splice($remaining, $nearest_index, 1);
    }

    return $route;
}

// Titik awal TB Sinar Terang BSD (latitude & longitude)
$start_lat = -6.320491312284424;
$start_lon = 106.68257462603398;

// Ambil data toko dari database
$toko_result = $conn->query("SELECT id, nama, latitude, longitude FROM toko WHERE latitude IS NOT NULL AND longitude IS NOT NULL");
$toko_tujuan = [];

if ($toko_result->num_rows > 0) {
    while ($row = $toko_result->fetch_assoc()) {
        // Skip toko yang titiknya sama dengan titik awal
        if (abs($row['latitude'] - $start_lat) < 0.00001 && abs($row['longitude'] - $start_lon) < 0.00001) {
            continue;
        }
        $toko_tujuan[] = [
            'id' => $row['id'],
            'nama' => $row['nama'],
            'latitude' => (float)$row['latitude'],
            'longitude' => (float)$row['longitude']
        ];
    }
} else {
    echo "Data toko tidak ditemukan.";
    exit;
}

// Hitung rute menggunakan Nearest Neighbor
$route = nearest_neighbor_route($start_lat, $start_lon, $toko_tujuan);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Rute Semua Toko</title>
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f5f7fa; padding: 30px; color: #333; max-width: 700px; margin: auto; }
        h2 { color: #2c3e50; }
        ol { background: white; border-radius: 12px; box-shadow: 0 3px 8px rgba(0,0,0,0.1); padding: 20px 30px; }
        li { padding: 12px 0; border-bottom: 1px solid #eee; }
        li:last-child { border-bottom: none; }
        a { color: #3498db; font-weight: 600; text-decoration: none; }
        a:hover { text-decoration: underline; }
        .nav-links { margin-top: 20px; }
        .nav-links a { margin-right: 15px; }
    </style>
</head>
<body>

    <h2>Rute Pengiriman Semua Toko (Heuristik Nearest Neighbor)</h2>
    <p>Start dari: <strong>TB Sinar Terang BSD</strong> (<?= $start_lat ?>, <?= $start_lon ?>)</p>
    <ol>
        <?php foreach ($route as $toko): ?>
            <li><?= htmlspecialchars($toko['nama']) ?> (Lat: <?= $toko['latitude'] ?>, Lon: <?= $toko['longitude'] ?>)</li>
        <?php endforeach; ?>
    </ol>

    <div class="nav-links">
        <a href="pilih_sopir.php">← Pilih Sopir</a>
        <a href="dashboard.php">Dashboard →</a>
    </div>

</body>
</html>
