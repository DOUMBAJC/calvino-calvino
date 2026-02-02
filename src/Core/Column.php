<?php

namespace Calvino\Core;

/**
 * Classe pour représenter une colonne dans un schéma de table
 */
class Column
{
    /**
     * Nom de la colonne
     *
     * @var string
     */
    protected string $name;
    
    /**
     * Type de données de la colonne
     *
     * @var string
     */
    protected string $type;
    
    /**
     * Indique si la colonne peut être NULL
     *
     * @var bool
     */
    protected bool $nullable = false;
    
    /**
     * Indique si la colonne est une clé primaire
     *
     * @var bool
     */
    protected bool $isPrimary = false;
    
    /**
     * Indique si la colonne est auto-incrémentée
     *
     * @var bool
     */
    protected bool $autoIncrement = false;
    
    /**
     * Indique si la colonne doit être unique
     *
     * @var bool
     */
    protected bool $isUnique = false;
    
    /**
     * Valeur par défaut de la colonne
     *
     * @var mixed
     */
    protected $default = null;
    
    /**
     * Indique si une valeur par défaut a été définie
     *
     * @var bool
     */
    protected bool $hasDefault = false;
    
    /**
     * Commentaire de la colonne
     *
     * @var string|null
     */
    protected ?string $comment = null;
    
    /**
     * Constructeur
     *
     * @param string $name Nom de la colonne
     * @param string $type Type de données
     */
    public function __construct(string $name, string $type)
    {
        $this->name = $name;
        $this->type = $type;
    }
    
    /**
     * Rend la colonne nullable
     *
     * @return $this
     */
    public function nullable(): self
    {
        $this->nullable = true;
        return $this;
    }
    
    /**
     * Définit la colonne comme clé primaire
     *
     * @return $this
     */
    public function primary(): self
    {
        $this->isPrimary = true;
        return $this;
    }
    
    /**
     * Définit la colonne comme auto-incrémentée
     *
     * @return $this
     */
    public function autoIncrement(): self
    {
        $this->autoIncrement = true;
        return $this;
    }
    
    /**
     * Définit la colonne comme unique
     *
     * @return $this
     */
    public function unique(): self
    {
        $this->isUnique = true;
        return $this;
    }
    
    /**
     * Définit une valeur par défaut pour la colonne
     *
     * @param mixed $value Valeur par défaut
     * @return $this
     */
    public function default($value): self
    {
        $this->default = $value;
        $this->hasDefault = true;
        return $this;
    }
    
    /**
     * Ajoute un commentaire à la colonne
     *
     * @param string $comment Commentaire
     * @return $this
     */
    public function comment(string $comment): self
    {
        $this->comment = $comment;
        return $this;
    }
    
    /**
     * Échappe une chaîne pour une utilisation dans une requête SQL
     *
     * @param string $string
     * @return string
     */
    private function escape(string $string): string
    {
        return str_replace("'", "\'", $string);
    }
    
    /**
     * Génère le SQL pour la colonne
     *
     * @return string
     */
    public function toSql(): string
    {
        $sql = "`{$this->name}` {$this->type}";
        
        if ($this->isUnique) {
            $sql .= " UNIQUE";
        }
        
        if (!$this->nullable) {
            $sql .= " NOT NULL";
        } else {
            $sql .= " NULL";
        }
        
        if ($this->autoIncrement) {
            $sql .= " AUTO_INCREMENT";
        }
        
        if ($this->hasDefault) {
            if ($this->default === null) {
                $sql .= " DEFAULT NULL";
            } elseif (is_bool($this->default)) {
                $sql .= " DEFAULT " . ($this->default ? '1' : '0');
            } elseif (is_string($this->default) && !preg_match('/^CURRENT_TIMESTAMP|NOW\(\)$/i', $this->default)) {
                $sql .= " DEFAULT '{$this->escape($this->default)}'";
            } else {
                $sql .= " DEFAULT {$this->default}";
            }
        }
        
        if ($this->comment !== null) {
            $sql .= " COMMENT '{$this->escape($this->comment)}'";
        }
        
        return $sql;
    }

    /**
     * Vérifie si la colonne est une clé primaire
     * 
     * @return bool
     */
    public function isPrimary(): bool
    {
        return $this->isPrimary;
    }

    /**
     * Obtient le nom de la colonne
     * 
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
} 