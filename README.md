# MariaDB mit Docker Compose

## Voraussetzungen

- Docker Desktop oder Docker Engine
- Docker Compose

## Start

1. Passwörter und Datenbanknamen in `.env` anpassen.
2. Im Projektordner starten:

```bash
docker compose up -d
```

Status prüfen:

```bash
docker compose ps
```

Logs anzeigen:

```bash
docker compose logs -f mariadb
```

## Verbindung

| Einstellung | Wert |
|---|---|
| Host | `localhost` |
| Port | Wert aus `MARIADB_PORT`, standardmäßig `3306` |
| Datenbank | Wert aus `MARIADB_DATABASE` |
| Benutzer | Wert aus `MARIADB_USER` |
| Passwort | Wert aus `MARIADB_PASSWORD` |

Für DBeaver den Treiber **MariaDB** auswählen.

Andere Container im selben Docker-Netzwerk verwenden als Hostnamen den Servicenamen `mariadb` und Port `3306`.

## MariaDB-Konsole öffnen

```bash
docker compose exec mariadb mariadb -u root -p
```

## Stoppen

```bash
docker compose down
```

Die Daten bleiben dabei im Volume `mariadb_data` erhalten.

## Komplett zurücksetzen

Achtung: Der folgende Befehl löscht auch alle Daten:

```bash
docker compose down -v
```

Danach werden beim nächsten Start die Dateien aus `init/` erneut ausgeführt.

## Backup

```bash
mkdir -p backups
docker compose exec -T mariadb sh -c '
  mariadb-dump -u root -p"$MARIADB_ROOT_PASSWORD" --all-databases
' > backups/mariadb-backup.sql
```


## Restore

```bash
docker compose exec -T mariadb sh -c '
  mariadb -u root -p"$MARIADB_ROOT_PASSWORD"
' < backups/mariadb-backup.sql
```
