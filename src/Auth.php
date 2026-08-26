<?php
declare(strict_types=1); // Erzwingt strikte Typprüfung

require_once __DIR__ . '/UserRepository.php'; // Lädt die Klasse für Datenbankzugriffe rund um Benutzer

/**
 * Zuständig für Registrierung, Login/Logout, Session-Handling
 * und Rollenprüfung.
 */
class Auth
{
    private UserRepository $repo; // Repository für alle Benutzer-DB-Zugriffe

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) { // Prüft, ob noch keine PHP-Session gestartet wurde
            session_start(); // Startet die Session (nötig für $_SESSION-Zugriffe)
        }
        $this->repo = new UserRepository(); // Erstellt das Repository-Objekt für DB-Zugriffe
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
        string $passwortWdh,
        string $rolle = User::ROLE_USER // Standardrolle bei der Registrierung: normaler Benutzer
    ): int {
        if (!in_array($rolle, [User::ROLE_USER, User::ROLE_ADMIN], true)) { // Nur "benutzer" oder "admin" dürfen hier vergeben werden (Owner nicht)
            throw new InvalidArgumentException('Ungültige Rolle.'); // Bricht bei unerlaubter Rolle ab
        }

        $vorname  = trim($vorname); // Entfernt führende/nachfolgende Leerzeichen
        $nachname = trim($nachname); // Entfernt führende/nachfolgende Leerzeichen
        $adresse  = trim($adresse); // Entfernt führende/nachfolgende Leerzeichen
        $email    = trim($email); // Entfernt führende/nachfolgende Leerzeichen
        $telefon  = trim($telefon); // Entfernt führende/nachfolgende Leerzeichen

        if ($vorname === '' || $nachname === '' || $adresse === '' || $telefon === '') { // Prüft, ob Pflichtfelder leer sind
            throw new InvalidArgumentException('Bitte alle Felder ausfüllen.'); // Bricht bei fehlenden Angaben ab
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { // Prüft das E-Mail-Format
            throw new InvalidArgumentException('Ungültige E-Mail-Adresse.'); // Bricht bei ungültiger E-Mail ab
        }
        if (strlen($passwort) < 8) { // Prüft die Mindestlänge des Passworts
            throw new InvalidArgumentException('Das Passwort muss mindestens 8 Zeichen lang sein.'); // Bricht bei zu kurzem Passwort ab
        }
        if ($passwort !== $passwortWdh) { // Vergleicht Passwort und Wiederholung
            throw new InvalidArgumentException('Die Passwörter stimmen nicht überein.'); // Bricht bei Abweichung ab
        }
        if ($this->repo->emailExists($email)) { // Prüft, ob die E-Mail bereits registriert ist
            throw new InvalidArgumentException('Diese E-Mail-Adresse ist bereits registriert.'); // Bricht bei Duplikat ab
        }

        $hash = password_hash($passwort, PASSWORD_DEFAULT); // Erzeugt einen sicheren Passwort-Hash (aktuell bcrypt)

        return $this->repo->create($vorname, $nachname, $adresse, $email, $telefon, $hash, $rolle); // Legt den Benutzer in der DB an, gibt die neue ID zurück
    }

    /** Meldet einen Benutzer an. Gibt true bei Erfolg zurück. */
    public function login(string $email, string $passwort): bool
    {
        $email = trim($email); // Entfernt führende/nachfolgende Leerzeichen
        $hash  = $this->repo->getPasswordHash($email); // Lädt den gespeicherten Passwort-Hash zu dieser E-Mail (null, falls nicht gefunden)

        if ($hash === null || !password_verify($passwort, $hash)) { // Kein Benutzer gefunden ODER Passwort stimmt nicht mit dem Hash überein
            return false; // Login fehlgeschlagen
        }

        $user = $this->repo->findByEmail($email); // Lädt das vollständige Benutzerobjekt

        // Session-Fixation verhindern
        session_regenerate_id(true); // Erzeugt eine neue Session-ID und verwirft die alte (Schutz vor Session-Fixation-Angriffen)
        $_SESSION['user_id'] = $user->getId(); // Speichert die Benutzer-ID in der Session (markiert den Benutzer als eingeloggt)

        // Hash bei Bedarf modernisieren
        if (password_needs_rehash($hash, PASSWORD_DEFAULT)) { // Prüft, ob der Hash mit veralteten Parametern erzeugt wurde
            $this->repo->updatePassword( // Speichert einen neu berechneten, aktuellen Hash
                $user->getId(),
                password_hash($passwort, PASSWORD_DEFAULT) // Neuer Hash mit aktuellen Standard-Parametern
            );
        }

        return true; // Login erfolgreich
    }

    public function logout(): void
    {
        $_SESSION = []; // Leert alle Session-Daten
        if (ini_get('session.use_cookies')) { // Prüft, ob die Session über Cookies verwaltet wird
            $p = session_get_cookie_params(); // Liest die aktuellen Cookie-Parameter (Pfad, Domain etc.)
            setcookie(session_name(), '', time() - 42000, // Setzt das Session-Cookie mit einem Ablaufdatum in der Vergangenheit (löscht es im Browser)
                $p['path'], $p['domain'], $p['secure'], $p['httponly']); // Übernimmt dieselben Cookie-Parameter wie die ursprüngliche Session
        }
        session_destroy(); // Zerstört die Session-Daten serverseitig
    }

    public function isLoggedIn(): bool
    {
        return isset($_SESSION['user_id']); // True, wenn eine Benutzer-ID in der Session gespeichert ist
    }

    public function getCurrentUser(): ?User
    {
        if (!$this->isLoggedIn()) { // Kein eingeloggter Benutzer vorhanden
            return null; // Kein Benutzerobjekt zurückgeben
        }

        return $this->repo->findById((int) $_SESSION['user_id']); // Lädt den Benutzer anhand der in der Session gespeicherten ID
    }

    /** Bricht ab (Redirect), wenn niemand angemeldet ist. */
    public function requireLogin(): User
    {
        $user = $this->getCurrentUser(); // Versucht, den aktuell eingeloggten Benutzer zu laden
        if ($user === null) { // Niemand ist eingeloggt
            header('Location: login.php'); // Leitet zur Login-Seite weiter
            exit; // Beendet die Skriptausführung sofort (verhindert weiteren geschützten Code)
        }

        return $user; // Gibt den eingeloggten Benutzer zurück
    }

    /** Bricht ab, wenn der angemeldete Benutzer kein Administrator/Owner ist. */
    public function requireAdmin(): User
    {
        $user = $this->requireLogin(); // Erzwingt zunächst, dass überhaupt ein Login besteht
        if (!$user->hasAdminRights()) { // Prüft, ob der Benutzer Admin- oder Owner-Rechte besitzt
            http_response_code(403); // Setzt den HTTP-Statuscode "Forbidden"
            exit('Zugriff verweigert: Administratorrechte erforderlich.'); // Gibt eine Fehlermeldung aus und beendet das Skript
        }

        return $user; // Gibt den Admin/Owner-Benutzer zurück
    }

    /**
     * Ändert das Passwort des angemeldeten Benutzers.
     *
     * @throws InvalidArgumentException bei falschen/ungültigen Eingaben
     */
    public function changePassword(string $altesPasswort, string $neuesPasswort, string $neuesPasswortWdh): void
    {
        $user = $this->requireLogin(); // Stellt sicher, dass ein Benutzer eingeloggt ist, und lädt ihn
        $hash = $this->repo->getPasswordHash($user->getEmail()); // Lädt den aktuell gespeicherten Passwort-Hash

        if (!password_verify($altesPasswort, $hash)) { // Prüft, ob das eingegebene alte Passwort korrekt ist
            throw new InvalidArgumentException('Das aktuelle Passwort ist falsch.'); // Bricht bei falschem altem Passwort ab
        }
        if (strlen($neuesPasswort) < 8) { // Prüft die Mindestlänge des neuen Passworts
            throw new InvalidArgumentException('Das neue Passwort muss mindestens 8 Zeichen lang sein.'); // Bricht bei zu kurzem Passwort ab
        }
        if ($neuesPasswort !== $neuesPasswortWdh) { // Vergleicht neues Passwort und Wiederholung
            throw new InvalidArgumentException('Die neuen Passwörter stimmen nicht überein.'); // Bricht bei Abweichung ab
        }

        $this->repo->updatePassword($user->getId(), password_hash($neuesPasswort, PASSWORD_DEFAULT)); // Speichert das neue, gehashte Passwort
    }
}
