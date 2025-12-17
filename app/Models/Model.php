<?php
namespace App\Models;

use PDO;

abstract class Model
{
    protected PDO $db;
    protected string $table;

    public function __construct(PDO $db, string $table )
    {
        $this->db = $db;
        if ($table !== null) {
            $this->table = $table;
        }
    }

    protected function setTable(string $table): void
    {
        $this->table = $table;
    }

    public function table(): string
    {
        return $this->table;
    }

    public function db(): PDO
    {
        return $this->db;
    }

    public function findById(int $id): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    protected function findAll(){

        $sql = "SELECT * FROM {$this->table}";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function deleteById(int $id): int
    {
        $sql = "DELETE FROM `{$this->table}` WHERE `id` = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]); // pas de bindValue
        return $stmt->rowCount();
    }

}
