<?php
declare(strict_types=1); // Erzwingt strikte Typprüfung

require_once __DIR__ . '/UserRepository.php'; // Lädt die Klasse für Datenbankzugriffe rund um Benutzer

/**
 * Verwaltungsfunktionen für Administratoren.
 */
class UserManager
{
    private UserRepository $repo; // Repository für alle Benutzer-DB-Zugriffe

    public function __construct(private User $admin)
    // Nimmt den aktuell eingeloggten Admin/Owner entgegen (Constructor-Property-Promotion)
    {
        if (!$admin->hasAdminRights()) { // Prüft, ob der übergebene Benutzer wirklich Admin- oder Owner-Rechte hat
            throw new RuntimeException('Nur Administratoren dürfen die Benutzerverwaltung nutzen.'); // Bricht ab, falls nicht (Schutz vor Fehlbenutzung der Klasse)
        }
        $this->repo = new UserRepository(); // Erstellt das Benutzer-Repository
    }

    /** @return User[] Alle Benutzer (Admins zuerst) */
    public function listUsers(): array
    {
        return $this->repo->findAll(); // Delegiert an das Repository, liefert alle Benutzer sortiert
    }

    /**
     * Ändert die Rolle eines Benutzers (Befördern/Zurückstufen).
     * Befördern dürfen alle Admins; Administratoren zurückstufen
     * darf nur der Owner. Die Owner-Rolle selbst kann nicht
     * vergeben oder entzogen werden.
     *
     * @throws InvalidArgumentException bei fehlender Berechtigung,
     *                                  ungültiger Rolle oder unbekanntem Benutzer
     */
    public function setRole(int $userId, string $rolle): void
    {
        if (!in_array($rolle, [User::ROLE_USER, User::ROLE_ADMIN], true)) { // Nur "benutzer" oder "admin" sind als Zielrolle erlaubt
            throw new InvalidArgumentException('Ungültige Rolle.'); // Bricht bei unerlaubter Zielrolle ab
        }

        $user = $this->repo->findById($userId); // Lädt den Benutzer, dessen Rolle geändert werden soll

        if ($user === null) { // Benutzer mit dieser ID existiert nicht
            throw new InvalidArgumentException('Benutzer nicht gefunden.'); // Bricht ab
        }
        if ($user->isOwner()) { // Ziel ist der Owner-Account
            throw new InvalidArgumentException('Die Rolle des Owners kann nicht geändert werden.'); // Owner-Rolle ist unveränderlich
        }
        if ($user->isAdmin() && $rolle === User::ROLE_USER && !$this->admin->isOwner()) {
            // Ziel ist aktuell Admin, soll zurückgestuft werden, aber der ausführende Benutzer ist nicht der Owner
            throw new InvalidArgumentException('Nur der Owner darf Administratoren zurückstufen.'); // Bricht ab (fehlende Berechtigung)
        }

        $this->repo->updateRole($userId, $rolle); // Speichert die neue Rolle in der Datenbank
    }

    /**
     * Löscht einen normalen Benutzer.
     *
     * @throws InvalidArgumentException wenn der Benutzer nicht existiert
     *                                  oder ein Admin gelöscht werden soll
     */
    public function deleteUser(int $userId): void
    {
        $user = $this->repo->findById($userId); // Lädt den zu löschenden Benutzer

        if ($user === null) { // Benutzer mit dieser ID existiert nicht
            throw new InvalidArgumentException('Benutzer nicht gefunden.'); // Bricht ab
        }
        if ($user->hasAdminRights()) { // Ziel ist Admin oder Owner
            throw new InvalidArgumentException('Administratoren und der Owner können nicht gelöscht werden.'); // Schützt privilegierte Accounts vor Löschung
        }

        $this->repo->delete($userId); // Löscht den (normalen) Benutzer endgültig aus der Datenbank
    }
}
