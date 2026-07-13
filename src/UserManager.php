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
        if (!$admin->isAdmin()) {
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
        if ($user->isAdmin()) {
            throw new InvalidArgumentException('Administratoren können nicht gelöscht werden.');
        }

        $this->repo->delete($userId);
    }
}
