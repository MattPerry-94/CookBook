<?php

namespace App\Controllers;

use PDO;
use Twig\Environment;

abstract class Controller {

    protected PDO $pdo;
    protected Environment $twig;  // <-- ICI : Environment (Twig), pas Environnement

    public function __construct(PDO $pdo, Environment $twig)
    {
        $this->pdo  = $pdo;
        $this->twig = $twig;
    }

    protected function json($json)
    {
        return $json;
    }

    protected function render(string $template, ?array $params = []): void
    {
        echo $this->twig->render($template, $params);
    }
}
