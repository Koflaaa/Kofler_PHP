<?php
declare(strict_types=1);

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/User.php';

/**
 * Kapselt alle Datenbankzugriffe rund um Benutzer (Repository-Pattern).
 */
class UserRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function findByEmail(string $email): ?User
    {
        $stmt = $this->db->prepare('SELECT * FROM benutzer WHERE email = :email');
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch();

        return $row ? User::fromArray($row) : null;
    }

    public function findById(int $id): ?User
    {
        $stmt = $this->db->prepare('SELECT * FROM benutzer WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ? User::fromArray($row) : null;
    }

    /** Liefert den gespeicherten Passwort-Hash zu einer E-Mail. */
    public function getPasswordHash(string $email): ?string
    {
        $stmt = $this->db->prepare('SELECT passwort_hash FROM benutzer WHERE email = :email');
        $stmt->execute(['email' => $email]);
        $hash = $stmt->fetchColumn();

        return $hash !== false ? (string) $hash : null;
    }

    public function emailExists(string $email): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM benutzer WHERE email = :email');
        $stmt->execute(['email' => $email]);

        return (int) $stmt->fetchColumn() > 0;
    }

    public function create(
        string $vorname,
        string $nachname,
        string $adresse,
        string $email,
        string $telefon,
        string $passwordHash,
        string $rolle = User::ROLE_USER
    ): int {
        $stmt = $this->db->prepare(
            'INSERT INTO benutzer (vorname, nachname, adresse, email, telefonnummer, passwort_hash, rolle)
             VALUES (:vorname, :nachname, :adresse, :email, :telefon, :hash, :rolle)'
        );
        $stmt->execute([
            'vorname'  => $vorname,
            'nachname' => $nachname,
            'adresse'  => $adresse,
            'email'    => $email,
            'telefon'  => $telefon,
            'hash'     => $passwordHash,
            'rolle'    => $rolle,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function updateRole(int $userId, string $rolle): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE benutzer SET rolle = :rolle WHERE id = :id'
        );

        return $stmt->execute(['rolle' => $rolle, 'id' => $userId]);
    }

    public function updatePassword(int $userId, string $newHash): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE benutzer SET passwort_hash = :hash WHERE id = :id'
        );

        return $stmt->execute(['hash' => $newHash, 'id' => $userId]);
    }

    public function delete(int $userId): bool
    {
        $stmt = $this->db->prepare('DELETE FROM benutzer WHERE id = :id');

        return $stmt->execute(['id' => $userId]);
    }

    /** @return User[] */
    public function findAll(): array
    {
        $stmt = $this->db->query(
            'SELECT * FROM benutzer ORDER BY rolle, nachname, vorname'
        );

        return array_map(fn (array $row) => User::fromArray($row), $stmt->fetchAll());
    }
}