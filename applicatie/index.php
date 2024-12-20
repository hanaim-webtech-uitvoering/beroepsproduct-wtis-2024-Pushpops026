<?php
session_start();
include 'header.php';
require_once 'db_connectie.php';

// maak verbinding met de database
$db = maakVerbinding();

$_SESSION['bestelling'] = $_SESSION['bestelling'] ?? [];

if (!isset($_SESSION['bestelling'])) {
    $_SESSION['bestelling'] = [];
}

try {
    // Hier wordt de productenlijst opgehaald zoals je het al hebt ingesteld
    $query = "SELECT name, price, type_id FROM Product";
    $stmt = $db->query($query);
    $producten = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Als er een product wordt toegevoegd aan de bestelling
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_name'], $_POST['hoeveelheid'])) {
        $product_name = $_POST['product_name'];
        $hoeveelheid = (int) $_POST['hoeveelheid'];

        if ($hoeveelheid > 0) {
            if (!isset($_SESSION['bestelling'][$product_name])) {
                $_SESSION['bestelling'][$product_name] = $hoeveelheid; // Voeg nieuw product toe
            } else {
                $_SESSION['bestelling'][$product_name] += $hoeveelheid; // Verhoog bestaande hoeveelheid
            }
        }
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verwijder_product'])) {
        $product_name = $_POST['product_name'];
        $aantal_verwijderen = (int) $_POST['aantal_verwijderen'];

        if (isset($_SESSION['bestelling'][$product_name])) {
            // Verminder het aantal producten of verwijder het product als de hoeveelheid op 0 komt
            if ($_SESSION['bestelling'][$product_name] > $aantal_verwijderen) {
                $_SESSION['bestelling'][$product_name] -= $aantal_verwijderen;
            } else {
                unset($_SESSION['bestelling'][$product_name]); // Verwijder het product als het aantal 0 of minder is
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
                            <form method="POST" action="" style="display:inline;">
                                <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($product); ?>">
                                <label for="aantal_verwijderen-<?php echo htmlspecialchars($product); ?>">Aantal te
                                    verwijderen:</label>
                                <input type="number" name="aantal_verwijderen"
                                    id="aantal_verwijderen-<?php echo htmlspecialchars($product); ?>" min="1"
                                    max="<?php echo $hoeveelheid; ?>" value="1" style="width: 60px;">
                                <button type="submit" name="verwijder_product">Verwijderen</button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <form method="GET" action="bestelling.php">
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
    header("Location: index.php");
    exit;
}
?>