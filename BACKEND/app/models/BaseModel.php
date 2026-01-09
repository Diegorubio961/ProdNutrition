<?php

namespace App\Models;

use Core\Database;
use Core\QueryBuilder;
use PDO;

abstract class BaseModel
{
    protected static string $table;

    /* -------- Query builder -------- */
    public static function query(): QueryBuilder
    {
        return new QueryBuilder(Database::pdo(), static::$table);
    }

    /* -------- CRUD básicos -------- */
    public static function all(): array
    {
        return static::query()->get();
    }
    public static function find(int|string $id): ?array
    {
        return static::query()->where('id', '=', $id)->first();
    }
    public static function findBy(string $column, mixed $value): ?array
    {
        return static::query()->where($column, '=', $value)->first();
    }

    public static function create(array $data): array
    {
        $columns = array_keys($data);
        $place   = array_map(fn($c) => ':' . $c, $columns);

        $sql  = 'INSERT INTO ' . static::$table;
        $sql .= ' (' . implode(',', $columns) . ')';
        $sql .= ' VALUES (' . implode(',', $place) . ')';

        $pdo  = Database::pdo();
        $stmt = $pdo->prepare($sql);
        foreach ($data as $col => $val) $stmt->bindValue(':' . $col, $val);
        $stmt->execute();
        return ['id' => $pdo->lastInsertId()] + $data;
    }
    public static function update(int|string $id, array $data): bool
    {
        $sets = [];
        foreach ($data as $col => $val) $sets[] = "$col = :$col";
        $sql  = 'UPDATE ' . static::$table . ' SET ' . implode(',', $sets) . ' WHERE id = :id';
        $stmt = Database::pdo()->prepare($sql);
        $stmt->bindValue(':id', $id);
        foreach ($data as $col => $val) $stmt->bindValue(':' . $col, $val);
        return $stmt->execute();
    }
    public static function delete(int|string $id): bool
    {
        $stmt = Database::pdo()->prepare('DELETE FROM ' . static::$table . ' WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }
}
