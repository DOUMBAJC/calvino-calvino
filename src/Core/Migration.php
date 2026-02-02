<?php

namespace Calvino\Core;

/**
 * Classe de base pour les migrations
 */
abstract class Migration
{
    /**
     * Exécute la migration
     * 
     * @return void
     */
    abstract public function up(): void;
    
    /**
     * Annule la migration
     * 
     * @return void
     */
    abstract public function down(): void;
    
    /**
     * Crée une table
     * 
     * @param string $table
     * @param callable $callback
     * @return bool
     */
    protected function create(string $table, callable $callback): bool
    {
        $sql = Schema::create($table, $callback);
        
        $pdo = require dirname(__DIR__, 2) . '/database/connection.php';
        $pdo->exec($sql);
        
        echo "Table '{$table}' créée avec succès.\n";
        return true;
    }
    
    /**
     * Supprime une table
     * 
     * @param string $table
     * @return bool
     */
    protected function drop(string $table): bool
    {
        $sql = Schema::drop($table);
        
        $pdo = require dirname(__DIR__, 2) . '/database/connection.php';
        $pdo->exec($sql);
        
        echo "Table '{$table}' supprimée avec succès.\n";
        return true;
    }

    /**
     * Supprime une table si elle existe
     * 
     * @param string $table
     * @return bool
     */
    protected function dropIfExists(string $table): bool
    {
        $sql = Schema::dropIfExists($table);
        
        $pdo = require dirname(__DIR__, 2) . '/database/connection.php';
        $pdo->exec($sql);
        
        echo "Table '{$table}' supprimée (si elle existait) avec succès.\n";
        return true;
    }
    
    /**
     * Modifie une table existante
     * 
     * @param string $table
     * @param callable $callback
     * @return bool
     */
    protected function table(string $table, callable $callback): bool
    {
        // Obtenir le schéma de la table existante
        $pdo = require dirname(__DIR__, 2) . '/database/connection.php';
        
        // Créer un schéma temporaire qui sera modifié par la fonction de rappel
        $schema = new TableModifier($table, $pdo);
        $callback($schema);
        
        // Exécuter les modifications
        $sql = $schema->toSql();
        
        if (empty($sql)) {
            echo "Aucune modification à effectuer sur la table '{$table}'.\n";
            return true;
        }
        
        $pdo->exec($sql);
        
        echo "Table '{$table}' modifiée avec succès.\n";
        return true;
    }
}

/**
 * Classe pour modifier une table existante
 */
class TableModifier
{
    /**
     * Nom de la table
     *
     * @var string
     */
    protected string $table;
    
    /**
     * Connexion PDO
     *
     * @var \PDO
     */
    protected \PDO $pdo;
    
    /**
     * Liste des colonnes à ajouter
     *
     * @var array
     */
    protected array $addColumns = [];
    
    /**
     * Liste des colonnes à modifier
     *
     * @var array
     */
    protected array $modifyColumns = [];
    
    /**
     * Liste des colonnes à renommer
     *
     * @var array
     */
    protected array $renameColumns = [];
    
    /**
     * Liste des colonnes à supprimer
     *
     * @var array
     */
    protected array $dropColumns = [];
    
    /**
     * Liste des index à ajouter
     *
     * @var array
     */
    protected array $addIndexes = [];
    
    /**
     * Liste des index à supprimer
     *
     * @var array
     */
    protected array $dropIndexes = [];
    
    /**
     * Liste des clés étrangères à ajouter
     *
     * @var array
     */
    protected array $addForeignKeys = [];
    
    /**
     * Liste des clés étrangères à supprimer
     *
     * @var array
     */
    protected array $dropForeignKeys = [];
    
    /**
     * Constructeur
     *
     * @param string $table Nom de la table
     * @param \PDO $pdo Connexion PDO
     */
    public function __construct(string $table, \PDO $pdo)
    {
        $this->table = $table;
        $this->pdo = $pdo;
    }
    
    /**
     * Ajoute une colonne VARCHAR
     *
     * @param string $column
     * @param int $length
     * @return Column
     */
    public function string(string $column, int $length = 255): Column
    {
        $col = new Column($column, "VARCHAR({$length})");
        $this->addColumns[] = $col;
        return $col;
    }

    /**
     * Ajoute une colonne UUID
     * 
     * @param string $column
     * @return Column
     */
    public function uuid(string $column = 'uuid'): Column
    {
        $col = new Column($column, "CHAR(36)");
        $this->addColumns[] = $col;
        return $col;
    }

    /**
     * Ajoute une colonne UUID servant de clé étrangère
     * 
     * @param string $column
     * @return Column
     */
    public function foreignUuid(string $column): Column
    {
        $col = new Column($column, "CHAR(36)");
        $this->addColumns[] = $col;
        return $col;
    }
    
    /**
     * Ajoute une colonne TEXT
     *
     * @param string $column
     * @return Column
     */
    public function text(string $column): Column
    {
        $col = new Column($column, "TEXT");
        $this->addColumns[] = $col;
        return $col;
    }
    
    /**
     * Ajoute une colonne INTEGER
     *
     * @param string $column
     * @param bool $autoIncrement
     * @return Column
     */
    public function integer(string $column, bool $autoIncrement = false): Column
    {
        $col = new Column($column, "INT");
        
        if ($autoIncrement) {
            $col->autoIncrement();
        }
        
        $this->addColumns[] = $col;
        return $col;
    }
    
    /**
     * Ajoute une colonne BOOLEAN
     *
     * @param string $column
     * @return Column
     */
    public function boolean(string $column): Column
    {
        $col = new Column($column, "TINYINT(1)");
        $this->addColumns[] = $col;
        return $col;
    }
    
    /**
     * Ajoute une colonne DATE
     *
     * @param string $column
     * @return Column
     */
    public function date(string $column): Column
    {
        $col = new Column($column, "DATE");
        $this->addColumns[] = $col;
        return $col;
    }
    
    /**
     * Ajoute une colonne DATETIME
     *
     * @param string $column
     * @return Column
     */
    public function dateTime(string $column): Column
    {
        $col = new Column($column, "DATETIME");
        $this->addColumns[] = $col;
        return $col;
    }
    
    /**
     * Ajoute une colonne TIMESTAMP
     *
     * @param string $column
     * @return Column
     */
    public function timestamp(string $column): Column
    {
        $col = new Column($column, "TIMESTAMP");
        $this->addColumns[] = $col;
        return $col;
    }
    
    /**
     * Ajoute une colonne DECIMAL
     *
     * @param string $column
     * @param int $precision
     * @param int $scale
     * @return Column
     */
    public function decimal(string $column, int $precision = 8, int $scale = 2): Column
    {
        $col = new Column($column, "DECIMAL({$precision},{$scale})");
        $this->addColumns[] = $col;
        return $col;
    }
    
    /**
     * Supprime une colonne
     *
     * @param string $column
     * @return $this
     */
    public function dropColumn(string $column): self
    {
        $this->dropColumns[] = $column;
        return $this;
    }
    
    /**
     * Renomme une colonne
     *
     * @param string $from
     * @param string $to
     * @param string $type
     * @return $this
     */
    public function renameColumn(string $from, string $to, string $type): self
    {
        $this->renameColumns[] = [
            'from' => $from,
            'to' => $to,
            'type' => $type
        ];
        
        return $this;
    }
    
    /**
     * Modifie une colonne
     *
     * @param string $column
     * @param string $type
     * @return Column
     */
    public function modifyColumn(string $column, string $type): Column
    {
        $col = new Column($column, $type);
        $this->modifyColumns[] = $col;
        return $col;
    }
    
    /**
     * Ajoute un index
     *
     * @param string|array $columns
     * @param string|null $name
     * @return $this
     */
    public function index($columns, ?string $name = null): self
    {
        $columns = is_array($columns) ? $columns : [$columns];
        $name = $name ?? 'index_' . implode('_', $columns);
        
        $this->addIndexes[] = [
            'columns' => $columns,
            'name' => $name,
            'type' => 'INDEX'
        ];
        
        return $this;
    }
    
    /**
     * Ajoute un index unique
     *
     * @param string|array $columns
     * @param string|null $name
     * @return $this
     */
    public function unique($columns, ?string $name = null): self
    {
        $columns = is_array($columns) ? $columns : [$columns];
        $name = $name ?? 'unique_' . implode('_', $columns);
        
        $this->addIndexes[] = [
            'columns' => $columns,
            'name' => $name,
            'type' => 'UNIQUE'
        ];
        
        return $this;
    }
    
    /**
     * Supprime un index
     *
     * @param string $name
     * @return $this
     */
    public function dropIndex(string $name): self
    {
        $this->dropIndexes[] = $name;
        return $this;
    }
    
    /**
     * Ajoute une clé étrangère
     *
     * @param string $column
     * @param string $references
     * @param string $on
     * @param string $onDelete
     * @param string $onUpdate
     * @return $this
     */
    public function foreign(string $column, string $references, string $on = 'id', string $onDelete = 'CASCADE', string $onUpdate = 'CASCADE'): self
    {
        $name = "fk_{$this->table}_{$column}";
        
        $this->addForeignKeys[] = [
            'name' => $name,
            'column' => $column,
            'references' => $references,
            'on' => $on,
            'onDelete' => $onDelete,
            'onUpdate' => $onUpdate
        ];
        
        return $this;
    }
    
    /**
     * Supprime une clé étrangère
     *
     * @param string $name
     * @return $this
     */
    public function dropForeign(string $name): self
    {
        $this->dropForeignKeys[] = $name;
        return $this;
    }
    
    /**
     * Génère le SQL pour les modifications de la table
     *
     * @return string
     */
    public function toSql(): string
    {
        $statements = [];
        
        // Supprimer les clés étrangères
        foreach ($this->dropForeignKeys as $name) {
            $statements[] = "ALTER TABLE `{$this->table}` DROP FOREIGN KEY `{$name}`;";
        }
        
        // Supprimer les index
        foreach ($this->dropIndexes as $name) {
            $statements[] = "ALTER TABLE `{$this->table}` DROP INDEX `{$name}`;";
        }
        
        // Supprimer les colonnes
        foreach ($this->dropColumns as $column) {
            $statements[] = "ALTER TABLE `{$this->table}` DROP COLUMN `{$column}`;";
        }
        
        // Renommer les colonnes
        foreach ($this->renameColumns as $column) {
            $statements[] = "ALTER TABLE `{$this->table}` CHANGE `{$column['from']}` `{$column['to']}` {$column['type']};";
        }
        
        // Modifier les colonnes
        foreach ($this->modifyColumns as $column) {
            $statements[] = "ALTER TABLE `{$this->table}` MODIFY COLUMN {$column->toSql()};";
        }
        
        // Ajouter les colonnes
        foreach ($this->addColumns as $column) {
            $statements[] = "ALTER TABLE `{$this->table}` ADD COLUMN {$column->toSql()};";
        }
        
        // Ajouter les index
        foreach ($this->addIndexes as $index) {
            $columns = implode('`, `', $index['columns']);
            $statements[] = "ALTER TABLE `{$this->table}` ADD {$index['type']} `{$index['name']}` (`{$columns}`);";
        }
        
        // Ajouter les clés étrangères
        foreach ($this->addForeignKeys as $fk) {
            $statements[] = "ALTER TABLE `{$this->table}` ADD CONSTRAINT `{$fk['name']}` FOREIGN KEY (`{$fk['column']}`) REFERENCES `{$fk['references']}` (`{$fk['on']}`) ON DELETE {$fk['onDelete']} ON UPDATE {$fk['onUpdate']};";
        }
        
        return implode("\n", $statements);
    }
} 