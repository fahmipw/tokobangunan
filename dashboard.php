<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "toko_bangunan");
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Ambil data summary surat jalan (status)
$sql = "SELECT 
            SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) AS pending,
            SUM(CASE WHEN status = 'Dalam Perjalanan' THEN 1 ELSE 0 END) AS on_delivery,
            SUM(CASE WHEN status = 'Terkirim' THEN 1 ELSE 0 END) AS delivered,
            SUM(CASE WHEN status = 'Ditolak' THEN 1 ELSE 0 END) AS rejected
        FROM surat_jalan";
$result = $conn->query($sql);
$summary = $result->fetch_assoc();

$tanggal_hari_ini = date('Y-m-d');
$sql_today = "SELECT COUNT(*) AS total_hari_ini FROM surat_jalan WHERE tanggal = '$tanggal_hari_ini'";
$result_today = $conn->query($sql_today);
$today = $result_today->fetch_assoc();

$sql_total_sj = "SELECT COUNT(*) AS total_surat_jalan FROM surat_jalan";
$result_total_sj = $conn->query($sql_total_sj);
$total_sj = $result_total_sj->fetch_assoc();

// Data pengiriman 7 hari terakhir
$dates = [];
$counts = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $dates[] = $date;
    $sql_day = "SELECT COUNT(*) AS count FROM surat_jalan WHERE tanggal = '$date'";
    $result_day = $conn->query($sql_day);
    $data_day = $result_day->fetch_assoc();
    $counts[] = (int)$data_day['count'];
}

// --- BAGIAN BARANG MASUK DAN KELUAR ---
// Total barang masuk (jenis mengandung kata 'masuk')
$sql_total_masuk = "SELECT COALESCE(SUM(stok_baru - stok_lama), 0) AS total_masuk 
                    FROM riwayat_stok 
                    WHERE LOWER(jenis) LIKE '%masuk%'";
$result_total_masuk = $conn->query($sql_total_masuk);
$total_masuk = $result_total_masuk->fetch_assoc();

// Total barang keluar (jenis mengandung kata 'keluar' atau 'pengiriman')
$sql_total_keluar = "SELECT COALESCE(SUM(stok_lama - stok_baru), 0) AS total_keluar 
                     FROM riwayat_stok 
                     WHERE LOWER(jenis) LIKE '%keluar%' OR LOWER(jenis) LIKE '%pengiriman%'";
$result_total_keluar = $conn->query($sql_total_keluar);
$total_keluar = $result_total_keluar->fetch_assoc();

// Total stok produk saat ini (tabel produk)
$sql_total_stok = "SELECT COALESCE(SUM(stok), 0) AS total_stok FROM produk";
$result_total_stok = $conn->query($sql_total_stok);
$total_stok = $result_total_stok->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Dashboard Admin</title>

<!-- Font Awesome CDN -->
<link
  rel="stylesheet"
  href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
/>

<style>
body {
  margin: 0;
  font-family: Arial, sans-serif;
  background: #f9f9f9;
  display: flex;
  height: 100vh;
}
.sidebar {
  width: 260px;
  background-color: #2c3e50;
  color: #ecf0f1;
  display: flex;
  flex-direction: column;
  padding: 25px 15px;
  box-shadow: 2px 0 6px rgba(0,0,0,0.15);
}

/* Sidebar styles - sesuaikan dengan file sidebar.php kamu */
.sidebar a {
  color: #ecf0f1;
  text-decoration: none;
  padding: 12px 15px;
  margin-bottom: 10px;
  border-radius: 4px;
  font-weight: 600;
}
.sidebar a:hover, .sidebar a.active {
  background-color: #34495e;
  cursor: pointer;
}

main {
  flex-grow: 1;
  padding: 35px 40px;
  overflow-y: auto;
  background: white;
  box-shadow: 0 0 12px rgba(0,0,0,0.1);
}
.card-container {
  display: flex;
  gap: 18px;
  margin-top: 20px;
  flex-wrap: wrap;
}
.card {
  position: relative;
  border: 2px solid transparent;
  transition: border-color 0.3s ease, transform 0.3s ease, box-shadow 0.3s ease;
  background-color: #34495e;
  border-radius: 8px;
  padding: 12px 18px;
  color: #ecf0f1;
  font-weight: 700;
  font-size: 1.3rem;
  flex-grow: 1;
  min-width: 180px;
  text-align: center;
  box-shadow: 0 3px 8px rgba(0,0,0,0.15);
}
.card:hover {
  border-color: #fff;
  box-shadow: 0 8px 20px rgba(255, 255, 255, 0.6);
  transform: scale(1.05);
  cursor: default;
}
.pending { background-color: #e67e22; }
.on_delivery { background-color: #3498db; }
.delivered { background-color: #2ecc71; }
.rejected { background-color: #e74c3c; }
.stat-today { background-color: #1abc9c; }
.stat-total { background-color: #9b59b6; }
.stat-masuk { background-color: #27ae60; }  /* hijau */
.stat-keluar { background-color: #c0392b; } /* merah */
.stat-stok { background-color: #8e44ad; } /* ungu */
hr {
  margin: 40px 0;
  border: none;
  border-top: 1px solid #ddd;
}
h2 {
  margin-top: 0;
  font-weight: 700;
  font-size: 1.5rem;
  color: #2c3e50;
}
.count {
  margin-top: 12px;
  font-size: 2.8rem;
  font-weight: 900;
  user-select: none;
}
.chart-container {
  margin-top: 40px;
  max-width: 700px;
  width: 100%;
}
.card h3 {
  display: flex;
  align-items: center;
  gap: 10px;
  font-weight: 700;
  font-size: 1.3rem;
  user-select: none;
}
.card h3 i {
  color: rgba(255, 255, 255, 0.85);
  font-size: 1.4rem;
}
.card.rejected {
  max-width: 200px;
}
.card.rejected h3 {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
</style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<main>
  <h1>Selamat datang di Dashboard Admin</h1>
  <p>Pilih menu di sebelah kiri untuk mulai mengelola data toko bangunan.</p>

  <div class="card-container">
    <div class="card pending">
      <h3><i class="fas fa-hourglass-start"></i> Pending</h3>
      <div class="count"><?= $summary['pending'] ?? 0 ?></div>
    </div>
    <div class="card on_delivery">
      <h3><i class="fas fa-truck"></i> Dalam Perjalanan</h3>
      <div class="count"><?= $summary['on_delivery'] ?? 0 ?></div>
    </div>
    <div class="card delivered">
      <h3><i class="fas fa-check-circle"></i> Terkirim</h3>
      <div class="count"><?= $summary['delivered'] ?? 0 ?></div>
    </div>
    <div class="card rejected">
      <h3><i class="fas fa-times-circle"></i> Ditolak</h3>
      <div class="count"><?= $summary['rejected'] ?? 0 ?></div>
    </div>
  </div>

  <hr>

  <h2>Statistik Tambahan</h2>
  <div class="card-container">
    <div class="card stat-today">
      <h3><i class="fas fa-calendar-day"></i> Pengiriman Hari Ini</h3>
      <div class="count"><?= $today['total_hari_ini'] ?? 0 ?></div>
    </div>
    <div class="card stat-total">
      <h3><i class="fas fa-file-invoice"></i> Total Surat Jalan</h3>
      <div class="count"><?= $total_sj['total_surat_jalan'] ?? 0 ?></div>
    </div>
  </div>

  <div class="card-container">
    <div class="card stat-masuk">
      <h3><i class="fas fa-arrow-down"></i> Total Barang Masuk</h3>
      <div class="count"><?= number_format($total_masuk['total_masuk']) ?></div>
    </div>
    <div class="card stat-keluar">
      <h3><i class="fas fa-arrow-up"></i> Total Barang Keluar</h3>
      <div class="count"><?= number_format($total_keluar['total_keluar']) ?></div>
    </div>
    <div class="card stat-stok">
      <h3><i class="fas fa-boxes"></i> Total Stok Barang</h3>
      <div class="count"><?= number_format($total_stok['total_stok']) ?></div>
    </div>
  </div>

  <div class="chart-container">
    <h2>Grafik Status Surat Jalan</h2>
    <canvas id="statusChart"></canvas>
  </div>

  <div class="chart-container">
    <h2>Pengiriman 7 Hari Terakhir</h2>
    <canvas id="weeklyChart"></canvas>
  </div>
</main>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const statusData = {
    labels: ['Pending', 'Dalam Perjalanan', 'Terkirim', 'Ditolak'],
    datasets: [{
      label: 'Jumlah Surat Jalan',
      data: [
        <?= (int)($summary['pending'] ?? 0) ?>,
        <?= (int)($summary['on_delivery'] ?? 0) ?>,
        <?= (int)($summary['delivered'] ?? 0) ?>,
        <?= (int)($summary['rejected'] ?? 0) ?>
      ],
      backgroundColor: [
        '#e67e22',
        '#3498db',
        '#2ecc71',
        '#e74c3c'
      ],
      borderRadius: 5,
      borderWidth: 1
    }]
  };

  const statusConfig = {
    type: 'bar',
    data: statusData,
    options: {
      responsive: true,
      plugins: {
        legend: { display: false },
        tooltip: { enabled: true }
      },
      scales: {
        y: { beginAtZero: true, precision: 0 }
      }
    }
  };

  const weeklyData = {
    labels: <?= json_encode(array_map(function($d){ return date('d M', strtotime($d)); }, $dates)); ?>,
    datasets: [{
      label: 'Pengiriman per Hari',
      data: <?= json_encode($counts); ?>,
      fill: true,
      backgroundColor: 'rgba(52, 152, 219, 0.2)',
      borderColor: '#2980b9',
      tension: 0.3,
      borderWidth: 3,
      pointRadius: 6,
      pointHoverRadius: 8,
      pointBackgroundColor: '#3498db'
    }]
  };

  const weeklyConfig = {
    type: 'line',
    data: weeklyData,
    options: {
      responsive: true,
      plugins: {
        legend: { display: true }
      },
      scales: {
        y: { beginAtZero: true, precision: 0 }
      }
    }
  };

  const statusChart = new Chart(
    document.getElementById('statusChart'),
    statusConfig
  );

  const weeklyChart = new Chart(
    document.getElementById('weeklyChart'),
    weeklyConfig
  );
</script>

</body>
</html>
