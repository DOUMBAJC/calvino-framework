<?php

namespace Calvino\Traits;

use Calvino\Services\GeoLocationService;

/**
 * Trait ManageSessions
 * Logique de gestion des sessions pour le modèle UserSession
 */
trait ManageSessions
{
    /**
     * Crée une nouvelle session pour un utilisateur
     */
    public static function createSession(
        int|string $userId, 
        string $token, 
        string $refreshToken, 
        ?string $sessionId = null,
        array $additionalData = []
    ) {
        $sessionId = $sessionId ?: self::generateSessionId();
        
        $ipAddress = $additionalData['ip_address'] ?? $_SERVER['REMOTE_ADDR'] ?? null;
        $userAgent = $additionalData['user_agent'] ?? $_SERVER['HTTP_USER_AGENT'] ?? null;
        
        $deviceInfo = self::parseUserAgent($userAgent);
        
        $location = $additionalData['location'] ?? null;
        if (!$location && $ipAddress) {
            $location = GeoLocationService::getFormattedLocation($ipAddress);
        }
        
        return self::create([
            'user_id' => $userId,
            'session_id' => $sessionId,
            'token' => $token,
            'refresh_token' => $refreshToken,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'device_name' => $deviceInfo['device_name'],
            'device_type' => $deviceInfo['device_type'],
            'location' => $location ?: 'Inconnue',
            'last_activity' => date('Y-m-d H:i:s'),
            'is_active' => 1
        ]);
    }
    
    /**
     * Génère un identifiant de session unique
     */
    private static function generateSessionId(): string
    {
        return bin2hex(random_bytes(16));
    }
    
    /**
     * Analyse le user agent
     */
    private static function parseUserAgent(?string $userAgent): array
    {
        $deviceName = 'Unknown';
        $deviceType = 'unknown';
        
        if ($userAgent) {
            if (strpos($userAgent, 'Mobile') !== false || strpos($userAgent, 'Android') !== false) {
                $deviceType = 'mobile';
                if (preg_match('/iPhone|iPad|iPod/i', $userAgent)) {
                    $deviceName = 'iOS Device';
                } elseif (preg_match('/Android/i', $userAgent)) {
                    $deviceName = 'Android Device';
                }
            } elseif (strpos($userAgent, 'Tablet') !== false || strpos($userAgent, 'iPad') !== false) {
                $deviceType = 'tablet';
                $deviceName = 'Tablet';
            } else {
                $deviceType = 'desktop';
                if (strpos($userAgent, 'Windows') !== false) {
                    $deviceName = 'Windows PC';
                } elseif (strpos($userAgent, 'Macintosh') !== false) {
                    $deviceName = 'Mac';
                } elseif (strpos($userAgent, 'Linux') !== false) {
                    $deviceName = 'Linux';
                }
            }
        }
        
        return [
            'device_name' => $deviceName,
            'device_type' => $deviceType
        ];
    }
    
    /**
     * Trouve une session par son ID
     */
    public static function findBySessionId(string $sessionId)
    {
        return self::where('session_id', $sessionId)->first();
    }
    
    /**
     * Récupère toutes les sessions actives d'un utilisateur
     */
    public static function getActiveSessions($userId): array
    {
        return self::where('user_id', $userId)->where('is_active', 1)->get();
    }

    /**
     * Met à jour l'activité
     */
    public function updateActivity(): bool
    {
        $this->last_activity = date('Y-m-d H:i:s');
        return $this->save();
    }
    
    /**
     * Désactive la session
     */
    public function deactivate(): bool
    {
        $this->is_active = 0;
        return $this->save();
    }
}
