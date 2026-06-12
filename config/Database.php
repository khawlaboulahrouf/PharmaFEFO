<?php


namespace PharmaFEFO\Config;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;

    private const HOST = '127.0.0.1';
    private const DB_NAME = 'pharmafefo';
    private const USER = 'root';
    private const PASS = '';
    private const CHARSET = 'utf8mb4';

    private function __construct()
    {
       
    }

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $dsn = "mysql:host=" . self::HOST . ";dbname=" . self::DB_NAME . ";charset=" . self::CHARSET;
            try {
                self::$instance = new PDO($dsn, self::USER, self::PASS, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                throw new PDOException("Erreur de connexion BDD : " . $e->getMessage(), (int) $e->getCode());
            }
        }

        return self::$instance;
    }
}
