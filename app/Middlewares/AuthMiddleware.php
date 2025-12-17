<?php


namespace App\middlewares;

final class AuthMiddleware{

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
            $payload = JwtSeervice::verify($token);
            $_REQUEST["auth_user"] = $payload;
    
        } catch (\Exception $e) {
            http_response_code(401);
            echo json_encode(["error" => "Token invalid"]);
            exit;
        }
    }

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

