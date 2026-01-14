<?php
namespace App\Database;

use PDO;
use PDOException;


class Database{

    /**
     * Instance PDO unique (singleton).
     *
     * @var PDO|null
     */
    private static ? PDO $instance = null;
    
    /**
     * Retourne l'instance PDO unique de l'application.
     * La connexion est créée au premier appel.
     *
     * @return PDO Instance PDO connectée à la base de données.
     */
    public static function getInstance(): PDO {
        if (self::$instance === null) {
            $dsn = 'mysql:host=localhost;dbname=cook_book;charset=utf8mb4';
            $user = 'root';
            $pass = '';

            try {
                self::$instance = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            } catch (PDOException $e) {
                die('Erreur connexion DB: ' . $e->getMessage());
            }
        }
        return self::$instance;
    }



}
