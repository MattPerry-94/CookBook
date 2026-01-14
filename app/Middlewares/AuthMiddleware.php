<?php


namespace App\middlewares;

use App\Middlewares\JwtService;

final class AuthMiddleware{

    /**
     * Vérifie l'authentification via API (Bearer Token).
     *
     * @return void
     */
    public static function authApi(): void
    {
        $header = getallheaders();
        $auth = $headers["Authorization"] ?? null;

        if(!$auth || !str_starts_with($auth, "Bearer")) {
            http_response_code(401);
            echo json_encode(["error" => "Missing or invalid token"]);
            exit;
        }

        $token = substr($auth, 7);

        try {
            $payload = JwtService::verify($token);
            $_REQUEST["auth_user"] = $payload;
    
        } catch (\Exception $e) {
            http_response_code(401);
            echo json_encode(["error" => "Token invalid"]);
            exit;
        }
    }

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
            header("Location: /CookBook");
            exit;
        }
    }
}

