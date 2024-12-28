<?php
session_start();

include 'header.php';
require_once 'db_connectie.php';

$db = maakVerbinding();
$message = '';

// Alleen toegankelijk voor klanten
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Client') {
    header("Location: login.php");
    exit;
}

$client_username = $_SESSION['username'];

// Haal de laatste bestelling op van de ingelogde klant
$query = "
    SELECT TOP 1 order_id, personnel_username, datetime, status, address
    FROM [Pizza_Order]
    WHERE client_username = :client_username
    ORDER BY datetime DESC
";
$stmt = $db->prepare($query);
$stmt->execute([':client_username' => $client_username]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

$status_map = [
    0 => 'In behandeling',
    1 => 'Bereid',
    2 => 'Bezorgd',
    3 => 'Geannuleerd'
];

// Annuleer de bestelling
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_order']) && $order) {
    $update_query = "
        UPDATE [Pizza_Order]
        SET status = 3
        WHERE order_id = :order_id
    ";
    $update_stmt = $db->prepare($update_query);
    $update_stmt->execute([':order_id' => $order['order_id']]);

    $message = "Uw bestelling is geannuleerd.";
    // Refresh de bestelling na annulering
    header("Location: klantBestelOverzicht.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Mijn Bestelling</title>
</head>

<body>
    <h1>Mijn Bestelling</h1>

    <?php if ($order): ?>
        <p><strong>Bestelnummer:</strong> <?php echo $order['order_id']; ?></p>
        <p><strong>Bezorgadres:</strong> <?php echo htmlspecialchars($order['address']); ?></p>
        <p><strong>Bestelling geplaatst op:</strong> <?php echo $order['datetime']; ?></p>
        <p><strong>Huidige status:</strong> <?php echo $status_map[$order['status']] ?? 'Onbekend'; ?></p>
        <p><strong>Personeelslid toegewezen:</strong> <?php echo htmlspecialchars($order['personnel_username']); ?></p>
        <?php if ($order['status'] !== 3): ?>
            <form method="POST" action="">
                <button type="submit" name="cancel_order">Annuleer Bestelling</button>
            </form>
        <?php else: ?>
            <p><strong>Status:</strong> Deze bestelling is geannuleerd.</p>
        <?php endif; ?>
    <?php else: ?>
        <p>Er zijn geen bestellingen gevonden voor uw account.</p>
    <?php endif; ?>

    <a href="index.php">Terug naar Menu</a>
</body>

<?php
include 'footer.php';
?>

</html>