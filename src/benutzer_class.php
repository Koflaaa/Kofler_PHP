<?php

class Benutzer
{
    private PDO $pdo;

    private ?int $id;
    private string $vorname;
    private string $nachname;
    private string $email;
    private string $passwort;
    private string $addresse;
    private string $telefonnummer;
    private bool $ist_admin;

    public function __construct(
        PDO $pdo,
        string $vorname = '',
        string $nachname = '',
        string $email = '',
        string $passwort = '',
        ?int $id = null,
        string $addresse = '',
        string $telefonnummer = '',
        bool $ist_admin = false
    ) {
        $this->pdo = $pdo;
        $this->vorname = $vorname;
        $this->nachname = $nachname;
        $this->email = $email;
        $this->passwort = $passwort;
        $this->id = $id;
        $this->addresse = $addresse;
        $this->telefonnummer = $telefonnummer;
        $this->ist_admin = $ist_admin;
    }

    public function speichern(): bool
    {
        $sql = '
            INSERT INTO benutzer (
                vorname,
                nachname,
                email,
                passwort
            )
            VALUES (
                :vorname,
                :nachname,
                :email,
                :passwort
            )
        ';

        $stmt = $this->pdo->prepare($sql);

        $passwortHash = password_hash(
            $this->passwort,
            PASSWORD_DEFAULT
        );

        $erfolgreich = $stmt->execute([
            'benutzername' => $this->benutzername,
            'email' => $this->email,
            'passwort' => $passwortHash
        ]);

        if ($erfolgreich) {
            $this->id = (int) $this->pdo->lastInsertId();
        }

        return $erfolgreich;
    }

    public function getId(): ?int
    {
        return $this->id;
        
    }

    public function getName(): string
    {
        return $this->vorname . ' ' . $this->nachname;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getAddresse(): string
    {
        return $this->addresse;
    }

    public function getTelefonnummer(): string
    {
        return $this->telefonnummer;
    }

    public function isIstAdmin(): bool
    {
        return $this->ist_admin;
    }
}