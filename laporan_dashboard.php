<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "toko_bangunan");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$today = date('Y-m-d');

$sql_total = "SELECT COUNT(*) as total_pengiriman FROM surat_jalan WHERE tanggal = ?";
$stmt_total = $conn->prepare($sql_total);
$stmt_total->bind_param("s", $today);
$stmt_total->execute();
$result_total = $stmt_total->get_result();
$total_pengiriman = $result_total->fetch_assoc()['total_pengiriman'];
$stmt_total->close();

$sql_status = "SELECT status, COUNT(*) as jumlah FROM surat_jalan WHERE tanggal = ? GROUP BY status";
$stmt_status = $conn->prepare($sql_status);
$stmt_status->bind_param("s", $today);
$stmt_status->execute();
$result_status = $stmt_status->get_result();

$status_counts = [
    'Pending' => 0,
    'Dalam Perjalanan' => 0,
    'Terkirim' => 0,
    'Ditolak' => 0,
];

while ($row = $result_status->fetch_assoc()) {
    $status_counts[$row['status']] = $row['jumlah'];
}
$stmt_status->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Laporan Pengiriman Hari Ini</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet" />
<style>
  /* Reset & base */
  * {
    box-sizing: border-box;
  }
  body {
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg, #ffffff, #f0f7ff);
    margin: 0;
    padding: 20px;
    color: #333;
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: flex-start;
  }

  .container {
    max-width: 600px;
    background: white;
    padding: 30px 40px;
    border-radius: 12px;
    box-shadow: 0 6px 18px rgba(41, 128, 185, 0.2);
    text-align: center;
  }

  h3 {
    color: #2c3e50;
    font-weight: 700;
    margin-bottom: 30px;
    letter-spacing: 0.05em;
  }

  ul {
    list-style: none;
    padding: 0;
    margin: 0 auto;
    max-width: 320px;
  }

  ul li {
    font-weight: 600;
    font-size: 18px;
    padding: 15px 20px;
    margin-bottom: 12px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgb(0 0 0 / 0.07);
    transition: background-color 0.3s ease;
  }

  /* Status colors */
  .status-pending {
    background-color: #f39c12;
    color: white;
  }
  .status-dalam-perjalanan {
    background-color: #3498db;
    color: white;
  }
  .status-terkirim {
    background-color: #27ae60;
    color: white;
  }
  .status-ditolak {
    background-color: #c0392b;
    color: white;
  }

  /* Total pengiriman style */
  .total {
    background-color: #2980b9;
    color: white;
    font-size: 22px;
  }

  /* Tombol kembali ke dashboard */
  .button-link {
  display: inline-block;
  margin-top: 30px;
  padding: 12px 28px;
  background: #7f8c8d; /* Abu-abu */
  color: white;
  border-radius: 8px;
  text-decoration: none;
  font-weight: 700;
  font-size: 16px;
  box-shadow: 0 4px 8px rgba(127, 140, 141, 0.4);
  transition: background-color 0.3s ease, box-shadow 0.3s ease, transform 0.2s ease;
  user-select: none;
}
.button-link:hover {
  background: #636e72; /* Abu-abu lebih gelap saat hover */
  box-shadow: 0 6px 15px rgba(99, 110, 114, 0.6);
  transform: translateY(-2px);
}
.button-link:active {
  transform: translateY(0);
  box-shadow: 0 3px 6px rgba(99, 110, 114, 0.4);
}



  @media (max-width: 480px) {
    .container {
      padding: 25px 20px;
      max-width: 100%;
    }
    ul li {
      font-size: 16px;
      padding: 12px 15px;
    }
  }
</style>
</head>
<body>
  <div class="container">
    <h3>Laporan Pengiriman Hari Ini (<?= htmlspecialchars($today) ?>)</h3>
    <ul>
      <li class="total">Total Pengiriman: <?= $total_pengiriman ?></li>
      <li class="status-pending">Pending: <?= $status_counts['Pending'] ?></li>
      <li class="status-dalam-perjalanan">Dalam Perjalanan: <?= $status_counts['Dalam Perjalanan'] ?></li>
      <li class="status-terkirim">Terkirim: <?= $status_counts['Terkirim'] ?></li>
      <li class="status-ditolak">Ditolak: <?= $status_counts['Ditolak'] ?></li>
    </ul>
    <a href="dashboard.php" class="button-link">🏠︎ Kembali ke Dashboard</a>
  </div>
</body>
</html>
