<?php
declare(strict_types=1); // Erzwingt strikte Typprüfung

require_once __DIR__ . '/Database.php'; // Lädt die Database-Klasse
require_once __DIR__ . '/Benutzer.php'; // Lädt eine Klasse "Benutzer" (Hinweis: diese Datei existiert aktuell nicht im Projekt, das Skript würde daher mit einem Fehler abbrechen)

$database = new Database(); // Erstellt ein Database-Objekt (Hinweis: Database.php definiert kein öffentliches __construct(), Aufruf würde derzeit fehlschlagen)
$pdo = $database->connect(); // Versucht, eine PDO-Verbindung über eine Methode connect() zu holen (Hinweis: diese Methode existiert nicht in Database.php, dort heißt sie getConnection())

$benutzer = new Benutzer( // Erstellt ein Objekt der (fehlenden) Klasse Benutzer mit Testdaten
    $pdo, // PDO-Verbindung
    'Max', // Vorname (Testwert)
    'Mustermann', // Nachname (Testwert)
    'test@example.com', // E-Mail (Testwert)
    'MeinPasswort123' // Passwort im Klartext (Testwert, würde intern gehasht werden müssen)
);

if ($benutzer->speichern()) { // Ruft eine Methode speichern() auf, die den Benutzer in die DB schreiben soll
    echo 'Benutzer wurde gespeichert.'; // Erfolgstext ausgeben
    echo '<br>ID: ' . $benutzer->getId(); // Gibt die neu vergebene ID aus
}
