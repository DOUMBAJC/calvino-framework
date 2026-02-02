<?php

namespace Calvino\Traits;

/**
 * Trait BaseNotification
 * Logique pour le modèle Notification
 */
trait BaseNotification
{
    /**
     * Récupérer les notifications pour un utilisateur
     */
    public function getForUser($userId, $unreadOnly = false, $limit = 50): array
    {
        $query = self::where('user_id', $userId);
        
        if ($unreadOnly) {
            $query->where('is_read', 0);
        }
        
        return $query->limit($limit)->get();
    }

    /**
     * Marquer une notification comme lue
     */
    public function markAsRead($id, $userId): bool
    {
        $notification = self::where('id', $id)->where('user_id', $userId)->first();
        if ($notification) {
            $notification->is_read = 1;
            return $notification->save();
        }
        return false;
    }

    /**
     * Marquer toutes les notifications d'un utilisateur comme lues
     */
    public function markAllAsRead($userId): bool
    {
        $pdo = self::getPdo();
        $table = (new static())->getTable();
        $stmt = $pdo->prepare("UPDATE {$table} SET is_read = 1 WHERE user_id = :user_id AND is_read = 0");
        $stmt->bindParam(':user_id', $userId);
        return $stmt->execute();
    }

    /**
     * Créer une nouvelle notification
     */
    public function createNotification($userId, $title, $message, $type = 'info', $data = null)
    {
        return self::create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'is_read' => 0,
            'data' => $data ? json_encode($data) : null,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Supprimer une notification spécifique
     */
    public function deleteNotification($id, $userId): bool
    {
        $notification = self::where('id', $id)->where('user_id', $userId)->first();
        if ($notification) {
            return $notification->delete();
        }
        return false;
    }

    /**
     * Supprimer toutes les notifications d'un utilisateur
     */
    public function deleteAllNotifications($userId): bool
    {
        $pdo = self::getPdo();
        $table = (new static())->getTable();
        $stmt = $pdo->prepare("DELETE FROM {$table} WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $userId);
        return $stmt->execute();
    }

    /**
     * Compter les notifications non lues d'un utilisateur
     */
    public function countUnread($userId): int
    {
        $pdo = self::getPdo();
        $table = (new static())->getTable();
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM {$table} WHERE user_id = :user_id AND is_read = 0");
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return isset($result['count']) ? (int)$result['count'] : 0;
    }
}
