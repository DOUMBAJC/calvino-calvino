<?php

namespace App\Traits;

use App\Models\ActivityLog;

trait LogsActivity
{
    /**
     * Hook pour la journalisation des activités après création
     */
    protected static function bootLogsActivity()
    {
        // Journaliser les créations
        static::created(function ($model) {
            if (auth()->check()) {
                $user = auth()->user();
                ActivityLog::log(
                    $user->id,
                    'create',
                    $model->getLogModule(),
                    "Création de {$model->getLogName()} #" . $model->getAttribute('id'),
                    null,
                    $model->getLogAttributes()
                );
            }
        });

        // Journaliser les mises à jour
        static::updated(function ($model) {
            if (auth()->check() && !empty($model->getDirty())) {
                $user = auth()->user();
                ActivityLog::log(
                    $user->id,
                    'update',
                    $model->getLogModule(),
                    "Mise à jour de {$model->getLogName()} #" . $model->getAttribute('id'),
                    $model->getOriginalLogAttributes(),
                    $model->getChangedLogAttributes()
                );
            }
        });

        // Journaliser les suppressions
        static::deleted(function ($model) {
            if (auth()->check()) {
                $user = auth()->user();
                ActivityLog::log(
                    $user->id,
                    'delete',
                    $model->getLogModule(),
                    "Suppression de {$model->getLogName()} #" . $model->getAttribute('id'),
                    $model->getLogAttributes(),
                    null
                );
            }
        });
    }

    /**
     * Récupère le nom du module pour la journalisation
     *
     * @return string
     */
    public function getLogModule(): string
    {
        if (property_exists($this, 'logModule')) {
            return $this->logModule;
        }

        return strtolower($this->getClassBasename());
    }

    /**
     * Récupère le nom de l'élément pour la journalisation
     *
     * @return string
     */
    public function getLogName(): string
    {
        if (property_exists($this, 'logName')) {
            return $this->logName;
        }

        return $this->getClassBasename();
    }

    /**
     * Récupère le nom de la classe sans namespace
     *
     * @return string
     */
    protected function getClassBasename(): string
    {
        $class = get_class($this);
        return basename(str_replace('\\', '/', $class));
    }

    /**
     * Récupère les attributs à journaliser
     *
     * @return array
     */
    public function getLogAttributes(): array
    {
        if (property_exists($this, 'logAttributes') && is_array($this->logAttributes)) {
            $attributes = [];
            foreach ($this->logAttributes as $attribute) {
                $attributes[$attribute] = $this->getAttribute($attribute);
            }
            return $attributes;
        }

        return $this->attributesToArray();
    }

    /**
     * Récupère les attributs originaux pour la journalisation
     *
     * @return array
     */
    public function getOriginalLogAttributes(): array
    {
        $dirtyAttributes = $this->getDirty();
        $originalAttributes = [];

        foreach (array_keys($dirtyAttributes) as $attribute) {
            if (property_exists($this, 'logAttributes') && !in_array($attribute, $this->logAttributes)) {
                continue;
            }
            $originalAttributes[$attribute] = $this->getOriginal($attribute);
        }

        return $originalAttributes;
    }

    /**
     * Récupère les attributs modifiés pour la journalisation
     *
     * @return array
     */
    public function getChangedLogAttributes(): array
    {
        $dirtyAttributes = $this->getDirty();
        
        if (property_exists($this, 'logAttributes') && is_array($this->logAttributes)) {
            return array_intersect_key($dirtyAttributes, array_flip($this->logAttributes));
        }

        return $dirtyAttributes;
    }
} 