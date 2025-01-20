<?php
session_start();
include 'header.php';
require_once 'db_connectie.php';

// Maak verbinding met de database
$db = maakVerbinding();

// Controleer of de gebruiker is ingelogd en een personeelslid is
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Personnel') {
    header("Location: inlogPagina.php");
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

// Actieve bestellingen ophalen (status ≠ geannuleerd)
$active_query = "
    SELECT order_id, client_name, datetime, status, address
    FROM [Pizza_Order]
    WHERE personnel_username = :personnel_username AND status != 3
    ORDER BY datetime DESC
";
$active_stmt = $db->prepare($active_query);
$active_stmt->execute([':personnel_username' => $username]);
$active_orders = $active_stmt->fetchAll(PDO::FETCH_ASSOC);

// Geannuleerde bestellingen ophalen (status = geannuleerd)
$cancelled_query = "
    SELECT order_id, client_name, datetime, status, address
    FROM [Pizza_Order]
    WHERE personnel_username = :personnel_username AND status = 3
    ORDER BY datetime DESC
";
$cancelled_stmt = $db->prepare($cancelled_query);
$cancelled_stmt->execute([':personnel_username' => $username]);
$cancelled_orders = $cancelled_stmt->fetchAll(PDO::FETCH_ASSOC);
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

    <!-- Sectie: Actieve Bestellingen -->
    <h2>Actieve Bestellingen</h2>
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
        <p>Er zijn geen actieve bestellingen.</p>
    <?php endif; ?>

    <!-- Sectie: Geannuleerde Bestellingen -->
    <h2>Geannuleerde Bestellingen</h2>
    <?php if ($cancelled_orders): ?>
        <table border="1">
            <tr>
                <th>Bestelling ID</th>
                <th>Klantnaam</th>
                <th>Datum</th>
                <th>Adres</th>
            </tr>
            <?php foreach ($cancelled_orders as $order): ?>
                <tr>
                    <td><?php echo htmlspecialchars($order['order_id']); ?></td>
                    <td><?php echo htmlspecialchars($order['client_name']); ?></td>
                    <td><?php echo htmlspecialchars($order['datetime']); ?></td>
                    <td><?php echo htmlspecialchars($order['address']); ?></td>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>Er zijn geen geannuleerde bestellingen.</p>
    <?php endif; ?>
</body>

<?php
include 'footer.php';
?>

</html>