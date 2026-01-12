<?php

namespace Core;

class ForeignKey
{
    public string $column;
    public string $referenceColumn = 'id';
    public string $onTable;
    public string $onDelete = 'RESTRICT'; // CASCADE, SET NULL, etc.

    public function __construct(string $column)
    {
        $this->column = $column;
    }

    public function references(string $column)
    {
        $this->referenceColumn = $column;
        return $this;
    }

    public function on(string $table)
    {
        $this->onTable = $table;
        return $this;
    }

    public function onDelete(string $action)
    {
        $this->onDelete = strtoupper($action);
        return $this;
    }

    // Genera el SQL de la restricción
    public function toSql(): string
    {
        // Generamos un nombre único para la FK para evitar colisiones
        // Ej: fk_users_role_id
        $constraintName = "fk_{$this->onTable}_{$this->column}_" . uniqid(); 

        return "CONSTRAINT {$constraintName} FOREIGN KEY ({$this->column}) " .
               "REFERENCES {$this->onTable}({$this->referenceColumn}) " .
               "ON DELETE {$this->onDelete}";
    }
}