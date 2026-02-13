<?php
session_start();

// buat koneksi database dulu
$conn = new mysqli("localhost", "root", "", "toko_bangunan");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// cek login sopir/admin
if (isset($_SESSION['sopir_id'])) {
    $user_type = 'sopir';
    $sopir_id = $_SESSION['sopir_id'];
    $sopir_nama = $_SESSION['sopir_nama'] ?? 'Sopir';
} elseif (isset($_SESSION['admin_id'])) {
    $user_type = 'admin';
    if (!isset($_GET['sopir_id'])) {
        die("Sopir belum dipilih. <a href='pilih_sopir.php'>Pilih sopir</a>");
    }
    $sopir_id = intval($_GET['sopir_id']);

    // ambil nama sopir
    $stmt2 = $conn->prepare("SELECT nama FROM sopir WHERE id = ?");
    $stmt2->bind_param("i", $sopir_id);
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    $row2 = $result2->fetch_assoc();
    $sopir_nama = $row2['nama'] ?? 'Nama sopir tidak ditemukan';
    $stmt2->close();
} else {
    header("Location: login.php");
    exit();
}

// ... lanjutkan dengan query rute


if (isset($_SESSION['sopir_id'])) {
    $user_type = 'sopir';
    $sopir_id = $_SESSION['sopir_id'];
    $sopir_nama = $_SESSION['sopir_nama'] ?? 'Sopir';
} elseif (isset($_SESSION['admin_id'])) {
    $user_type = 'admin';
    if (!isset($_GET['sopir_id'])) {
        die("Sopir belum dipilih. <a href='pilih_sopir.php'>Pilih sopir</a>");
    }
    $sopir_id = intval($_GET['sopir_id']);

    // ambil nama sopir
    $stmt2 = $conn->prepare("SELECT nama FROM sopir WHERE id = ?");
    $stmt2->bind_param("i", $sopir_id);
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    $row2 = $result2->fetch_assoc();
    $sopir_nama = $row2['nama'] ?? 'Nama sopir tidak ditemukan';
    $stmt2->close();
}


// Ambil filter dari URL (jika ada)
$filter_date = $_GET['tanggal'] ?? '';
$filter_status = $_GET['status'] ?? '';

// Fungsi hitung jarak (haversine)
function hitungJarak($lat1, $lon1, $lat2, $lon2) {
    $earth_radius = 6371; // km
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat/2) * sin($dLat/2) +
          cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
          sin($dLon/2) * sin($dLon/2);
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    return $earth_radius * $c;
}

// Ambil semua toko tujuan pengiriman sopir
$sql = "SELECT t.id, t.nama, t.latitude, t.longitude, GROUP_CONCAT(DISTINCT sj.status ORDER BY sj.tanggal SEPARATOR ', ') AS status
        FROM surat_jalan sj
        JOIN pengiriman p ON sj.id = p.surat_jalan_id
        JOIN toko t ON p.toko_id = t.id
        WHERE sj.sopir_id = ?";

$params = [$sopir_id];
$types = "i";

// Filter tanggal
if (!empty($filter_date)) {
    $sql .= " AND sj.tanggal = ?";
    $params[] = $filter_date;
    $types .= "s";
}

// Filter status
if (!empty($filter_status)) {
    $sql .= " AND sj.status = ?";
    $params[] = $filter_status;
    $types .= "s";
}

// Tambahkan GROUP BY setelah semua filter
$sql .= " GROUP BY t.id, t.nama, t.latitude, t.longitude";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();


$tokoList = [];
while ($row = $result->fetch_assoc()) {
    $tokoList[] = $row;
}

$notification_message = '';
if (count($tokoList) == 0) {
    $notification_message = 'Tidak ada pengiriman untuk sopir ini dengan filter yang dipilih.';
}

// Titik awal (TB sinar terang)
$start = [
    'nama' => 'TB Sinar Terang BSD Pusat',
    'latitude' => -6.32107566722301,
    'longitude' => 106.68213134994039,
    'status' => 'Mulai'
];

$rute = [];
$currentLat = $start['latitude'];
$currentLon = $start['longitude'];

if (!empty($tokoList)) {
    // Implementasi algoritma Greedy Nearest Neighbor
    while (count($tokoList) > 0) {
        $nearestIndex = 0;
        $nearestDistance = hitungJarak($currentLat, $currentLon, $tokoList[0]['latitude'], $tokoList[0]['longitude']);
        for ($i = 1; $i < count($tokoList); $i++) {
            $dist = hitungJarak($currentLat, $currentLon, $tokoList[$i]['latitude'], $tokoList[$i]['longitude']);
            if ($dist < $nearestDistance) {
                $nearestDistance = $dist;
                $nearestIndex = $i;
            }
        }

        $nearestToko = $tokoList[$nearestIndex];
        $nearestToko['jarak'] = $nearestDistance;
        array_splice($tokoList, $nearestIndex, 1);
        $rute[] = $nearestToko;
        $currentLat = $nearestToko['latitude'];
        $currentLon = $nearestToko['longitude'];
    }
}

// Tambah TB sinar terang ke awal rute
array_unshift($rute, $start);

// Siapkan data JS dengan status
$js_rute = [];
foreach ($rute as $loc) {
    $js_rute[] = [
        'nama' => $loc['nama'],
        'lat' => (float)$loc['latitude'],
        'lon' => (float)$loc['longitude'],
        'status' => $loc['status']
    ];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Rute Pengiriman Sopir</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; max-width: 900px; margin: auto; }
        #notification-box {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background-color: #f44336;
            color: white;
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.5s, visibility 0.5s;
            text-align: center;
        }
        #notification-box.show {
            opacity: 1;
            visibility: visible;
        }
        #map { height: 500px; margin-bottom: 20px; }
        h2 { margin-bottom: 10px; }
        ol li { margin-bottom: 10px; }
        .total-jarak { margin-top: 20px; font-weight: bold; }
        a { margin-right: 10px; }
        .filter-form { display: flex; gap: 10px; margin-bottom: 20px; align-items: flex-end; }
        .filter-form label { font-weight: bold; }
        .filter-form select, .filter-form input[type="date"] { padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
        .filter-form button { padding: 8px 12px; background-color: #2980b9; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .filter-form button:hover { background-color: #1f6391; }
        .status-terkirim { color: #2ecc71; font-weight: bold; }
        .status-dalam-perjalanan { color: #3498db; font-weight: bold; }
        .status-ditolak { color: #FF0000; font-weight: bold; }
        .status-pending { color: #2F4F4F; font-weight: bold; }
        .dashboard-btn-container {
    margin-top: 20px;
    text-align: center;
}

.dashboard-btn {
    display: inline-block;
    padding: 10px 20px;
    background-color: #2980b9;
    color: white;
    text-decoration: none;
    font-weight: bold;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.2);
    transition: all 0.3s ease;
}

.dashboard-btn:hover {
    background-color: #1f6391;
    transform: translateY(-2px);
    box-shadow: 0 6px 10px rgba(0,0,0,0.25);
}

    </style>
</head>
<body>

<div id="notification-box"></div>

<h2>Rute Pengiriman - Sopir: <?= htmlspecialchars($sopir_nama) ?> (ID: <?= htmlspecialchars($sopir_id) ?>)</h2>

<form method="get" action="" class="filter-form">
    <input type="hidden" name="sopir_id" value="<?= htmlspecialchars($sopir_id) ?>">
    <div>
        <label for="tanggal">Tanggal:</label>
        <input type="date" id="tanggal" name="tanggal" value="<?= htmlspecialchars($filter_date) ?>">
    </div>
    <div>
        <label for="status">Status:</label>
        <select id="status" name="status">
            <option value="">Semua Status</option>
            <option value="Pending" <?= $filter_status == 'Pending' ? 'selected' : '' ?>>Pending</option>
            <option value="Dalam Perjalanan" <?= $filter_status == 'Dalam Perjalanan' ? 'selected' : '' ?>>Dalam Perjalanan</option>
            <option value="Terkirim" <?= $filter_status == 'Terkirim' ? 'selected' : '' ?>>Terkirim</option>
            <option value="Ditolak" <?= $filter_status == 'Ditolak' ? 'selected' : '' ?>>Ditolak</option>
        </select>
    </div>
    <button type="submit">Filter</button>
</form>

<div id="map"></div>

<h3>Daftar Rute:</h3>
<ol id="rute-list">
    </ol>

<p class="total-jarak">Total Jarak Rute: 0 km</p>


<div class="dashboard-btn-container">
<?php if ($user_type == 'sopir'): ?>
    <a href="sopir_dashboard.php" class="dashboard-btn">← Kembali ke Dashboard</a>
<?php else: ?>
    <a href="dashboard.php" class="dashboard-btn">← Kembali ke Dashboard</a>
<?php endif; ?>
</div>



<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
    const route = <?= json_encode($js_rute) ?>;
    const notificationMessage = '<?= htmlspecialchars($notification_message) ?>';
    
    // Tampilkan notifikasi jika ada pesan
    if (notificationMessage) {
        const notifBox = document.getElementById('notification-box');
        notifBox.textContent = notificationMessage;
        notifBox.classList.add('show');
        setTimeout(() => {
            notifBox.classList.remove('show');
        }, 5000); // Pesan menghilang setelah 5 detik
    }

    const map = L.map('map').setView([route[0].lat, route[0].lon], 12);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    const startIcon = L.icon({
        iconUrl: 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzIiIGhlaWdodD0iMzciIHZpZXdCb3g9IjAgMCAzMiAzNyIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48Y2lyY2xlIGN4PSIxNiIgY3k9IjE2IiByPSIxMiIgc3Ryb2tlPSIjMjdhNDIwIiBzdHJva2Utd2lkdGg9IjMiIGZpbGw9IiMwMGZhMDAiLz48L3N2Zz4=',
        iconSize: [32, 37],
        iconAnchor: [16, 37],
        popupAnchor: [0, -28]
    });

    route.forEach((point, idx) => {
        let markerOptions = {};
        if (idx === 0) {
            markerOptions.icon = startIcon;
        }
        L.marker([point.lat, point.lon], markerOptions)
         .addTo(map)
         .bindPopup(`<b>${point.nama}</b><br>(${point.lat.toFixed(5)}, ${point.lon.toFixed(5)})<br>Status: ${point.status}`);
    });

    async function getRouteDetails(coords) {
        const coordString = coords.map(c => c.join(',')).join(';');
        const url = `https://router.project-osrm.org/route/v1/driving/${coordString}?overview=full&geometries=geojson`;
        const res = await fetch(url);
        if (!res.ok) throw new Error('Gagal fetch rute dari OSRM');
        const data = await res.json();
        if (data.code !== "Ok") {
            throw new Error("Routing gagal: " + data.message);
        }
        return data.routes[0];
    }

    (async () => {
        try {
            const allCoords = route.map(p => [p.lon, p.lat]);
            if (allCoords.length <= 1) {
                document.getElementById('rute-list').innerHTML = `<li>🟢 <strong>${route[0].nama}</strong> (Lat: ${route[0].lat.toFixed(5)}, Lon: ${route[0].lon.toFixed(5)})<br><em>Titik awal pengiriman</em></li>`;
                document.querySelector('.total-jarak').textContent = `Total Jarak Rute: 0 km`;
                map.setView([route[0].lat, route[0].lon], 15);
                return;
            }

            for (let i = 0; i < route.length - 1; i++) {
                const startPoint = route[i];
                const endPoint = route[i + 1];
                const coords = [
                    [startPoint.lon, startPoint.lat],
                    [endPoint.lon, endPoint.lat]
                ];
                const routeData = await getRouteDetails(coords);
                const latlngs = routeData.geometry.coordinates.map(c => [c[1], c[0]]);
                let color;
                switch(endPoint.status) {
                    case 'Terkirim': color = '#2ecc71'; break;
                    case 'Dalam Perjalanan': color = '#3498db'; break;
                    case 'Ditolak': color = '#FF0000'; break;
                    case 'Pending': color = '#2F4F4F'; break;
                    default: color = '#000000';
                }
                L.polyline(latlngs, {color: color, weight: 5, opacity: 0.7}).addTo(map);
            }
            
            const ol = document.getElementById('rute-list');
            ol.innerHTML = '';
            let totalKm = 0;
            const routeDataFull = await getRouteDetails(allCoords);
            
            ol.innerHTML += `<li>🟢 <strong>${route[0].nama}</strong> (Lat: ${route[0].lat.toFixed(5)}, Lon: ${route[0].lon.toFixed(5)})<br><em>Titik awal pengiriman</em></li>`;
            
            const legs = routeDataFull.legs;
            for (let i = 0; i < legs.length; i++) {
                const loc = route[i + 1];
                const jarakKm = (legs[i].distance / 1000).toFixed(2);
                totalKm += parseFloat(jarakKm);
                
                let statusClass;
                switch(loc.status) {
                    case 'Terkirim': statusClass = 'status-terkirim'; break;
                    case 'Dalam Perjalanan': statusClass = 'status-dalam-perjalanan'; break;
                    case 'Ditolak': statusClass = 'status-ditolak'; break;
                    case 'Pending': statusClass = 'status-pending'; break;
                    default: statusClass = '';
                }
                ol.innerHTML += `<li>${loc.nama} (Lat: ${loc.lat.toFixed(5)}, Lon: ${loc.lon.toFixed(5)})<br><small>Jarak dari sebelumnya: ${jarakKm} km</small> <span class="${statusClass}">(${loc.status})</span></li>`;
            }
            
            document.querySelector('.total-jarak').textContent = `Total Jarak Rute: ${totalKm.toFixed(2)} km`;
            map.fitBounds(allCoords.map(c => [c[1], c[0]]));
        } catch (e) {
            console.error(e);
            const fallback_message = "Gagal mendapatkan rute jalan, tampilkan garis lurus saja.";
            const notifBox = document.getElementById('notification-box');
            notifBox.textContent = fallback_message;
            notifBox.classList.add('show');
            
            const latlngsFallback = route.map(p => [p.lat, p.lon]);
            if (latlngsFallback.length > 1) {
              L.polyline(latlngsFallback, {color: 'blue'}).addTo(map);
              map.fitBounds(latlngsFallback);
            }
            const ol = document.getElementById('rute-list');
            ol.innerHTML = '';
            let totalDistPHP = 0;
            for (let i = 0; i < route.length; i++) {
                if (i === 0) {
                    ol.innerHTML += `<li>🟢 <strong>${route[i].nama}</strong> (Lat: ${route[i].lat.toFixed(5)}, Lon: ${route[i].lon.toFixed(5)})<br><em>Titik awal pengiriman</em></li>`;
                } else {
                    totalDistPHP += route[i].jarak;
                    let statusClass;
                    switch(route[i].status) {
                        case 'Terkirim': statusClass = 'status-terkirim'; break;
                        case 'Dalam Perjalanan': statusClass = 'status-dalam-perjalanan'; break;
                        case 'Ditolak': statusClass = 'status-ditolak'; break;
                        case 'Pending': statusClass = 'status-pending'; break;
                        default: statusClass = '';
                    }
                    ol.innerHTML += `<li>${route[i].nama} (Lat: ${route[i].lat.toFixed(5)}, Lon: ${route[i].lon.toFixed(5)})<br><small>Jarak dari sebelumnya: ${route[i].jarak.toFixed(2)} km</small> <span class="${statusClass}">(${route[i].status})</span></li>`;
                }
            }
            document.querySelector('.total-jarak').textContent = `Total Jarak Rute: ${totalDistPHP.toFixed(2)} km`;
        }
    })();
</script>
</body>
</html>