<?php
session_start();

$conn = new mysqli("localhost", "root", "", "toko_bangunan");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

if (isset($_POST['username']) && isset($_POST['password'])) {

    $user = $_POST['username'];
    $pass = $_POST['password'];

    // === LOGIN ADMIN ===
    if ($user === 'admin' && $pass === 'admin') {
        $_SESSION['admin_id'] = 1;
        header("Location: dashboard.php");
        exit();
    }

    // === LOGIN SOPIR ===
    $stmt = $conn->prepare("SELECT id, nama, password FROM sopir WHERE username = ?");
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // cek password plain text
        if ($pass === $row['password']) {
            $_SESSION['sopir_id'] = $row['id'];
            $_SESSION['sopir_nama'] = $row['nama'];
            header("Location: sopir_dashboard.php");
            exit();
        }
    }

    // Jika keduanya gagal
    $error = "Username atau password salah.";
}
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Login Admin</title>
    <style>
        * {
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        html, body {
            margin: 0;
            padding: 0;
            height: 100%;
            background: linear-gradient(rgba(255,255,255,0.6), rgba(255,255,255,0.6)), 
                        url('bglogin.png') no-repeat center center fixed;
            background-size: 100% 100%;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(6px);
            padding: 32px 28px;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.12);
            max-width: 340px;
            width: 100%;
            text-align: center;
            animation: fadeIn 0.6s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        h2 {
            margin-bottom: 12px;
            color: #2c3e50;
            font-weight: 700;
            font-size: 1.5rem;
        }

        p.subtitle {
            font-size: 0.95rem;
            color: #666;
            margin-bottom: 24px;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        input[type="text"],
        input[type="password"] {
            padding: 12px 14px;
            border: 1.8px solid #ccc;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        input:focus {
            border-color: #2980b9;
            outline: none;
            box-shadow: 0 0 5px rgba(41, 128, 185, 0.2);
        }

        button {
            background-color: #2980b9;
            border: none;
            color: white;
            font-weight: 600;
            padding: 12px;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        button:hover {
            background-color: #1f5a8a;
            transform: scale(1.03);
        }

        .error {
            color: #e74c3c;
            background: #fdecea;
            border: 1px solid #e0b4b4;
            border-radius: 6px;
            padding: 10px;
            margin-bottom: 12px;
            font-size: 0.95rem;
        }

        @media (max-width: 400px) {
            .login-container {
                padding: 24px 20px;
            }
            h2 {
                font-size: 1.3rem;
            }
            p.subtitle {
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>

    <div class="login-container">
        <h2>Toko Bangunan Sinar Terang BSD</h2>
        <p class="subtitle">Silakan login terlebih dahulu</p>

        <?php if (isset($error)): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post" action="">
            <input type="text" name="username" placeholder="Username" required autofocus>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Masuk</button>
        </form>
    </div>

</body>
</html>
