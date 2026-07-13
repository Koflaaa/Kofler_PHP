<?php
declare(strict_types=1);

require_once __DIR__ . '/UserRepository.php';

/**
 * Zuständig für Registrierung, Login/Logout, Session-Handling
 * und Rollenprüfung.
 */
class Auth
{
    private UserRepository $repo;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->repo = new UserRepository();
    }

    /**
     * Registriert einen neuen Benutzer.
     *
     * @throws InvalidArgumentException bei ungültigen Eingaben
     */
    public function register(
        string $vorname,
        string $nachname,
        string $adresse,
        string $email,
        string $telefon,
        string $passwort,
        string $passwortWdh
    ): int {
        $vorname  = trim($vorname);
        $nachname = trim($nachname);
        $adresse  = trim($adresse);
        $email    = trim($email);
        $telefon  = trim($telefon);

        if ($vorname === '' || $nachname === '' || $adresse === '' || $telefon === '') {
            throw new InvalidArgumentException('Bitte alle Felder ausfüllen.');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Ungültige E-Mail-Adresse.');
        }
        if (strlen($passwort) < 8) {
            throw new InvalidArgumentException('Das Passwort muss mindestens 8 Zeichen lang sein.');
        }
        if ($passwort !== $passwortWdh) {
            throw new InvalidArgumentException('Die Passwörter stimmen nicht überein.');
        }
        if ($this->repo->emailExists($email)) {
            throw new InvalidArgumentException('Diese E-Mail-Adresse ist bereits registriert.');
        }

        $hash = password_hash($passwort, PASSWORD_DEFAULT);

        return $this->repo->create($vorname, $nachname, $adresse, $email, $telefon, $hash);
    }

    /** Meldet einen Benutzer an. Gibt true bei Erfolg zurück. */
    public function login(string $email, string $passwort): bool
    {
        $email = trim($email);
        $hash  = $this->repo->getPasswordHash($email);

        if ($hash === null || !password_verify($passwort, $hash)) {
            return false;
        }

        $user = $this->repo->findByEmail($email);

        // Session-Fixation verhindern
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user->getId();

        // Hash bei Bedarf modernisieren
        if (password_needs_rehash($hash, PASSWORD_DEFAULT)) {
            $this->repo->updatePassword(
                $user->getId(),
                password_hash($passwort, PASSWORD_DEFAULT)
            );
        }

        return true;
    }

    public function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }

    public function isLoggedIn(): bool
    {
        return isset($_SESSION['user_id']);
    }

    public function getCurrentUser(): ?User
    {
        if (!$this->isLoggedIn()) {
            return null;
        }

        return $this->repo->findById((int) $_SESSION['user_id']);
    }

    /** Bricht ab (Redirect), wenn niemand angemeldet ist. */
    public function requireLogin(): User
    {
        $user = $this->getCurrentUser();
        if ($user === null) {
            header('Location: login.php');
            exit;
        }

        return $user;
    }

    /** Bricht ab, wenn der angemeldete Benutzer kein Administrator ist. */
    public function requireAdmin(): User
    {
        $user = $this->requireLogin();
        if (!$user->isAdmin()) {
            http_response_code(403);
            exit('Zugriff verweigert: Administratorrechte erforderlich.');
        }

        return $user;
    }

    /**
     * Ändert das Passwort des angemeldeten Benutzers.
     *
     * @throws InvalidArgumentException bei falschen/ungültigen Eingaben
     */
    public function changePassword(string $altesPasswort, string $neuesPasswort, string $neuesPasswortWdh): void
    {
        $user = $this->requireLogin();
        $hash = $this->repo->getPasswordHash($user->getEmail());

        if (!password_verify($altesPasswort, $hash)) {
            throw new InvalidArgumentException('Das aktuelle Passwort ist falsch.');
        }
        if (strlen($neuesPasswort) < 8) {
            throw new InvalidArgumentException('Das neue Passwort muss mindestens 8 Zeichen lang sein.');
        }
        if ($neuesPasswort !== $neuesPasswortWdh) {
            throw new InvalidArgumentException('Die neuen Passwörter stimmen nicht überein.');
        }

        $this->repo->updatePassword($user->getId(), password_hash($neuesPasswort, PASSWORD_DEFAULT));
    }
}
