<?php
declare(strict_types=1); // Erzwingt strikte Typprüfung

require_once __DIR__ . '/../src/Auth.php'; // Lädt die Authentifizierungs-/Session-Logik

$auth = new Auth(); // Erstellt das Auth-Objekt (startet ggf. die Session)
$auth->logout(); // Beendet die Session und löscht das Session-Cookie

header('Location: login.php'); // Leitet den Benutzer nach dem Logout zur Login-Seite weiter
exit; // Beendet die Skriptausführung nach dem Redirect
