<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['place_order'])) {
    $address = htmlspecialchars($_POST['address']);
    $order_items = $_SESSION['order'] ?? [];

    if (empty($order_items)) {
        $message = "Je hebt geen producten in je bestelling.";
    } elseif (empty($address)) {
        $message = "Vul een afleveradres in.";
    } else {
        // Logica om bestelling op te slaan (voorbeeld)
        $message = "Bestelling geplaatst! Jouw bestelling wordt bezorgd op: $address";
        unset($_SESSION['order']); // Bestelling wissen
    }
}
?>

<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <title>Jouw Bestelling</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <h1>Jouw Bestelling</h1>

    <?php if (!empty($_SESSION['order'])): ?>
        <ul>
            <?php
            $total = 0;
            foreach ($_SESSION['order'] as $item):
                $subtotal = $item['price'] * $item['quantity'];
                $total += $subtotal;
                ?>
                <li>
                    <?php echo htmlspecialchars($item['name']); ?> -
                    Aantal: <?php echo $item['quantity']; ?> -
                    Subtotaal: €<?php echo number_format($subtotal, 2); ?>
                </li>
            <?php endforeach; ?>
        </ul>
        <p><strong>Totaal: €<?php echo number_format($total, 2); ?></strong></p>
    <?php else: ?>
        <p>Je hebt geen producten in je bestelling.</p>
    <?php endif; ?>

    <h2>Afleveradres</h2>
    <form method="POST" action="order.php">
        <label>Adres: </label>
        <input type="text" name="address" required>
        <button type="submit" name="place_order">Bestelling Plaatsen</button>
    </form>

    <?php if (isset($message))
        echo "<p>$message</p>"; ?>

    <a href="menu.php">Terug naar Menu</a>
</body>

</html>