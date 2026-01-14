<?php
/**
 * Script de vérification des chemins d'images des recettes.
 *
 * Affiche les recettes ayant un chemin d'image non vide.
 */
require __DIR__ . '/../vendor/autoload.php';

use App\Database\Database;

$pdo = Database::getInstance();

$stmt = $pdo->query("SELECT id, title, image_path FROM recipes WHERE image_path IS NOT NULL AND image_path != ''");
$recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Found " . count($recipes) . " recipes with images.\n";
foreach ($recipes as $recipe) {
    echo "ID: {$recipe['id']} - Title: {$recipe['title']} - Path: {$recipe['image_path']}\n";
}
