<?php
// db_config.php
$host = 'localhost';
$db   = 'goor_quiz';
$user = 'webuser'; // Jouw database gebruiker
$pass = 'binck@guus2025';     // Jouw database wachtwoord

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Verbinding mislukt: De tap is stuk.");
}
?>