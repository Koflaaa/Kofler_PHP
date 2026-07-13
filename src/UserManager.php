<?php
declare(strict_types=1);

require_once __DIR__ . '/UserRepository.php';

/**
 * Verwaltungsfunktionen für Administratoren.
 */
class UserManager
{
    private UserRepository $repo;

    public function __construct(private User $admin)
    {
        if (!$admin->hasAdminRights()) {
            throw new RuntimeException('Nur Administratoren dürfen die Benutzerverwaltung nutzen.');
        }
        $this->repo = new UserRepository();
    }

    /** @return User[] Alle Benutzer (Admins zuerst) */
    public function listUsers(): array
    {
        return $this->repo->findAll();
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
        if (!in_array($rolle, [User::ROLE_USER, User::ROLE_ADMIN], true)) {
            throw new InvalidArgumentException('Ungültige Rolle.');
        }

        $user = $this->repo->findById($userId);

        if ($user === null) {
            throw new InvalidArgumentException('Benutzer nicht gefunden.');
        }
        if ($user->isOwner()) {
            throw new InvalidArgumentException('Die Rolle des Owners kann nicht geändert werden.');
        }
        if ($user->isAdmin() && $rolle === User::ROLE_USER && !$this->admin->isOwner()) {
            throw new InvalidArgumentException('Nur der Owner darf Administratoren zurückstufen.');
        }

        $this->repo->updateRole($userId, $rolle);
    }

    /**
     * Löscht einen normalen Benutzer.
     *
     * @throws InvalidArgumentException wenn der Benutzer nicht existiert
     *                                  oder ein Admin gelöscht werden soll
     */
    public function deleteUser(int $userId): void
    {
        $user = $this->repo->findById($userId);

        if ($user === null) {
            throw new InvalidArgumentException('Benutzer nicht gefunden.');
        }
        if ($user->hasAdminRights()) {
            throw new InvalidArgumentException('Administratoren und der Owner können nicht gelöscht werden.');
        }

        $this->repo->delete($userId);
    }
}
