<?php
session_start();
include 'header.php';
require_once 'db_connectie.php';
$message = '';
$db = maakVerbinding();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = htmlspecialchars($_POST['username']);
    $password = htmlspecialchars($_POST['password']);
    $confirm_password = htmlspecialchars($_POST['confirm_password']);
    $first_name = htmlspecialchars($_POST['first_name']);
    $last_name = htmlspecialchars($_POST['last_name']);
    $role = 'klant'; // Standaard rol is klant

    // Validatie
    if (empty($username) || empty($password) || empty($first_name) || empty($last_name)) {
        $message = "Alle velden zijn verplicht.";
    } elseif ($password !== $confirm_password) {
        $message = "Wachtwoorden komen niet overeen.";
    } else {
        // Controleren of de gebruikersnaam al bestaat
        $query = "SELECT username FROM [User] WHERE username = :username";
        $stmt = $db->prepare($query);
        $stmt->execute([':username' => $username]);

        if ($stmt->fetch()) {
            $message = "De gebruikersnaam is al in gebruik.";
        } else {
            // Hash wachtwoord
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

            // Nieuwe gebruiker toevoegen
            $query = "INSERT INTO [User] (username, password, first_name, last_name, role) VALUES (:username, :password, :first_name, :last_name, :role)";
            $stmt = $db->prepare($query);
            $stmt->execute([
                ':username' => $username,
                ':password' => $hashed_password,
                ':first_name' => $first_name,
                ':last_name' => $last_name,
                ':role' => $role,
            ]);

            $message = "Registratie succesvol! Je kunt nu <a href='inlogPagina.php'>inloggen</a>.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registratie</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="content">
        <div class="menu-container">
            <h1>Registreer</h1>
            <?php if (!empty($message)): ?>
                <p><?php echo $message; ?></p>
            <?php endif; ?>
            <form method="POST" action="">
                <label for="username">Gebruikersnaam:</label>
                <input type="text" id="username" name="username" required>

                <label for="password">Wachtwoord:</label>
                <input type="password" id="password" name="password" required>

                <label for="confirm_password">Bevestig Wachtwoord:</label>
                <input type="password" id="confirm_password" name="confirm_password" required>

                <label for="first_name">Voornaam:</label>
                <input type="text" id="first_name" name="first_name" required>

                <label for="last_name">Achternaam:</label>
                <input type="text" id="last_name" name="last_name" required>

                <button type="submit">Registreer</button>
            </form>
        </div>
    </div>
</body>

</html>
<?php
include 'footer.php';
?>