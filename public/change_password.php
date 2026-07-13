<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/_layout.php';

$auth = new Auth();
$auth->requireLogin();

$fehler = null;
$erfolg = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $auth->changePassword(
            $_POST['altes_passwort']     ?? '',
            $_POST['neues_passwort']     ?? '',
            $_POST['neues_passwort_wdh'] ?? ''
        );
        $erfolg = 'Ihr Passwort wurde erfolgreich geändert.';
    } catch (InvalidArgumentException $e) {
        $fehler = $e->getMessage();
    }
}

renderHeader('Passwort ändern');
?>
<nav><a href="dashboard.php">Zurück zum Dashboard</a></nav>

<?php if ($fehler): ?><p class="fehler"><?= htmlspecialchars($fehler) ?></p><?php endif; ?>
<?php if ($erfolg): ?><p class="erfolg"><?= htmlspecialchars($erfolg) ?></p><?php endif; ?>

<form method="post" action="change_password.php">
    <input type="password" name="altes_passwort"     placeholder="Aktuelles Passwort" required>
    <input type="password" name="neues_passwort"     placeholder="Neues Passwort (min. 8 Zeichen)" required>
    <input type="password" name="neues_passwort_wdh" placeholder="Neues Passwort wiederholen" required>
    <button type="submit">Passwort ändern</button>
</form>
<?php renderFooter(); ?>
