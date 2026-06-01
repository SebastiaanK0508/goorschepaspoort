<?php
header('Content-Type: application/json');
require 'db_config.php';

// Haal instellingen op
$settings_query = $conn->query("SELECT setting_value FROM settings WHERE setting_key = 'slagingspercentage'");
$slagingspercentage = 60; // Standaardwaarde
if ($settings_query && $row = $settings_query->fetch_assoc()) {
    $slagingspercentage = (int)$row['setting_value'];
}

// Haal de willekeurige vragen op (bijv. 10 stuks)
$sql = "SELECT * FROM vragen ORDER BY RAND() LIMIT 10";
$result = $conn->query($sql);
$vragen = [];

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $vragen[] = [
            "question" => $row["vraag"],
            "options" => [$row["optie1"], $row["optie2"], $row["optie3"]],
            "answer" => (int)$row["correct_antwoord"],
            "explanation" => $row["uitleg"]
        ];
    }
}

// We sturen nu BEIDE door: De instellingen én de vragen
echo json_encode([
    "settings" => ["pass_percentage" => $slagingspercentage],
    "questions" => $vragen
]);

$conn->close();
?>