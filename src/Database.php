<?php
declare(strict_types=1);

/**
 * Datenbank-Verbindung als Singleton auf PDO-Basis.
 */
final class Database
{
    private static ?PDO $instance = null;

    private const HOST    = 'localhost';
    private const DBNAME  = 'app_db';
    private const USER    = 'viuser';
    private const PASS    = 'root';
    private const CHARSET = 'utf8mb4';

    private function __construct() {}
    private function __clone() {}

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=%s',
                self::HOST,
                self::DBNAME,
                self::CHARSET
            );

            self::$instance = new PDO($dsn, self::USER, self::PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        }

        return self::$instance;
    }
}
