<header>
    <link rel="stylesheet" href="style.css">
    <div class="container">
        <h1>Pizzeria Sole Machina</h1>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <?php if (isset($_SESSION['role'])): ?>
                    <li>Welkom, <?php echo htmlspecialchars($_SESSION['username']); ?></li>
                    <?php if ($_SESSION['role'] === 'Client'): ?>
                        <li><a href="klantBestelOverzicht.php">Mijn Bestelling</a></li>
                    <?php elseif ($_SESSION['role'] === 'Personnel'): ?>
                        <li><a href="personeelDashboard.php">Dashboard</a></li>
                    <?php endif; ?>
                    <li><a href="uitloggen.php">Uitloggen</a></li>
                <?php else: ?>
                    <li><a href="inlogPagina.php">Inloggen</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>