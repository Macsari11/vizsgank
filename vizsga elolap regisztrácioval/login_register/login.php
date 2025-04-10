<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection
$conn = new mysqli("localhost", "root", "", "user_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Login handling
if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT password, role, is_banned FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($hashed_password, $role, $is_banned);
    
    if (!$stmt->fetch() || !password_verify($password, $hashed_password)) {
        echo "<script>alert('Invalid username or password!'); showForm('login-form');</script>";
    } else {
        if ($is_banned == 1) {
            echo "<script>alert('This user is banned!'); showForm('login-form');</script>";
        } else {
            $_SESSION['logged_in'] = true;
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $role;

            if ($role === 'admin') {
                header("Location: ../admin/admin_dashboard.php");
            } else {
                header("Location: ../index.php");
            }
            exit();
        }
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bejelentkezés</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="form-box" id="login-form">
            <h2>Bejelentkezés</h2>
            <form method="POST">
                <input type="text" name="username" placeholder="Felhasználónév" required>
                <input type="password" name="password" placeholder="Jelszó" required>
                <button type="submit" name="login">Bejelentkezés</button>
            </form>
            <p>Nincs fiókod? <a href="register.php">Regisztrálj itt!</a></p>
        </div>
    </div>

    <script src="script.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            showForm('login-form');
        });
    </script>
</body>
</html>