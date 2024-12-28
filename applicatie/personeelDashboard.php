<?php
session_start();
include 'header.php';
require_once 'db_connectie.php';

// Maak verbinding met de database
$db = maakVerbinding();

// Controleer of de gebruiker is ingelogd en een personeelslid is
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Personnel') {
    header("Location: login.php");
    exit;
}

$message = '';
$username = $_SESSION['username'];

// Verwerk statuswijziging
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['status'])) {
    $order_id = (int) $_POST['order_id'];
    $status = (int) $_POST['status'];

    $query = "UPDATE [Pizza_Order] SET status = :status WHERE order_id = :order_id AND personnel_username = :personnel_username";
    $stmt = $db->prepare($query);
    $stmt->execute([
        ':status' => $status,
        ':order_id' => $order_id,
        ':personnel_username' => $username
    ]);

    $message = "De status van bestelling #$order_id is bijgewerkt.";
}

// Verwijder bestelling
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_order'])) {
    $delete_query = "
        DELETE FROM [Pizza_Order]
        WHERE order_id = :order_id
    ";
    $delete_stmt = $db->prepare($delete_query);
    $delete_stmt->execute([':order_id' => $_POST['order_id']]);
    $message = "De bestelling is succesvol verwijderd.";
    // Refresh de pagina na verwijderen
    header("Location: personeelDashboard.php");
    exit;
}

// Haal alle bestellingen op die aan het personeelslid zijn toegewezen
$query = "
    SELECT order_id, client_name, datetime, status, address
    FROM [Pizza_Order]
    WHERE personnel_username = :personnel_username
    ORDER BY datetime DESC
";
$stmt = $db->prepare($query);
$stmt->execute([':personnel_username' => $username]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Haal geannuleerde bestellingen op
$cancelled_orders = array_filter($orders, function ($order) {
    return $order['status'] === 3;
});

// Haal actieve bestellingen op
$active_orders = array_filter($orders, function ($order) {
    return $order['status'] !== 3;
});
?>

<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personeelsdashboard</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <h1>Personeelsdashboard</h1>

    <!-- Feedbackbericht -->
    <?php if ($message): ?>
        <p><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <!-- Sectie: Toegewezen bestellingen -->
    <h2>Toegewezen Bestellingen</h2>
    <?php if ($active_orders): ?>
        <table border="1">
            <tr>
                <th>Bestelling ID</th>
                <th>Klantnaam</th>
                <th>Datum</th>
                <th>Status</th>
                <th>Adres</th>
                <th>Acties</th>
            </tr>
            <?php foreach ($active_orders as $order): ?>
                <tr>
                    <td><?php echo htmlspecialchars($order['order_id']); ?></td>
                    <td><?php echo htmlspecialchars($order['client_name']); ?></td>
                    <td><?php echo htmlspecialchars($order['datetime']); ?></td>
                    <td>
                        <?php
                        // Status weergeven als tekst
                        $status_labels = [
                            0 => 'In behandeling',
                            1 => 'Bereid',
                            2 => 'Bezorgd',
                            3 => 'Geannuleerd',
                        ];
                        echo htmlspecialchars($status_labels[$order['status']] ?? 'Onbekend');
                        ?>
                    </td>
                    <td><?php echo htmlspecialchars($order['address']); ?></td>
                    <td>
                        <!-- Dropdownmenu voor statuswijziging -->
                        <form method="POST" action="">
                            <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                            <select name="status" required>
                                <option value="0" <?php echo $order['status'] == 0 ? 'selected' : ''; ?>>In behandeling</option>
                                <option value="1" <?php echo $order['status'] == 1 ? 'selected' : ''; ?>>Bereid</option>
                                <option value="2" <?php echo $order['status'] == 2 ? 'selected' : ''; ?>>Bezorgd</option>
                                <option value="3" <?php echo $order['status'] == 3 ? 'selected' : ''; ?>>Geannuleerd</option>
                            </select>
                            <button type="submit">Wijzig Status</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>Er zijn geen toegewezen bestellingen.</p>
    <?php endif; ?>

    <!-- Sectie: Geannuleerde bestellingen (WERKT NOG NIET)-->
    <h2>Geannuleerde Bestellingen</h2>
    <?php if ($cancelled_orders): ?>
        <ul>
            <?php foreach ($cancelled_orders as $order): ?>
                <li>
                    <p><strong>Bestelnummer:</strong> <?php echo htmlspecialchars($order['order_id']); ?></p>
                    <p><strong>Klant:</strong> <?php echo htmlspecialchars($order['client_name']); ?></p>
                    <p><strong>Bezorgadres:</strong> <?php echo htmlspecialchars($order['address']); ?></p>
                    <p><strong>Bestelling geplaatst op:</strong> <?php echo $order['datetime']; ?></p>

                    <form method="POST" action="">
                        <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                        <button type="submit" name="delete_order">Verwijder Bestelling</button>
                    </form>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>Er zijn geen geannuleerde bestellingen.</p>
    <?php endif; ?>
</body>

<?php
include 'footer.php';
?>

</html>