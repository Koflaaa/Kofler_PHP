<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/_layout.php';

$auth   = new Auth();
$fehler = null;

if ($auth->isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ok = $auth->login($_POST['email'] ?? '', $_POST['passwort'] ?? '');
    if ($ok) {
        header('Location: dashboard.php');
        exit;
    }
    $fehler = 'E-Mail-Adresse oder Passwort ist falsch.';
}

renderHeader('Login');
?>
<?php if ($fehler): ?><p class="fehler"><?= htmlspecialchars($fehler) ?></p><?php endif; ?>

<form method="post" action="login.php">
    <input type="email"    name="email"    placeholder="E-Mail-Adresse" required>
    <input type="password" name="passwort" placeholder="Passwort" required>
    <button type="submit">Anmelden</button>
</form>

<p>
    <a href="register.php">Neu registrieren</a> ·
    <a href="forgot_password.php">Passwort vergessen?</a>
</p>
<?php renderFooter(); ?>
