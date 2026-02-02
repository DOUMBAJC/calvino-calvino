<?php

namespace Calvino\Core;

use PDO;
use ReflectionClass;
use ReflectionProperty;
use JsonSerializable;

/**
 * Classe Model
 * Classe de base pour les modèles d'entités
 */
abstract class Model implements JsonSerializable
{
    /**
     * Table associée au modèle
     *
     * @var string
     */
    protected string $table;

    /**
     * Clé primaire
     *
     * @var string
     */
    protected string $primaryKey = 'id';

    /**
     * Le type de la clé primaire
     * 
     * @var string
     */
    protected string $keyType = 'int';

    /**
     * Indique si l'ID est auto-incrémenté
     * 
     * @var bool
     */
    public bool $incrementing = true;
    
    /**
     * Champs remplissables
     *
     * @var array
     */
    protected array $fillable = [];
    
    /**
     * Connexion PDO
     *
     * @var PDO|null
     */
    protected static ?PDO $pdo = null;
    
    /**
     * Les attributs du modèle
     *
     * @var array
     */
    protected array $attributes = [];

    /**
     * Constructeur
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->fill($attributes);
    }

    /**
     * Remplit le modèle avec des attributs
     *
     * @param array $attributes
     * @return self
     */
    public function fill(array $attributes): self
    {
        foreach ($attributes as $key => $value) {
            // Toujours inclure la clé primaire dans les attributs, même si elle n'est pas dans fillable
            if ($key === $this->primaryKey || in_array($key, $this->fillable)) {
                $this->attributes[$key] = $value;
            }
        }
        
        return $this;
    }

    /**
     * Obtient la connexion PDO
     *
     * @return PDO
     */
    protected static function getPdo(): PDO
    {
        if (self::$pdo === null) {
            $provider = new \Calvino\Providers\DatabaseServiceProvider(app());
            self::$pdo = $provider->getConnection();
        }
        
        return self::$pdo;
    }

    /**
     * Récupère tous les enregistrements
     *
     * @return array
     */
    public static function all(): array
    {
        try {
            $model = new static();
            $table = $model->getTable();
            
            $pdo = self::getPdo();
            $stmt = $pdo->query("SELECT * FROM {$table}");
            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $models = [];
            foreach ($records as $record) {
                $models[] = new static($record);
            }
            
            return $models;
        } catch (\PDOException $e) {
            error_log("Erreur dans Model::all() - " . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            error_log("Exception dans Model::all() - " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Trouve un enregistrement par sa clé primaire
     *
     * @param mixed $id
     * @return static|null
     */
    public static function find($id)
    {
        $model = new static();
        $table = $model->getTable();
        $primaryKey = $model->getPrimaryKey();
        
        $stmt = self::getPdo()->prepare("SELECT * FROM {$table} WHERE {$primaryKey} = ?");
        $stmt->execute([$id]);
        
        $record = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$record) {
            return null;
        }
        
        return new static($record);
    }

    /**
     * Crée et enregistre un nouveau modèle
     *
     * @param array $attributes
     * @return static
     */
    public static function create(array $attributes)
    {
        $model = new static($attributes);
        $model->save();
        
        return $model;
    }

    /**
     * Enregistre le modèle dans la base de données
     *
     * @return bool
     */
    public function save(): bool
    {
        if (isset($this->attributes[$this->primaryKey])) {
            return $this->update();
        }
        
        return $this->insert();
    }

    /**
     * Insère un nouvel enregistrement
     *
     * @return bool
     */
    protected function insert(): bool
    {
        // Générer un UUID si la clé est de type string et non incrémentée
        if ($this->keyType === 'string' && !$this->incrementing && !isset($this->attributes[$this->primaryKey])) {
            $this->attributes[$this->primaryKey] = $this->generateUuid();
        }

        $fields = array_keys($this->attributes);
        $placeholders = array_fill(0, count($fields), '?');
        
        $columns = implode(', ', $fields);
        $valueStr = implode(', ', $placeholders);
        
        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$valueStr})";
        
        $stmt = self::getPdo()->prepare($sql);
        $result = $stmt->execute(array_values($this->attributes));
        
        if ($result && $this->incrementing) {
            $this->attributes[$this->primaryKey] = self::getPdo()->lastInsertId();
        }
        
        return $result;
    }

    /**
     * Génère un UUID v4
     * 
     * @return string
     */
    protected function generateUuid(): string
    {
        try {
            $data = \random_bytes(16);
        } catch (\Exception $e) {
            $data = \openssl_random_pseudo_bytes(16);
        }

        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    /**
     * Met à jour l'enregistrement
     *
     * @return bool
     */
    protected function update(): bool
    {
        $fields = [];
        $values = [];
        
        foreach ($this->attributes as $key => $value) {
            if ($key !== $this->primaryKey) {
                $fields[] = "{$key} = ?";
                $values[] = $value;
            }
        }
        
        $setStr = implode(', ', $fields);
        
        $sql = "UPDATE {$this->table} SET {$setStr} WHERE {$this->primaryKey} = ?";
        
        $values[] = $this->attributes[$this->primaryKey];
        
        $stmt = self::getPdo()->prepare($sql);
        return $stmt->execute($values);
    }

    /**
     * Supprime l'enregistrement
     *
     * @return bool
     */
    public function delete(): bool
    {
        if (!isset($this->attributes[$this->primaryKey])) {
            return false;
        }
        
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
        
        $stmt = self::getPdo()->prepare($sql);
        return $stmt->execute([$this->attributes[$this->primaryKey]]);
    }

    /**
     * Obtient le nom de la table
     *
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * Obtient le nom de la clé primaire
     *
     * @return string
     */
    public function getPrimaryKey(): string
    {
        return $this->primaryKey;
    }

    /**
     * Méthode magique pour accéder aux attributs
     *
     * @param string $name
     * @return mixed|null
     */
    public function __get(string $name)
    {
        // Si l'attribut existe dans les attributs du modèle, le retourner
        if (isset($this->attributes[$name])) {
            return $this->attributes[$name];
        }
        
        return null;
    }

    /**
     * Méthode pour définir les attributs
     *
     * @param string $name
     * @param mixed $value
     */
    public function __set(string $name, $value)
    {
        if ($name === $this->primaryKey || in_array($name, $this->fillable)) {
            $this->attributes[$name] = $value;
        }
    }

    /**
     * Vérifie si un attribut existe
     *
     * @param string $name
     * @return bool
     */
    public function __isset(string $name): bool
    {
        return isset($this->attributes[$name]);
    }

    /**
     * Relation un-à-plusieurs (inverse)
     *
     * @param string $related
     * @param string|null $foreignKey
     * @return mixed
     */
    protected function belongsTo(string $related, ?string $foreignKey = null)
    {
        $instance = new $related();
        $foreignKey ??= strtolower(class_basename($related)) . '_id';
        $primaryKey = $instance->getPrimaryKey();
        
        $foreignId = $this->attributes[$foreignKey] ?? null;
        
        if ($foreignId === null) {
            return null;
        }
        
        return $related::find($foreignId);
    }

    /**
     * Relation un-à-plusieurs
     *
     * @param string $related
     * @param string|null $foreignKey
     * @return array
     */
    protected function hasMany(string $related, ?string $foreignKey = null): array
    {
        $instance = new $related();
        $foreignKey ??= strtolower(class_basename(static::class)) . '_id';
        
        $table = $instance->getTable();
        $primaryKeyValue = $this->attributes[$this->primaryKey] ?? null;
        
        if ($primaryKeyValue === null) {
            return [];
        }
        
        $stmt = self::getPdo()->prepare("SELECT * FROM {$table} WHERE {$foreignKey} = ?");
        $stmt->execute([$primaryKeyValue]);
        
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $models = [];
        foreach ($records as $record) {
            $models[] = new $related($record);
        }
        
        return $models;
    }

    /**
     * Requête avec condition WHERE
     *
     * @param string $column
     * @param mixed $value
     * @return QueryBuilder
     */
    public static function where(string $column, $value, string $operator = '=')
    {
        $model = new static();
        $table = $model->getTable();
        
        $query = new QueryBuilder(self::getPdo());
        $query->setModel(static::class);
        $query->setTable($table);
        $query->where($column, $value, $operator);
        
        return $query;
    }

    /**
     * Spécifie les données à sérialiser en JSON
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->attributes;
    }
}

/**
 * Obtient le nom de la classe sans espace de noms
 *
 * @param string $class
 * @return string
 */
function class_basename(string $class): string
{
    $parts = explode('\\', $class);
    return end($parts);
} 