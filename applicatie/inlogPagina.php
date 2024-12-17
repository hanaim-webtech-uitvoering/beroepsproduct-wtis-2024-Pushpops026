<?php
require_once 'db_connectie.php';

// maak verbinding met de database (zie db_connection.php)
$db = maakVerbinding();



// haal alle componisten op en tel het aantal stukken
$query = 'select c.componistId as id, c.naam as naam, count(S.stuknr) as aantal
from Componist C left outer join Stuk S on C.componistId = S.componistId
group by C.componistId, C.naam
order by naam';

$data = $db->query($query);

$html_table = '<table>';
$html_table = $html_table . '<tr><th>Id</th><th>Naam</th><th>Aantal stukken</th></tr>';

while ($rij = $data->fetch()) {
    $id = $rij['id'];
    $naam = $rij['naam'];
    $aantal = $rij['aantal'];

    $html_table = $html_table . "<tr><td>$id</td><td>$naam</td><td>$aantal</td></tr>";
}
$html_table = $html_table . "</table>";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>

    </style>
    <title>Inloggen</title>
</head>

<body>
    <h1>Log in</h1>
    <?php

    ?>
</body>

</html>