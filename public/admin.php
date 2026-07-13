<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/UserManager.php';
require_once __DIR__ . '/_layout.php';

$auth    = new Auth();
$admin   = $auth->requireAdmin();
$manager = new UserManager($admin);

$fehler = null;
$erfolg = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    try {
        $manager->deleteUser((int) $_POST['delete_id']);
        $erfolg = 'Der Benutzer wurde gelöscht.';
    } catch (InvalidArgumentException $e) {
        $fehler = $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create'])) {
    try {
        $auth->register(
            $_POST['vorname']      ?? '',
            $_POST['nachname']     ?? '',
            $_POST['adresse']      ?? '',
            $_POST['email']        ?? '',
            $_POST['telefon']      ?? '',
            $_POST['passwort']     ?? '',
            $_POST['passwort_wdh'] ?? '',
            $_POST['rolle']        ?? User::ROLE_USER
        );
        $erfolg = 'Der Benutzer wurde angelegt.';
    } catch (InvalidArgumentException $e) {
        $fehler = $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['role_id'], $_POST['neue_rolle'])) {
    try {
        $manager->setRole((int) $_POST['role_id'], $_POST['neue_rolle']);
        $erfolg = 'Die Rolle wurde geändert.';
    } catch (InvalidArgumentException $e) {
        $fehler = $e->getMessage();
    }
}

$benutzer = $manager->listUsers();

renderHeader('Benutzerverwaltung');
?>
<nav><a href="dashboard.php">Zurück zum Dashboard</a></nav>

<?php if ($fehler): ?><p class="fehler"><?= htmlspecialchars($fehler) ?></p><?php endif; ?>
<?php if ($erfolg): ?><p class="erfolg"><?= htmlspecialchars($erfolg) ?></p><?php endif; ?>

<?php if (count($benutzer) === 0): ?>
    <p>Es sind keine Benutzer vorhanden.</p>
<?php else: ?>
    <table>
        <tr><th>Name</th><th>E-Mail</th><th>Telefon</th><th>Rolle</th><th>Aktion</th></tr>
        <?php foreach ($benutzer as $b): ?>
            <tr>
                <td><?= htmlspecialchars($b->getVollerName()) ?></td>
                <td><?= htmlspecialchars($b->getEmail()) ?></td>
                <td><?= htmlspecialchars($b->getTelefon()) ?></td>
                <td><?= $b->getRolleLabel() ?></td>
                <td>
                    <?php if ($b->getId() === $admin->getId()): ?>
                        (Sie)
                    <?php elseif ($b->isOwner()): ?>
                        &mdash;
                    <?php elseif ($b->isAdmin()): ?>
                        <?php if ($admin->isOwner()): ?>
                            <form method="post" action="admin.php" style="margin:0">
                                <input type="hidden" name="role_id" value="<?= $b->getId() ?>">
                                <input type="hidden" name="neue_rolle" value="<?= User::ROLE_USER ?>">
                                <button type="submit">Zum Benutzer zurückstufen</button>
                            </form>
                        <?php else: ?>
                            &mdash;
                        <?php endif; ?>
                    <?php else: ?>
                        <form method="post" action="admin.php" style="margin:0; display:inline">
                            <input type="hidden" name="role_id" value="<?= $b->getId() ?>">
                            <input type="hidden" name="neue_rolle" value="<?= User::ROLE_ADMIN ?>">
                            <button type="submit">Zum Admin ernennen</button>
                        </form>
                        <form method="post" action="admin.php"
                              onsubmit="return confirm('Diesen Benutzer wirklich löschen?');"
                              style="margin:0; display:inline">
                            <input type="hidden" name="delete_id" value="<?= $b->getId() ?>">
                            <button type="submit">Löschen</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>
<?php renderFooter(); ?>
