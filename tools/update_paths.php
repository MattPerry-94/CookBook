<?php
/**
 * Script de maintenance pour mettre à jour les chemins d'images des recettes.
 *
 * Remplace les anciens chemins /application/public/uploads/ par
 * /CookBook/public/uploads/ dans la table recipes.
 */
require __DIR__ . '/../vendor/autoload.php';

use App\Database\Database;

$pdo = Database::getInstance();

echo "Updating image paths...\n";

// Update paths replacing /application/ with /CookBook/
$sql = "UPDATE recipes 
        SET image_path = REPLACE(image_path, '/application/public/uploads/', '/CookBook/public/uploads/') 
        WHERE image_path LIKE '/application/public/uploads/%'";

$stmt = $pdo->prepare($sql);
$stmt->execute();

$count = $stmt->rowCount();
echo "Updated $count recipes.\n";

// Verify
$stmt = $pdo->query("SELECT id, title, image_path FROM recipes WHERE image_path IS NOT NULL AND image_path != ''");
$recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Current paths:\n";
foreach ($recipes as $recipe) {
    echo "ID: {$recipe['id']} - Title: {$recipe['title']} - Path: {$recipe['image_path']}\n";
}
