<?php
declare(strict_types=1); // Erzwingt strikte Typprüfung

require_once __DIR__ . '/../src/Auth.php'; // Lädt die Authentifizierungs-/Session-Logik
require_once __DIR__ . '/_layout.php'; // Lädt renderHeader()/renderFooter() für das HTML-Grundgerüst

$auth    = new Auth(); // Erstellt das Auth-Objekt (startet ggf. die Session)
$fehler  = null; // Platzhalter für eine eventuelle Fehlermeldung
$erfolg  = null; // Platzhalter für eine eventuelle Erfolgsmeldung

if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Prüft, ob das Registrierungsformular abgeschickt wurde
    try {
        $auth->register( // Ruft die Registrierungslogik auf (validiert und legt den Benutzer an)
            $_POST['vorname']      ?? '', // Vorname aus dem Formular
            $_POST['nachname']     ?? '', // Nachname aus dem Formular
            $_POST['adresse']      ?? '', // Adresse aus dem Formular
            $_POST['email']        ?? '', // E-Mail aus dem Formular
            $_POST['telefon']      ?? '', // Telefonnummer aus dem Formular
            $_POST['passwort']     ?? '', // Passwort aus dem Formular
            $_POST['passwort_wdh'] ?? '' // Passwort-Wiederholung aus dem Formular
        ); // Rolle wird nicht übergeben -> Standard "Benutzer" wird intern verwendet
        $erfolg = 'Registrierung erfolgreich! Sie können sich jetzt anmelden.'; // Erfolgstext für die Anzeige
    } catch (InvalidArgumentException $e) { // Fängt Validierungsfehler ab (z. B. E-Mail bereits vergeben, Passwort zu kurz)
        $fehler = $e->getMessage(); // Übernimmt die Fehlermeldung zur Anzeige
    }
}

renderHeader('Registrierung'); // Gibt den HTML-Kopf inkl. Titel aus
?>
<?php if ($fehler): ?><p class="fehler"><?= htmlspecialchars($fehler) ?></p><?php endif; ?>
<!-- Zeigt die Fehlermeldung an (falls vorhanden) -->
<?php if ($erfolg): ?><p class="erfolg"><?= htmlspecialchars($erfolg) ?></p><?php endif; ?>
<!-- Zeigt die Erfolgsmeldung an (falls vorhanden) -->

<form method="post" action="register.php">
    <input type="text"     name="vorname"      placeholder="Vorname" required>
    <!-- Eingabefeld für den Vornamen, Pflichtfeld -->
    <input type="text"     name="nachname"     placeholder="Nachname" required>
    <!-- Eingabefeld für den Nachnamen, Pflichtfeld -->
    <input type="text"     name="adresse"      placeholder="Adresse" required>
    <!-- Eingabefeld für die Adresse, Pflichtfeld -->
    <input type="email"    name="email"        placeholder="E-Mail-Adresse" required>
    <!-- Eingabefeld für die E-Mail-Adresse, Pflichtfeld -->
    <input type="tel"      name="telefon"      placeholder="Telefonnummer" required>
    <!-- Eingabefeld für die Telefonnummer, Pflichtfeld -->
    <input type="password" name="passwort"     placeholder="Passwort (min. 8 Zeichen)" required>
    <!-- Eingabefeld für das Passwort, Pflichtfeld -->
    <input type="password" name="passwort_wdh" placeholder="Passwort wiederholen" required>
    <!-- Eingabefeld zur Wiederholung des Passworts, Pflichtfeld -->
    <button type="submit">Registrieren</button>
    <!-- Sendet das Formular ab -->
</form>

<p>Bereits registriert? <a href="login.php">Zum Login</a></p>
<!-- Link zurück zur Login-Seite -->
<?php renderFooter(); ?>
<!-- Gibt den schließenden HTML-Teil aus -->
