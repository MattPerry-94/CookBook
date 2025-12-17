<?php
namespace App\Models;

use PDO;
use App\Models\Model;

class UserModel extends Model {

     public function __construct(PDO $db)
    {
        
        parent::__construct($db, 'users');
    }

    public function addUser(string $mail,string $pwd){
        $request = "INSERT INTO {$this->table} (email,pwd,active) VALUES(:email,:pwd,1)";
        $stmt = $this->db->prepare($request);
        $stmt->execute(["email"=>$mail,"pwd"=>$pwd]);
        return $stmt->rowCount();
    }

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
    public function findByEmail($email)
    {
        $sql = "SELECT * FROM users WHERE email = :email";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(["email" => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function allUsers(): array
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY created_at DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

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



