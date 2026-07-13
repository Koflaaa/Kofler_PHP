<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Auth.php';

$auth = new Auth();
$auth->logout();

header('Location: login.php');
exit;
