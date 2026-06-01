<?php
session_start();
require 'db_config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$bericht = "";

// 1. INSTELLINGEN OPSLAAN
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'save_settings') {
    $percentage = (int)$_POST['slagingspercentage'];
    $conn->query("UPDATE settings SET setting_value = '$percentage' WHERE setting_key = 'slagingspercentage'");
    $bericht = "<div class='msg success'>✅ Instellingen succesvol opgeslagen!</div>";
}

// 2. VRAAG VERWIJDEREN
if (isset($_GET['delete'])) {
    $id_to_delete = (int)$_GET['delete'];
    $conn->query("DELETE FROM vragen WHERE id = $id_to_delete");
    $bericht = "<div class='msg success'>✅ Vraag succesvol verwijderd uit de Tente!</div>";
}

// 3. VRAAG TOEVOEGEN OF UPDATEN
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    if ($_POST['action'] == 'add' || $_POST['action'] == 'update') {
        $vraag = $conn->real_escape_string($_POST['vraag']);
        $optie1 = $conn->real_escape_string($_POST['optie1']);
        $optie2 = $conn->real_escape_string($_POST['optie2']);
        $optie3 = $conn->real_escape_string($_POST['optie3']);
        $antwoord = (int)$_POST['antwoord'];
        $uitleg = $conn->real_escape_string($_POST['uitleg']);

        if ($_POST['action'] == 'add') {
            $sql = "INSERT INTO vragen (vraag, optie1, optie2, optie3, correct_antwoord, uitleg) 
                    VALUES ('$vraag', '$optie1', '$optie2', '$optie3', $antwoord, '$uitleg')";
            $bericht = $conn->query($sql) ? "<div class='msg success'>✅ Nieuwe vraag toegevoegd!</div>" : "<div class='msg error'>❌ Fout: " . $conn->error . "</div>";
        } elseif ($_POST['action'] == 'update') {
            $id = (int)$_POST['vraag_id'];
            $sql = "UPDATE vragen SET vraag='$vraag', optie1='$optie1', optie2='$optie2', optie3='$optie3', correct_antwoord=$antwoord, uitleg='$uitleg' WHERE id=$id";
            $bericht = $conn->query($sql) ? "<div class='msg success'>✅ Vraag succesvol aangepast!</div>" : "<div class='msg error'>❌ Fout: " . $conn->error . "</div>";
        }
    }
}

// Haal instellingen op
$settings_result = $conn->query("SELECT setting_value FROM settings WHERE setting_key = 'slagingspercentage'");
$huidig_percentage = $settings_result->fetch_assoc()['setting_value'] ?? 60;

// Haal specifieke vraag op om te bewerken (als ?edit is meegegeven)
$edit_mode = false;
$edit_data = null;
if (isset($_GET['edit'])) {
    $edit_mode = true;
    $edit_id = (int)$_GET['edit'];
    $edit_result = $conn->query("SELECT * FROM vragen WHERE id = $edit_id");
    $edit_data = $edit_result->fetch_assoc();
}

$vragen_result = $conn->query("SELECT * FROM vragen ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Beheer - Goor Quiz</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #fdf5f5; padding: 20px; color: #333; }
        .header { display: flex; justify-content: space-between; align-items: center; background: #C8102E; color: white; padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .header a { color: white; text-decoration: none; font-weight: bold; background: #111; padding: 8px 15px; border-radius: 4px; }
        .container { display: flex; gap: 20px; flex-wrap: wrap; align-items: flex-start; }
        .box { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); flex: 1; min-width: 300px; border-top: 4px solid #C8102E; }
        .full-width { flex: 100%; }
        input, select, textarea { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { background: #C8102E; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; width: 100%; font-size: 16px; font-weight: bold; }
        button:hover { background: #9e0c24; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 0.9em; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        th { background-color: #f8f9fa; color: #C8102E; }
        .action-btns a { display: inline-block; padding: 5px 10px; text-decoration: none; border-radius: 4px; color: white; margin-right: 5px; font-size: 0.9em; }
        .btn-edit { background: #FFC000; color: #333 !important; }
        .btn-delete { background: #d9534f; }
        .msg { padding: 15px; margin-bottom: 20px; border-radius: 4px; font-weight: bold; }
        .success { background: #d4edda; color: #155724; border-left: 5px solid #28a745; }
        .error { background: #f8d7da; color: #721c24; border-left: 5px solid #dc3545; }
    </style>
</head>
<body>

<div class="header">
    <h2>🏰 Beheerderspaneel Goor Quiz (Ingelogd als: <?= htmlspecialchars($_SESSION['admin_username']) ?>)</h2>
    <a href="logout.php">Uitloggen ➔</a>
</div>

<?= $bericht ?>

<div class="container">
    
    <!-- INSTELLINGEN BOX -->
    <div class="box full-width">
        <h3>⚙️ Algemene Instellingen</h3>
        <form method="POST" action="beheercentrum.php" style="display: flex; gap: 15px; align-items: center;">
            <input type="hidden" name="action" value="save_settings">
            <label style="white-space: nowrap;"><strong>Slagingspercentage voor Certificaat (%):</strong></label>
            <input type="number" name="slagingspercentage" value="<?= $huidig_percentage ?>" min="1" max="100" required style="width: 100px; margin: 0;">
            <button type="submit" style="width: auto; margin: 0;">Opslaan</button>
        </form>
    </div>

    <!-- VRAGEN TOEVOEGEN/BEWERKEN BOX -->
    <div class="box" style="flex: 0 0 350px;">
        <h3><?= $edit_mode ? '✏️ Vraag Bewerken' : '➕ Nieuwe Vraag Toevoegen' ?></h3>
        <form method="POST" action="beheercentrum.php">
            <input type="hidden" name="action" value="<?= $edit_mode ? 'update' : 'add' ?>">
            <?php if($edit_mode): ?><input type="hidden" name="vraag_id" value="<?= $edit_data['id'] ?>"><?php endif; ?>

            <label>De Vraag:</label>
            <input type="text" name="vraag" required value="<?= $edit_mode ? htmlspecialchars($edit_data['vraag']) : '' ?>">

            <label>Optie 1:</label><input type="text" name="optie1" required value="<?= $edit_mode ? htmlspecialchars($edit_data['optie1']) : '' ?>">
            <label>Optie 2:</label><input type="text" name="optie2" required value="<?= $edit_mode ? htmlspecialchars($edit_data['optie2']) : '' ?>">
            <label>Optie 3:</label><input type="text" name="optie3" required value="<?= $edit_mode ? htmlspecialchars($edit_data['optie3']) : '' ?>">

            <label>Juiste Antwoord:</label>
            <select name="antwoord" required>
                <option value="0" <?= ($edit_mode && $edit_data['correct_antwoord'] == 0) ? 'selected' : '' ?>>Optie 1 is correct</option>
                <option value="1" <?= ($edit_mode && $edit_data['correct_antwoord'] == 1) ? 'selected' : '' ?>>Optie 2 is correct</option>
                <option value="2" <?= ($edit_mode && $edit_data['correct_antwoord'] == 2) ? 'selected' : '' ?>>Optie 3 is correct</option>
            </select>

            <label>Uitleg achteraf:</label>
            <textarea name="uitleg" rows="3" required><?= $edit_mode ? htmlspecialchars($edit_data['uitleg']) : '' ?></textarea>

            <button type="submit"><?= $edit_mode ? 'Wijzigingen Opslaan' : 'Opslaan in Database' ?></button>
            <?php if($edit_mode): ?>
                <a href="beheercentrum.php" style="display:block; text-align:center; margin-top:10px; color:#C8102E;">Annuleren</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- VRAGEN LIJST BOX -->
    <div class="box">
        <h3>📋 Vragen in de Database</h3>
        <table>
            <tr>
                <th>ID</th>
                <th>Vraag & Uitleg</th>
                <th>Goede Antwoord</th>
                <th>Acties</th>
            </tr>
            <?php while($row = $vragen_result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><strong><?= htmlspecialchars($row['vraag']) ?></strong><br><small style="color: #666;"><?= htmlspecialchars($row['uitleg']) ?></small></td>
                <td>
                    <?php 
                        $opties = [$row['optie1'], $row['optie2'], $row['optie3']];
                        echo htmlspecialchars($opties[$row['correct_antwoord']]);
                    ?>
                </td>
                <td class="action-btns">
                    <a href="beheercentrum.php?edit=<?= $row['id'] ?>" class="btn-edit">✏️</a>
                    <a href="beheercentrum.php?delete=<?= $row['id'] ?>" class="btn-delete" onclick="return confirm('Weet je zeker dat je deze vraag wilt wissen?');">🗑️</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>

</body>
</html>