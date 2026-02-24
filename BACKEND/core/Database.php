<?php

namespace Core;

use PDO;

class Database
{
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (!self::$pdo) {
            $cfg = require BASE_PATH . '/config/database.php';

            // --- TRUCO PARA SQLITE ---
            // Si es SQLite, verificamos si el archivo existe. Si no, lo creamos vacío.
            if (isset($cfg['driver']) && $cfg['driver'] === 'sqlite') {
                // Extraemos la ruta del DSN (quitamos "sqlite:")
                $path = substr($cfg['dsn'], 7);

                // Verificamos que la carpeta exista, si no, daría error al crear el archivo
                $dir = dirname($path);
                if (!is_dir($dir)) {
                    mkdir($dir, 0777, true);
                }

                if (!file_exists($path)) {
                    touch($path); // Crea el archivo vacío
                }
            }
            // -------------------------

            self::$pdo = new PDO($cfg['dsn'], $cfg['user'], $cfg['pass'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);

            // --- ACTIVAR FOREIGN KEYS EN SQLITE ---
            if (isset($cfg['driver']) && $cfg['driver'] === 'sqlite') {
                self::$pdo->exec("PRAGMA foreign_keys = ON;");
            }
        }
        return self::$pdo;
    }

    public static function close(): void
    {
        self::$pdo = null; // O como se llame tu variable donde guardas el PDO
    }
}
