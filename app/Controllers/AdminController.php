<?php
namespace App\Controllers;
use Twig\Environment;
use App\Middlewares\JwtService;
use App\Models\UserModel;
use App\Models\RecipeModel;
use App\Models\CommentModel;

use PDO;

final class AdminController extends Controller{

    /**
     * Constructeur du contrôleur Admin.
     *
     * @param PDO $pdo Instance de la connexion PDO.
     * @param Environment $twig Instance de Twig.
     */
     public function __construct(PDO $pdo, Environment $twig)
    {
        parent::__construct($pdo, $twig);
    }

    /**
     * Vérifie que l'utilisateur connecté est administrateur.
     * Redirige vers l'accueil ou la connexion sinon.
     *
     * @return void
     */
    private function ensureAdmin(): void
    {
        if (empty($_SESSION['id_user'])) {
            header('Location: /CookBook/signin');
            exit;
        }

        $userModel = new UserModel($this->pdo);
        $current   = $userModel->findById((int) $_SESSION['id_user']);

        if (!$current || $current['role'] !== 'admin') {
            header('Location: /CookBook');
            exit;
        }
    }

    /**
     * Tableau de bord principal de l'administration.
     *
     * @return void
     */
    public function index(): void
    {
        $this->ensureAdmin();
        $userModel   = new UserModel($this->pdo);
        $users       = $userModel->allUsers();

        // Statistiques simples
        $totalUsers      = count($users);
        $totalAdmins     = 0;
        $totalActive     = 0;
        $totalInactive   = 0;

        foreach ($users as $u) {
            if (!empty($u['role']) && $u['role'] === 'admin') {
                $totalAdmins++;
            }
            if (!isset($u['active']) || (int)$u['active'] === 1) {
                $totalActive++;
            } else {
                $totalInactive++;
            }
        }

        // Nombre de recettes
        $recipeCount = 0;
        $stmt = $this->pdo->query("SELECT COUNT(*) AS c FROM recipes");
        if ($stmt) {
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            $recipeCount = (int)($row['c'] ?? 0);
        }

        $this->render('admin.html.twig', [
            'users'         => $users,
            'totalUsers'    => $totalUsers,
            'totalAdmins'   => $totalAdmins,
            'totalActive'   => $totalActive,
            'totalInactive' => $totalInactive,
            'recipeCount'   => $recipeCount,
        ]);
    }

    /**
     * Affiche le formulaire d'édition d'un utilisateur.
     *
     * @param int $id ID de l'utilisateur à éditer.
     * @return void
     */
    public function editForm(int $id): void
    {
        $this->ensureAdmin();
        $userModel = new UserModel($this->pdo);
        $user      = $userModel->findById($id);

        if (!$user) {
            header('Location: /CookBook/admin');
            return;
        }

        $this->render('admin_user_edit.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * Traite la mise à jour des informations d'un utilisateur.
     *
     * @param int $id ID de l'utilisateur.
     * @return void
     */
    public function edit(int $id): void
    {
        $this->ensureAdmin();
        $userModel = new UserModel($this->pdo);
        $user      = $userModel->findById($id);

        if (!$user) {
            header('Location: /CookBook/admin');
            return;
        }

        $email = trim($_POST['email'] ?? '');
        $name  = trim($_POST['name'] ?? '');
        $role  = $_POST['role'] ?? 'user';
        $active = isset($_POST['active']) ? 1 : 0;

        if ($email === '' || !in_array($role, ['user','admin'], true)) {
            $this->render('admin_user_edit.html.twig', [
                'user'  => $user,
                'error' => 'Email et rôle sont obligatoires.',
            ]);
            return;
        }

        $userModel->updateUser($id, $email, $name !== '' ? $name : null, $role, $active);
        header('Location: /CookBook/admin');
    }

    /**
     * Supprime un utilisateur (sauf soi-même).
     *
     * @param int $id ID de l'utilisateur à supprimer.
     * @return void
     */
    public function delete(int $id): void
    {
        $this->ensureAdmin();
        $userModel = new UserModel($this->pdo);

        // On évite de supprimer son propre compte admin
        if ($id === (int) $_SESSION['id_user']) {
            header('Location: /CookBook/admin');
            return;
        }

        $userModel->deleteById($id);
        header('Location: /CookBook/admin');
    }

    /**
     * Liste toutes les recettes pour modération.
     *
     * @return void
     */
    public function recipes(): void
    {
        $this->ensureAdmin();
        $recipeModel = new RecipeModel($this->pdo);
        $recipes     = $recipeModel->findAllWithUser();

        $this->render('admin_recipes.html.twig', [
            'recipes' => $recipes,
        ]);
    }

    /**
     * Supprime une recette en tant qu'admin.
     *
     * @param int $id ID de la recette.
     * @return void
     */
    public function deleteRecipe(int $id): void
    {
        $this->ensureAdmin();
        $recipeModel = new RecipeModel($this->pdo);
        $recipeModel->deleteById($id);
        header('Location: /CookBook/admin/recipes');
    }

    /**
     * Supprime un commentaire en tant qu'admin.
     *
     * @param int $id ID du commentaire.
     * @return void
     */
    public function deleteComment(int $id): void
    {
        $this->ensureAdmin();
        $commentModel = new CommentModel($this->pdo);
        
        // On pourrait récupérer le recipe_id avant de supprimer pour rediriger vers la recette
        // Mais pour faire simple, on va utiliser HTTP_REFERER ou rediriger vers l'accueil
        $commentModel->delete($id);
        
        if (!empty($_SERVER['HTTP_REFERER'])) {
            header('Location: ' . $_SERVER['HTTP_REFERER']);
        } else {
            header('Location: /CookBook/');
        }
    }

}
