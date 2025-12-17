<?php
namespace App\Models;

use PDO;

class CommentModel extends Model
{
    public function __construct(PDO $db)
    {
        parent::__construct($db, 'comments');
    }

    public function create(array $data): bool
    {
        $sql = "INSERT INTO {$this->table} (recipe_id, user_id, content) VALUES (:recipe_id, :user_id, :content)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'recipe_id' => $data['recipe_id'],
            'user_id'   => $data['user_id'],
            'content'   => $data['content'],
        ]);
    }

    public function findAllByRecipe(int $recipeId): array
    {
        $sql = "SELECT c.*, u.name AS author_name, u.email AS author_email 
                FROM {$this->table} c
                JOIN users u ON c.user_id = u.id
                WHERE c.recipe_id = :recipe_id
                ORDER BY c.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['recipe_id' => $recipeId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function delete(int $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
}
