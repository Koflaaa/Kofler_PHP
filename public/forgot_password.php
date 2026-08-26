<?php
declare(strict_types=1); // Erzwingt strikte Typprüfung

require_once __DIR__ . '/../src/PasswordResetService.php'; // Lädt die Klasse, die Reset-Tokens erzeugt und verwaltet
require_once __DIR__ . '/_layout.php'; // Lädt renderHeader()/renderFooter() für das HTML-Grundgerüst

$service   = new PasswordResetService(); // Erstellt eine Instanz des Passwort-Reset-Dienstes
$resetLink = null; // Platzhalter für den generierten Reset-Link (nur im Demo-Modus sichtbar)
$gesendet  = false; // Merkt sich, ob das Formular bereits abgeschickt wurde

if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Prüft, ob das Formular per POST abgeschickt wurde
    $token    = $service->createToken($_POST['email'] ?? ''); // Erzeugt ein Token für die eingegebene E-Mail (null, falls E-Mail unbekannt)
    $gesendet = true; // Markiert, dass die Anfrage verarbeitet wurde (unabhängig davon, ob die E-Mail existiert)

    // In einer echten Anwendung würde dieser Link per E-Mail versendet.
    // Zu Demonstrationszwecken wird er hier direkt angezeigt.
    if ($token !== null) { // Nur wenn ein gültiges Token erzeugt wurde (E-Mail existiert)
        $resetLink = 'reset_password.php?token=' . urlencode($token); // Baut den Reset-Link inkl. URL-kodiertem Token
    }
}

renderHeader('Passwort vergessen'); // Gibt den HTML-Kopf inkl. Titel aus
?>
<?php if ($gesendet): ?>
    <!-- Formular wurde bereits abgeschickt: Hinweistext anzeigen -->
    <p class="erfolg">
        Falls die E-Mail-Adresse registriert ist, wurde ein Link zum
        Zurücksetzen des Passworts erstellt (Gültigkeit: 30 Minuten).
    </p>
    <!-- Bewusst neutrale Meldung, damit nicht erkennbar ist, ob die E-Mail existiert -->
    <?php if ($resetLink): ?>
        <p><em>Demo-Modus:</em> <a href="<?= htmlspecialchars($resetLink) ?>">Passwort jetzt zurücksetzen</a></p>
        <!-- Nur zu Demozwecken: zeigt den Link direkt an, statt ihn per E-Mail zu senden -->
    <?php endif; ?>
<?php else: ?>
    <!-- Formular noch nicht abgeschickt: Eingabeformular anzeigen -->
    <form method="post" action="forgot_password.php">
        <input type="email" name="email" placeholder="E-Mail-Adresse" required>
        <!-- Eingabefeld für die E-Mail-Adresse, Pflichtfeld -->
        <button type="submit">Link anfordern</button>
        <!-- Sendet das Formular ab -->
    </form>
<?php endif; ?>

<p><a href="login.php">Zurück zum Login</a></p>
<!-- Link zurück zur Login-Seite -->
<?php renderFooter(); ?>
<!-- Gibt den schließenden HTML-Teil aus -->
