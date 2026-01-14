<?php
namespace App\Models;

use PDO;
use App\Models\Model;

class UserModel extends Model {

    /**
     * Constructeur du modèle User.
     *
     * @param PDO $db Instance de la connexion PDO.
     */
     public function __construct(PDO $db)
    {
        
        parent::__construct($db, 'users');
    }

    /**
     * Ajoute un nouvel utilisateur.
     *
     * @param string $mail Email de l'utilisateur.
     * @param string $pwd Mot de passe hashé de l'utilisateur.
     * @param string|null $username Pseudo de l'utilisateur (optionnel).
     * @return int ID de l'utilisateur créé ou 0 si échec.
     */
    public function addUser(string $mail, string $pwd, ?string $username = null){
        $request = "INSERT INTO {$this->table} (email, pwd, name, active) VALUES(:email, :pwd, :name, 1)";
        $stmt = $this->db->prepare($request);
        $stmt->execute([
            "email" => $mail,
            "pwd"   => $pwd,
            "name"  => $username
        ]);
        
        // Retourne l'ID du nouvel utilisateur ou 0 si échec
        if ($stmt->rowCount() > 0) {
            return (int) $this->db->lastInsertId();
        }
        return 0;
    }

    /**
     * Vérifie les identifiants de connexion.
     *
     * @param string $email Email de l'utilisateur.
     * @param string $password Mot de passe en clair.
     * @return array|false Retourne les données de l'utilisateur si succès, sinon false.
     */
    public function login(string $email, string $password): array|false
    {
        $sql ="SELECT * FROM {$this->table} WHERE email = :email LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(["email" => $email]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if(!$user){
            return false;
        }

        // compte désactivé ?
        if (isset($user['active']) && (int)$user['active'] === 0) {
            return false;
        }

        if(!password_verify($password, $user["pwd"])){
            return false;
        }

        return $user;

    }

    /**
     * Trouve un utilisateur par son email.
     *
     * @param string $email Email de l'utilisateur.
     * @return array|false Données de l'utilisateur ou false si non trouvé.
     */
    public function findByEmail($email)
    {
        $sql = "SELECT * FROM users WHERE email = :email";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(["email" => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère tous les utilisateurs.
     *
     * @return array Liste des utilisateurs.
     */
    public function allUsers(): array
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY created_at DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Met à jour les informations d'un utilisateur.
     *
     * @param int $id ID de l'utilisateur.
     * @param string $email Nouvel email.
     * @param string|null $name Nouveau nom.
     * @param string $role Nouveau rôle.
     * @param int $active Statut d'activation (0 ou 1).
     * @return int Nombre de lignes affectées.
     */
    public function updateUser(int $id, string $email, ?string $name, string $role, int $active): int
    {
        $sql = "UPDATE {$this->table}
                SET email = :email,
                    name  = :name,
                    role  = :role,
                    active = :active
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'email' => $email,
            'name'  => $name,
            'role'  => $role,
            'active'=> $active,
            'id'    => $id,
        ]);
        return $stmt->rowCount();
    }

    /**
     * Crée un utilisateur administrateur si l'email n'existe pas encore.
     *
     * @param string $email Email de l'admin.
     * @param string $password Mot de passe de l'admin.
     * @param string|null $name Nom de l'admin.
     * @return bool Retourne true si créé, false sinon.
     */
    public function createAdmin(string $email, string $password, ?string $name = null): bool
    {
        $existing = $this->findByEmail($email);
        if ($existing) {
            return false;
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $sql  = "INSERT INTO {$this->table} (name, email, pwd, role)
                 VALUES (:name, :email, :pwd, 'admin')";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'name'  => $name,
            'email' => $email,
            'pwd'   => $hash,
        ]);
    }


}



