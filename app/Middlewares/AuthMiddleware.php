<?php


namespace App\middlewares;

final class AuthMiddleware{

    /**
     * Vérifie l'authentification via session PHP.
     * Redirige vers la page d'accueil si non connecté.
     *
     * @return void
     */
    public static function authSession(): void
    {
        if(session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (empty($_SESSION["user"]))
        {
            header('Location: https://cookbook.fm-tech.fr/');
            if (PHP_SAPI !== 'cli') {
                exit;
            }
        }
    }
}

