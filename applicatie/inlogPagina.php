<?php
session_start();
include 'header.php';
require_once 'db_connectie.php';

// Verbinding maken met de database
$db = maakVerbinding();
$message = '';

// Verwerk loginformulier
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $username = htmlspecialchars($_POST['username']);
    $password = htmlspecialchars($_POST['password']);

    if (empty($username) || empty($password)) {
        $message = "Vul zowel een gebruikersnaam als een wachtwoord in.";
    } else {
        try {
            // Zoek gebruiker op basis van de gebruikersnaam
            $query = "SELECT username, password, role FROM [User] WHERE username = :username";
            $stmt = $db->prepare($query);
            $stmt->execute([':username' => $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Als de gebruiker bestaat en het wachtwoord klopt
            if ($user && password_verify($password, $user['password']) || $user && $password === $user['password']) {
                // Sessie-instellingen voor de ingelogde gebruiker
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                // Redirect naar de juiste pagina op basis van de rol
                if ($user['role'] === 'Client') {
                    // Eerst de sessie-instellingen verwerken, daarna doorsturen
                    header("Location:index.php");

                } elseif ($user['role'] === 'Personnel') {
                    // Eerst de sessie-instellingen verwerken, daarna doorsturen
                    header("Location:personeelDashboard.php");
                }
                exit;
            } else {
                $message = "Ongeldige gebruikersnaam of wachtwoord.";
            }
        } catch (PDOException $e) {
            $message = "Er is een fout opgetreden: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inloggen</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <h1>Inloggen</h1>

    <form method="POST" action="">
        <label for="username">Gebruikersnaam:</label>
        <input type="text" id="username" name="username" required>

        <label for="password">Wachtwoord:</label>
        <input type="password" id="password" name="password" required>

        <button type="submit" name="login">Inloggen</button>
    </form>

    <?php if (!empty($message)): ?>
        <p><?php echo $message; ?></p>
    <?php endif; ?>

    <p>Heb je nog geen account? <a href="registratie.php">Registreer hier</a></p>
</body>

<?php
include 'footer.php';
?>

</html>