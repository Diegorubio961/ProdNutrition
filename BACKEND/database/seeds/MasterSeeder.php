<?php

require_once __DIR__ . '/InsertRoles.php';
require_once __DIR__ . '/InsertDocumentsTypes.php';
require_once __DIR__ . '/InsertAdminUsers.php';
require_once __DIR__ . '/MeasureSeeder.php';
require_once __DIR__ . '/MeasureNullSeeder.php';

use Core\Database;

class MasterSeeder
{
    public static function run(): void
    {
        $pdo = Database::pdo();

        echo "Running InsertRoles...\n";
        InsertRoles::run($pdo);

        echo "\nRunning InsertDocumentsTypes...\n";
        InsertDocumentsTypes::run();

        echo "\nRunning InsertAdminUsers...\n";
        InsertAdminUsers::run($pdo);

        echo "\nRunning MeasureSeeder...\n";
        MeasureSeeder::run();

        echo "\nRunning MeasureNullSeeder...\n";
        MeasureNullSeeder::run();

        echo "\nAll seeders completed successfully.\n";
    }
}
