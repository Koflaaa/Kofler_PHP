# Login- und Benutzerverwaltungs-Komponente (PHP OOP)

## Funktionen
1. **Registrierung** mit Vorname, Nachname, Adresse, E-Mail, Telefon, Passwort
2. **Login** mit E-Mail + Passwort (Session-basiert)
3. **Rollen:** `user` und `admin`
4. **Admin-Bereich:** Administratoren können normale Benutzer löschen
5. **Passwortverwaltung:** Passwort ändern + "Passwort vergessen" mit Reset-Token (30 Min gültig)

## Struktur
```
src/
  Database.php              PDO-Verbindung (Singleton)
  User.php                  Entity-Klasse mit Rollenlogik
  UserRepository.php        Alle DB-Zugriffe (Prepared Statements)
  Auth.php                  Registrierung, Login/Logout, Session, Passwort ändern
  PasswordResetService.php  "Passwort vergessen" per Token
  UserManager.php           Admin-Funktionen (Benutzer löschen)
public/
  register.php, login.php, logout.php, dashboard.php,
  change_password.php, forgot_password.php, reset_password.php, admin.php
sql/
  schema.sql                Datenbank-Schema + Beispiel-Admin
```

## Installation
1. `sql/schema.sql` in MySQL/MariaDB importieren:
   `mysql -u root -p < sql/schema.sql`
2. Zugangsdaten in `src/Database.php` anpassen (HOST, USER, PASS)
3. Webserver auf den Ordner `public/` zeigen lassen, z. B.:
   `php -S localhost:8000 -t public`
4. Aufrufen: http://localhost:8000/login.php

**Beispiel-Admin:** admin@example.com / Admin123!

## Sicherheit
- Passwörter mit `password_hash()` / `password_verify()` (bcrypt)
- Prepared Statements gegen SQL-Injection
- `htmlspecialchars()` gegen XSS
- `session_regenerate_id()` gegen Session-Fixation
- Reset-Tokens werden nur als SHA-256-Hash gespeichert, sind einmalig verwendbar und 30 Minuten gültig
- Kein Rückschluss möglich, ob eine E-Mail registriert ist (neutrale Meldung bei "Passwort vergessen")

## Hinweis (Demo-Modus)
Da kein Mailserver konfiguriert ist, wird der Reset-Link auf der
"Passwort vergessen"-Seite direkt angezeigt. In Produktion würde
er per E-Mail versendet.
