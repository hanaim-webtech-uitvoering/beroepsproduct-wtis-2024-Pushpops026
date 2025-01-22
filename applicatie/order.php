<?php
session_start();

include 'header.php';
require_once 'db_connectie.php';

$db = maakVerbinding();
$message = '';
$client_address = '';
$personnel_username = null;

// check if client is logged in
if (isset($_SESSION['role']) && $_SESSION['role'] === 'Client') {
    // retrieve client data
    $query = "SELECT address, first_name, last_name FROM [User] WHERE username = :username";
    $stmt = $db->prepare($query);
    $stmt->execute([':username' => $_SESSION['username']]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    // add data to the session variable
    $client_address = $result['address'] ?? '';
    $_SESSION['first_name'] = $result['first_name'] ?? '';
    $_SESSION['last_name'] = $result['last_name'] ?? '';
}

// process the order
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['place_order'])) {
    $client_username = $_SESSION['username'] ?? null; // check if the username is available
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'Client' && isset($_SESSION['first_name'], $_SESSION['last_name'])) {
        $client_name = htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']);
    } else {
        $client_name = 'Gast'; // if there is no account, default to gast
    }
    $address = htmlspecialchars($_POST['address'] ?? $client_address);
    $order_items = $_SESSION['order'] ?? [];
    $datetime = date('Y-m-d H:i:s');
    $status = 0;

    // check if adress is filled in
    if (empty($address)) {
        $message = "Vul een afleveradres in.";
    } else {
        // safe address of logged in client
        if (!empty($client_username) && $_SESSION['role'] === 'Client') {
            $updateQuery = "UPDATE [User] SET address = :address WHERE username = :username";
            $updateStmt = $db->prepare($updateQuery);
            $updateStmt->execute([
                ':address' => $address,
                ':username' => $client_username
            ]);
        }

        // give order to a random employee
        $query = "SELECT username FROM [User] WHERE role = 'Personnel' ORDER BY NEWID()";
        $stmt = $db->query($query);
        $personnel_username = $stmt->fetchColumn();

        // safe the order
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

        // retrieve order id
        $order_id = $db->lastInsertId();

        // add product to pizza order
        $insertItem = "INSERT INTO [Pizza_Order_Product] (order_id, product_name, quantity) VALUES (:order_id, :product_name, :quantity)";
        $itemStmt = $db->prepare($insertItem);

        foreach ($order_items as $product => $amount) {
            $itemStmt->execute([
                ':order_id' => $order_id,
                ':product_name' => $product,
                ':quantity' => $amount
            ]);
        }
        $message = "Bestelling geplaatst! Jouw bestelling wordt bezorgd op: $address";

        // empty session after order is completed
        unset($_SESSION['order']);
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

    <?php if (!empty($_SESSION['order'])): ?>
        <ul>
            <?php
            $total = 0;
            foreach ($_SESSION['order'] as $product => $amount):
                // retrieve price from database
                $query = "SELECT price FROM Product WHERE name = :name";
                $stmt = $db->prepare($query);
                $stmt->execute([':name' => $product]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $price = $result['price'];

                $subtotal = $price * $amount;
                $total += $subtotal;
                ?>
                <li>
                    <?php echo htmlspecialchars($product); ?> -
                    Aantal: <?php echo $amount; ?> -
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