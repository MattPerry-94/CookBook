<?php
namespace App\Models;

use PDO;

class RecipeModel extends Model
{
    public function __construct(PDO $db)
    {
        parent::__construct($db, 'recipes');
    }

    public function findAllByUser(int $userId): array
    {
        $sql = "SELECT r.*, c.name AS category_name
                FROM {$this->table} r
                LEFT JOIN categories c ON r.category_id = c.id
                WHERE r.user_id = :user_id
                ORDER BY r.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findPublicList(): array
    {
        $sql = "SELECT r.*, u.name AS author_name
                FROM {$this->table} r
                JOIN users u ON r.user_id = u.id
                ORDER BY r.created_at DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findAllWithUser(): array
    {
        $sql = "SELECT r.*, u.email AS author_email, u.name AS author_name
                FROM {$this->table} r
                JOIN users u ON r.user_id = u.id
                ORDER BY r.created_at DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findByIdForUser(int $id, int $userId): ?array
    {
        $sql = "SELECT r.*, c.name AS category_name
                FROM {$this->table} r
                LEFT JOIN categories c ON r.category_id = c.id
                WHERE r.id = :id AND r.user_id = :user_id
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id, 'user_id' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function create(array $data): int
    {
        $sql = "INSERT INTO {$this->table}
                (user_id, category_id, title, description, ingredients, steps, image_path)
                VALUES (:user_id, :category_id, :title, :description, :ingredients, :steps, :image_path)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'user_id'     => $data['user_id'],
            'category_id' => $data['category_id'] ?: null,
            'title'       => $data['title'],
            'description' => $data['description'] ?? null,
            'ingredients' => $data['ingredients'],
            'steps'       => $data['steps'],
            'image_path'  => $data['image_path'] ?? null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function updateForUser(int $id, int $userId, array $data): int
    {
        $sql = "UPDATE {$this->table}
                SET category_id = :category_id,
                    title = :title,
                    description = :description,
                    ingredients = :ingredients,
                    steps = :steps,
                    image_path = :image_path,
                    updated_at = NOW()
                WHERE id = :id AND user_id = :user_id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'id'          => $id,
            'user_id'     => $userId,
            'category_id' => $data['category_id'] ?: null,
            'title'       => $data['title'],
            'description' => $data['description'] ?? null,
            'ingredients' => $data['ingredients'],
            'steps'       => $data['steps'],
            'image_path'  => $data['image_path'] ?? null,
        ]);

        return $stmt->rowCount();
    }

    public function searchPublic(string $query): array
    {
        $like = '%' . $query . '%';
        $sql = "SELECT r.*, u.name AS author_name
                FROM {$this->table} r
                JOIN users u ON r.user_id = u.id
                WHERE r.title LIKE :q1
                   OR r.ingredients LIKE :q2
                ORDER BY r.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'q1' => $like,
            'q2' => $like
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}


