<?php
declare(strict_types=1); // Erzwingt strikte Typprüfung

/**
 * Repräsentiert einen Benutzer (Entity-Klasse).
 */
class User
{
    public const ROLE_USER  = 'benutzer'; // Konstante für die Rolle "normaler Benutzer"
    public const ROLE_ADMIN = 'admin'; // Konstante für die Rolle "Administrator"
    public const ROLE_OWNER = 'owner'; // Konstante für die Rolle "Owner" (höchste Berechtigungsstufe)

    public function __construct(
        private int $id, // Eindeutige Benutzer-ID
        private string $vorname, // Vorname
        private string $nachname, // Nachname
        private string $adresse, // Adresse
        private string $email, // E-Mail-Adresse
        private string $telefonnummer, // Telefonnummer
        private string $rolle // Aktuelle Rolle (benutzer/admin/owner)
    ) {} // Constructor-Property-Promotion: Parameter werden direkt als private Eigenschaften übernommen

    public static function fromArray(array $row): self
    // Fabrikmethode: erzeugt ein User-Objekt aus einer DB-Zeile (assoziatives Array)
    {
        return new self( // Erstellt eine neue User-Instanz aus den Array-Werten
            (int) $row['id'], // Wandelt die ID explizit in int um
            $row['vorname'], // Vorname aus der DB-Zeile
            $row['nachname'], // Nachname aus der DB-Zeile
            $row['adresse'], // Adresse aus der DB-Zeile
            $row['email'], // E-Mail aus der DB-Zeile
            $row['telefonnummer'], // Telefonnummer aus der DB-Zeile
            $row['rolle'] // Rolle aus der DB-Zeile
        );
    }

    public function getId(): int          { return $this->id; } // Gibt die Benutzer-ID zurück
    public function getVorname(): string  { return $this->vorname; } // Gibt den Vornamen zurück
    public function getNachname(): string { return $this->nachname; } // Gibt den Nachnamen zurück
    public function getAdresse(): string  { return $this->adresse; } // Gibt die Adresse zurück
    public function getEmail(): string    { return $this->email; } // Gibt die E-Mail-Adresse zurück
    public function getTelefon(): string  { return $this->telefonnummer; } // Gibt die Telefonnummer zurück
    public function getRolle(): string    { return $this->rolle; } // Gibt den internen Rollen-Code zurück

    public function getVollerName(): string
    {
        return $this->vorname . ' ' . $this->nachname; // Verkettet Vor- und Nachname zu einem Anzeigenamen
    }

    public function isAdmin(): bool
    {
        return $this->rolle === self::ROLE_ADMIN; // True, wenn die Rolle exakt "admin" ist
    }

    public function isOwner(): bool
    {
        return $this->rolle === self::ROLE_OWNER; // True, wenn die Rolle exakt "owner" ist
    }

    /** Admin ODER Owner — für Zugriff auf die Benutzerverwaltung. */
    public function hasAdminRights(): bool
    {
        return $this->isAdmin() || $this->isOwner(); // True, wenn Admin- oder Owner-Rolle vorliegt
    }

    public function getRolleLabel(): string
    {
        return match ($this->rolle) { // Wandelt den internen Rollen-Code in einen lesbaren Anzeigetext um
            self::ROLE_OWNER => 'Owner', // "owner" -> "Owner"
            self::ROLE_ADMIN => 'Administrator', // "admin" -> "Administrator"
            default          => 'Benutzer', // alles andere (normale Benutzer) -> "Benutzer"
        };
    }
}
