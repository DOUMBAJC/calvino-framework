<?php

namespace Calvino\Traits;

/**
 * Trait Notifiable
 * Permet à un modèle de recevoir des notifications
 */
trait Notifiable
{
    /**
     * Envoie une notification à l'utilisateur
     */
    public function notify(string $title, string $message, string $type = 'info', $data = null)
    {
        // Si l'utilisateur veut le changer, il peut surcharger cette méthode
        $notificationClass = '\App\Models\Notification';
        if (class_exists($notificationClass)) {
            $notification = new $notificationClass();
            return $notification->createNotification($this->id, $title, $message, $type, $data);
        }
        return false;
    }
}
