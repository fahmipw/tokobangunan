<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <title>Panel Admin - Toko Bangunan</title>
  <style>
    body {
      margin: 0;
      display: flex;
      font-family: Arial, sans-serif;
      height: 100vh;
      background: #f4f6f8;
    }
    #sidebar-container {
      width: 260px;
      background-color: #2c3e50;
      color: #ecf0f1;
      overflow-y: auto;
      box-shadow: 2px 0 6px rgba(0,0,0,0.15);
    }
    #main-content {
      flex: 1;
      padding: 20px;
      overflow-y: auto;
    }
  </style>
</head>
<body>
  <div id="sidebar-container">
    <?php include 'sidebar.php'; ?>
  </div>
  <main id="main-content">
    <?php include 'dashboard.php'; ?>
  </main>
</body>
</html>
