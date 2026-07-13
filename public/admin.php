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

$benutzer = $manager->listNormalUsers();

renderHeader('Benutzerverwaltung');
?>
<nav><a href="dashboard.php">Zurück zum Dashboard</a></nav>

<?php if ($fehler): ?><p class="fehler"><?= htmlspecialchars($fehler) ?></p><?php endif; ?>
<?php if ($erfolg): ?><p class="erfolg"><?= htmlspecialchars($erfolg) ?></p><?php endif; ?>

<?php if (count($benutzer) === 0): ?>
    <p>Es sind keine normalen Benutzer vorhanden.</p>
<?php else: ?>
    <table>
        <tr><th>Name</th><th>E-Mail</th><th>Telefon</th><th>Aktion</th></tr>
        <?php foreach ($benutzer as $b): ?>
            <tr>
                <td><?= htmlspecialchars($b->getVollerName()) ?></td>
                <td><?= htmlspecialchars($b->getEmail()) ?></td>
                <td><?= htmlspecialchars($b->getTelefon()) ?></td>
                <td>
                    <form method="post" action="admin.php"
                          onsubmit="return confirm('Diesen Benutzer wirklich löschen?');"
                          style="margin:0">
                        <input type="hidden" name="delete_id" value="<?= $b->getId() ?>">
                        <button type="submit">Löschen</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>
<?php renderFooter(); ?>
