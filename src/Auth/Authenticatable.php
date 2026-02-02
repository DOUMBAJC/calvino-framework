<?php

namespace Calvino\Auth;

/**
 * Trait Authenticatable
 * Gère l'authentification et les tokens JWT pour un modèle User
 */
trait Authenticatable
{
    /**
     * Vérifie si le mot de passe est correct
     *
     * @param string $password
     * @return bool
     */
    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->password);
    }
    
    /**
     * Hash le mot de passe avant de l'enregistrer
     *
     * @param string $password
     * @return string
     */
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }
    
    /**
     * Crée un token JWT avec un identifiant de session
     *
     * @param string|null $sessionId Identifiant de session
     * @return string
     */
    public function createToken(?string $sessionId = null): string
    {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $header = $this->base64UrlEncode($header);
        
        $payload = json_encode([
            'sub' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'sid' => $sessionId, // Identifiant de session
            'iat' => time(),
            'exp' => time() + 60 * 60 * 24 // 24 heures
        ]);
        $payload = $this->base64UrlEncode($payload);
        
        $signature = hash_hmac('sha256', "$header.$payload", env('JWT_SECRET', 'default_secret'), true);
        $signature = $this->base64UrlEncode($signature);
        
        return "$header.$payload.$signature";
    }
    
    /**
     * Crée un refresh token JWT avec une durée de validité plus longue
     *
     * @param string|null $sessionId Identifiant de session
     * @return string
     */
    public function createRefreshToken(?string $sessionId = null): string
    {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $header = $this->base64UrlEncode($header);
        
        $payload = json_encode([
            'sub' => $this->id,
            'type' => 'refresh',
            'sid' => $sessionId, // Identifiant de session
            'iat' => time(),
            'exp' => time() + 60 * 60 * 24  // 24 heures
        ]);
        $payload = $this->base64UrlEncode($payload);
        
        $signature = hash_hmac('sha256', "$header.$payload", env('JWT_SECRET', 'default_secret') . '_refresh', true);
        $signature = $this->base64UrlEncode($signature);
        
        return "$header.$payload.$signature";
    }
    
    /**
     * Encode en base64url compatible avec les standards JWT
     * 
     * @param string $data
     * @return string
     */
    private function base64UrlEncode($data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Vérifie si le mot de passe fourni est un mot de passe par défaut
     *
     * @param string $password
     * @return bool
     */
    public static function isDefaultPassword(string $password): bool
    {
        // Simple heuristic - can be improved
        return strpos($password, 'PHS') === 0 && strlen($password) === 7;
    }
}
