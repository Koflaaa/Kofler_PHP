<?php

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Benutzer.php';

$database = new Database();
$pdo = $database->connect();

$benutzer = new Benutzer(
    $pdo,
    'Max',
    'Mustermann',
    'test@example.com',
    'MeinPasswort123'
);

if ($benutzer->speichern()) {
    echo 'Benutzer wurde gespeichert.';
    echo '<br>ID: ' . $benutzer->getId();
}