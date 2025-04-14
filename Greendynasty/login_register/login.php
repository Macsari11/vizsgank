<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Hibák tárolására szolgáló tömb
$errors = [];

// Database connection
try {
    $conn = new mysqli("localhost", "root", "", "user_db");
    if ($conn->connect_error) {
        throw new Exception("Adatbázis kapcsolódási hiba: " . $conn->connect_error);
    }
} catch (Exception $e) {
    $errors[] = $e->getMessage();
}

// Login handling
if (empty($errors) && isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Input validáció
    if (empty($username)) {
        $errors[] = "A felhasználónév mező nem lehet üres!";
    }
    if (empty($password)) {
        $errors[] = "A jelszó mező nem lehet üres!";
    }

    if (empty($errors)) {
        // Felhasználó lekérdezése
        $stmt = $conn->prepare("SELECT password, role, is_banned FROM users WHERE username = ?");
        if (!$stmt) {
            $errors[] = "Hiba az adatbázis lekérdezés előkészítése során.";
        } else {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows === 0) {
                $errors[] = "A megadott felhasználónév nem létezik!";
            } else {
                $stmt->bind_result($hashed_password, $role, $is_banned);
                $stmt->fetch();

                if ($is_banned == 1) {
                    $errors[] = "Ez a felhasználó tiltva van!";
                } elseif (!password_verify($password, $hashed_password)) {
                    $errors[] = "Hibás jelszó!";
                } else {
                    // Sikeres bejelentkezés
                    $_SESSION['logged_in'] = true;
                    $_SESSION['username'] = $username;
                    $_SESSION['role'] = $role;

                    // Átirányítás szerepkör alapján
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
    }
}

// Adatbázis kapcsolat bezárása
if (isset($conn) && $conn) {
    $conn->close();
}
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
            
            <?php if (!empty($errors)): ?>
                <div class="error-message">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
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