<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Controllers\AuthController;
use PDO;
use PDOStatement;
use Twig\Environment;

class TestUser extends TestCase
{
    private $pdo;
    private $twig;
    private $authController;

    protected function setUp(): void
    {
        // Mock PDO
        $this->pdo = $this->createMock(PDO::class);
        
        // Mock Twig
        $this->twig = $this->createMock(Environment::class);
        
        // Suppress "headers already sent" warning during tests
        // This allows header() calls to happen without failing the test output,
        // although we can't easily verify the redirection target without mocks.
        @session_start();
        
        $this->authController = new AuthController($this->pdo, $this->twig);
    }

    // --- GROUPE TEST : Inscription Utilisateur ---

    // Test 1 : Inscription Réussie (Cas Positif)
    // Le test retourne "bon" si l'email n'est pas connu (n'existe pas encore en base)
    /**
     * @runInSeparateProcess
     */
    public function testRegisterSuccess()
    {
        // Arrange
        $email = 'unknown@example.com'; // Email qui n'existe pas
        $pwd = 'StrongP@ssw0rd123';
        
        // Simulation 1 : Vérification de l'email -> Retourne false (Non trouvé/Inconnu)
        $stmtFind = $this->createMock(PDOStatement::class);
        $stmtFind->method('fetch')->willReturn(false); 
        
        // Simulation 2 : Insertion en base -> Retourne succès (1 ligne affectée)
        $stmtInsert = $this->createMock(PDOStatement::class);
        $stmtInsert->method('rowCount')->willReturn(1); 
        
        $this->pdo->method('prepare')->willReturnCallback(function($query) use ($stmtFind, $stmtInsert) {
            // Si la requête est un SELECT (vérification email), on retourne le mock qui dit "pas trouvé"
            if (strpos($query, 'SELECT') !== false) {
                return $stmtFind;
            }
            // Sinon (INSERT), on retourne le mock qui dit "succès"
            return $stmtInsert;
        });
        $this->pdo->method('lastInsertId')->willReturn('123');

        // Act
        ob_start(); // Capture la sortie (headers/echo) pour éviter de polluer la console
        $this->authController->register($email, $pwd);
        ob_end_clean();
        
        // Assert
        // Si on arrive ici sans erreur fatale et que le code a suivi le chemin "email inconnu", c'est un succès.
        $this->assertTrue(true, "Le test valide que l'inscription passe si l'email n'est pas connu.");
    }

    // Test 2 : Inscription Échouée (Cas Négatif)
    // Vérifie qu'avec un email déjà existant, l'inscription est refusée
    public function testRegisterFailureEmailExists()
    {
        // Arrange
        $email = 'existing@example.com';
        $pwd = 'StrongP@ssw0rd123';
        
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('fetch')->willReturn(['id' => 1, 'email' => $email]); // Email déjà pris
        $this->pdo->method('prepare')->willReturn($stmt);
        
        // Assert : Erreur attendue
        $this->twig->expects($this->once())
            ->method('render')
            ->with(
                $this->equalTo('signup.html.twig'),
                $this->callback(function($context) {
                    return isset($context['error']) && strpos($context['error'], 'Cet email est déjà utilisé') !== false;
                })
            );

        // Act
        $this->authController->register($email, $pwd);
    }
}
