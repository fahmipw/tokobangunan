<?php
session_start();
$conn = new mysqli("localhost","root","","toko_bangunan");

if(!isset($_SESSION['sopir_id'])){
    header("Location: sopir_login.php");
    exit();
}

$sopir_id = $_SESSION['sopir_id'];

if ($_SERVER['REQUEST_METHOD'] == "POST") {

    $surat_jalan_id = $_POST['surat_jalan_id'];
    $status = $_POST['status'];

    // Update surat jalan
    $stmt = $conn->prepare("UPDATE surat_jalan SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $surat_jalan_id);
    $stmt->execute();

    // Tambahkan log timestamp
    $stmt = $conn->prepare("
        INSERT INTO pengiriman_status_log (surat_jalan_id, sopir_id, status)
        VALUES (?,?,?)
    ");
    $stmt->bind_param("iis",$surat_jalan_id,$sopir_id,$status);
    $stmt->execute();

    header("Location: sopir_dashboard.php?msg=Status berhasil diperbarui");
    exit();
}
?>
