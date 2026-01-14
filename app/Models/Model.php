<?php
namespace App\Models;

use PDO;

abstract class Model
{
    /**
     * Instance PDO utilisée pour les requêtes.
     *
     * @var PDO
     */
    protected PDO $db;

    /**
     * Nom de la table associée au modèle.
     *
     * @var string
     */
    protected string $table;

    /**
     * Constructeur de base pour les modèles.
     *
     * @param PDO $db Instance de la connexion PDO.
     * @param string $table Nom de la table.
     */
    public function __construct(PDO $db, string $table )
    {
        $this->db = $db;
        if ($table !== null) {
            $this->table = $table;
        }
    }

    /**
     * Définit dynamiquement le nom de la table.
     *
     * @param string $table Nom de la table.
     * @return void
     */
    protected function setTable(string $table): void
    {
        $this->table = $table;
    }

    /**
     * Retourne le nom de la table associée.
     *
     * @return string Nom de la table.
     */
    public function table(): string
    {
        return $this->table;
    }

    /**
     * Retourne l'instance PDO associée.
     *
     * @return PDO Instance PDO.
     */
    public function db(): PDO
    {
        return $this->db;
    }

    /**
     * Trouve un enregistrement par son ID.
     *
     * @param int $id ID de l'enregistrement.
     * @return array|null Enregistrement ou null si non trouvé.
     */
    public function findById(int $id): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Récupère tous les enregistrements de la table.
     *
     * @return array Liste des enregistrements.
     */
    protected function findAll(){

        $sql = "SELECT * FROM {$this->table}";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Supprime un enregistrement par son ID.
     *
     * @param int $id ID de l'enregistrement.
     * @return int Nombre de lignes supprimées.
     */
    public function deleteById(int $id): int
    {
        $sql = "DELETE FROM `{$this->table}` WHERE `id` = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]); // pas de bindValue
        return $stmt->rowCount();
    }

}
