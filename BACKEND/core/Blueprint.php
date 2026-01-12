<?php

namespace Core;

class Blueprint
{
    private string $table;
    private array $columns = [];
    private array $foreignKeys = []; 

    public function __construct(string $table)
    {
        $this->table = $table;
    }

    // --- COLUMNAS ---

    // 1. Auto-incremental (Primary Key)
    public function id(string $name = 'id')
    {
        $this->columns[] = ['type' => 'pk', 'name' => $name];
    }

    // 2. Foreign Key ID (Entero positivo grande)
    public function foreignId(string $name)
    {
        $this->columns[] = ['type' => 'bigInteger', 'name' => $name];
        return $this; 
    }

    public function string(string $name, int $length = 255, bool $unique = false, bool $nullable = false)
    {
        $this->columns[] = [
            'type' => 'string', 
            'name' => $name, 
            'length' => $length, 
            'unique' => $unique, 
            'nullable' => $nullable
        ];
    }
    
    public function integer(string $name)
    {
        $this->columns[] = ['type' => 'integer', 'name' => $name];
    }

    // Para descripciones largas
    public function text(string $name)
    {
        $this->columns[] = ['type' => 'text', 'name' => $name];
    }

    // Para precios/moneda
    public function decimal(string $name, int $precision = 10, int $scale = 2)
    {
        $this->columns[] = [
            'type' => 'decimal', 
            'name' => $name, 
            'precision' => $precision, 
            'scale' => $scale
        ];
    }

    public function timestamp(string $name)
    {
        $this->columns[] = ['type' => 'timestamp_simple', 'name' => $name];
    }

    // 3. Timestamps (created_at, updated_at) - ¡AGREGADO!
    public function timestamps()
    {
        // created_at: Se llena solo al inicio
        $this->columns[] = ['type' => 'timestamp_create', 'name' => 'created_at'];
        // updated_at: Puede ser nulo al principio
        $this->columns[] = ['type' => 'timestamp_update', 'name' => 'updated_at'];
    }

    public function softDeletes()
    {
        $this->columns[] = ['type' => 'soft_delete', 'name' => 'deleted_at'];
    }

    // --- RELACIONES ---
    
    public function foreign(string $column): ForeignKey
    {
        $fk = new ForeignKey($column);
        $this->foreignKeys[] = $fk;
        return $fk; 
    }

    // --- GENERADOR SQL ---

    public function toSql(string $driver): string
    {
        $definitions = [];

        // 1. Procesar Columnas
        foreach ($this->columns as $col) {
            $definitions[] = $this->getColumnSql($driver, $col);
        }

        // 2. Procesar Relaciones (Foreign Keys)
        foreach ($this->foreignKeys as $fk) {
            $definitions[] = $fk->toSql();
        }

        $body = implode(",\n    ", $definitions);

        return "CREATE TABLE IF NOT EXISTS {$this->table} (\n    {$body}\n);";
    }

    private function getColumnSql(string $driver, array $col): string
{
    $sql = "";

    // Lógica para ID Autoincremental Universal
    if ($col['type'] === 'pk') {
        if ($driver === 'sqlite') {
            return "{$col['name']} INTEGER PRIMARY KEY AUTOINCREMENT";
        }
        return "{$col['name']} BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY";
    }

    // Lógica para Foreign ID
    if ($col['type'] === 'bigInteger') {
        if ($driver === 'sqlite') {
            return "{$col['name']} INTEGER"; 
        }
        return "{$col['name']} BIGINT UNSIGNED"; 
    }

    // Strings y Enteros
    if ($col['type'] === 'string') {
        $sql = "{$col['name']} VARCHAR({$col['length']})";
        if (!$col['nullable']) $sql .= " NOT NULL";
        if ($col['unique']) $sql .= " UNIQUE";
        return $sql;
    }
    
    if ($col['type'] === 'integer') {
        return "{$col['name']} INTEGER";
    }

    // --- LÓGICA DE TIMESTAMPS CORREGIDA ---
    if ($col['type'] === 'timestamp_create') {
        return "{$col['name']} DATETIME DEFAULT CURRENT_TIMESTAMP";
    }

    if ($col['type'] === 'timestamp_update') {
        // Solo MySQL soporta el ON UPDATE en el motor de DB
        if ($driver === 'mysql') {
            return "{$col['name']} DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP";
        }
        // SQLite: Se define como DATETIME normal (PHP se encargará de actualizarlo)
        return "{$col['name']} DATETIME DEFAULT CURRENT_TIMESTAMP";
    }

    // Otros tipos
    if ($col['type'] === 'soft_delete') {
        return "{$col['name']} DATETIME DEFAULT NULL";
    }

    if ($col['type'] === 'text') {
        return "{$col['name']} TEXT";
    }

    if ($col['type'] === 'decimal') {
        return "{$col['name']} DECIMAL({$col['precision']}, {$col['scale']})";
    }

    if ($col['type'] === 'timestamp_simple') {
        return "{$col['name']} DATETIME DEFAULT NULL";
    }

    return "";
}
}