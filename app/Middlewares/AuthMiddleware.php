<?php


namespace App\Middlewares;

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
            $basePath = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
            header('Location: ' . ($basePath !== '' ? $basePath : '') . '/');
            if (PHP_SAPI !== 'cli') {
                exit;
            }
        }
    }
}

