<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/_layout.php';

$auth = new Auth();
$user = $auth->requireLogin();

renderHeader('Dashboard');
?>
<nav>
    <a href="change_password.php">Passwort ändern</a>
    <?php if ($user->hasAdminRights()): ?><a href="admin.php">Benutzerverwaltung</a><?php endif; ?>
    <a href="logout.php">Abmelden</a>
</nav>

<p>Willkommen, <strong><?= htmlspecialchars($user->getVollerName()) ?></strong>!</p>

<table>
    <tr><th>E-Mail</th><td><?= htmlspecialchars($user->getEmail()) ?></td></tr>
    <tr><th>Adresse</th><td><?= htmlspecialchars($user->getAdresse()) ?></td></tr>
    <tr><th>Telefon</th><td><?= htmlspecialchars($user->getTelefon()) ?></td></tr>
    <tr><th>Rolle</th><td><?= $user->getRolleLabel() ?></td></tr>
</table>
<?php renderFooter(); ?>
