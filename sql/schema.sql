-- Datenbank-Schema für die Login- und Benutzerverwaltungs-Komponente
CREATE DATABASE IF NOT EXISTS benutzerverwaltung
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE benutzerverwaltung;

CREATE TABLE users (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    vorname       VARCHAR(100)  NOT NULL,
    nachname      VARCHAR(100)  NOT NULL,
    adresse       VARCHAR(255)  NOT NULL,
    email         VARCHAR(255)  NOT NULL UNIQUE,
    telefon       VARCHAR(50)   NOT NULL,
    password_hash VARCHAR(255)  NOT NULL,
    rolle         ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    erstellt_am   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE password_resets (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    INT UNSIGNED NOT NULL,
    token_hash VARCHAR(64)  NOT NULL,
    expires_at DATETIME     NOT NULL,
    used       TINYINT(1)   NOT NULL DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Beispiel-Administrator (Passwort: Admin123!)
-- Hash ggf. neu erzeugen mit: php -r "echo password_hash('Admin123!', PASSWORD_DEFAULT);"
INSERT INTO users (vorname, nachname, adresse, email, telefon, password_hash, rolle)
VALUES ('Max', 'Mustermann', 'Musterstraße 1, 1010 Wien', 'admin@example.com',
        '+43 660 0000000',
        '$2y$10$0GfHkCx0V0zubfR8jZ0J1eYQeVQ9rW4nqzD0dK9cbnwlR3h9O5oQe',
        'admin');
