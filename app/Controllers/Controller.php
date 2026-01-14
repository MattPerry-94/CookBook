<?php

namespace App\Controllers;

use PDO;
use Twig\Environment;

abstract class Controller {

    /**
     * Connexion PDO à la base de données.
     *
     * @var PDO
     */
    protected PDO $pdo;

    /**
     * Moteur de templates Twig.
     *
     * @var Environment
     */
    protected Environment $twig;

    /**
     * Constructeur de base pour tous les contrôleurs.
     *
     * @param PDO $pdo Instance de la connexion PDO.
     * @param Environment $twig Instance de Twig.
     */
    public function __construct(PDO $pdo, Environment $twig)
    {
        $this->pdo  = $pdo;
        $this->twig = $twig;
    }

    /**
     * Retourne une réponse JSON (méthode simple).
     *
     * @param mixed $json Données à retourner.
     * @return mixed Données passées en paramètre.
     */
    protected function json($json)
    {
        return $json;
    }

    /**
     * Rend un template Twig avec des paramètres.
     *
     * @param string $template Nom du template Twig.
     * @param array|null $params Paramètres passés au template.
     * @return void
     */
    protected function render(string $template, ?array $params = []): void
    {
        echo $this->twig->render($template, $params);
    }
}
