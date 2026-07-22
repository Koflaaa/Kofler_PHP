# Login- und Benutzerverwaltungs-Komponente (PHP OOP)

## Funktionen
1. **Registrierung** mit Vorname, Nachname, Adresse, E-Mail, Telefon, Passwort (neue Konten erhalten immer die Rolle `benutzer`)
2. **Login** mit E-Mail + Passwort (Session-basiert, Schutz vor Session-Fixation)
3. **Rollen:** `benutzer`, `admin` und `owner`
4. **Admin-Bereich** (`admin.php`, zugänglich für `admin` und `owner`):
   - Benutzer anlegen
   - Benutzer zu Admin ernennen (Admin zu Benutzer zurückstufen darf nur der Owner)
   - normale Benutzer löschen (Admins und der Owner können nicht gelöscht/zurückgestuft werden von anderen Admins)
5. **Passwortverwaltung:** Passwort ändern (eingeloggt) + "Passwort vergessen" mit Reset-Token (30 Min gültig, einmalig verwendbar)

## Struktur
```
src/
  Database.php              PDO-Verbindung (Singleton)
  User.php                  Entity-Klasse mit Rollenlogik (benutzer/admin/owner)
  UserRepository.php        Alle DB-Zugriffe für Benutzer (Prepared Statements)
  Auth.php                  Registrierung, Login/Logout, Session, Passwort ändern
  PasswordResetService.php  "Passwort vergessen" per Token
  UserManager.php           Admin-Funktionen (Rollen ändern, Benutzer löschen)
public/
  _layout.php               gemeinsames HTML-Grundgerüst (Header/Footer)
  register.php, login.php, logout.php, dashboard.php,
  change_password.php, forgot_password.php, reset_password.php,
  admin.php                 Benutzerverwaltung (anlegen, Rollen ändern, löschen)
sql/
  schema.sql                Datenbank-Schema (Tabellen `benutzer`, `passwort_reset_tokens`) + vorgegebener Owner-Account
backup.sql                  MariaDB-Dump der bestehenden `benutzerverwaltung`-Datenbank (Schema + Daten)
```

## Installation
1. Datenbank importieren — zwei Möglichkeiten:
   - **Leeres Schema** (nur Tabellen + vorgegebener Owner-Account):
     `mysql -u root -p < sql/schema.sql`
   - **Bestehender Datenstand** (Schema + alle bisherigen Benutzer, z. B. beim Umzug
     auf einen anderen Rechner):
     `mysql -u root -p < backup.sql`
   (alternativ per Docker: `docker compose up -d` startet eine passende MariaDB gemäß `.env`)
2. Zugangsdaten in `src/Database.php` anpassen (HOST, DBNAME, USER, PASS)
3. Webserver auf den Ordner `public/` zeigen lassen, z. B.:
   `php -S localhost:8000 -t public`
4. Aufrufen: http://localhost:8000/login.php

**Vorgegebener Owner-Account:** `sql/schema.sql` legt beim Import automatisch einen
Owner-Account an, über den sich weitere Admins ernennen lassen (selbst registrieren kann
man sich nur mit der Rolle `benutzer`):
- E-Mail: `kofler.admin@owner.com`
- Passwort: `kofler_admin1`

Das Passwort sollte nach dem ersten Login über "Passwort ändern" ersetzt werden.

**Datenbank aktualisieren:** Um den aktuellen Datenstand für den nächsten Rechner
zu sichern, `backup.sql` neu erzeugen:
`mysqldump -u root -p --routines --triggers --add-drop-table --databases benutzerverwaltung > backup.sql`

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
