<?php
declare(strict_types=1); // Erzwingt strikte Typprüfung

require_once __DIR__ . '/../src/Auth.php'; // Lädt die Authentifizierungs-/Session-Logik
require_once __DIR__ . '/../src/UserManager.php'; // Lädt die Klasse für Benutzerverwaltungs-Operationen (Rollen ändern, löschen, anlegen)
require_once __DIR__ . '/_layout.php'; // Lädt renderHeader()/renderFooter() für das HTML-Grundgerüst

$auth    = new Auth(); // Erstellt das Auth-Objekt (startet ggf. die Session)
$admin   = $auth->requireAdmin(); // Erzwingt Login + Admin/Owner-Rechte, bricht sonst mit 403 ab; liefert den eingeloggten Benutzer
$manager = new UserManager($admin); // Erstellt den UserManager im Kontext des eingeloggten Admins (für Berechtigungsprüfungen)

$fehler = null; // Platzhalter für eine eventuelle Fehlermeldung
$erfolg = null; // Platzhalter für eine eventuelle Erfolgsmeldung

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    // Zweig: Formular "Benutzer löschen" wurde abgeschickt
    try {
        $manager->deleteUser((int) $_POST['delete_id']); // Löscht den Benutzer mit der übergebenen ID
        $erfolg = 'Der Benutzer wurde gelöscht.'; // Erfolgsmeldung setzen
    } catch (InvalidArgumentException $e) { // Fängt z. B. "Benutzer nicht gefunden" oder "Admin kann nicht gelöscht werden" ab
        $fehler = $e->getMessage(); // Fehlermeldung übernehmen
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create'])) {
    // Zweig: Formular "Neuen Benutzer anlegen" wurde abgeschickt
    try {
        $auth->register( // Registriert einen neuen Benutzer über die Auth-Klasse
            $_POST['vorname']      ?? '', // Vorname aus dem Formular
            $_POST['nachname']     ?? '', // Nachname aus dem Formular
            $_POST['adresse']      ?? '', // Adresse aus dem Formular
            $_POST['email']        ?? '', // E-Mail aus dem Formular
            $_POST['telefon']      ?? '', // Telefonnummer aus dem Formular
            $_POST['passwort']     ?? '', // Passwort aus dem Formular
            $_POST['passwort_wdh'] ?? '', // Passwort-Wiederholung aus dem Formular
            $_POST['rolle']        ?? User::ROLE_USER // Gewünschte Rolle, Standard: normaler Benutzer
        );
        $erfolg = 'Der Benutzer wurde angelegt.'; // Erfolgsmeldung setzen
    } catch (InvalidArgumentException $e) { // Fängt Validierungsfehler ab (z. B. E-Mail existiert bereits)
        $fehler = $e->getMessage(); // Fehlermeldung übernehmen
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['role_id'], $_POST['neue_rolle'])) {
    // Zweig: Formular "Rolle ändern" wurde abgeschickt
    try {
        $manager->setRole((int) $_POST['role_id'], $_POST['neue_rolle']); // Setzt die neue Rolle für den angegebenen Benutzer
        $erfolg = 'Die Rolle wurde geändert.'; // Erfolgsmeldung setzen
    } catch (InvalidArgumentException $e) { // Fängt Berechtigungs-/Validierungsfehler ab
        $fehler = $e->getMessage(); // Fehlermeldung übernehmen
    }
}

$benutzer = $manager->listUsers(); // Lädt die aktuelle Liste aller Benutzer (nach jeder möglichen Änderung oben)

renderHeader('Benutzerverwaltung'); // Gibt den HTML-Kopf mit Titel aus
?>
<nav><a href="dashboard.php">Zurück zum Dashboard</a></nav>
<!-- Navigationslink zurück zum Dashboard -->

<?php if ($fehler): ?><p class="fehler"><?= htmlspecialchars($fehler) ?></p><?php endif; ?>
<!-- Zeigt eine Fehlermeldung an, falls eine der Aktionen oben fehlgeschlagen ist -->
<?php if ($erfolg): ?><p class="erfolg"><?= htmlspecialchars($erfolg) ?></p><?php endif; ?>
<!-- Zeigt eine Erfolgsmeldung an, falls eine der Aktionen oben erfolgreich war -->

<?php if (count($benutzer) === 0): ?>
    <!-- Keine Benutzer vorhanden -> Hinweistext anzeigen -->
    <p>Es sind keine Benutzer vorhanden.</p>
<?php else: ?>
    <table>
        <tr><th>Name</th><th>E-Mail</th><th>Telefon</th><th>Rolle</th><th>Aktion</th></tr>
        <!-- Tabellenkopf mit den Spaltenüberschriften -->
        <?php foreach ($benutzer as $b): ?>
            <!-- Iteriert über jeden Benutzer und rendert eine Tabellenzeile -->
            <tr>
                <td><?= htmlspecialchars($b->getVollerName()) ?></td>
                <!-- Vollständiger Name des Benutzers -->
                <td><?= htmlspecialchars($b->getEmail()) ?></td>
                <!-- E-Mail-Adresse des Benutzers -->
                <td><?= htmlspecialchars($b->getTelefon()) ?></td>
                <!-- Telefonnummer des Benutzers -->
                <td><?= $b->getRolleLabel() ?></td>
                <!-- Rollenbezeichnung (Owner/Administrator/Benutzer) als lesbarer Text -->
                <td>
                    <?php if ($b->getId() === $admin->getId()): ?>
                        <!-- Zeile entspricht dem gerade eingeloggten Admin: keine Aktionen anbieten -->
                        (Sie)
                    <?php elseif ($b->isOwner()): ?>
                        <!-- Der Owner darf nie verändert werden -->
                        &mdash;
                    <?php elseif ($b->isAdmin()): ?>
                        <!-- Zeile ist ein Administrator -->
                        <?php if ($admin->isOwner()): ?>
                            <!-- Nur der Owner darf einen Admin zurückstufen -->
                            <form method="post" action="admin.php" style="margin:0">
                                <input type="hidden" name="role_id" value="<?= $b->getId() ?>">
                                <!-- ID des Benutzers, dessen Rolle geändert werden soll -->
                                <input type="hidden" name="neue_rolle" value="<?= User::ROLE_USER ?>">
                                <!-- Zielrolle: normaler Benutzer -->
                                <button type="submit">Zum Benutzer zurückstufen</button>
                                <!-- Sendet die Rückstufung ab -->
                            </form>
                        <?php else: ?>
                            <!-- Eingeloggter Admin ist selbst kein Owner -> keine Aktion möglich -->
                            &mdash;
                        <?php endif; ?>
                    <?php else: ?>
                        <!-- Zeile ist ein normaler Benutzer: befördern oder löschen möglich -->
                        <form method="post" action="admin.php" style="margin:0; display:inline">
                            <input type="hidden" name="role_id" value="<?= $b->getId() ?>">
                            <!-- ID des zu befördernden Benutzers -->
                            <input type="hidden" name="neue_rolle" value="<?= User::ROLE_ADMIN ?>">
                            <!-- Zielrolle: Administrator -->
                            <button type="submit">Zum Admin ernennen</button>
                            <!-- Sendet die Beförderung ab -->
                        </form>
                        <form method="post" action="admin.php"
                              onsubmit="return confirm('Diesen Benutzer wirklich löschen?');"
                              style="margin:0; display:inline">
                            <!-- onsubmit zeigt einen Bestätigungsdialog, bevor das Löschen abgeschickt wird -->
                            <input type="hidden" name="delete_id" value="<?= $b->getId() ?>">
                            <!-- ID des zu löschenden Benutzers -->
                            <button type="submit">Löschen</button>
                            <!-- Sendet das Löschen ab -->
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>
<?php renderFooter(); ?>
<!-- Gibt den schließenden HTML-Teil aus -->
