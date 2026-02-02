<?php

namespace Calvino\Core;

/**
 * Classe Request
 * Gère les requêtes HTTP
 */
class Request
{
    /**
     * Méthode HTTP
     *
     * @var string
     */
    private string $method;

    /**
     * Chemin de la requête
     *
     * @var string
     */
    private string $path;

    /**
     * Paramètres de la requête
     *
     * @var array
     */
    private array $query = [];

    /**
     * Corps de la requête
     *
     * @var array
     */
    private array $body = [];

    /**
     * En-têtes de la requête
     *
     * @var array
     */
    private array $headers = [];

    /**
     * Fichiers de la requête
     *
     * @var array
     */
    private array $files = [];
    
    /**
     * Règles de validation
     *
     * @var array
     */
    private array $rules = [];
    
    /**
     * Messages d'erreur personnalisés
     *
     * @var array
     */
    private array $messages = [];
    
    /**
     * Erreurs de validation
     *
     * @var array
     */
    private array $errors = [];
    
    /**
     * Indique si la validation a été effectuée
     *
     * @var bool
     */
    private bool $validated = false;

    /**
     * Limite de taille pour les chaînes très longues
     * 
     * @var int
     */
    private int $maxStringLength = 1048576; // 1MB 
    
    /**
     * Nombre maximal de règles à traiter par champ
     * 
     * @var int
     */
    private int $maxRulesPerField = 10;
    
    /**
     * Taille maximale de tableau acceptée
     * 
     * @var int
     */
    private int $maxArraySize = 1000;
    
    /**
     * Limite de mémoire pour les opérations de validation (en octets)
     * 
     * @var int
     */
    private int $memoryLimit = 104857600; // 100MB
    
    /**
     * Temps maximal d'exécution pour la validation (en secondes)
     * 
     * @var int
     */
    private int $maxExecutionTime = 60;
    
    /**
     * Timestamp de début de validation
     * 
     * @var float
     */
    private float $startTime;

    /**
     * Constructeur
     */
    public function __construct()
    {
        $this->method = $this->getRequestMethod();
        $this->path = $this->getRequestPath();
        $this->query = $this->getQueryParams();
        $this->body = $this->getBodyParams();
        $this->headers = $this->getHeaderParams();
        $this->files = $_FILES;
        
        // Augmenter temporairement la limite de mémoire pour cette requête
        ini_set('memory_limit', $this->memoryLimit / 1024 / 1024 . 'M');
        
        // Augmenter temporairement le temps d'exécution maximum
        ini_set('max_execution_time', $this->maxExecutionTime * 2);
        
        // Initialiser le temps de début
        $this->startTime = microtime(true);
    }

    /**
     * Récupère la méthode HTTP
     *
     * @return string
     */
    private function getRequestMethod(): string
    {
        $method = $_SERVER['REQUEST_METHOD'];
        
        // Vérifier si la méthode est écrasée via X-HTTP-Method-Override
        if ($method === 'POST') {
            if (isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
                $method = $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'];
            }
        }
        
        return $method;
    }

    /**
     * Récupère le chemin de la requête
     *
     * @return string
     */
    private function getRequestPath(): string
    {
        $path = $_SERVER['REQUEST_URI'] ?? '/';
        
        // Supprime les paramètres de recherche
        $position = strpos($path, '?');
        if ($position !== false) {
            $path = substr($path, 0, $position);
        }
        
        // Supprime le trailing slash
        if ($path !== '/' && substr($path, -1) === '/') {
            $path = rtrim($path, '/');
        }
        
        return $path;
    }

    /**
     * Récupère les paramètres de requête
     *
     * @return array
     */
    private function getQueryParams(): array
    {
        return $_GET;
    }

    /**
     * Récupère les paramètres du corps
     *
     * @return array
     */
    private function getBodyParams(): array
    {
        $body = [];
        
        // Si c'est un formulaire
        if ($_POST) {
            $body = $_POST;
        } else {
            // Si c'est du JSON
            $inputJSON = file_get_contents('php://input');
            if ($inputJSON) {
                $body = json_decode($inputJSON, true) ?? [];
            }
        }
        
        return $body;
    }

    /**
     * Récupère les en-têtes HTTP
     *
     * @return array
     */
    private function getHeaderParams(): array
    {
        $headers = [];
        
        foreach ($_SERVER as $key => $value) {
            if (substr($key, 0, 5) === 'HTTP_') {
                $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
                $headers[$header] = $value;
            }
        }
        
        return $headers;
    }

    /**
     * Vérifie si la requête est en AJAX
     *
     * @return bool
     */
    public function isAjax(): bool
    {
        return isset($this->headers['X-Requested-With']) && 
               $this->headers['X-Requested-With'] === 'XMLHttpRequest';
    }

    /**
     * Vérifie si la requête est en JSON
     *
     * @return bool
     */
    public function isJson(): bool
    {
        return isset($this->headers['Content-Type']) && 
               strpos($this->headers['Content-Type'], 'application/json') !== false;
    }

    /**
     * Obtient la méthode HTTP
     *
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Obtient le chemin de la requête
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Obtient un paramètre de requête
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function query(string $name, $default = null)
    {
        return $this->query[$name] ?? $default;
    }

    /**
     * Obtient un paramètre du corps
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function input(string $name, $default = null)
    {
        return $this->body[$name] ?? $default;
    }

    /**
     * Obtient tous les paramètres du corps
     *
     * @return array
     */
    public function all(): array
    {
        return $this->body;
    }

    /**
     * Obtient un en-tête
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function header(string $name, $default = null)
    {
        return $this->headers[$name] ?? $default;
    }

    /**
     * Obtient un fichier
     *
     * @param string $name
     * @return array|null
     */
    public function file(string $name): ?array
    {
        return $this->files[$name] ?? null;
    }
    
    /**
     * Définit les règles de validation pour la requête
     *
     * @param array $rules Les règles de validation
     * @return self
     */
    public function setRules(array $rules): self
    {
        $this->rules = $rules;
        return $this;
    }
    
    /**
     * Définit les messages d'erreur personnalisés
     *
     * @param array $messages Les messages d'erreur
     * @return self
     */
    public function setMessages(array $messages): self
    {
        $this->messages = $messages;
        return $this;
    }
    
    /**
     * Valide les données de la requête selon les règles définies
     *
     * @return bool
     */
    public function validate(): bool
    {
        $this->errors = [];
        $this->validated = true;
        $this->startTime = microtime(true);
        
        // Prétraitement des données pour éviter les dépassements mémoire
        foreach ($this->rules as $field => $rules) {
            // Vérifier si le temps d'exécution est dépassé
            if ($this->isTimeLimitExceeded()) {
                $this->addError($field, 'time_limit', []);
                return false;
            }
            
            $value = $this->input($field);
            
            // Vérification rapide pour les valeurs très volumineuses
            if ($this->isQuicklyTooComplex($field, $value)) {
                continue; // L'erreur a déjà été ajoutée par isQuicklyTooComplex
            }
        }
        
        // Si des erreurs ont été détectées, ne pas continuer la validation
        if (!empty($this->errors)) {
            return false;
        }
        
        // Validation champ par champ
        foreach ($this->rules as $field => $rules) {
            // Vérifier si le temps d'exécution est dépassé
            if ($this->isTimeLimitExceeded()) {
                $this->addError($field, 'time_limit', []);
                break;
            }
            
            // Convertir une règle unique en tableau
            if (!is_array($rules)) {
                $rules = explode('|', $rules);
            }
            
            // Limiter le nombre de règles pour éviter un traitement excessif
            if (count($rules) > $this->maxRulesPerField) {
                $rules = array_slice($rules, 0, $this->maxRulesPerField);
            }
            
            $value = $this->input($field);
            
            // Traiter chaque règle séparément
            foreach ($rules as $rule) {
                // Vérifier le temps d'exécution régulièrement
                if ($this->isTimeLimitExceeded()) {
                    $this->addError($field, 'time_limit', []);
                    break 2; // Sortir des deux boucles
                }
                
                // Vérifier l'usage de la mémoire avant chaque validation
                if (memory_get_usage() > $this->memoryLimit * 0.8) {
                    $this->addError($field, 'memory_limit', []);
                    break 2; // Sortir des deux boucles
                }
                
                // Extraction des paramètres de règle (ex: min:3)
                $parameters = [];
                if (strpos($rule, ':') !== false) {
                    [$ruleName, $paramString] = explode(':', $rule, 2);
                    $parameters = explode(',', $paramString);
                    
                    // Limiter la taille des paramètres
                    if (count($parameters) > 5) {
                        $parameters = array_slice($parameters, 0, 5);
                    }
                } else {
                    $ruleName = $rule;
                }
                
                // Appliquer la règle
                $methodName = 'validate' . ucfirst($ruleName);
                if (method_exists($this, $methodName)) {
                    try {
                        $valid = $this->$methodName($field, $value, $parameters);
                        if (!$valid) {
                            $this->addError($field, $ruleName, $parameters);
                        }
                    } catch (\Throwable $e) {
                        // En cas d'erreur pendant la validation, considérer comme échec
                        $this->addError($field, 'validation_error', [substr($e->getMessage(), 0, 100)]);
                    }
                } else {
                    // Règle non reconnue
                    trigger_error("Règle de validation inconnue: $ruleName", E_USER_WARNING);
                }
            }
        }
        
        return empty($this->errors);
    }
    
    /**
     * Vérifie si le temps d'exécution a dépassé la limite
     * 
     * @return bool
     */
    private function isTimeLimitExceeded(): bool
    {
        $elapsed = microtime(true) - $this->startTime;
        return $elapsed > $this->maxExecutionTime;
    }
    
    /**
     * Vérification rapide si une valeur est trop complexe
     * Cette méthode effectue uniquement des vérifications simples et rapides
     * 
     * @param string $field
     * @param mixed $value
     * @return bool
     */
    private function isQuicklyTooComplex(string $field, $value): bool
    {
        // Vérifier les chaînes trop longues (vérification rapide sans mb_strlen)
        if (is_string($value)) {
            if (strlen($value) > $this->maxStringLength) {
                $this->addError($field, 'max_length', [$this->maxStringLength]);
                return true;
            }
            return false;
        }
        
        // Vérifier les tableaux trop grands
        if (is_array($value)) {
            if (count($value) > $this->maxArraySize) {
                $this->addError($field, 'max_array_size', [$this->maxArraySize]);
                return true;
            }
            return false;
        }
        
        // Vérifier les objets rapidement
        if (is_object($value)) {
            if (method_exists($value, 'count') && $value->count() > $this->maxArraySize) {
                $this->addError($field, 'max_object_complexity', [$this->maxArraySize]);
                return true;
            }
            
            // Si l'objet est visiblement trop complexe (par exemple, une requête imbriquée)
            if (get_class($value) == 'Request' || get_class($value) == self::class) {
                $this->addError($field, 'complex_object', [get_class($value)]);
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Vérifie si une valeur est trop complexe pour être traitée
     * Cette méthode effectue une vérification plus approfondie que isQuicklyTooComplex
     * 
     * @param string $field Le nom du champ
     * @param mixed $value La valeur à vérifier
     * @return bool
     */
    private function isTooComplex(string $field, $value): bool
    {
        // Vérifier d'abord avec la méthode rapide
        if ($this->isQuicklyTooComplex($field, $value)) {
            return true;
        }
        
        // Vérifier régulièrement si on dépasse le temps d'exécution
        if ($this->isTimeLimitExceeded()) {
            $this->addError($field, 'time_limit', []);
            return true;
        }
        
        // Vérifications plus poussées pour les objets
        if (is_object($value)) {
            try {
                // On limite le temps pour cette opération potentiellement coûteuse
                if ($this->isTimeLimitExceeded()) {
                    $this->addError($field, 'time_limit', []);
                    return true;
                }
                
                // Essayer de convertir l'objet en tableau pour vérifier sa complexité
                $valueArray = (array) $value;
                if (count($valueArray) > $this->maxArraySize) {
                    $this->addError($field, 'max_object_complexity', [$this->maxArraySize]);
                    return true;
                }
            } catch (\Throwable $e) {
                $this->addError($field, 'complex_object', []);
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Ajoute une erreur de validation
     *
     * @param string $field Le champ concerné
     * @param string $rule La règle non respectée
     * @param array $parameters Les paramètres de la règle
     * @return void
     */
    private function addError(string $field, string $rule, array $parameters = []): void
    {
        // Vérifier si un message personnalisé existe
        $messageKey = "$field.$rule";
        if (isset($this->messages[$messageKey])) {
            $message = $this->messages[$messageKey];
        } else {
            // Message par défaut
            $message = $this->getDefaultErrorMessage($field, $rule, $parameters);
        }
        
        // Remplacer les placeholders comme :attribute
        $message = str_replace(':attribute', $field, $message);
        
        // Remplacer les autres placeholders comme :min, :max, etc.
        foreach ($parameters as $index => $parameter) {
            $placeholder = ':param' . ($index + 1);
            $message = str_replace($placeholder, $parameter, $message);
            
            // Cas spécifiques comme :min, :max
            if ($index === 0) {
                if ($rule === 'min') $message = str_replace(':min', $parameter, $message);
                if ($rule === 'max') $message = str_replace(':max', $parameter, $message);
                if ($rule === 'size') $message = str_replace(':size', $parameter, $message);
            }
        }
        
        // Ajouter l'erreur
        $this->errors[$field][] = $message;
    }
    
    /**
     * Retourne un message d'erreur par défaut pour une règle
     *
     * @param string $field Le champ concerné
     * @param string $rule La règle non respectée
     * @param array $parameters Les paramètres de la règle
     * @return string
     */
    private function getDefaultErrorMessage(string $field, string $rule, array $parameters = []): string
    {
        $messages = [
            'required' => 'Le champ :attribute est obligatoire.',
            'min' => 'Le champ :attribute doit avoir au moins :min caractères.',
            'max' => 'Le champ :attribute ne peut pas avoir plus de :max caractères.',
            'email' => 'Le champ :attribute doit être une adresse email valide.',
            'numeric' => 'Le champ :attribute doit être un nombre.',
            'integer' => 'Le champ :attribute doit être un entier.',
            'float' => 'Le champ :attribute doit être un nombre décimal.',
            'date' => 'Le champ :attribute doit être une date valide.',
            'in' => 'La valeur du champ :attribute est invalide.',
            'regex' => 'Le format du champ :attribute est invalide.',
            'unique' => 'La valeur du champ :attribute est déjà utilisée.',
            'same' => 'Les champs :attribute et :param1 doivent correspondre.',
            'different' => 'Les champs :attribute et :param1 doivent être différents.',
            'size' => 'Le champ :attribute doit avoir une taille de :size.',
            'between' => 'Le champ :attribute doit être entre :param1 et :param2.',
            'boolean' => 'Le champ :attribute doit être vrai ou faux.',
            'confirmed' => 'La confirmation du champ :attribute ne correspond pas.',
            'array' => 'Le champ :attribute doit être un tableau.',
            'exists' => 'Le champ :attribute sélectionné est invalide.',
            'validation_error' => 'Erreur de validation pour le champ :attribute: :param1.',
            'memory_limit' => 'La validation a dépassé la limite de mémoire. Simplifiez vos données.',
            'time_limit' => 'La validation a dépassé le temps d\'exécution maximal. Simplifiez vos données.',
            'max_length' => 'Le champ :attribute est trop long (maximum :param1 caractères).',
            'max_array_size' => 'Le tableau :attribute contient trop d\'éléments (maximum :param1).',
            'max_object_complexity' => 'L\'objet :attribute est trop complexe.',
            'complex_object' => 'L\'objet :attribute ne peut pas être traité en raison de sa complexité.',
            'url' => 'Le champ :attribute doit être une URL valide.'
        ];
        
        return $messages[$rule] ?? "Le champ $field est invalide.";
    }
    
    /**
     * Vérifie si le champ est obligatoire
     */
    private function validateRequired(string $field, $value, array $parameters = []): bool
    {
        if (is_null($value)) {
            return false;
        }
        
        if (is_string($value) && trim($value) === '') {
            return false;
        }
        
        if (is_array($value) && count($value) === 0) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Vérifie si le champ a une longueur minimale
     */
    private function validateMin(string $field, $value, array $parameters = []): bool
    {
        // Vérifier si on dépasse le temps d'exécution
        if ($this->isTimeLimitExceeded()) {
            return false;
        }
        
        if (is_null($value) || $value === '') {
            return true; // Ne pas valider si vide (sauf si aussi required)
        }
        
        $min = isset($parameters[0]) ? (int) $parameters[0] : 0;
        
        if (is_string($value)) {
            // Utiliser strlen au lieu de mb_strlen pour les performances
            if (strlen($value) > $this->maxStringLength) {
                return false;
            }
            return strlen($value) >= $min;
        }
        
        if (is_array($value)) {
            return count($value) >= $min;
        }
        
        if (is_numeric($value)) {
            return $value >= $min;
        }
        
        return false;
    }
    
    /**
     * Vérifie si le champ a une longueur maximale
     */
    private function validateMax(string $field, $value, array $parameters = []): bool
    {
        // Vérifier si on dépasse le temps d'exécution
        if ($this->isTimeLimitExceeded()) {
            return false;
        }
        
        if (is_null($value) || $value === '') {
            return true; // Ne pas valider si vide
        }
        
        $max = isset($parameters[0]) ? (int) $parameters[0] : PHP_INT_MAX;
        
        if (is_string($value)) {
            // Utiliser strlen au lieu de mb_strlen pour les performances
            if (strlen($value) > $this->maxStringLength) {
                return false;
            }
            return strlen($value) <= $max;
        }
        
        if (is_array($value)) {
            return count($value) <= $max;
        }
        
        if (is_numeric($value)) {
            return $value <= $max;
        }
        
        return false;
    }
    
    /**
     * Vérifie si le champ est un email valide
     */
    private function validateEmail(string $field, $value, array $parameters = []): bool
    {
        if (is_null($value) || $value === '') {
            return true; // Ne pas valider si vide
        }
        
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Vérifie si le champ est un nombre
     */
    private function validateNumeric(string $field, $value, array $parameters = []): bool
    {
        if (is_null($value) || $value === '') {
            return true; // Ne pas valider si vide
        }
        
        return is_numeric($value);
    }
    
    /**
     * Vérifie si le champ est un entier
     */
    private function validateInteger(string $field, $value, array $parameters = []): bool
    {
        if (is_null($value) || $value === '') {
            return true; // Ne pas valider si vide
        }
        
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }
    
    /**
     * Vérifie si le champ est une date valide
     */
    private function validateDate(string $field, $value, array $parameters = []): bool
    {
        if (is_null($value) || $value === '') {
            return true; // Ne pas valider si vide
        }
        
        $format = $parameters[0] ?? 'Y-m-d';
        $date = \DateTime::createFromFormat($format, $value);
        return $date && $date->format($format) === $value;
    }
    
    /**
     * Vérifie si le champ est une des valeurs dans une liste
     */
    private function validateIn(string $field, $value, array $parameters = []): bool
    {
        if (is_null($value) || $value === '') {
            return true; // Ne pas valider si vide
        }
        
        return in_array($value, $parameters);
    }
    
    /**
     * Vérifie si le champ est un booléen
     */
    private function validateBoolean(string $field, $value, array $parameters = []): bool
    {
        if (is_null($value) || $value === '') {
            return true; // Ne pas valider si vide
        }
        
        $acceptable = [true, false, 0, 1, '0', '1', 'true', 'false'];
        return in_array($value, $acceptable, true);
    }
    
    /**
     * Vérifie si le champ est une URL valide
     */
    private function validateUrl(string $field, $value, array $parameters = []): bool
    {
        if (is_null($value) || $value === '') {
            return true; // Ne pas valider si vide
        }
        
        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }
    
    /**
     * Vérifie si les erreurs ont été définies pour un champ
     *
     * @param string $field Le champ à vérifier
     * @return bool
     */
    public function hasError(string $field): bool
    {
        return isset($this->errors[$field]);
    }
    
    /**
     * Obtient les erreurs pour un champ spécifique
     *
     * @param string $field Le champ concerné
     * @return array
     */
    public function getFieldErrors(string $field): array
    {
        return $this->errors[$field] ?? [];
    }
    
    /**
     * Obtient toutes les erreurs de validation
     *
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
    
    /**
     * Obtient le premier message d'erreur pour un champ
     *
     * @param string $field Le champ concerné
     * @return string|null
     */
    public function getFirstError(string $field): ?string
    {
        return $this->errors[$field][0] ?? null;
    }
    
    /**
     * Vérifie si la validation a échoué
     *
     * @return bool
     */
    public function fails(): bool
    {
        if (!$this->validated) {
            $this->validate();
        }
        return !empty($this->errors);
    }
    
    /**
     * Vérifie si la validation a réussi
     *
     * @return bool
     */
    public function passes(): bool
    {
        return !$this->fails();
    }
    
    /**
     * Renvoie un tableau avec toutes les données validées
     * Les champs qui ne respectent pas les règles ne seront pas inclus
     *
     * @return array
     */
    public function validated(): array
    {
        if (!$this->validated) {
            $this->validate();
        }
        
        $validData = [];
        $this->startTime = microtime(true); // Réinitialiser le temps pour cette opération
        
        foreach ($this->rules as $field => $rules) {
            // Vérifier si on dépasse le temps d'exécution
            if ($this->isTimeLimitExceeded()) {
                $this->addError($field, 'time_limit', []);
                break;
            }
            
            if (!$this->hasError($field)) {
                $value = $this->input($field);
                
                // Vérification rapide pour les valeurs complexes
                if ($this->isQuicklyTooComplex($field, $value)) {
                    continue;
                }
                
                // Vérifier l'usage de la mémoire
                if (memory_get_usage() > $this->memoryLimit * 0.8) {
                    $this->addError($field, 'memory_limit', []);
                    break;
                }
                
                $validData[$field] = $value;
            }
        }
        
        return $validData;
    }
} 