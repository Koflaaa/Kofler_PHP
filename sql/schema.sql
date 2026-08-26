-- Active: 1787728980938@@127.0.0.1@3306
CREATE DATABASE IF NOT EXISTS benutzerverwaltung
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE benutzerverwaltung;

CREATE TABLE IF NOT EXISTS benutzer (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    vorname         VARCHAR(100)  NOT NULL,
    nachname        VARCHAR(100)  NOT NULL,
    adresse         VARCHAR(255)  NOT NULL,
    email           VARCHAR(255)  NOT NULL UNIQUE,
    telefonnummer   VARCHAR(50)   NOT NULL,
    passwort_hash   VARCHAR(255)  NOT NULL,
    rolle           ENUM('benutzer', 'admin', 'owner') NOT NULL DEFAULT 'benutzer',
    erstellt_am     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    aktualisiert_am TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS passwort_reset_tokens (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    benutzer_id  INT UNSIGNED NOT NULL,
    token_hash   VARCHAR(64)  NOT NULL UNIQUE,
    gueltig_bis  DATETIME     NOT NULL,
    verwendet    TINYINT(1)   NOT NULL DEFAULT 0,
    erstellt_am  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_passwort_reset_tokens_benutzer
        FOREIGN KEY (benutzer_id) REFERENCES benutzer(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE INDEX idx_passwort_reset_tokens_gueltig_bis ON passwort_reset_tokens(gueltig_bis);

-- Fest vorgegebener Owner-Account (Passwort: kofler_admin1)
INSERT IGNORE INTO benutzer (vorname, nachname, adresse, email, telefonnummer, passwort_hash, rolle)
VALUES (
    'Admin',
    'Kofler',
    'Europastraße 9a',
    'kofler.admin@owner.com',
    '+49 176 0390500',
    '$2y$10$KNJvJtEU1XuP8lebz121GO5itg/uDsU5lCHpPDbDb8h4Vs7d1GDES', -- Owner Passwort: kofler_admin1
    'owner'
);


