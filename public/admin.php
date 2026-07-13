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

$benutzer = $manager->listUsers();

renderHeader('Benutzerverwaltung');
?>
<nav><a href="dashboard.php">Zurück zum Dashboard</a></nav>

<?php if ($fehler): ?><p class="fehler"><?= htmlspecialchars($fehler) ?></p><?php endif; ?>
<?php if ($erfolg): ?><p class="erfolg"><?= htmlspecialchars($erfolg) ?></p><?php endif; ?>

<h2>Neuen Benutzer anlegen</h2>
<form method="post" action="admin.php">
    <input type="text"     name="vorname"      placeholder="Vorname" required>
    <input type="text"     name="nachname"     placeholder="Nachname" required>
    <input type="text"     name="adresse"      placeholder="Adresse" required>
    <input type="email"    name="email"        placeholder="E-Mail-Adresse" required>
    <input type="tel"      name="telefon"      placeholder="Telefonnummer" required>
    <input type="password" name="passwort"     placeholder="Passwort (min. 8 Zeichen)" required>
    <input type="password" name="passwort_wdh" placeholder="Passwort wiederholen" required>
    <select name="rolle">
        <option value="<?= User::ROLE_USER ?>">Benutzer</option>
        <option value="<?= User::ROLE_ADMIN ?>">Administrator</option>
    </select>
    <button type="submit" name="create" value="1">Benutzer anlegen</button>
</form>

<h2>Alle Benutzer</h2>
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
                <td><?= $b->isAdmin() ? 'Administrator' : 'Benutzer' ?></td>
                <td>
                    <?php if (!$b->isAdmin()): ?>
                        <form method="post" action="admin.php"
                              onsubmit="return confirm('Diesen Benutzer wirklich löschen?');"
                              style="margin:0">
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
