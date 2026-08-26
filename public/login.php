<?php
declare(strict_types=1); // Erzwingt strikte Typprüfung

require_once __DIR__ . '/../src/Auth.php'; // Lädt die Authentifizierungs-/Session-Logik
require_once __DIR__ . '/_layout.php'; // Lädt renderHeader()/renderFooter() für das HTML-Grundgerüst

$auth   = new Auth(); // Erstellt das Auth-Objekt (startet ggf. die Session)
$fehler = null; // Platzhalter für eine eventuelle Fehlermeldung

if ($auth->isLoggedIn()) { // Prüft, ob bereits eine gültige Session besteht
    header('Location: dashboard.php'); // Leitet bereits eingeloggte Benutzer direkt zum Dashboard weiter
    exit; // Beendet die Skriptausführung nach dem Redirect
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Prüft, ob das Login-Formular abgeschickt wurde
    $ok = $auth->login($_POST['email'] ?? '', $_POST['passwort'] ?? ''); // Versucht, den Benutzer mit den eingegebenen Daten anzumelden
    if ($ok) { // Login war erfolgreich
        header('Location: dashboard.php'); // Weiterleitung zum Dashboard
        exit; // Beendet die Skriptausführung nach dem Redirect
    }
    $fehler = 'E-Mail-Adresse oder Passwort ist falsch.'; // Login fehlgeschlagen: generische Fehlermeldung (verrät nicht, welches Feld falsch war)
}

renderHeader('Login'); // Gibt den HTML-Kopf inkl. Titel aus
?>
<?php if ($fehler): ?><p class="fehler"><?= htmlspecialchars($fehler) ?></p><?php endif; ?>
<!-- Zeigt die Fehlermeldung an (falls vorhanden) -->

<form method="post" action="login.php">
    <input type="email"    name="email"    placeholder="E-Mail-Adresse" required>
    <!-- Eingabefeld für die E-Mail-Adresse, Pflichtfeld -->
    <input type="password" name="passwort" placeholder="Passwort" required>
    <!-- Eingabefeld für das Passwort, Pflichtfeld -->
    <button type="submit">Anmelden</button>
    <!-- Sendet das Formular ab -->
</form>

<p>
    <a href="register.php">Neu registrieren</a> ·
    <!-- Link zur Registrierungsseite -->
    <a href="forgot_password.php">Passwort vergessen?</a>
    <!-- Link zur "Passwort vergessen"-Seite -->
</p>
<?php renderFooter(); ?>
<!-- Gibt den schließenden HTML-Teil aus -->
