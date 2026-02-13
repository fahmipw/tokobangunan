<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "toko_bangunan");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Fungsi hitung jarak Haversine (km)
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

// Fungsi Nearest Neighbor
function nearest_neighbor_route($start_lat, $start_lon, $toko_tujuan) {
    $route = [];
    $current_lat = $start_lat;
    $current_lon = $start_lon;
    $remaining = $toko_tujuan;

    while (count($remaining) > 0) {
        $nearest_index = null;
        $nearest_distance = PHP_INT_MAX;

        foreach ($remaining as $index => $toko) {
            $dist = haversine_distance($current_lat, $current_lon, $toko['lat'], $toko['lon']);
            if ($dist < $nearest_distance) {
                $nearest_distance = $dist;
                $nearest_index = $index;
            }
        }

        $nearest_toko = $remaining[$nearest_index];
        $route[] = $nearest_toko;

        $current_lat = $nearest_toko['lat'];
        $current_lon = $nearest_toko['lon'];

        array_splice($remaining, $nearest_index, 1);
    }

    return $route;
}

// Titik awal TB Sinar Terang BSD (latitude & longitude)
$start_lat = -6.320491312284424;
$start_lon = 106.68257462603398;

// Ambil data toko dari database
$toko_result = $conn->query("SELECT id, nama, lat, lon FROM toko WHERE lat IS NOT NULL AND lon IS NOT NULL");
$toko_tujuan = [];

if ($toko_result->num_rows > 0) {
    while ($row = $toko_result->fetch_assoc()) {
        // Skip toko yang titiknya sama dengan titik awal
        if (abs($row['lat'] - $start_lat) < 0.00001 && abs($row['lon'] - $start_lon) < 0.00001) {
            continue;
        }
        $toko_tujuan[] = [
            'id' => $row['id'],
            'nama' => $row['nama'],
            'lat' => (float)$row['lat'],
            'lon' => (float)$row['lon']
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
<html>
<head>
    <title>Rute Pengiriman Otomatis</title>
</head>
<body>
    <h2>Rute Pengiriman (Heuristik Nearest Neighbor)</h2>
    <p>Start dari: <strong>TB Sinar Terang BSD</strong> (<?= $start_lat ?>, <?= $start_lon ?>)</p>
    <ol>
        <?php foreach ($route as $toko): ?>
            <li><?= htmlspecialchars($toko['nama']) ?> (Lat: <?= $toko['lat'] ?>, Lon: <?= $toko['lon'] ?>)</li>
        <?php endforeach; ?>
    </ol>

    <br>
    <a href="dashboard.php">Kembali ke Dashboard</a>
</body>
</html>
