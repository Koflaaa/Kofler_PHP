<?php
declare(strict_types=1); // Erzwingt strikte Typprüfung (keine automatische Typumwandlung bei Funktionsaufrufen)

require_once __DIR__ . '/../src/PasswordResetService.php'; // Lädt die Klasse, die das Zurücksetzen des Passworts übernimmt
require_once __DIR__ . '/_layout.php'; // Lädt die Funktionen renderHeader() und renderFooter() für das HTML-Grundgerüst

$service = new PasswordResetService(); // Erstellt eine Instanz des Passwort-Reset-Dienstes
$token   = $_GET['token'] ?? $_POST['token'] ?? ''; // Liest das Token aus der URL (Link-Klick) oder aus dem Formular (Absenden); '' falls keins vorhanden
$fehler  = null; // Platzhalter für eine eventuelle Fehlermeldung
$erfolg  = null; // Platzhalter für eine eventuelle Erfolgsmeldung

if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Prüft, ob das Formular per POST abgeschickt wurde
    try {
        $service->resetPassword( // Ruft die Methode auf, die Token prüft und das neue Passwort setzt
            $token, // Das zu prüfende Reset-Token
            $_POST['neues_passwort']     ?? '', // Neues Passwort aus dem Formular, '' falls nicht gesetzt
            $_POST['neues_passwort_wdh'] ?? '' // Wiederholung des neuen Passworts zur Kontrolle
        );
        $erfolg = 'Ihr Passwort wurde neu gesetzt. Sie können sich jetzt anmelden.'; // Erfolgstext für die Anzeige
    } catch (InvalidArgumentException $e) { // Fängt Validierungsfehler ab (z. B. ungültiges Token, zu kurzes Passwort)
        $fehler = $e->getMessage(); // Übernimmt die Fehlermeldung zur Anzeige
    }
}

renderHeader('Neues Passwort setzen'); // Gibt den HTML-Kopf inkl. Titel aus
?>
<?php if ($fehler): ?><p class="fehler"><?= htmlspecialchars($fehler) ?></p><?php endif; ?>
<!-- Zeigt die Fehlermeldung an (falls vorhanden), htmlspecialchars() schützt vor XSS -->
<?php if ($erfolg): ?>
    <!-- Erfolgsfall: Meldung anzeigen und Link zum Login anbieten -->
    <p class="erfolg"><?= htmlspecialchars($erfolg) ?></p>
    <p><a href="login.php">Zum Login</a></p>
<?php else: ?>
    <!-- Kein Erfolg (noch) -> Formular zur Passworteingabe anzeigen -->
    <form method="post" action="reset_password.php">
        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
        <!-- Verstecktes Feld: reicht das Token beim Absenden des Formulars weiter -->
        <input type="password" name="neues_passwort"     placeholder="Neues Passwort (min. 8 Zeichen)" required>
        <!-- Eingabefeld für das neue Passwort, Pflichtfeld -->
        <input type="password" name="neues_passwort_wdh" placeholder="Neues Passwort wiederholen" required>
        <!-- Eingabefeld zur Wiederholung des neuen Passworts, Pflichtfeld -->
        <button type="submit">Passwort setzen</button>
        <!-- Sendet das Formular ab -->
    </form>
<?php endif; ?>
<?php renderFooter(); ?>
<!-- Gibt den schließenden HTML-Teil aus -->
