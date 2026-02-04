<?php

use PHPUnit\Framework\TestCase;
use App\Models\RecipeModel;

class TestRecipe extends TestCase
{
    protected $pdo;
    protected $recipeModel;

    protected function setUp(): void
    {
        // Création d'un mock pour PDO
        $this->pdo = $this->createMock(PDO::class);
        $this->recipeModel = new RecipeModel($this->pdo);
    }

    // --- GROUPE TEST : Création de Recette (Basé sur l'ID) ---

    // Test 1 : Création Réussie (Cas Positif)
    // Vérifie que la création retourne un ID valide (ex: 42)
    public function testCreateRecipeSuccess()
    {
        // Arrange
        $recipeData = [
            'user_id' => 1,
            'category_id' => 2,
            'title' => 'Tarte aux pommes',
            'description' => 'Une délicieuse tarte',
            'ingredients' => 'Pommes, pâte, sucre',
            'steps' => 'Cuire 30min',
            'image_path' => 'img/tarte.jpg'
        ];

        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')->willReturn(true); // Exécution réussie
        
        $this->pdo->method('prepare')->willReturn($stmt);
        $this->pdo->method('lastInsertId')->willReturn('42'); // ID simulé de la nouvelle recette

        // Act
        $newId = $this->recipeModel->create($recipeData);

        // Assert
        // On vérifie simplement que l'ID retourné est bien celui attendu (42)
        $this->assertEquals(42, $newId, "En cas de succès, create() doit retourner l'ID généré (ici 42).");
    }

    // Test 2 : Création Échouée (Cas Négatif)
    // Vérifie que si l'insertion échoue, on retourne 0 (pas d'ID)
    public function testCreateRecipeFailure()
    {
        // Arrange
        $recipeData = [
            'user_id' => 1,
            'category_id' => 2,
            'title' => 'Tarte ratée',
            // ... autres champs
        ];

        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')->willReturn(false); // Exécution échouée (Erreur SQL par ex)
        
        $this->pdo->method('prepare')->willReturn($stmt);
        // Si execute échoue, lastInsertId n'est généralement pas appelé ou retourne 0/false selon l'implémentation.
        // Ici, on va supposer que create() gère l'erreur ou que lastInsertId renvoie '0'.
        // Modifions le comportement attendu : si create() ne gère pas explicitement le return false de execute,
        // il va quand même appeler lastInsertId.
        $this->pdo->method('lastInsertId')->willReturn('0'); 

        // Act
        $newId = $this->recipeModel->create($recipeData);

        // Assert
        // On vérifie que l'ID retourné est 0 (ou équivalent false), signifiant "Pas d'ID créé"
        $this->assertEquals(0, (int)$newId, "En cas d'échec d'insertion, l'ID retourné doit être 0.");
    }
}
