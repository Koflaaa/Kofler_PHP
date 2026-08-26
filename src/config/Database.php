<?php
// Hinweis: Diese Datei gibt direkt eine PDO-Verbindung zurück, sobald sie per require/include eingebunden wird.

$host = '127.0.0.1'; // Adresse des Datenbankservers
$port = 3306; // Port des Datenbankservers (MySQL/MariaDB-Standardport)
$dbname = 'benutzerverwaltung'; // Name der zu verwendenden Datenbank
$username = 'root'; // Datenbank-Benutzername
$password = 'change_me_root'; // Datenbank-Passwort (Platzhalter, sollte vor Produktivbetrieb geändert werden)

try {
    return new PDO( // Baut eine neue PDO-Datenbankverbindung auf und gibt sie zurück
        "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4", // DSN: Treiber, Host, Port, Datenbankname, Zeichensatz
        $username, // Benutzername für die Verbindung
        $password, // Passwort für die Verbindung
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Fehler werfen Exceptions statt stille Fehler/Warnungen
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Abfrageergebnisse standardmäßig als assoziative Arrays liefern
            PDO::ATTR_EMULATE_PREPARES => false, // Echte (server-seitige) Prepared Statements statt PHP-emulierter, sicherer gegen SQL-Injection
        ]
    );
} catch (PDOException $error) { // Fängt Verbindungsfehler ab (z. B. falsches Passwort, Server nicht erreichbar)
    die('Datenbankverbindung fehlgeschlagen: ' . $error->getMessage()); // Bricht die Ausführung mit einer Fehlermeldung ab
}
