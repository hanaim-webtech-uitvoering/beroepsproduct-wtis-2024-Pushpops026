<?php
session_start();

include 'header.php';
require_once 'db_connectie.php';

$db = maakVerbinding();
$message = '';
$client_address = '';
$personnel_username = null;

// Controleer of de klant is ingelogd
if (isset($_SESSION['role']) && $_SESSION['role'] === 'Client') {
    // Haal de gegevens van de klant op
    $query = "SELECT address, first_name, last_name FROM [User] WHERE username = :username";
    $stmt = $db->prepare($query);
    $stmt->execute([':username' => $_SESSION['username']]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    // Vul de sessievariabelen in met de opgehaalde gegevens
    $client_address = $result['address'] ?? '';
    $_SESSION['first_name'] = $result['first_name'] ?? '';
    $_SESSION['last_name'] = $result['last_name'] ?? '';
}

// Verwerk bestelling
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['place_order'])) {
    $client_username = $_SESSION['username'] ?? null; // Controleer of gebruikersnaam beschikbaar is
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'Client' && isset($_SESSION['first_name'], $_SESSION['last_name'])) {
        $client_name = htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']);
    } else {
        $client_name = 'Gast'; // Gebruik "Gast" als geen naam beschikbaar is
    }
    $address = htmlspecialchars($_POST['address'] ?? $client_address);
    $order_items = $_SESSION['bestelling'] ?? [];
    $datetime = date('Y-m-d H:i:s');
    $status = 0; // Standaard status voor nieuwe bestellingen

    // Controleer of er producten in de bestelling zitten
    if (empty($order_items)) {
        $message = "Je hebt geen producten in je bestelling.";
    } elseif (empty($address)) {
        $message = "Vul een afleveradres in.";
    } else {
        // Sla het adres op voor ingelogde klanten
        if (!empty($client_username) && $_SESSION['role'] === 'Client') {
            $updateQuery = "UPDATE [User] SET address = :address WHERE username = :username";
            $updateStmt = $db->prepare($updateQuery);
            $updateStmt->execute([
                ':address' => $address,
                ':username' => $client_username
            ]);
        }

        // Willekeurig personeelslid toewijzen
        $query = "SELECT username FROM [User] WHERE role = 'Personnel' ORDER BY NEWID()";
        $stmt = $db->query($query);
        $personnel_username = $stmt->fetchColumn();

        // Bestelling opslaan
        $insertOrder = "INSERT INTO [Pizza_Order] 
        (client_username, client_name, personnel_username, datetime, status, address) 
        VALUES (:client_username, :client_name, :personnel_username, :datetime, :status, :address)";
        $stmt = $db->prepare($insertOrder);
        $stmt->execute([
            ':client_username' => $client_username,
            ':client_name' => $client_name,
            ':personnel_username' => $personnel_username,
            ':datetime' => $datetime,
            ':status' => $status,
            ':address' => $address
        ]);

        // Haal de order_id op
        $order_id = $db->lastInsertId();

        // Voeg de producten toe aan de Pizza_Order_Product-tabel
        $insertItem = "INSERT INTO [Pizza_Order_Product] (order_id, product_name, quantity) VALUES (:order_id, :product_name, :quantity)";
        $itemStmt = $db->prepare($insertItem);

        foreach ($order_items as $product => $hoeveelheid) {
            $itemStmt->execute([
                ':order_id' => $order_id,
                ':product_name' => $product,
                ':quantity' => $hoeveelheid
            ]);
        }

        // Succesbericht weergeven
        $message = "Bestelling geplaatst! Jouw bestelling wordt bezorgd op: $address";

        // Leeg de sessie na het plaatsen van de bestelling
        unset($_SESSION['bestelling']);
    }
}
?>

<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bestelling</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <h1>Bestelling</h1>

    <?php if (!empty($_SESSION['bestelling'])): ?>
        <ul>
            <?php
            $total = 0;
            foreach ($_SESSION['bestelling'] as $product => $hoeveelheid):
                // Prijs ophalen uit database
                $query = "SELECT price FROM Product WHERE name = :name";
                $stmt = $db->prepare($query);
                $stmt->execute([':name' => $product]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $price = $result['price'];

                $subtotal = $price * $hoeveelheid;
                $total += $subtotal;
                ?>
                <li>
                    <?php echo htmlspecialchars($product); ?> -
                    Aantal: <?php echo $hoeveelheid; ?> -
                    Subtotaal: €<?php echo number_format($subtotal, 2); ?>
                </li>
            <?php endforeach; ?>
        </ul>
        <p><strong>Totaal: €<?php echo number_format($total, 2); ?></strong></p>

        <h2>Afleveradres</h2>
        <form method="POST" action="">
            <label for="address">Adres:</label>
            <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($client_address); ?>"
                required>
            <button type="submit" name="place_order">Bestelling Plaatsen</button>
        </form>
    <?php else: ?>
        <p>Je hebt geen producten in je bestelling.</p>
    <?php endif; ?>

    <?php if (!empty($message)): ?>
        <p><?php echo $message; ?></p>
    <?php endif; ?>

    <a href="index.php">Terug naar Menu</a>
</body>

<?php
include 'footer.php';
?>

</html>