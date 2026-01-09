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
    public function where(string $column, string $operator, mixed $value): self
    {
        $param = ':w'.count($this->bindings);
        $this->wheres[] = "$column $operator $param";
        $this->bindings[$param] = $value;
        return $this;
    }
    public function orWhere(string $column, string $operator, mixed $value): self
    {
        $param = ':w'.count($this->bindings);
        $condition = "$column $operator $param";
        if ($this->wheres) {
            $this->wheres[count($this->wheres)-1] .= ' OR ' . $condition;
        } else {
            $this->wheres[] = $condition;
        }
        $this->bindings[$param] = $value;
        return $this;
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
        $sql  = 'SELECT '.implode(', ', $this->select)." FROM {$this->table}";
        if ($this->joins)  $sql .= ' '.implode(' ', $this->joins);
        if ($this->wheres) $sql .= ' WHERE '.implode(' AND ', $this->wheres);
        if ($this->order)  $sql .= ' ORDER BY '.$this->order;
        if ($this->limit)  $sql .= ' LIMIT '.$this->limit;
        return $sql;
    }
    private function executeSelect(): PDOStatement
    {
        $stmt = $this->pdo->prepare($this->buildSelect());
        foreach ($this->bindings as $param => $val) $stmt->bindValue($param, $val);
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
        $this->select('COUNT(*) as cnt');
        return (int) $this->first()['cnt'];
    }
}
