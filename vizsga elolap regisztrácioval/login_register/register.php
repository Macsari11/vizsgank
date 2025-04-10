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

// Email validation function
function isValidEmail($email) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    $pattern = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9-]+\.[a-zA-Z]{2,63}$/';
    return preg_match($pattern, $email) === 1;
}

// Complete registration (after quiz)
if (isset($_POST['complete_registration'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    if (!isValidEmail($email)) {
        echo "<script>alert('Invalid email format!'); showForm('register-form');</script>";
    } else {
        $stmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            echo "<script>alert('This email is already registered!'); showForm('login-form');</script>";
            $stmt->close();
        } else {
            $stmt->close();
            $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $email, $password);

            if ($stmt->execute()) {
                echo "<script>alert('Registration successful! Please login!'); showForm('login-form');</script>";
                session_unset();
            } else {
                echo "<script>alert('Registration error: " . $conn->error . "'); showForm('register-form');</script>";
            }
            $stmt->close();
            header("Location: index2.php");
            exit();
        }
    }
}

// Start registration (redirect to quiz)
if (isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (!isValidEmail($email)) {
        echo "<script>alert('Invalid email format!'); showForm('register-form');</script>";
    } else {
        $stmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            echo "<script>alert('This email is already registered!'); showForm('login-form');</script>";
            $stmt->close();
        } else {
            $stmt->close();
            $_SESSION['temp_registration'] = [
                'username' => $username,
                'email' => $email,
                'password' => $password
            ];
            header("Location: ../kviz/index.php");
            exit();
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Regisztráció</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="form-box" id="register-form">
            <h2>Regisztráció</h2>
            <form method="POST">
                <input type="text" name="username" placeholder="Felhasználónév" required>
                <input type="email" name="email" placeholder="E-mail" required>
                <input type="password" name="password" placeholder="Jelszó" required>
                <button type="submit" name="register">Regisztráció</button>
            </form>
            <p>Van már fiókod? <a href="login.php">Jelentkezz be itt!</a></p>
        </div>
    </div>

    <script src="script.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            showForm('register-form');
        });
    </script>
</body>
</html>