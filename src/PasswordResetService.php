<?php
declare(strict_types=1); // Erzwingt strikte Typprüfung

require_once __DIR__ . '/UserRepository.php'; // Lädt die Klasse für Datenbankzugriffe rund um Benutzer

/**
 * "Passwort vergessen"-Funktion:
 * erzeugt zeitlich begrenzte Reset-Tokens und setzt Passwörter neu.
 */
class PasswordResetService
{
    private const TOKEN_GUELTIGKEIT_MINUTEN = 30; // Anzahl Minuten, die ein Reset-Token gültig bleibt

    private PDO $db; // Direkte Datenbankverbindung für die Token-Tabelle
    private UserRepository $repo; // Repository für Benutzerzugriffe (z. B. Passwort-Update)

    public function __construct()
    {
        $this->db   = Database::getConnection(); // Holt die (Singleton-)Datenbankverbindung
        $this->repo = new UserRepository(); // Erstellt das Benutzer-Repository
    }

    /**
     * Erzeugt ein Reset-Token für die angegebene E-Mail-Adresse.
     * Gibt das Klartext-Token zurück (würde in der Praxis per E-Mail
     * versendet) oder null, wenn die E-Mail nicht existiert.
     */
    public function createToken(string $email): ?string
    {
        $user = $this->repo->findByEmail(trim($email)); // Sucht den Benutzer anhand der E-Mail-Adresse
        if ($user === null) { // Kein Benutzer mit dieser E-Mail gefunden
            return null; // Kein Token erzeugen (verrät aber nach außen nicht, ob die E-Mail existiert)
        }

        $token     = bin2hex(random_bytes(32)); // Erzeugt ein kryptografisch sicheres, zufälliges Klartext-Token (64 Hex-Zeichen)
        $tokenHash = hash('sha256', $token); // Berechnet den SHA-256-Hash des Tokens (nur der Hash wird gespeichert, nicht das Klartext-Token)
        $expires   = (new DateTime()) // Aktueller Zeitpunkt
            ->add(new DateInterval('PT' . self::TOKEN_GUELTIGKEIT_MINUTEN . 'M')) // Addiert die Gültigkeitsdauer in Minuten
            ->format('Y-m-d H:i:s'); // Formatiert als MySQL-DATETIME-String

        $stmt = $this->db->prepare( // Bereitet ein sicheres (parametrisiertes) INSERT-Statement vor
            'INSERT INTO passwort_reset_tokens (benutzer_id, token_hash, gueltig_bis)
             VALUES (:uid, :hash, :expires)'
        );
        $stmt->execute([ // Führt das Statement mit den konkreten Werten aus
            'uid'     => $user->getId(), // ID des betroffenen Benutzers
            'hash'    => $tokenHash, // Gehashtes Token
            'expires' => $expires, // Ablaufzeitpunkt
        ]);

        return $token; // Gibt das Klartext-Token zurück (z. B. um es in einen Link einzubauen)
    }

    /** Prüft, ob ein Token gültig ist, und liefert die zugehörige user_id. */
    private function validateToken(string $token): ?int
    {
        $stmt = $this->db->prepare( // Bereitet ein sicheres SELECT-Statement vor
            'SELECT benutzer_id FROM passwort_reset_tokens
             WHERE token_hash = :hash AND verwendet = 0 AND gueltig_bis > NOW()'
            // Bedingungen: Hash muss übereinstimmen, Token darf noch nicht verwendet und nicht abgelaufen sein
        );
        $stmt->execute(['hash' => hash('sha256', $token)]); // Sucht anhand des Hashes des übergebenen Klartext-Tokens
        $userId = $stmt->fetchColumn(); // Liest die gefundene benutzer_id (false, wenn kein Treffer)

        return $userId !== false ? (int) $userId : null; // Gibt die ID als int zurück oder null, wenn ungültig/abgelaufen
    }

    /**
     * Setzt mit einem gültigen Token ein neues Passwort.
     *
     * @throws InvalidArgumentException bei ungültigem Token oder Passwort
     */
    public function resetPassword(string $token, string $neuesPasswort, string $neuesPasswortWdh): void
    {
        $userId = $this->validateToken($token); // Prüft das Token und ermittelt die zugehörige Benutzer-ID
        if ($userId === null) { // Token ungültig, abgelaufen oder bereits verwendet
            throw new InvalidArgumentException('Der Link ist ungültig oder abgelaufen.'); // Bricht mit Fehlermeldung ab
        }
        if (strlen($neuesPasswort) < 8) { // Prüft die Mindestlänge des neuen Passworts
            throw new InvalidArgumentException('Das Passwort muss mindestens 8 Zeichen lang sein.'); // Bricht bei zu kurzem Passwort ab
        }
        if ($neuesPasswort !== $neuesPasswortWdh) { // Vergleicht neues Passwort und Wiederholung
            throw new InvalidArgumentException('Die Passwörter stimmen nicht überein.'); // Bricht bei Abweichung ab
        }

        $this->repo->updatePassword($userId, password_hash($neuesPasswort, PASSWORD_DEFAULT)); // Speichert das neue, gehashte Passwort

        // Token als verbraucht markieren
        $stmt = $this->db->prepare( // Bereitet ein sicheres UPDATE-Statement vor
            'UPDATE passwort_reset_tokens SET verwendet = 1 WHERE token_hash = :hash'
        );
        $stmt->execute(['hash' => hash('sha256', $token)]); // Markiert das Token als verwendet, damit es nicht erneut genutzt werden kann
    }
}
