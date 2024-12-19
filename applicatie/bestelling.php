<?php
include 'header.php';
session_start();
require_once 'db_connectie.php';
$db = maakVerbinding();
$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['place_order'])) {
    $client_name = htmlspecialchars($_POST['name'] ?? 'Gast'); // Gast of opgegeven naam
    $address = htmlspecialchars($_POST['address']);
    $order_items = $_SESSION['bestelling'] ?? [];

    if (empty($order_items)) {
        $message = "Je hebt geen producten in je bestelling.";
    } elseif (empty($address)) {
        $message = "Vul een afleveradres in.";
    } else {
        // Hier kun je de bestelling opslaan in een database
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
            <input type="text" id="address" name="address" required>
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