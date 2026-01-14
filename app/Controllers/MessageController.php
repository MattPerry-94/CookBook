<?php
namespace App\Controllers;

use App\Models\MessageModel;
use App\Models\UserModel;
use PDO;
use Twig\Environment;

class MessageController extends Controller
{
    private MessageModel $messageModel;
    private UserModel $userModel;

    /**
     * Constructeur du contrôleur Message.
     *
     * @param PDO $pdo Instance de la connexion PDO.
     * @param Environment $twig Instance de Twig.
     */
    public function __construct(PDO $pdo, Environment $twig)
    {
        parent::__construct($pdo, $twig);
        $this->messageModel = new MessageModel($pdo);
        $this->userModel    = new UserModel($pdo);
    }

    /**
     * Vérifie si l'utilisateur est connecté.
     * Redirige vers la page de connexion si ce n'est pas le cas.
     *
     * @return void
     */
    private function ensureAuth(): void
    {
        if (empty($_SESSION['id_user'])) {
            header('Location: /CookBook/signin');
            exit;
        }
    }

    /**
     * Affiche la liste des conversations de l'utilisateur.
     *
     * @return void
     */
    public function index(): void
    {
        $this->ensureAuth();
        $userId = (int) $_SESSION['id_user'];
        $conversations = $this->messageModel->getConversations($userId);

        $this->render('messages/index.html.twig', [
            'conversations' => $conversations,
        ]);
    }

    /**
     * Affiche la page de création d'une nouvelle conversation.
     *
     * @return void
     */
    public function newConversation(): void
    {
        $this->ensureAuth();
        $currentUserId = (int) $_SESSION['id_user'];
        $users = $this->userModel->allUsers();
        
        // Filtrer pour ne pas s'afficher soi-même
        $users = array_filter($users, fn($u) => $u['id'] != $currentUserId);

        $this->render('messages/new.html.twig', [
            'users' => $users,
        ]);
    }

    /**
     * Affiche une conversation spécifique avec un autre utilisateur.
     *
     * @param int $otherUserId ID de l'autre utilisateur.
     * @return void
     */
    public function show(int $otherUserId): void
    {
        $this->ensureAuth();
        $currentUserId = (int) $_SESSION['id_user'];

        if ($currentUserId === $otherUserId) {
            header('Location: /CookBook/messages');
            return;
        }

        $otherUser = $this->userModel->findById($otherUserId);
        if (!$otherUser) {
            header('Location: /CookBook/messages');
            return;
        }

        // Marquer les messages comme lus
        $this->messageModel->markAsRead($currentUserId, $otherUserId);

        $messages = $this->messageModel->getMessagesBetween($currentUserId, $otherUserId);

        $this->render('messages/show.html.twig', [
            'messages'  => $messages,
            'otherUser' => $otherUser,
        ]);
    }

    /**
     * Envoie un message à un utilisateur.
     *
     * @param int $receiverId ID du destinataire.
     * @return void
     */
    public function send(int $receiverId): void
    {
        $this->ensureAuth();
        $currentUserId = (int) $_SESSION['id_user'];
        $content = trim($_POST['content'] ?? '');

        if ($content !== '') {
            $this->messageModel->create($currentUserId, $receiverId, $content);
        }

        header('Location: /CookBook/messages/' . $receiverId);
    }

    /**
     * Supprime une conversation avec un contact.
     *
     * @param int $contactId ID du contact avec qui supprimer la conversation.
     * @return void
     */
    public function delete(int $contactId): void
    {
        $this->ensureAuth();
        $currentUserId = (int) $_SESSION['id_user'];
        
        $this->messageModel->deleteConversation($currentUserId, $contactId);
        
        header('Location: /CookBook/messages');
    }
}
