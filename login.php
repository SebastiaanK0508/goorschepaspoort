<?php
session_start();
require 'db_config.php';

// Handigheidje: Maak standaard admin aan als de tabel leeg is
$check_users = $conn->query("SELECT id FROM admin_users");

$foutmelding = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];

    $result = $conn->query("SELECT * FROM admin_users WHERE username = '$username'");
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        // Controleer of het ingevulde wachtwoord klopt met de versleutelde versie
        if (password_verify($password, $user['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $user['username'];
            header("Location: beheercentrum.php"); // Stuur door naar beheer
            exit();
        } else {
            $foutmelding = "Verkeerd wachtwoord, probeer het na een biertje nog eens.";
        }
    } else {
        $foutmelding = "Gebruiker niet gevonden.";
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Inloggen Beheer - Goor Quiz</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f9; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-box { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); width: 300px; text-align: center; border-top: 5px solid #007A33; }
        input { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { background: #007A33; color: white; padding: 10px; border: none; border-radius: 4px; width: 100%; cursor: pointer; font-weight: bold; }
        button:hover { background: #005a24; }
        .error { color: red; margin-bottom: 10px; font-size: 0.9em; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>De Tente (Beheer)</h2>
        <?php if($foutmelding): ?> <div class="error"><?= $foutmelding ?></div> <?php endif; ?>
        <form method="POST">
            <input type="text" name="username" placeholder="Gebruikersnaam" required>
            <input type="password" name="password" placeholder="Wachtwoord" required>
            <button type="submit">Inloggen</button>
        </form>
    </div>
</body>
</html>