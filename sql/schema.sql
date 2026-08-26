-- Active: 1787728980938@@127.0.0.1@3306
-- Metadaten der SQL-Erweiterung (VS Code), markiert die aktive DB-Verbindung; kein SQL-Befehl
CREATE DATABASE IF NOT EXISTS benutzerverwaltung
    -- Legt die Datenbank an, falls sie noch nicht existiert
    CHARACTER SET utf8mb4
    -- Zeichensatz utf8mb4: unterstützt den vollen Unicode-Bereich (inkl. Emojis)
    COLLATE utf8mb4_unicode_ci;
    -- Sortier-/Vergleichsregel: case-insensitive, unicode-korrekt

USE benutzerverwaltung;
-- Wählt die soeben angelegte Datenbank für alle folgenden Befehle aus

CREATE TABLE IF NOT EXISTS benutzer (
    -- Legt die Tabelle "benutzer" an, falls sie noch nicht existiert
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    -- Eindeutige, automatisch hochzählende ID, Primärschlüssel
    vorname         VARCHAR(100)  NOT NULL,
    -- Vorname, Pflichtfeld, max. 100 Zeichen
    nachname        VARCHAR(100)  NOT NULL,
    -- Nachname, Pflichtfeld, max. 100 Zeichen
    adresse         VARCHAR(255)  NOT NULL,
    -- Adresse, Pflichtfeld, max. 255 Zeichen
    email           VARCHAR(255)  NOT NULL UNIQUE,
    -- E-Mail-Adresse, Pflichtfeld, muss in der Tabelle eindeutig sein (verhindert Doppel-Registrierung)
    telefonnummer   VARCHAR(50)   NOT NULL,
    -- Telefonnummer, Pflichtfeld, max. 50 Zeichen
    passwort_hash   VARCHAR(255)  NOT NULL,
    -- Gehashtes Passwort (nie im Klartext gespeichert), Pflichtfeld
    rolle           ENUM('benutzer', 'admin', 'owner') NOT NULL DEFAULT 'benutzer',
    -- Rolle des Benutzers, eingeschränkt auf drei Werte, Standard: "benutzer"
    erstellt_am     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    -- Zeitpunkt der Erstellung, wird automatisch beim Anlegen gesetzt
    aktualisiert_am TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    -- Zeitpunkt der letzten Änderung, wird automatisch bei jedem UPDATE aktualisiert
) ENGINE=InnoDB;
-- InnoDB als Speicher-Engine: unterstützt Transaktionen und Fremdschlüssel

CREATE TABLE IF NOT EXISTS passwort_reset_tokens (
    -- Legt die Tabelle für "Passwort vergessen"-Tokens an, falls sie noch nicht existiert
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    -- Eindeutige, automatisch hochzählende ID, Primärschlüssel
    benutzer_id  INT UNSIGNED NOT NULL,
    -- Verweist auf den Benutzer, für den das Token ausgestellt wurde
    token_hash   VARCHAR(64)  NOT NULL UNIQUE,
    -- SHA-256-Hash des Tokens (nicht das Klartext-Token selbst), muss eindeutig sein
    gueltig_bis  DATETIME     NOT NULL,
    -- Ablaufzeitpunkt des Tokens
    verwendet    TINYINT(1)   NOT NULL DEFAULT 0,
    -- Markiert, ob das Token bereits zum Zurücksetzen genutzt wurde (0 = nein, 1 = ja)
    erstellt_am  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    -- Zeitpunkt der Erstellung, wird automatisch gesetzt

    CONSTRAINT fk_passwort_reset_tokens_benutzer
        -- Benannter Fremdschlüssel-Constraint für bessere Lesbarkeit in Fehlermeldungen
        FOREIGN KEY (benutzer_id) REFERENCES benutzer(id) ON DELETE CASCADE
        -- Verknüpft benutzer_id mit benutzer.id; wird der Benutzer gelöscht, werden auch seine Tokens gelöscht
) ENGINE=InnoDB;
-- InnoDB als Speicher-Engine: notwendig, damit der Fremdschlüssel funktioniert

CREATE INDEX idx_passwort_reset_tokens_gueltig_bis ON passwort_reset_tokens(gueltig_bis);
-- Index auf gueltig_bis, beschleunigt Abfragen/Aufräumen nach Ablaufzeit

-- Fest vorgegebener Owner-Account (Passwort: kofler_admin1)
INSERT IGNORE INTO benutzer (vorname, nachname, adresse, email, telefonnummer, passwort_hash, rolle)
-- Fügt den Owner-Account ein; IGNORE verhindert einen Fehler, falls die E-Mail bereits existiert (Skript ist so mehrfach ausführbar)
VALUES (
    'Admin',
    -- Vorname des Owner-Accounts
    'Kofler',
    -- Nachname des Owner-Accounts
    'Europastraße 9a',
    -- Adresse des Owner-Accounts
    'kofler.admin@owner.com',
    -- E-Mail-Adresse des Owner-Accounts (dient als Login)
    '+49 176 0390500',
    -- Telefonnummer des Owner-Accounts
    '$2y$10$KNJvJtEU1XuP8lebz121GO5itg/uDsU5lCHpPDbDb8h4Vs7d1GDES', -- Owner Passwort: kofler_admin1
    -- Vorab berechneter bcrypt-Hash des Passworts "kofler_admin1"
    'owner'
    -- Rolle: Owner (höchste Berechtigungsstufe, kann nicht verändert/gelöscht werden)
);
