<?php
namespace App\Models;

use PDO;

class MessageModel extends Model
{
    public function __construct(PDO $db)
    {
        parent::__construct($db, 'messages');
    }

    public function create(int $senderId, int $receiverId, string $content): bool
    {
        $sql = "INSERT INTO {$this->table} (sender_id, receiver_id, content) 
                VALUES (:sender_id, :receiver_id, :content)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'sender_id'   => $senderId,
            'receiver_id' => $receiverId,
            'content'     => $content
        ]);
    }

    // Récupère la liste des conversations (dernier message par interlocuteur)
    public function getConversations(int $userId): array
    {
        // On évite les paramètres nommés multiples identiques qui peuvent poser problème avec certains drivers PDO
        // ou si l'émulation des requêtes préparées est désactivée.
        
        $sql = "
            SELECT 
                m.*,
                u.name as contact_name,
                u.email as contact_email,
                CASE 
                    WHEN m.sender_id = :uid1 THEN m.receiver_id 
                    ELSE m.sender_id 
                END as contact_id
            FROM messages m
            JOIN users u ON u.id = (CASE WHEN m.sender_id = :uid2 THEN m.receiver_id ELSE m.sender_id END)
            WHERE m.id IN (
                SELECT MAX(id)
                FROM messages
                WHERE sender_id = :uid3 OR receiver_id = :uid4
                GROUP BY CASE WHEN sender_id = :uid5 THEN receiver_id ELSE sender_id END
            )
            ORDER BY m.created_at DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'uid1' => $userId,
            'uid2' => $userId,
            'uid3' => $userId,
            'uid4' => $userId,
            'uid5' => $userId
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupère tous les messages entre deux utilisateurs
    public function getMessagesBetween(int $userId1, int $userId2): array
    {
        $sql = "SELECT m.*, u.name as sender_name 
                FROM {$this->table} m
                JOIN users u ON m.sender_id = u.id
                WHERE (sender_id = :u1a AND receiver_id = :u2a)
                   OR (sender_id = :u2b AND receiver_id = :u1b)
                ORDER BY created_at ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'u1a' => $userId1,
            'u2a' => $userId2,
            'u2b' => $userId2,
            'u1b' => $userId1
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countUnread(int $userId): int
    {
        $sql = "SELECT COUNT(*) as c FROM {$this->table} WHERE receiver_id = :userId AND is_read = 0";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['userId' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) ($row['c'] ?? 0);
    }

    public function markAsRead(int $userId, int $otherUserId): void
    {
        $sql = "UPDATE {$this->table} 
                SET is_read = 1 
                WHERE receiver_id = :userId AND sender_id = :otherUserId AND is_read = 0";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'userId'      => $userId,
            'otherUserId' => $otherUserId
        ]);
    }
}
