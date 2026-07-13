<?php
declare(strict_types=1);

/**
 * Repräsentiert einen Benutzer (Entity-Klasse).
 */
class User
{
    public const ROLE_USER  = 'user';
    public const ROLE_ADMIN = 'admin';

    public function __construct(
        private int $id,
        private string $vorname,
        private string $nachname,
        private string $adresse,
        private string $email,
        private string $telefon,
        private string $rolle
    ) {}

    public static function fromArray(array $row): self
    {
        return new self(
            (int) $row['id'],
            $row['vorname'],
            $row['nachname'],
            $row['adresse'],
            $row['email'],
            $row['telefon'],
            $row['rolle']
        );
    }

    public function getId(): int          { return $this->id; }
    public function getVorname(): string  { return $this->vorname; }
    public function getNachname(): string { return $this->nachname; }
    public function getAdresse(): string  { return $this->adresse; }
    public function getEmail(): string    { return $this->email; }
    public function getTelefon(): string  { return $this->telefon; }
    public function getRolle(): string    { return $this->rolle; }

    public function getVollerName(): string
    {
        return $this->vorname . ' ' . $this->nachname;
    }

    public function isAdmin(): bool
    {
        return $this->rolle === self::ROLE_ADMIN;
    }
}
