<?php
namespace App\Controllers;

use App\Models\RecipeModel;
use App\Models\CategoryModel;
use PDO;
use Twig\Environment;

class RecipeController extends Controller
{
    private RecipeModel $recipeModel;
    private CategoryModel $categoryModel;

    public function __construct(PDO $pdo, Environment $twig)
    {
        parent::__construct($pdo, $twig);
        $this->recipeModel   = new RecipeModel($pdo);
        $this->categoryModel = new CategoryModel($pdo);
    }

    // Liste publique des recettes (page d'accueil)
    public function list(): void
    {
        $q = isset($_GET['q']) ? trim($_GET['q']) : '';
        if ($q !== '') {
            $recipes = $this->recipeModel->searchPublic($q);
        } else {
            $recipes = $this->recipeModel->findPublicList();
        }

        $this->render('home.html.twig', [
            'recipes' => $recipes,
            'query'   => $q,
        ]);
    }

    // Détail d'une recette
    public function show(int $id): void
    {
        $sql  = "SELECT r.*, u.name AS author_name, u.email AS author_email, c.name AS category_name
                 FROM recipes r
                 JOIN users u ON r.user_id = u.id
                 LEFT JOIN categories c ON r.category_id = c.id
                 WHERE r.id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $recipe = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$recipe) {
            header($_SERVER["SERVER_PROTOCOL"] . ' 404 Not Found');
            echo "Recette introuvable";
            return;
        }

        $this->render('recipe_show.html.twig', [
            'recipe' => $recipe,
        ]);
    }

    // Liste des recettes de l'utilisateur connecté
    public function myRecipes(): void
    {
        if (empty($_SESSION['id_user'])) {
            header('Location: /CookBook/signin');
            exit;
        }

        $userId  = (int) $_SESSION['id_user'];
        $recipes = $this->recipeModel->findAllByUser($userId);

        $this->render('recipes_my.html.twig', [
            'recipes' => $recipes,
        ]);
    }

    // Formulaire de création
    public function createForm(): void
    {
        if (empty($_SESSION['id_user'])) {
            header('Location: /CookBook/signin');
            exit;
        }

        $categories = $this->categoryModel->findAll();
        $this->render('recipe_form.html.twig', [
            'categories' => $categories,
            'mode'       => 'create',
        ]);
    }

    // Traitement création
    public function create(): void
    {
        if (empty($_SESSION['id_user'])) {
            header('Location: /CookBook/signin');
            exit;
        }

        $title       = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $ingredients = trim($_POST['ingredients'] ?? '');
        $steps       = trim($_POST['steps'] ?? '');
        $categoryId  = !empty($_POST['category_id']) ? (int) $_POST['category_id'] : null;

        // Règles minimales : titre, ingrédients, étapes
        if ($title === '' || $ingredients === '' || $steps === '') {
            $categories = $this->categoryModel->findAll();
            $this->render('recipe_form.html.twig', [
                'categories' => $categories,
                'mode'       => 'create',
                'error'      => 'Veuillez remplir au minimum le titre, les ingrédients et les étapes.',
                'old'        => $_POST,
            ]);
            return;
        }

        $imagePath = $this->handleUpload();

        $data = [
            'user_id'     => (int) $_SESSION['id_user'],
            'category_id' => $categoryId,
            'title'       => $title,
            'description' => $description,
            'ingredients' => $ingredients,
            'steps'       => $steps,
            'image_path'  => $imagePath,
        ];

        $this->recipeModel->create($data);
        header('Location: /CookBook/my-recipes');
    }

    // Formulaire d'édition
    public function editForm(int $id): void
    {
        if (empty($_SESSION['id_user'])) {
            header('Location: /CookBook/signin');
            exit;
        }

        $userId = (int) $_SESSION['id_user'];
        $recipe = $this->recipeModel->findByIdForUser($id, $userId);
        if (!$recipe) {
            header($_SERVER["SERVER_PROTOCOL"] . ' 404 Not Found');
            echo "Recette introuvable ou non autorisée.";
            return;
        }

        $categories = $this->categoryModel->findAll();
        $this->render('recipe_form.html.twig', [
            'categories' => $categories,
            'recipe'     => $recipe,
            'mode'       => 'edit',
        ]);
    }

    // Traitement édition
    public function edit(int $id): void
    {
        if (empty($_SESSION['id_user'])) {
            header('Location: /CookBook/signin');
            exit;
        }

        $userId = (int) $_SESSION['id_user'];
        $recipe = $this->recipeModel->findByIdForUser($id, $userId);
        if (!$recipe) {
            header($_SERVER["SERVER_PROTOCOL"] . ' 404 Not Found');
            echo "Recette introuvable ou non autorisée.";
            return;
        }

        $title       = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $ingredients = trim($_POST['ingredients'] ?? '');
        $steps       = trim($_POST['steps'] ?? '');
        $categoryId  = !empty($_POST['category_id']) ? (int) $_POST['category_id'] : null;

        if ($title === '' || $ingredients === '' || $steps === '') {
            $categories = $this->categoryModel->findAll();
            $this->render('recipe_form.html.twig', [
                'categories' => $categories,
                'mode'       => 'edit',
                'recipe'     => $recipe,
                'error'      => 'Veuillez remplir au minimum le titre, les ingrédients et les étapes.',
            ]);
            return;
        }

        $imagePath = $this->handleUpload($recipe['image_path']);

        $data = [
            'category_id' => $categoryId,
            'title'       => $title,
            'description' => $description,
            'ingredients' => $ingredients,
            'steps'       => $steps,
            'image_path'  => $imagePath,
        ];

        $this->recipeModel->updateForUser($id, $userId, $data);
        header('Location: /application/my-recipes');
    }

    // Suppression
    public function delete(int $id): void
    {
        if (empty($_SESSION['id_user'])) {
            header('Location: /CookBook/signin');
            exit;
        }

        $userId = (int) $_SESSION['id_user'];
        $recipe = $this->recipeModel->findByIdForUser($id, $userId);
        if ($recipe) {
            $this->recipeModel->deleteById($id);
        }
        header('Location: /CookBook/my-recipes');
    }

    /**
     * Gestion simple de l'upload d'image (type + taille)
     */
    private function handleUpload(?string $currentPath = null): ?string
    {
        if (empty($_FILES['image']) || $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE) {
            return $currentPath; // pas de nouvelle image
        }

        $file = $_FILES['image'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return $currentPath;
        }

        // 2 Mo max
        if ($file['size'] > 2 * 1024 * 1024) {
            return $currentPath;
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($file['type'], $allowedTypes, true)) {
            return $currentPath;
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = 'recipe_' . time() . '_' . mt_rand(1000, 9999) . '.' . $ext;

        $uploadDir  = __DIR__ . '/../../public/uploads';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $destination = $uploadDir . '/' . $fileName;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            return $currentPath;
        }

        // Chemin accessible depuis le navigateur (en supposant /application/public comme racine)
        return '/application/public/uploads/' . $fileName;
    }
}


