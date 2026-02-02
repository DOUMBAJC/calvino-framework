<?php

namespace Calvino\Traits;

use App\Models\ActivityLog;

/**
 * Trait Auditable
 * Permet de journaliser automatiquement les actions sur les modèles
 */
trait Auditable
{
    /**
     * Initialisation du trait
     */
    public static function bootAuditable()
    {
        // Journaliser les créations
        static::created(function ($model) {
            $model->audit('create', "Création de {$model->getAuditName()} #{$model->getAttribute($model->getPrimaryKey())}");
        });

        // Journaliser les mises à jour
        static::updated(function ($model) {
            $dirty = $model->getDirty();
            if (!empty($dirty)) {
                $model->audit(
                    'update', 
                    "Mise à jour de {$model->getAuditName()} #{$model->getAttribute($model->getPrimaryKey())}",
                    $model->getOriginalAuditAttributes(),
                    $model->getChangedAuditAttributes()
                );
            }
        });

        // Journaliser les suppressions
        static::deleted(function ($model) {
            $model->audit(
                'delete', 
                "Suppression de {$model->getAuditName()} #{$model->getAttribute($model->getPrimaryKey())}",
                $model->getAuditAttributes()
            );
        });
    }

    /**
     * Enregistre une action d'audit
     *
     * @param string $action
     * @param string $description
     * @param array|null $before
     * @param array|null $after
     * @return void
     */
    public function audit(string $action, string $description, ?array $before = null, ?array $after = null): void
    {
        if (auth()->check()) {
            $user = auth()->user();
            
            // On vérifie si le modèle ActivityLog existe
            if (class_exists(ActivityLog::class)) {
                ActivityLog::log(
                    userId: $user->id,
                    action: $action,
                    module: $this->getAuditModule(),
                    description: $description,
                    oldValues: $before,
                    newValues: $after
                );
            }
        }
    }

    /**
     * Récupère le nom du module pour l'audit
     *
     * @return string
     */
    public function getAuditModule(): string
    {
        if (property_exists($this, 'auditModule')) {
            return $this->auditModule;
        }

        return strtolower(class_basename(static::class));
    }

    /**
     * Récupère le nom de l'élément pour l'audit
     *
     * @return string
     */
    public function getAuditName(): string
    {
        if (property_exists($this, 'auditName')) {
            return $this->auditName;
        }

        return class_basename(static::class);
    }

    /**
     * Récupère les attributs à journaliser
     *
     * @return array
     */
    public function getAuditAttributes(): array
    {
        if (property_exists($this, 'auditAttributes') && is_array($this->auditAttributes)) {
            $attributes = [];
            foreach ($this->auditAttributes as $attribute) {
                $attributes[$attribute] = $this->getAttribute($attribute);
            }
            return $attributes;
        }

        return $this->attributesToArray();
    }

    /**
     * Récupère les attributs originaux pour l'audit
     *
     * @return array
     */
    public function getOriginalAuditAttributes(): array
    {
        $dirtyAttributes = $this->getDirty();
        $originalAttributes = [];

        foreach (array_keys($dirtyAttributes) as $attribute) {
            if (property_exists($this, 'auditAttributes') && !in_array($attribute, $this->auditAttributes)) {
                continue;
            }
            $originalAttributes[$attribute] = $this->getOriginal($attribute);
        }

        return $originalAttributes;
    }

    /**
     * Récupère les attributs modifiés pour l'audit
     *
     * @return array
     */
    public function getChangedAuditAttributes(): array
    {
        $dirtyAttributes = $this->getDirty();
        
        if (property_exists($this, 'auditAttributes') && is_array($this->auditAttributes)) {
            return array_intersect_key($dirtyAttributes, array_flip($this->auditAttributes));
        }

        return $dirtyAttributes;
    }
}
