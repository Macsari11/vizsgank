<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$errors = [];
$success = "";


try {
    $conn = new mysqli("localhost", "root", "", "user_db");
    if ($conn->connect_error) {
        throw new Exception("Adatbázis kapcsolódási hiba: " . $conn->connect_error);
    }
} catch (Exception $e) {
    $errors[] = $e->getMessage();
}


if (empty($errors) && isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    
    if (empty($username)) {
        $errors[] = "A felhasználónév mező nem lehet üres!";
    }

    if (empty($email)) {
        $errors[] = "Az email mező nem lehet üres!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Érvénytelen email formátum!";
    }

    if (empty($password)) {
        $errors[] = "A jelszó mező nem lehet üres!";
    }

    if ($password !== $confirm_password) {
        $errors[] = "A jelszavak nem egyeznek!";
    }

    
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        if (!$stmt) {
            $errors[] = "Hiba az adatbázis lekérdezés előkészítése során.";
        } else {
            $stmt->bind_param("ss", $username, $email);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $errors[] = "Ez a felhasználónév vagy email cím már foglalt!";
            }
            $stmt->close();
        }
    }

  
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $role = "user";
        $is_banned = 0;

        $stmt = $conn->prepare("INSERT INTO users (username, email, password, role, is_banned) VALUES (?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("ssssi", $username, $email, $hashed_password, $role, $is_banned);
            if ($stmt->execute()) {
                $success = "Sikeres regisztráció! Most már bejelentkezhetsz.";
            } else {
                $errors[] = "Hiba a regisztráció során.";
            }
            $stmt->close();
        } else {
            $errors[] = "Hiba az adatbázis lekérdezés előkészítése során.";
        }
    }
}

if (isset($conn) && $conn) {
    $conn->close();
}
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

            <?php if (!empty($errors)): ?>
                <div class="error-message">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php elseif (!empty($success)): ?>
                <div class="success-message">
                    <p><?php echo htmlspecialchars($success); ?></p>
                </div>
            <?php endif; ?>

            <form method="POST">
                <input type="text" name="username" placeholder="Felhasználónév" required>
                <input type="email" name="email" placeholder="Email cím" required>
                <input type="password" name="password" placeholder="Jelszó" required>
                <input type="password" name="confirm_password" placeholder="Jelszó megerősítése" required>
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
