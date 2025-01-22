<header>
    <link rel="stylesheet" href="style.css">
    <div class="container">
        <h1>Pizzeria Sole Machina</h1>
        <nav>
            <ul>
                <li><a href="index.php">Menu</a></li>
                <?php if (isset($_SESSION['role'])): ?>
                    <li>Welkom, <?php echo htmlspecialchars($_SESSION['username']); ?></li>
                    <?php if ($_SESSION['role'] === 'Client'): ?>
                        <li><a href="customerOrderDashboard.php">Mijn Bestelling</a></li>
                    <?php elseif ($_SESSION['role'] === 'Personnel'): ?>
                        <li><a href="personnelDashboard.php">Dashboard</a></li>
                    <?php endif; ?>
                    <li><a href="logout.php">Uitloggen</a></li>
                <?php else: ?>
                    <li><a href="login.php">Inloggen</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>