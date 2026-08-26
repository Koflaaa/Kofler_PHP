<?php
declare(strict_types=1); // Erzwingt strikte Typprüfung

require_once __DIR__ . '/../src/Auth.php'; // Lädt die Authentifizierungs-/Session-Logik
require_once __DIR__ . '/_layout.php'; // Lädt renderHeader()/renderFooter() für das HTML-Grundgerüst

$auth = new Auth(); // Erstellt das Auth-Objekt (startet ggf. die Session)
$user = $auth->requireLogin(); // Erzwingt Login, leitet sonst zu login.php weiter; liefert den eingeloggten Benutzer

renderHeader('Dashboard'); // Gibt den HTML-Kopf inkl. Titel aus
?>
<nav>
    <a href="change_password.php">Passwort ändern</a>
    <!-- Link zur Passwort-ändern-Seite -->
    <?php if ($user->hasAdminRights()): ?><a href="admin.php">Benutzerverwaltung</a><?php endif; ?>
    <!-- Link zur Benutzerverwaltung, nur sichtbar für Admin/Owner -->
    <a href="logout.php">Abmelden</a>
    <!-- Link zum Ausloggen -->
</nav>

<p>Willkommen, <strong><?= htmlspecialchars($user->getVollerName()) ?></strong>!</p>
<!-- Begrüßung mit dem vollen Namen des eingeloggten Benutzers -->

<table>
    <tr><th>E-Mail</th><td><?= htmlspecialchars($user->getEmail()) ?></td></tr>
    <!-- Zeigt die E-Mail-Adresse des Benutzers -->
    <tr><th>Adresse</th><td><?= htmlspecialchars($user->getAdresse()) ?></td></tr>
    <!-- Zeigt die Adresse des Benutzers -->
    <tr><th>Telefon</th><td><?= htmlspecialchars($user->getTelefon()) ?></td></tr>
    <!-- Zeigt die Telefonnummer des Benutzers -->
    <tr><th>Rolle</th><td><?= $user->getRolleLabel() ?></td></tr>
    <!-- Zeigt die Rolle als lesbaren Text (Owner/Administrator/Benutzer) -->
</table>
<?php renderFooter(); ?>
<!-- Gibt den schließenden HTML-Teil aus -->
