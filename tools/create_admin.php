<?php
/**
 * Petit outil de création d'un utilisateur administrateur pour CookBook.
 *
 * À lancer en local pour créer rapidement un compte admin par défaut.
 */

require __DIR__ . '/../vendor/autoload.php';

use App\Database\Database;
use App\Models\UserModel;

$pdo       = Database::getInstance();
$userModel = new UserModel($pdo);

// Identifiant et mot de passe demandés
$login    = 'MattP';
$password = 'mdp123';

// On utilise l'identifiant comme "name" et un email simple basé dessus
$email = 'mattp@example.com';

$created = $userModel->createAdmin($email, $password, $login);

if ($created) {
    echo "Administrateur créé avec succès.\n";
    echo "Identifiant (email de connexion) : {$email}\n";
    echo "Nom affiché : {$login}\n";
    echo "Mot de passe : {$password}\n";
} else {
    echo "Un utilisateur avec l'email {$email} existe déjà. Aucun admin créé.\n";
}


