<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/PasswordResetService.php';
require_once __DIR__ . '/_layout.php';

$service   = new PasswordResetService();
$resetLink = null;
$gesendet  = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token    = $service->createToken($_POST['email'] ?? '');
    $gesendet = true;

    // In einer echten Anwendung würde dieser Link per E-Mail versendet.
    // Zu Demonstrationszwecken wird er hier direkt angezeigt.
    if ($token !== null) {
        $resetLink = 'reset_password.php?token=' . urlencode($token);
    }
}

renderHeader('Passwort vergessen');
?>
<?php if ($gesendet): ?>
    <p class="erfolg">
        Falls die E-Mail-Adresse registriert ist, wurde ein Link zum
        Zurücksetzen des Passworts erstellt (Gültigkeit: 30 Minuten).
    </p>
    <?php if ($resetLink): ?>
        <p><em>Demo-Modus:</em> <a href="<?= htmlspecialchars($resetLink) ?>">Passwort jetzt zurücksetzen</a></p>
    <?php endif; ?>
<?php else: ?>
    <form method="post" action="forgot_password.php">
        <input type="email" name="email" placeholder="E-Mail-Adresse" required>
        <button type="submit">Link anfordern</button>
    </form>
<?php endif; ?>

<p><a href="login.php">Zurück zum Login</a></p>
<?php renderFooter(); ?>
