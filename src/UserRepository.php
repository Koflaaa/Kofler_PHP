<?php
declare(strict_types=1); // Erzwingt strikte Typprüfung

require_once __DIR__ . '/Database.php'; // Lädt die Database-Singleton-Klasse
require_once __DIR__ . '/User.php'; // Lädt die User-Entity-Klasse

/**
 * Kapselt alle Datenbankzugriffe rund um Benutzer (Repository-Pattern).
 */
class UserRepository
{
    private PDO $db; // Datenbankverbindung für alle Abfragen dieser Klasse

    public function __construct()
    {
        $this->db = Database::getConnection(); // Holt die (Singleton-)Datenbankverbindung
    }

    public function findByEmail(string $email): ?User
    {
        $stmt = $this->db->prepare('SELECT * FROM benutzer WHERE email = :email'); // Bereitet ein sicheres, parametrisiertes SELECT vor
        $stmt->execute(['email' => $email]); // Führt die Abfrage mit der konkreten E-Mail aus
        $row = $stmt->fetch(); // Holt die erste (und einzige, da email UNIQUE ist) Ergebniszeile, false falls keine gefunden

        return $row ? User::fromArray($row) : null; // Wandelt die Zeile in ein User-Objekt um, oder null bei keinem Treffer
    }

    public function findById(int $id): ?User
    {
        $stmt = $this->db->prepare('SELECT * FROM benutzer WHERE id = :id'); // Bereitet ein sicheres SELECT nach ID vor
        $stmt->execute(['id' => $id]); // Führt die Abfrage mit der konkreten ID aus
        $row = $stmt->fetch(); // Holt die Ergebniszeile, false falls keine gefunden

        return $row ? User::fromArray($row) : null; // Wandelt die Zeile in ein User-Objekt um, oder null bei keinem Treffer
    }

    /** Liefert den gespeicherten Passwort-Hash zu einer E-Mail. */
    public function getPasswordHash(string $email): ?string
    {
        $stmt = $this->db->prepare('SELECT passwort_hash FROM benutzer WHERE email = :email'); // Bereitet ein SELECT nur der Hash-Spalte vor
        $stmt->execute(['email' => $email]); // Führt die Abfrage mit der konkreten E-Mail aus
        $hash = $stmt->fetchColumn(); // Holt den Wert der ersten Spalte, false falls kein Treffer

        return $hash !== false ? (string) $hash : null; // Gibt den Hash als String zurück, oder null wenn nicht gefunden
    }

    public function emailExists(string $email): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM benutzer WHERE email = :email'); // Zählt Zeilen mit dieser E-Mail
        $stmt->execute(['email' => $email]); // Führt die Zählung aus

        return (int) $stmt->fetchColumn() > 0; // True, wenn mindestens ein Treffer existiert
    }

    public function create(
        string $vorname,
        string $nachname,
        string $adresse,
        string $email,
        string $telefon,
        string $passwordHash,
        string $rolle = User::ROLE_USER // Standardrolle, falls keine übergeben wird
    ): int {
        $stmt = $this->db->prepare( // Bereitet ein sicheres INSERT-Statement vor
            'INSERT INTO benutzer (vorname, nachname, adresse, email, telefonnummer, passwort_hash, rolle)
             VALUES (:vorname, :nachname, :adresse, :email, :telefon, :hash, :rolle)'
        );
        $stmt->execute([ // Führt das Statement mit den konkreten Werten aus
            'vorname'  => $vorname, // Vorname einsetzen
            'nachname' => $nachname, // Nachname einsetzen
            'adresse'  => $adresse, // Adresse einsetzen
            'email'    => $email, // E-Mail einsetzen
            'telefon'  => $telefon, // Telefonnummer einsetzen
            'hash'     => $passwordHash, // Passwort-Hash einsetzen
            'rolle'    => $rolle, // Rolle einsetzen
        ]);

        return (int) $this->db->lastInsertId(); // Gibt die automatisch vergebene ID des neuen Datensatzes zurück
    }

    public function updateRole(int $userId, string $rolle): bool
    {
        $stmt = $this->db->prepare( // Bereitet ein sicheres UPDATE-Statement vor
            'UPDATE benutzer SET rolle = :rolle WHERE id = :id'
        );

        return $stmt->execute(['rolle' => $rolle, 'id' => $userId]); // Führt das Update aus, gibt true/false für Erfolg zurück
    }

    public function updatePassword(int $userId, string $newHash): bool
    {
        $stmt = $this->db->prepare( // Bereitet ein sicheres UPDATE-Statement vor
            'UPDATE benutzer SET passwort_hash = :hash WHERE id = :id'
        );

        return $stmt->execute(['hash' => $newHash, 'id' => $userId]); // Führt das Update aus, gibt true/false für Erfolg zurück
    }

    public function delete(int $userId): bool
    {
        $stmt = $this->db->prepare('DELETE FROM benutzer WHERE id = :id'); // Bereitet ein sicheres DELETE-Statement vor

        return $stmt->execute(['id' => $userId]); // Führt das Löschen aus, gibt true/false für Erfolg zurück
    }

    /** @return User[] */
    public function findAll(): array
    {
        $stmt = $this->db->query( // Führt eine einfache Abfrage ohne Parameter aus (keine externen Eingaben, daher kein prepare() nötig)
            'SELECT * FROM benutzer ORDER BY rolle, nachname, vorname'
            // Sortiert nach Rolle (alphabetisch: admin, benutzer, owner), dann Nach- und Vorname
        );

        return array_map(fn (array $row) => User::fromArray($row), $stmt->fetchAll()); // Wandelt jede DB-Zeile in ein User-Objekt um
    }
}
