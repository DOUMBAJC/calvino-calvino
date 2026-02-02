<?php

namespace Calvino\Core;

/**
 * Classe Schema pour faciliter la création de tables
 */
class Schema
{
    /**
     * Le nom de la table en cours de création
     *
     * @var string
     */
    protected string $table;
    
    /**
     * Les colonnes de la table
     *
     * @var array
     */
    protected array $columns = [];
    
    /**
     * Les clés primaires
     *
     * @var array
     */
    protected array $primaryKeys = [];
    
    /**
     * Les clés étrangères
     *
     * @var array
     */
    protected array $foreignKeys = [];
    
    /**
     * Les index
     *
     * @var array
     */
    protected array $indexes = [];
    
    /**
     * Le moteur de stockage
     *
     * @var string
     */
    protected string $engine = 'InnoDB';
    
    /**
     * Le jeu de caractères
     *
     * @var string
     */
    protected string $charset = 'utf8mb4';
    
    /**
     * La collation
     *
     * @var string
     */
    protected string $collation = 'utf8mb4_unicode_ci';
    
    /**
     * Constructeur
     *
     * @param string $table Nom de la table
     */
    public function __construct(string $table)
    {
        $this->table = $table;
    }
    
    /**
     * Crée une nouvelle table
     *
     * @param string $table Nom de la table
     * @param callable $callback Fonction de définition des colonnes
     * @return string SQL généré
     */
    public static function create(string $table, callable $callback): string
    {
        $schema = new self($table);
        $callback($schema);
        return $schema->toSql();
    }

    /**
     * Modifie une table existante
     * 
     * @param string $table Nom de la table
     * @param callable $callback Fonction de modification
     * @return string SQL généré
     */
    public static function table(string $table, callable $callback): string
    {
        // Pour les modifications, on utilise TableModifier via la classe Migration
        // Mais si on veut générer du SQL brut via Schema:
        return ""; // TODO: Implémenter logic de génération de SQL pour ALTER si nécessaire
    }

    /**
     * Supprime une table
     * 
     * @param string $table
     * @return string
     */
    public static function drop(string $table): string
    {
        return "DROP TABLE `{$table}`;";
    }

    /**
     * Supprime une table si elle existe
     * 
     * @param string $table
     * @return string
     */
    public static function dropIfExists(string $table): string
    {
        return "DROP TABLE IF EXISTS `{$table}`;";
    }
    
    /**
     * Ajoute une colonne d'ID auto-incrémentée
     *
     * @param string $column Nom de la colonne (par défaut 'id')
     * @return $this
     */
    public function id(string $column = 'id'): self
    {
        $this->integer($column, true)->primary();
        return $this;
    }
    /**
     * Ajoute une colonne d'ID de type UUID (CHAR(36))
     * 
     * @param string $column
     * @return Column
     */
    public function uuid(string $column = 'uuid'): Column
    {
        return $this->addColumn($column, "CHAR(36)");
    }

    /**
     * Ajoute une colonne d'ID de type UUID servant de clé étrangère
     * 
     * @param string $column
     * @return Column
     */
    public function foreignUuid(string $column): Column
    {
        return $this->addColumn($column, "CHAR(36)");
    }
    
    /**
     * Ajoute les colonnes de timestamps (created_at, updated_at)
     *
     * @param bool $useDefault Utiliser la valeur par défaut CURRENT_TIMESTAMP
     * @return $this
     */
    public function timestamps(bool $useDefault = true): self
    {
        if ($useDefault) {
            $this->timestamp('created_at')->default('CURRENT_TIMESTAMP');
            $this->timestamp('updated_at')->default('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');
        } else {
            $this->timestamp('created_at')->nullable();
            $this->timestamp('updated_at')->nullable();
        }
        
        return $this;
    }
    
    /**
     * Ajoute une colonne VARCHAR
     *
     * @param string $column Nom de la colonne
     * @param int $length Longueur maximale
     * @return Column
     */
    public function string(string $column, int $length = 255): Column
    {
        return $this->addColumn($column, "VARCHAR({$length})");
    }
    
    /**
     * Ajoute une colonne TEXT
     *
     * @param string $column Nom de la colonne
     * @return Column
     */
    public function text(string $column): Column
    {
        return $this->addColumn($column, "TEXT");
    }
    
    /**
     * Ajoute une colonne MEDIUMTEXT
     *
     * @param string $column Nom de la colonne
     * @return Column
     */
    public function mediumText(string $column): Column
    {
        return $this->addColumn($column, "MEDIUMTEXT");
    }
    
    /**
     * Ajoute une colonne LONGTEXT
     *
     * @param string $column Nom de la colonne
     * @return Column
     */
    public function longText(string $column): Column
    {
        return $this->addColumn($column, "LONGTEXT");
    }
    
    /**
     * Ajoute une colonne INTEGER
     *
     * @param string $column Nom de la colonne
     * @param bool $autoIncrement Auto-incrémentation
     * @return Column
     */
    public function integer(string $column, bool $autoIncrement = false): Column
    {
        $column = $this->addColumn($column, "INT");
        
        if ($autoIncrement) {
            $column->autoIncrement();
        }
        
        return $column;
    }
    
    /**
     * Ajoute une colonne BIGINT
     *
     * @param string $column Nom de la colonne
     * @param bool $autoIncrement Auto-incrémentation
     * @return Column
     */
    public function bigInteger(string $column, bool $autoIncrement = false): Column
    {
        $column = $this->addColumn($column, "BIGINT");
        
        if ($autoIncrement) {
            $column->autoIncrement();
        }
        
        return $column;
    }
    
    /**
     * Ajoute une colonne TINYINT
     *
     * @param string $column Nom de la colonne
     * @return Column
     */
    public function tinyInteger(string $column): Column
    {
        return $this->addColumn($column, "TINYINT");
    }
    
    /**
     * Ajoute une colonne BOOLEAN (TINYINT(1))
     *
     * @param string $column Nom de la colonne
     * @return Column
     */
    public function boolean(string $column): Column
    {
        return $this->addColumn($column, "TINYINT(1)");
    }
    
    /**
     * Ajoute une colonne DATE
     *
     * @param string $column Nom de la colonne
     * @return Column
     */
    public function date(string $column): Column
    {
        return $this->addColumn($column, "DATE");
    }
    
    /**
     * Ajoute une colonne DATETIME
     *
     * @param string $column Nom de la colonne
     * @return Column
     */
    public function dateTime(string $column): Column
    {
        return $this->addColumn($column, "DATETIME");
    }
    
    /**
     * Ajoute une colonne TIMESTAMP
     *
     * @param string $column Nom de la colonne
     * @return Column
     */
    public function timestamp(string $column): Column
    {
        return $this->addColumn($column, "TIMESTAMP");
    }
    
    /**
     * Ajoute une colonne DECIMAL
     *
     * @param string $column Nom de la colonne
     * @param int $precision Précision totale
     * @param int $scale Précision décimale
     * @return Column
     */
    public function decimal(string $column, int $precision = 8, int $scale = 2): Column
    {
        return $this->addColumn($column, "DECIMAL({$precision},{$scale})");
    }
    
    /**
     * Ajoute une colonne FLOAT
     *
     * @param string $column Nom de la colonne
     * @return Column
     */
    public function float(string $column): Column
    {
        return $this->addColumn($column, "FLOAT");
    }
    
    /**
     * Ajoute une colonne DOUBLE
     *
     * @param string $column Nom de la colonne
     * @return Column
     */
    public function double(string $column): Column
    {
        return $this->addColumn($column, "DOUBLE");
    }
    
    /**
     * Ajoute une colonne ENUM
     *
     * @param string $column Nom de la colonne
     * @param array $values Valeurs possibles
     * @return Column
     */
    public function enum(string $column, array $values): Column
    {
        $valuesStr = implode("','", array_map(function($val) {
            return str_replace("'", "\'", $val);
        }, $values));
        
        return $this->addColumn($column, "ENUM('{$valuesStr}')");
    }
    
    /**
     * Ajoute une colonne JSON
     *
     * @param string $column Nom de la colonne
     * @return Column
     */
    public function json(string $column): Column
    {
        return $this->addColumn($column, "JSON");
    }
    
    /**
     * Définit une clé primaire
     *
     * @param string|array $columns Nom(s) de(s) colonne(s)
     * @return $this
     */
    public function primary($columns): self
    {
        $columns = is_array($columns) ? $columns : [$columns];
        $this->primaryKeys = array_merge($this->primaryKeys, $columns);
        return $this;
    }
    
    /**
     * Définit une clé étrangère
     *
     * @param string $column Nom de la colonne
     * @param string $references Table référencée
     * @param string $on Colonne référencée
     * @param string $onDelete Action à effectuer lors de la suppression
     * @param string $onUpdate Action à effectuer lors de la mise à jour
     * @return $this
     */
    public function foreign(string $column, string $references, string $on = 'id', string $onDelete = 'CASCADE', string $onUpdate = 'CASCADE'): self
    {
        $this->foreignKeys[] = [
            'column' => $column,
            'references' => $references,
            'on' => $on,
            'onDelete' => $onDelete,
            'onUpdate' => $onUpdate
        ];
        
        return $this;
    }
    
    /**
     * Définit un index
     *
     * @param string|array $columns Nom(s) de(s) colonne(s)
     * @param string $name Nom de l'index (facultatif)
     * @return $this
     */
    public function index($columns, ?string $name = null): self
    {
        $columns = is_array($columns) ? $columns : [$columns];
        $name = $name ?? 'index_' . implode('_', $columns);
        
        $this->indexes[] = [
            'columns' => $columns,
            'name' => $name,
            'type' => 'INDEX'
        ];
        
        return $this;
    }
    
    /**
     * Définit un index unique
     *
     * @param string|array $columns Nom(s) de(s) colonne(s)
     * @param string $name Nom de l'index (facultatif)
     * @return $this
     */
    public function unique($columns, ?string $name = null): self
    {
        $columns = is_array($columns) ? $columns : [$columns];
        $name = $name ?? 'unique_' . implode('_', $columns);
        
        $this->indexes[] = [
            'columns' => $columns,
            'name' => $name,
            'type' => 'UNIQUE'
        ];
        
        return $this;
    }
    
    /**
     * Définit le moteur de stockage
     *
     * @param string $engine Moteur de stockage
     * @return $this
     */
    public function engine(string $engine): self
    {
        $this->engine = $engine;
        return $this;
    }
    
    /**
     * Définit le jeu de caractères
     *
     * @param string $charset Jeu de caractères
     * @return $this
     */
    public function charset(string $charset): self
    {
        $this->charset = $charset;
        return $this;
    }
    
    /**
     * Définit la collation
     *
     * @param string $collation Collation
     * @return $this
     */
    public function collation(string $collation): self
    {
        $this->collation = $collation;
        return $this;
    }
    
    /**
     * Ajoute une colonne
     *
     * @param string $name Nom de la colonne
     * @param string $type Type de données
     * @return Column
     */
    protected function addColumn(string $name, string $type): Column
    {
        $column = new Column($name, $type);
        $this->columns[] = $column;
        return $column;
    }
    
    /**
     * Génère le SQL pour la création de la table
     *
     * @return string
     */
    public function toSql(): string
    {
        $sql = "CREATE TABLE IF NOT EXISTS `{$this->table}` (\n";
        
        // Colonnes
        $columnsSql = [];
        foreach ($this->columns as $column) {
            $columnsSql[] = "    " . $column->toSql();
        }
        
        // Collecter les clés primaires définies sur les colonnes
        $allPrimaryKeys = $this->primaryKeys;
        foreach ($this->columns as $column) {
            if ($column->isPrimary() && !in_array($column->getName(), $allPrimaryKeys)) {
                $allPrimaryKeys[] = $column->getName();
            }
        }

        // Clé primaire (si définie)
        if (!empty($allPrimaryKeys)) {
            $columns = implode('`, `', $allPrimaryKeys);
            $columnsSql[] = "    PRIMARY KEY (`{$columns}`)";
        }
        
        // Index
        foreach ($this->indexes as $index) {
            $columns = implode('`, `', $index['columns']);
            $columnsSql[] = "    {$index['type']} `{$index['name']}` (`{$columns}`)";
        }
        
        // Clés étrangères
        foreach ($this->foreignKeys as $fk) {
            $columnsSql[] = "    FOREIGN KEY (`{$fk['column']}`) REFERENCES `{$fk['references']}` (`{$fk['on']}`) ON DELETE {$fk['onDelete']} ON UPDATE {$fk['onUpdate']}";
        }
        
        $sql .= implode(",\n", $columnsSql);
        $sql .= "\n) ENGINE={$this->engine} DEFAULT CHARSET={$this->charset} COLLATE={$this->collation};";
        
        return $sql;
    }
} 