<?php

namespace App\Middlewares;

class JwtService
{
    private static string $secret = 'votre_secret_jwt_ici';

    /**
     * Vérifie et décode un token JWT.
     *
     * @param string $token Le token JWT à vérifier.
     * @return array Le payload du token décodé.
     * @throws \Exception Si le token est invalide.
     */
    public static function verify(string $token): array
    {
        // Implémentation basique/dummy pour l'instant
        // Idéalement, utiliser une librairie comme firebase/php-jwt
        
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            throw new \Exception("Invalid token format");
        }

        $payload = json_decode(base64_decode($parts[1]), true);
        if (!$payload) {
            throw new \Exception("Invalid token payload");
        }

        // Vérification de la signature à implémenter
        
        return $payload;
    }
}
