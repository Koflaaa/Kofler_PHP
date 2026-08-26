<?php
declare(strict_types=1); // Erzwingt strikte Typprüfung

require_once __DIR__ . '/../src/Auth.php'; // Lädt die Authentifizierungs-/Session-Logik
require_once __DIR__ . '/_layout.php'; // Lädt renderHeader()/renderFooter() für das HTML-Grundgerüst

$auth = new Auth(); // Erstellt das Auth-Objekt (startet ggf. die Session)
$auth->requireLogin(); // Erzwingt, dass ein Benutzer eingeloggt ist; leitet sonst zu login.php weiter

$fehler = null; // Platzhalter für eine eventuelle Fehlermeldung
$erfolg = null; // Platzhalter für eine eventuelle Erfolgsmeldung

if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Prüft, ob das Formular per POST abgeschickt wurde
    try {
        $auth->changePassword( // Ruft die Methode auf, die altes Passwort prüft und neues setzt
            $_POST['altes_passwort']     ?? '', // Aktuelles Passwort zur Verifikation
            $_POST['neues_passwort']     ?? '', // Gewünschtes neues Passwort
            $_POST['neues_passwort_wdh'] ?? '' // Wiederholung des neuen Passworts zur Kontrolle
        );
        $erfolg = 'Ihr Passwort wurde erfolgreich geändert.'; // Erfolgstext für die Anzeige
    } catch (InvalidArgumentException $e) { // Fängt Validierungsfehler ab (falsches altes Passwort, zu kurz, ungleich)
        $fehler = $e->getMessage(); // Übernimmt die Fehlermeldung zur Anzeige
    }
}

renderHeader('Passwort ändern'); // Gibt den HTML-Kopf inkl. Titel aus
?>
<nav><a href="dashboard.php">Zurück zum Dashboard</a></nav>
<!-- Navigationslink zurück zum Dashboard -->

<?php if ($fehler): ?><p class="fehler"><?= htmlspecialchars($fehler) ?></p><?php endif; ?>
<!-- Zeigt die Fehlermeldung an (falls vorhanden) -->
<?php if ($erfolg): ?><p class="erfolg"><?= htmlspecialchars($erfolg) ?></p><?php endif; ?>
<!-- Zeigt die Erfolgsmeldung an (falls vorhanden) -->

<form method="post" action="change_password.php">
    <input type="password" name="altes_passwort"     placeholder="Aktuelles Passwort" required>
    <!-- Eingabefeld für das aktuelle Passwort, Pflichtfeld -->
    <input type="password" name="neues_passwort"     placeholder="Neues Passwort (min. 8 Zeichen)" required>
    <!-- Eingabefeld für das neue Passwort, Pflichtfeld -->
    <input type="password" name="neues_passwort_wdh" placeholder="Neues Passwort wiederholen" required>
    <!-- Eingabefeld zur Wiederholung des neuen Passworts, Pflichtfeld -->
    <button type="submit">Passwort ändern</button>
    <!-- Sendet das Formular ab -->
</form>
<?php renderFooter(); ?>
<!-- Gibt den schließenden HTML-Teil aus -->
