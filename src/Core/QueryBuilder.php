<?php

namespace Calvino\Core;

use PDO;

/**
 * Classe QueryBuilder
 * Construit des requêtes SQL de manière fluide
 */
class QueryBuilder
{
    /**
     * Instance PDO
     *
     * @var PDO
     */
    protected PDO $pdo;
    
    /**
     * Nom de la table
     *
     * @var string
     */
    protected string $table;
    
    /**
     * Classe du modèle
     *
     * @var string
     */
    protected string $model;
    
    /**
     * Conditions WHERE
     *
     * @var array
     */
    protected array $wheres = [];
    
    /**
     * Colonnes à sélectionner
     *
     * @var array
     */
    protected array $columns = ['*'];
    
    /**
     * Limite de résultats
     *
     * @var int|null
     */
    protected ?int $limit = null;
    
    /**
     * Constructeur
     *
     * @param PDO $pdo
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }
    
    /**
     * Définit le modèle
     *
     * @param string $model
     * @return self
     */
    public function setModel(string $model): self
    {
        $this->model = $model;
        return $this;
    }
    
    /**
     * Définit la table
     *
     * @param string $table
     * @return self
     */
    public function setTable(string $table): self
    {
        $this->table = $table;
        return $this;
    }
    
    /**
     * Ajoute une condition WHERE
     *
     * @param string $column
     * @param mixed $value
     * @param string $operator
     * @return self
     */
    public function where(string $column, $value, string $operator = '='): self
    {
        $this->wheres[] = [
            'column' => $column,
            'value' => $value,
            'operator' => $operator
        ];
        
        return $this;
    }
    
    /**
     * Spécifie les colonnes à sélectionner
     *
     * @param array $columns
     * @return self
     */
    public function select(array $columns = ['*']): self
    {
        $this->columns = $columns;
        return $this;
    }
    
    /**
     * Limite le nombre de résultats
     *
     * @param int $limit
     * @return self
     */
    public function limit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }
    
    /**
     * Récupère le premier résultat
     *
     * @return mixed
     */
    public function first()
    {
        $this->limit = 1;
        $results = $this->get();
        
        return !empty($results) ? $results[0] : null;
    }
    
    /**
     * Exécute la requête et retourne les résultats
     *
     * @return array
     */
    public function get(): array
    {
        $sql = $this->toSql();
        $values = $this->getBindingValues();
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($values);
        
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $this->hydrate($records);
    }
    
    /**
     * Construit la requête SQL
     *
     * @return string
     */
    protected function toSql(): string
    {
        $columns = implode(', ', $this->columns);
        $sql = "SELECT {$columns} FROM {$this->table}";
        
        if (!empty($this->wheres)) {
            $sql .= " WHERE ";
            $conditions = [];
            
            foreach ($this->wheres as $where) {
                $conditions[] = "{$where['column']} {$where['operator']} ?";
            }
            
            $sql .= implode(' AND ', $conditions);
        }
        
        if ($this->limit !== null) {
            $sql .= " LIMIT {$this->limit}";
        }
        
        return $sql;
    }
    
    /**
     * Récupère les valeurs pour les paramètres liés
     *
     * @return array
     */
    protected function getBindingValues(): array
    {
        $values = [];
        
        foreach ($this->wheres as $where) {
            $values[] = $where['value'];
        }
        
        return $values;
    }
    
    /**
     * Convertit les résultats en instances de modèle
     *
     * @param array $records
     * @return array
     */
    protected function hydrate(array $records): array
    {
        $models = [];
        
        foreach ($records as $record) {
            $models[] = new $this->model($record);
        }
        
        return $models;
    }
} 