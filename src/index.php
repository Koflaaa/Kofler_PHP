<?php

$pdo = require __DIR__ . '/config/database.php';

$stmt = $pdo->query('SELECT * FROM person');
$personen = $stmt->fetchAll();

foreach ($personen as $person) {
    echo htmlspecialchars($person['name']) . '<br>';
}