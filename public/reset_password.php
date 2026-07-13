<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/PasswordResetService.php';
require_once __DIR__ . '/_layout.php';

$service = new PasswordResetService();
$token   = $_GET['token'] ?? $_POST['token'] ?? '';
$fehler  = null;
$erfolg  = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $service->resetPassword(
            $token,
            $_POST['neues_passwort']     ?? '',
            $_POST['neues_passwort_wdh'] ?? ''
        );
        $erfolg = 'Ihr Passwort wurde neu gesetzt. Sie können sich jetzt anmelden.';
    } catch (InvalidArgumentException $e) {
        $fehler = $e->getMessage();
    }
}

renderHeader('Neues Passwort setzen');
?>
<?php if ($fehler): ?><p class="fehler"><?= htmlspecialchars($fehler) ?></p><?php endif; ?>
<?php if ($erfolg): ?>
    <p class="erfolg"><?= htmlspecialchars($erfolg) ?></p>
    <p><a href="login.php">Zum Login</a></p>
<?php else: ?>
    <form method="post" action="reset_password.php">
        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
        <input type="password" name="neues_passwort"     placeholder="Neues Passwort (min. 8 Zeichen)" required>
        <input type="password" name="neues_passwort_wdh" placeholder="Neues Passwort wiederholen" required>
        <button type="submit">Passwort setzen</button>
    </form>
<?php endif; ?>
<?php renderFooter(); ?>
