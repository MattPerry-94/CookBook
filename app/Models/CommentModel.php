<?php
namespace App\Models;

use PDO;

class CommentModel extends Model
{
    /**
     * Constructeur du modèle Commentaire.
     *
     * @param PDO $db Instance de la connexion PDO.
     */
    public function __construct(PDO $db)
    {
        parent::__construct($db, 'comments');
    }

    /**
     * Crée un nouveau commentaire.
     *
     * @param array $data Données du commentaire (recipe_id, user_id, content).
     * @return bool Retourne true si succès, sinon false.
     */
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

    /**
     * Récupère tous les commentaires d'une recette.
     *
     * @param int $recipeId ID de la recette.
     * @return array Liste des commentaires avec infos de l'auteur.
     */
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
    
    /**
     * Supprime un commentaire par son ID.
     *
     * @param int $id ID du commentaire.
     * @return bool Retourne true si succès, sinon false.
     */
    public function delete(int $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
}
