<?php
namespace App\Models;

use PDO;

class CategoryModel extends Model
{
    /**
     * Constructeur du modèle Catégorie.
     *
     * @param PDO $db Instance de la connexion PDO.
     */
    public function __construct(PDO $db)
    {
        parent::__construct($db, 'categories');
    }

    /**
     * Récupère toutes les catégories.
     *
     * @return array Liste des catégories triées par nom.
     */
    public function findAll(): array
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY name ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}


