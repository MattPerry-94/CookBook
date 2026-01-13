<?php
//require 'vendor/autoload.php';
require __DIR__ . '/vendor/autoload.php';

//var_dump(class_exists(\Twig\Loader\FilesystemLoader::class));
//var_dump(class_exists(\Twig\Environment::class));

use App\Database\Database;
use App\Controllers\AuthController;
use App\Controllers\SiteController;
use App\Controllers\AdminController;
use App\Controllers\RecipeController;
use App\Controllers\MessageController;
use App\Middlewares\AuthMiddleware;
use App\Models\MessageModel;
use Twig\Loader\FilesystemLoader;
use Twig\Environment;


$loader = new FilesystemLoader(__DIR__ . '/views');

// Pas de require manuels pour Twig ici

//$loader = new FilesystemLoader(__DIR__ . '/views');

$twig = new Environment($loader, [
    'cache' => false,
    'debug' => true,
]);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$pdo = Database::getInstance();

// Compter les messages non lus si l'utilisateur est connecté
$unreadCount = 0;
if (!empty($_SESSION['id_user'])) {
    $messageModel = new MessageModel($pdo);
    $unreadCount = $messageModel->countUnread((int) $_SESSION['id_user']);
}

// Variable globale Twig pour l'utilisateur courant
$twig->addGlobal('currentUser', $_SESSION['user'] ?? null);
$twig->addGlobal('currentUserId', $_SESSION['id_user'] ?? null); // Ajout ID utilisateur
$twig->addGlobal('isAdmin', (!empty($_SESSION['role']) && $_SESSION['role'] === 'admin'));
$twig->addGlobal('unreadMessagesCount', $unreadCount);

$router = new AltoRouter();
$router->setBasePath("/CookBook");

// map routes

$router->map('GET', '/', function () use ($pdo,$twig) {
    // Page d'accueil : liste publique des recettes
    $recipeController = new RecipeController($pdo, $twig);
    $recipeController->list();
});

$router->map('GET', '/signup', function () use ($pdo,$twig) {
    $AuthController = new AuthController($pdo,$twig);
    $AuthController->signup();   
});

$router->map('POST', '/signup', function () use ($pdo,$twig) {
    $email = $_POST["email"];
    $pwd = $_POST["password"];
    $username = $_POST["username"] ?? null;

    if(!empty($email) && isset($email) &&
       !empty($pwd) && isset($pwd) 
    )
    {
        $AuthController = new AuthController($pdo,$twig);
        $AuthController->register($email, $pwd, $username);
    }
});

$router->map("GET","/signin", function() use ($pdo,$twig){
    $AuthController = new AuthController($pdo,$twig);
    $AuthController->signin();
});

$router->map("POST","/signin", function() use ($pdo,$twig){
    $mail = $_POST["mail"];
    $pwd = $_POST["pwd"];
   
    $AuthController = new AuthController($pdo,$twig);
    $AuthController->login($mail,$pwd);
});

$router->map("GET","/admin", function () use($pdo, $twig){
    AuthMiddleware::authSession();
    $AdminController = new AdminController($pdo, $twig);
    $AdminController->index();
});

$router->map("GET","/admin/recipes", function () use($pdo, $twig){
    AuthMiddleware::authSession();
    $AdminController = new AdminController($pdo, $twig);
    $AdminController->recipes();
});

$router->map("POST","/admin/recipes/[i:id]/delete", function (int $id) use($pdo, $twig){
    AuthMiddleware::authSession();
    $AdminController = new AdminController($pdo, $twig);
    $AdminController->deleteRecipe($id);
});

$router->map("GET","/admin/users/[i:id]/edit", function (int $id) use($pdo, $twig){
    AuthMiddleware::authSession();
    $AdminController = new AdminController($pdo, $twig);
    $AdminController->editForm($id);
});

$router->map("POST","/admin/users/[i:id]/edit", function (int $id) use($pdo, $twig){
    AuthMiddleware::authSession();
    $AdminController = new AdminController($pdo, $twig);
    $AdminController->edit($id);
});

$router->map("POST","/admin/users/[i:id]/delete", function (int $id) use($pdo, $twig){
    AuthMiddleware::authSession();
    $AdminController = new AdminController($pdo, $twig);
    $AdminController->delete($id);
});

$router->map( 'GET', '/logout', function() use($pdo, $twig){
    AuthMiddleware::authSession();
    $AuthController = new AuthController($pdo, $twig);
    $AuthController->logout();
});

// Routes des recettes
$router->map('GET', '/recipes/[i:id]', function($id) use ($pdo, $twig) {
    $recipeController = new RecipeController($pdo, $twig);
    $recipeController->show($id);
});

$router->map('GET', '/my-recipes', function () use ($pdo, $twig) {
    AuthMiddleware::authSession();
    $recipeController = new RecipeController($pdo, $twig);
    $recipeController->myRecipes();
});

$router->map('GET', '/recipes/create', function () use ($pdo, $twig) {
    AuthMiddleware::authSession();
    $recipeController = new RecipeController($pdo, $twig);
    $recipeController->createForm();
});

$router->map('POST', '/recipes/create', function () use ($pdo, $twig) {
    AuthMiddleware::authSession();
    $recipeController = new RecipeController($pdo, $twig);
    $recipeController->create();
});

$router->map('GET', '/recipes/[i:id]/edit', function ($id) use ($pdo, $twig) {
    AuthMiddleware::authSession();
    $recipeController = new RecipeController($pdo, $twig);
    $recipeController->editForm($id);
});

$router->map('POST', '/recipes/[i:id]/edit', function ($id) use ($pdo, $twig) {
    AuthMiddleware::authSession();
    $recipeController = new RecipeController($pdo, $twig);
    $recipeController->edit($id);
});

$router->map('POST', '/recipes/[i:id]/delete', function ($id) use ($pdo, $twig) {
    AuthMiddleware::authSession();
    $recipeController = new RecipeController($pdo, $twig);
    $recipeController->delete($id);
});

$router->map('POST', '/recipes/[i:id]/comment', function ($id) use ($pdo, $twig) {
    AuthMiddleware::authSession();
    $recipeController = new RecipeController($pdo, $twig);
    $recipeController->addComment($id);
});

$router->map('POST', '/admin/comments/[i:id]/delete', function ($id) use ($pdo, $twig) {
    AuthMiddleware::authSession();
    $AdminController = new AdminController($pdo, $twig);
    $AdminController->deleteComment($id);
});

// Routes Messagerie
$router->map('GET', '/messages', function () use ($pdo, $twig) {
    AuthMiddleware::authSession();
    $messageController = new MessageController($pdo, $twig);
    $messageController->index();
});

$router->map('GET', '/messages/new', function () use ($pdo, $twig) {
    AuthMiddleware::authSession();
    $messageController = new MessageController($pdo, $twig);
    $messageController->newConversation();
});

$router->map('GET', '/messages/[i:id]', function ($id) use ($pdo, $twig) {
    AuthMiddleware::authSession();
    $messageController = new MessageController($pdo, $twig);
    $messageController->show($id);
});

$router->map('POST', '/messages/[i:id]', function ($id) use ($pdo, $twig) {
    AuthMiddleware::authSession();
    $messageController = new MessageController($pdo, $twig);
    $messageController->send($id);
});

$router->map('POST', '/messages/[i:id]/delete', function ($id) use ($pdo, $twig) {
    AuthMiddleware::authSession();
    $messageController = new MessageController($pdo, $twig);
    $messageController->delete($id);
});

$match = $router->match();


// call closure or throw 404 status
if( is_array($match) && is_callable( $match['target'] ) ) {
	call_user_func_array( $match['target'], $match['params'] );
} else {
	// no route was matched
	header( $_SERVER["SERVER_PROTOCOL"] . ' 404 Not Found');
}
