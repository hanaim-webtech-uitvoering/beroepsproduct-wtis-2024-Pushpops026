<?php
session_start();

require_once 'db_connectie.php';

// maak verbinding met de database (zie db_connection.php)
$db = maakVerbinding();

if (!isset($_SESSION['bestelling'])) {
    $_SESSION['bestelling'] = [];
}

try {
    // Haal producten op uit de database
    $query = "SELECT name, price, type_id FROM Product";
    $stmt = $db->query($query);
    $producten = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Verwerk formulierinzending (product toevoegen aan bestelling)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_name'], $_POST['hoeveelheid'])) {
        $product_name = $_POST['product_name'];
        $hoeveelheid = (int) $_POST['hoeveelheid']; // Zorg dat het een integer is

        if ($hoeveelheid > 0) {
            if (!isset($_SESSION['bestelling'][$product_name])) {
                $_SESSION['bestelling'][$product_name] = $hoeveelheid; // Voeg nieuw product toe
            } else {
                $_SESSION['bestelling'][$product_name] += $hoeveelheid; // Verhoog bestaande hoeveelheid
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
    <title>Pizzeria Menu</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <h1>Welkom bij de Pizzeria!</h1>
    <h2>Ons Menu</h2>

    <div class="menu-container">
        <?php if (!empty($producten)): ?>
            <?php foreach ($producten as $product): ?>
                <div class="menu-item">
                    <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                    <p>Prijs: €<?php echo number_format($product['price'], 2); ?></p>
                    <p>Type: <?php echo htmlspecialchars($product['type_id']); ?></p>

                    <!-- Formulier om een product toe te voegen -->
                    <form method="POST" action="">
                        <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($product['name']); ?>">
                        <label for="hoeveelheid-<?php echo htmlspecialchars($product['name']); ?>">Hoeveelheid:</label>
                        <input type="number" name="hoeveelheid"
                            id="hoeveelheid-<?php echo htmlspecialchars($product['name']); ?>" min="1" value="1"
                            style="width: 60px;">
                        <button type="submit">Toevoegen aan bestelling</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Er zijn momenteel geen producten beschikbaar.</p>
        <?php endif; ?>
    </div>

    <h2>Je Bestelling</h2>
    <div class="bestelling-container">
        <?php if (!empty($_SESSION['bestelling'])): ?>
            <ul>
                <?php foreach ($_SESSION['bestelling'] as $product => $hoeveelheid): ?>
                    <li>
                        <?php echo htmlspecialchars($product) . " - Hoeveelheid: " . $hoeveelheid; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>Je hebt nog geen producten toegevoegd.</p>
        <?php endif; ?>
    </div>
</body>

</html>

<?php
// Toevoegen aan bestelling
if (isset($_POST['add_to_order'])) {
    $product_name = $_POST['product_name'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];

    $item = [
        'name' => $product_name,
        'price' => $price,
        'quantity' => $quantity
    ];

    if (!isset($_SESSION['order'])) {
        $_SESSION['order'] = [];
    }

    $_SESSION['order'][] = $item;
    header("Location: menu.php");
    exit;
}
?>