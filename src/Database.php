<?php
declare(strict_types=1); // Erzwingt strikte Typprüfung

/**
 * Datenbank-Verbindung als Singleton auf PDO-Basis.
 */
final class Database
// "final" verhindert, dass diese Klasse weiter vererbt wird
{
    private static ?PDO $instance = null; // Hält die einzige PDO-Verbindung der Anwendung (null, solange noch keine erstellt wurde)

    private const HOST    = 'localhost'; // Adresse des Datenbankservers
    private const DBNAME  = 'benutzerverwaltung'; // Name der zu verwendenden Datenbank
    private const USER    = 'root'; // Datenbank-Benutzername
    private const PASS    = ''; // Datenbank-Passwort (hier leer)
    private const CHARSET = 'utf8mb4'; // Zeichensatz für die Verbindung

    private function __construct() {} // Privater Konstruktor: verhindert Instanziierung von außen (erzwingt Singleton-Zugriff über getConnection())
    private function __clone() {} // Privates Klonen: verhindert das Duplizieren der Singleton-Instanz

    public static function getConnection(): PDO
    {
        if (self::$instance === null) { // Nur beim allerersten Aufruf eine neue Verbindung erstellen
            $dsn = sprintf( // Baut den DSN-Verbindungsstring zusammen
                'mysql:host=%s;dbname=%s;charset=%s',
                self::HOST, // Host einsetzen
                self::DBNAME, // Datenbankname einsetzen
                self::CHARSET // Zeichensatz einsetzen
            );

            self::$instance = new PDO($dsn, self::USER, self::PASS, [ // Erstellt die PDO-Verbindung und speichert sie als Singleton
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Fehler werfen Exceptions statt stille Fehler
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Ergebnisse standardmäßig als assoziative Arrays
                PDO::ATTR_EMULATE_PREPARES   => false, // Echte Prepared Statements, sicherer gegen SQL-Injection
            ]);
        }

        return self::$instance; // Gibt die (neu erstellte oder bereits bestehende) Verbindung zurück
    }
}
