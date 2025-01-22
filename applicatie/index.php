<?php
session_start();
include 'header.php';
require_once 'db_connectie.php';

$db = maakVerbinding();

$_SESSION['order'] = $_SESSION['order'] ?? [];

if (!isset($_SESSION['order'])) {
    $_SESSION['order'] = [];
}

try {
    // retrieve products
    $query = "SELECT name, price, type_id FROM Product";
    $stmt = $db->query($query);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // adding products to order
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_name'], $_POST['amount'])) {
        $product_name = $_POST['product_name'];
        $amount = (int) $_POST['amount'];

        if ($amount > 0) {
            if (!isset($_SESSION['order'][$product_name])) {
                $_SESSION['order'][$product_name] = $amount;
            } else {
                $_SESSION['order'][$product_name] += $amount;
            }
        }
    }
    // remove/delete products from order
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_product'])) {
        $product_name = $_POST['product_name'];
        $delete_amount = (int) $_POST['delete_amount'];

        if (isset($_SESSION['order'][$product_name])) {

            if ($_SESSION['order'][$product_name] > $delete_amount) {
                $_SESSION['order'][$product_name] -= $delete_amount;
            } else {
                unset($_SESSION['order'][$product_name]);
            }
        }
    }

} catch (PDOException $e) {
    die("Fout bij ophalen van producten: " . $e->getMessage());
}

?>

<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pizzeria sole machina</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <h1>Ons Menu</h1>
    <div class="content">
        <div class="menu-container">
            <?php if (!empty($products)): ?>
                <?php foreach ($products as $product): ?>
                    <div class="menu-item">
                        <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                        <p>Prijs: €<?php echo number_format($product['price'], 2); ?></p>
                        <p>Type: <?php echo htmlspecialchars($product['type_id']); ?></p>

                        <form method="POST" action="">
                            <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($product['name']); ?>">
                            <label for="amount-<?php echo htmlspecialchars($product['name']); ?>">Hoeveelheid:</label>
                            <input type="number" name="amount" id="amount-<?php echo htmlspecialchars($product['name']); ?>"
                                min="1" value="1" style="width: 60px;">
                            <button type="submit">Toevoegen aan bestelling</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Er zijn momenteel geen producten beschikbaar.</p>
            <?php endif; ?>
        </div>

        <h2>Je Bestelling</h2>
        <div class="order-container">
            <?php if (!empty($_SESSION['order'])): ?>
                <ul>
                    <?php foreach ($_SESSION['order'] as $product => $amount): ?>
                        <li>
                            <?php echo htmlspecialchars($product) . " - amount: " . $amount; ?>
                            <form method="POST" action="" style="display:inline;">
                                <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($product); ?>">
                                <label for="delete_amount-<?php echo htmlspecialchars($product); ?>">Aantal te
                                    verwijderen:</label>
                                <input type="number" name="delete_amount"
                                    id="delete_amount-<?php echo htmlspecialchars($product); ?>" min="1"
                                    max="<?php echo $amount; ?>" value="1" style="width: 60px;">
                                <button type="submit" name="delete_product">Verwijderen</button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <form method="GET" action="order.php">
                    <button type="submit">Ga naar Bestellen</button>
                </form>
            <?php else: ?>
                <p>Je hebt nog geen producten toegevoegd.</p>
            <?php endif; ?>
        </div>
</body>

<?php
include 'footer.php';
?>

</html>