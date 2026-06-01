<?php
$host = 'localhost';
$db   = 'goor_quiz';
$user = 'webuser'; 
$pass = 'binck@guus2025'; 

$conn = new mysqli($host, $user, $pass, $db);
$bericht = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $vraag = $conn->real_escape_string($_POST['vraag']);
    $optie1 = $conn->real_escape_string($_POST['optie1']);
    $optie2 = $conn->real_escape_string($_POST['optie2']);
    $optie3 = $conn->real_escape_string($_POST['optie3']);
    $antwoord = (int)$_POST['antwoord'];
    $uitleg = $conn->real_escape_string($_POST['uitleg']);

    $sql = "INSERT INTO vragen (vraag, optie1, optie2, optie3, correct_antwoord, uitleg) 
            VALUES ('$vraag', '$optie1', '$optie2', '$optie3', $antwoord, '$uitleg')";

    if ($conn->query($sql) === TRUE) {
        $bericht = "<div style='color: green; font-weight: bold; margin-bottom: 15px;'>✅ Nieuwe vraag succesvol toegevoegd aan de Tente!</div>";
    } else {
        $bericht = "<div style='color: red;'>❌ Fout: " . $conn->error . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Goor Quiz - Vraag Toevoegen</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f9; padding: 20px; }
        .admin-box { background: white; max-width: 500px; margin: 0 auto; padding: 20px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); border-top: 5px solid #007A33; }
        input, select, textarea { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { background: #007A33; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; width: 100%; font-size: 16px; }
        button:hover { background: #005a24; }
    </style>
</head>
<body>

<div class="admin-box">
    <h2>Nieuwe Vraag Toevoegen</h2>
    <?= $bericht ?>
    
    <form method="POST" action="">
        <label>De Vraag:</label>
        <input type="text" name="vraag" required placeholder="Bijv: Wie is de burgemeester?">

        <label>Optie 1:</label>
        <input type="text" name="optie1" required>

        <label>Optie 2:</label>
        <input type="text" name="optie2" required>

        <label>Optie 3:</label>
        <input type="text" name="optie3" required>

        <label>Welke optie is het goede antwoord?</label>
        <select name="antwoord" required>
            <option value="0">Optie 1 is correct</option>
            <option value="1">Optie 2 is correct</option>
            <option value="2">Optie 3 is correct</option>
        </select>

        <label>Uitleg (verschijnt na het antwoorden):</label>
        <textarea name="uitleg" rows="3" required placeholder="Typ hier het leuke feitje..."></textarea>

        <button type="submit">Vraag Opslaan</button>
    </form>
    <br>
    <a href="index.html" style="display:block; text-align:center; color:#666;">Terug naar de quiz</a>
</div>

</body>
</html>