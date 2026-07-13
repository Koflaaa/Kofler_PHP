<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/_layout.php';

$auth    = new Auth();
$fehler  = null;
$erfolg  = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $auth->register(
            $_POST['vorname']      ?? '',
            $_POST['nachname']     ?? '',
            $_POST['adresse']      ?? '',
            $_POST['email']        ?? '',
            $_POST['telefon']      ?? '',
            $_POST['passwort']     ?? '',
            $_POST['passwort_wdh'] ?? ''
        );
        $erfolg = 'Registrierung erfolgreich! Sie können sich jetzt anmelden.';
    } catch (InvalidArgumentException $e) {
        $fehler = $e->getMessage();
    }
}

renderHeader('Registrierung');
?>
<?php if ($fehler): ?><p class="fehler"><?= htmlspecialchars($fehler) ?></p><?php endif; ?>
<?php if ($erfolg): ?><p class="erfolg"><?= htmlspecialchars($erfolg) ?></p><?php endif; ?>

<form method="post" action="register.php">
    <input type="text"     name="vorname"      placeholder="Vorname" required>
    <input type="text"     name="nachname"     placeholder="Nachname" required>
    <input type="text"     name="adresse"      placeholder="Adresse" required>
    <input type="email"    name="email"        placeholder="E-Mail-Adresse" required>
    <input type="tel"      name="telefon"      placeholder="Telefonnummer" required>
    <input type="password" name="passwort"     placeholder="Passwort (min. 8 Zeichen)" required>
    <input type="password" name="passwort_wdh" placeholder="Passwort wiederholen" required>
    <button type="submit">Registrieren</button>
</form>

<p>Bereits registriert? <a href="login.php">Zum Login</a></p>
<?php renderFooter(); ?>
