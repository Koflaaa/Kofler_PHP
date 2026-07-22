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
  schema.sql                Datenbank-Schema (Tabellen `benutzer`, `passwort_reset_tokens`)
```

## Installation
1. `sql/schema.sql` in MySQL/MariaDB importieren:
   `mysql -u root -p < sql/schema.sql`
   (alternativ per Docker: `docker compose up -d` startet eine passende MariaDB gemäß `.env`)
2. Zugangsdaten in `src/Database.php` anpassen (HOST, DBNAME, USER, PASS)
3. Webserver auf den Ordner `public/` zeigen lassen, z. B.:
   `php -S localhost:8000 -t public`
4. Aufrufen: http://localhost:8000/login.php

**Ersten Admin/Owner anlegen:** Es gibt keinen vorangelegten Admin-Account mehr. Ein Benutzer
kann sich nur mit der Rolle `benutzer` registrieren; Admin/Owner-Rechte lassen sich nur von
einem bestehenden Admin bzw. Owner vergeben. Um den allerersten Zugang zu `admin.php` zu
bekommen, muss nach der Registrierung einmalig direkt in der Datenbank die Rolle gesetzt werden:
```sql
UPDATE benutzer SET rolle = 'owner' WHERE email = 'ihre@email.de';
```
Danach lassen sich über `admin.php` weitere Admins ernennen.

## Bekanntes Problem
`PasswordResetService.php` liest/schreibt aktuell die Tabelle `password_resets`
(Spalten `user_id`, `token_hash`, `expires_at`, `used`), `sql/schema.sql` legt
aber `passwort_reset_tokens` (Spalten `benutzer_id`, `token_hash`, `gueltig_bis`,
`verwendet`) an. Dadurch schlägt "Passwort vergessen" nach einer frischen
Installation mit einem Datenbankfehler fehl, bis Tabellen-/Spaltennamen auf
beiden Seiten angeglichen werden.

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
