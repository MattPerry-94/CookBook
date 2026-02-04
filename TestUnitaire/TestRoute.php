<?php

use PHPUnit\Framework\TestCase;

class TestRoute extends TestCase
{
    // --- GROUPE TEST : Routage (AltoRouter) ---

    // Test 1 : Route existante (Cas Positif)
    // Vérifie qu'une route définie (ex: /) est bien reconnue
    public function testRouteExists()
    {
        // Arrange
        $router = new AltoRouter();
        $router->setBasePath('/CookBook');
        
        // On mappe une route de test pour être sûr de l'environnement
        $router->map('GET', '/', function() { return 'home'; }, 'home');

        // Act
        // Simulation d'une requête GET sur /CookBook/
        // AltoRouter utilise $_SERVER['REQUEST_URI'] par défaut, mais match() prend aussi une URL en paramètre.
        // Attention : AltoRouter::match($requestUrl) compare avec les routes définies.
        // Si setBasePath est utilisé, il faut que l'URL passée à match() inclue ou non le base path selon l'implémentation.
        // Après vérification d'AltoRouter : match($requestUrl) doit contenir l'URL complète relative au serveur.
        // Si on mappe '/', et basepath '/CookBook', l'URL attendue est '/CookBook/'
        
        $match = $router->match('/CookBook/', 'GET');

        // Assert
        $this->assertIsArray($match, "La route '/' devrait être trouvée");
        $this->assertEquals('home', $match['name'], "Le nom de la route devrait être 'home'");
    }

    // Test 2 : Route inexistante (Cas Négatif)
    // Vérifie qu'une route inconnue renvoie false (404)
    public function testRouteNotFound()
    {
        // Arrange
        $router = new AltoRouter();
        $router->setBasePath('/CookBook');
        
        $router->map('GET', '/', function() { return 'home'; }, 'home');

        // Act
        // Simulation d'une requête sur une URL qui n'existe pas
        $match = $router->match('/CookBook/page-qui-n-existe-pas-404', 'GET');

        // Assert
        $this->assertFalse($match, "Une route inexistante devrait renvoyer false");
    }
}
