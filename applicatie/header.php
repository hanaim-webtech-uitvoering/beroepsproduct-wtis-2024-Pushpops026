<header>
    <link rel="stylesheet" href="style.css">
    <div class="container">
        <h1>Pizzeria Sole Machina</h1>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <?php if (isset($_SESSION['username'])): ?>
                    <li>Welkom, <?php echo htmlspecialchars($_SESSION['username']); ?></li>
                    <li><a href="uitloggen.php">Uitloggen</a></li>
                <?php else: ?>
                    <li><a href="inlogPagina.php">Log in</a></li>
                    <li><a href="registratie.php">Registreren</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>