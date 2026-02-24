<?php

namespace Core;

use PDO;
use PDOException;

class Schema
{
    /**
     * Crea una nueva tabla en la base de datos.
     *
     * @param string $table Nombre de la tabla
     * @param callable $callback Función anónima que recibe el Blueprint ($table)
     */
    public static function create(string $table, callable $callback)
    {
        try {
            $pdo = Database::pdo();

            // 1. Detectar el motor de base de datos actual (mysql o sqlite) directamente de la conexión
            $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

            // 2. Instanciar el Blueprint (el plano de la tabla)
            $blueprint = new Blueprint($table);

            // 3. Ejecutar la función anónima definida en la migración
            // Esto llena el Blueprint con las columnas ($table->id(), $table->string()...)
            $callback($blueprint);

            // 4. Generar el SQL específico para el driver actual
            $sql = $blueprint->toSql($driver);

            // 5. Ejecutar la consulta en la BD
            $pdo->exec($sql);

            echo "✅ Tabla '{$table}' creada (Driver: {$driver}).\n";

        } catch (PDOException $e) {
            echo "\n❌ Error creando la tabla '{$table}': " . $e->getMessage() . "\n";
            // Si quieres ver qué SQL falló, descomenta la siguiente línea:
            // echo "DEBUG SQL:\n" . $blueprint->toSql($driver) . "\n";
            exit(1); // Detener el proceso si hay error
        }
    }

    /**
     * Elimina una tabla si existe.
     * Útil para el método down() de las migraciones.
     *
     * @param string $table Nombre de la tabla
     */
    public static function dropIfExists(string $table)
    {
        try {
            $pdo = Database::pdo();
            
            // Esta sintaxis es compatible con MySQL y SQLite moderno
            $sql = "DROP TABLE IF EXISTS {$table}";
            
            $pdo->exec($sql);

            echo "🔥 Tabla '{$table}' eliminada.\n";

        } catch (PDOException $e) {
            echo "❌ Error eliminando la tabla '{$table}': " . $e->getMessage() . "\n";
        }
    }
}