<?php
declare(strict_types=1);

require_once __DIR__ . '/UserRepository.php';

/**
 * "Passwort vergessen"-Funktion:
 * erzeugt zeitlich begrenzte Reset-Tokens und setzt Passwörter neu.
 */
class PasswordResetService
{
    private const TOKEN_GUELTIGKEIT_MINUTEN = 30;

    private PDO $db;
    private UserRepository $repo;

    public function __construct()
    {
        $this->db   = Database::getConnection();
        $this->repo = new UserRepository();
    }

    /**
     * Erzeugt ein Reset-Token für die angegebene E-Mail-Adresse.
     * Gibt das Klartext-Token zurück (würde in der Praxis per E-Mail
     * versendet) oder null, wenn die E-Mail nicht existiert.
     */
    public function createToken(string $email): ?string
    {
        $user = $this->repo->findByEmail(trim($email));
        if ($user === null) {
            return null;
        }

        $token     = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        $expires   = (new DateTime())
            ->add(new DateInterval('PT' . self::TOKEN_GUELTIGKEIT_MINUTEN . 'M'))
            ->format('Y-m-d H:i:s');

        $stmt = $this->db->prepare(
            'INSERT INTO passwort_reset_tokens (benutzer_id, token_hash, gueltig_bis)
             VALUES (:uid, :hash, :expires)'
        );
        $stmt->execute([
            'uid'     => $user->getId(),
            'hash'    => $tokenHash,
            'expires' => $expires,
        ]);

        return $token;
    }

    /** Prüft, ob ein Token gültig ist, und liefert die zugehörige user_id. */
    private function validateToken(string $token): ?int
    {
        $stmt = $this->db->prepare(
            'SELECT benutzer_id FROM passwort_reset_tokens
             WHERE token_hash = :hash AND verwendet = 0 AND gueltig_bis > NOW()'
        );
        $stmt->execute(['hash' => hash('sha256', $token)]);
        $userId = $stmt->fetchColumn();

        return $userId !== false ? (int) $userId : null;
    }

    /**
     * Setzt mit einem gültigen Token ein neues Passwort.
     *
     * @throws InvalidArgumentException bei ungültigem Token oder Passwort
     */
    public function resetPassword(string $token, string $neuesPasswort, string $neuesPasswortWdh): void
    {
        $userId = $this->validateToken($token);
        if ($userId === null) {
            throw new InvalidArgumentException('Der Link ist ungültig oder abgelaufen.');
        }
        if (strlen($neuesPasswort) < 8) {
            throw new InvalidArgumentException('Das Passwort muss mindestens 8 Zeichen lang sein.');
        }
        if ($neuesPasswort !== $neuesPasswortWdh) {
            throw new InvalidArgumentException('Die Passwörter stimmen nicht überein.');
        }

        $this->repo->updatePassword($userId, password_hash($neuesPasswort, PASSWORD_DEFAULT));

        // Token als verbraucht markieren
        $stmt = $this->db->prepare(
            'UPDATE passwort_reset_tokens SET verwendet = 1 WHERE token_hash = :hash'
        );
        $stmt->execute(['hash' => hash('sha256', $token)]);
    }
}
