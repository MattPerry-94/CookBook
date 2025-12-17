<?php
namespace App\Models;

use PDO;

class CategoryModel extends Model
{
    public function __construct(PDO $db)
    {
        parent::__construct($db, 'categories');
    }

    public function findAll(): array
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY name ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}


