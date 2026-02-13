<?php
session_start();
$conn = new mysqli("localhost", "root", "", "toko_bangunan");

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM sopir WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password'])) {

            $_SESSION['sopir_id'] = $row['id'];
            $_SESSION['sopir_nama'] = $row['nama'];

            header("Location: sopir_dashboard.php");
            exit();
        }
    }

    $error = "Username atau password salah.";
}
?>
