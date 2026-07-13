<?php

class Benutzer
{
    private PDO $pdo;

    private ?int $id;
    private string $vorname;
    private string $nachname;
    private string $email;
    private string $passwort;
    private string $adresse;
    private string $telefonnummer;
    private bool $istAdmin;

    public function __construct(
        PDO $pdo,
        string $vorname = '',
        string $nachname = '',
        string $email = '',
        string $passwort = '',
        string $adresse = '',
        string $telefonnummer = '',
        bool $istAdmin = false,
        ?int $id = null
    ) {
        $this->pdo = $pdo;
        $this->vorname = $vorname;
        $this->nachname = $nachname;
        $this->email = $email;
        $this->passwort = $passwort;
        $this->adresse = $adresse;
        $this->telefonnummer = $telefonnummer;
        $this->istAdmin = $istAdmin;
        $this->id = $id;
    }

    public function speichern(): bool
    {
        $sql = '
            INSERT INTO benutzer (
                vorname,
                nachname,
                email,
                passwort,
                adresse,
                telefonnummer,
                ist_admin
            )
            VALUES (
                :vorname,
                :nachname,
                :email,
                :passwort,
                :adresse,
                :telefonnummer,
                :ist_admin
            )
        ';

        $stmt = $this->pdo->prepare($sql);

        $passwortHash = password_hash(
            $this->passwort,
            PASSWORD_DEFAULT
        );

        $erfolgreich = $stmt->execute([
            'vorname' => $this->vorname,
            'nachname' => $this->nachname,
            'email' => $this->email,
            'passwort' => $passwortHash,
            'adresse' => $this->adresse,
            'telefonnummer' => $this->telefonnummer,
            'ist_admin' => $this->istAdmin ? 1 : 0
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

    public function getVorname(): string
    {
        return $this->vorname;
    }

    public function getNachname(): string
    {
        return $this->nachname;
    }

    public function getName(): string
    {
        return $this->vorname . ' ' . $this->nachname;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getAdresse(): string
    {
        return $this->adresse;
    }

    public function getTelefonnummer(): string
    {
        return $this->telefonnummer;
    }

    public function istAdmin(): bool
    {
        return $this->istAdmin;
    }
}