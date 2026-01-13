<?php

namespace Core;

use PDO;
use PDOStatement;

class QueryBuilder
{
    private PDO $pdo;
    private string $table;
    private array $select = ['*'];
    private array $joins = [];
    private array $wheres = [];
    private array $bindings = [];
    private ?string $order = null;
    private ?int $limit = null;

    public function __construct(PDO $pdo, string $table)
    {
        $this->pdo   = $pdo;
        $this->table = $table;
    }

    /* -------- SELECT -------- */
    public function select(string ...$columns): self
    {
        if ($columns) $this->select = $columns;
        return $this;
    }

    /* -------- JOINS -------- */
    public function join(string $table, string $on, string $type = 'INNER'): self
    {
        $this->joins[] = "$type JOIN $table ON $on";
        return $this;
    }

    public function leftJoin(string $table, string $on): self  { return $this->join($table, $on, 'LEFT'); }
    public function rightJoin(string $table, string $on): self { return $this->join($table, $on, 'RIGHT'); }

    /* -------- WHERE -------- */

    /**
     * Condición WHERE estándar
     */
    public function where(string $column, string $operator, mixed $value): self
    {
        $param = ':w' . count($this->bindings);
        $this->addWhereClause("$column $operator $param", 'AND');
        $this->bindings[$param] = $value;
        return $this;
    }

    /**
     * Condición OR WHERE estándar
     */
    public function orWhere(string $column, string $operator, mixed $value): self
    {
        $param = ':w' . count($this->bindings);
        $this->addWhereClause("$column $operator $param", 'OR');
        $this->bindings[$param] = $value;
        return $this;
    }

    /**
     * Condición WHERE IN (múltiples valores)
     */
    public function whereIn(string $column, array $values): self
    {
        return $this->buildWhereIn($column, $values, 'AND');
    }

    /**
     * Condición OR WHERE IN (múltiples valores)
     */
    public function orWhereIn(string $column, array $values): self
    {
        return $this->buildWhereIn($column, $values, 'OR');
    }

    /**
     * Lógica interna para construir el fragmento IN (:w1, :w2...)
     */
    private function buildWhereIn(string $column, array $values, string $boolean): self
    {
        if (empty($values)) {
            // Si no hay valores, forzamos una condición falsa para no devolver resultados
            $this->addWhereClause("1=0", $boolean);
            return $this;
        }

        $placeholders = [];
        foreach ($values as $value) {
            $param = ':w' . count($this->bindings);
            $placeholders[] = $param;
            $this->bindings[$param] = $value;
        }

        $condition = "$column IN (" . implode(', ', $placeholders) . ")";
        $this->addWhereClause($condition, $boolean);

        return $this;
    }

    /**
     * Helper para añadir la cláusula al array respetando el operador lógico
     */
    private function addWhereClause(string $condition, string $boolean): void
    {
        if (empty($this->wheres)) {
            $this->wheres[] = $condition;
        } else {
            $this->wheres[] = "$boolean $condition";
        }
    }

    /* -------- ORDER / LIMIT -------- */
    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $this->order = "$column $direction";
        return $this;
    }

    public function limit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    /* -------- EXECUTE -------- */
    private function buildSelect(): string
    {
        $sql = 'SELECT ' . implode(', ', $this->select) . " FROM {$this->table}";

        if ($this->joins) {
            $sql .= ' ' . implode(' ', $this->joins);
        }

        if ($this->wheres) {
            // Unimos los wheres con espacio ya que los operadores (AND/OR) 
            // se gestionan en addWhereClause
            $sql .= ' WHERE ' . implode(' ', $this->wheres);
        }

        if ($this->order) {
            $sql .= ' ORDER BY ' . $this->order;
        }

        if ($this->limit) {
            $sql .= ' LIMIT ' . $this->limit;
        }

        return $sql;
    }

    private function executeSelect(): PDOStatement
    {
        $stmt = $this->pdo->prepare($this->buildSelect());
        foreach ($this->bindings as $param => $val) {
            $stmt->bindValue($param, $val);
        }
        $stmt->execute();
        return $stmt;
    }

    /* -------- PUBLIC FETCHERS -------- */
    public function get(): array
    {
        return $this->executeSelect()->fetchAll(PDO::FETCH_ASSOC);
    }

    public function first(): ?array
    {
        $this->limit(1);
        $row = $this->executeSelect()->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function count(): int
    {
        // Guardamos las columnas originales
        $originalSelect = $this->select;
        $this->select = ['COUNT(*) as cnt'];
        $result = $this->first();
        // Restauramos
        $this->select = $originalSelect;
        
        return (int) ($result['cnt'] ?? 0);
    }
}