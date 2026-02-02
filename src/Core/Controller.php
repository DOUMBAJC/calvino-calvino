<?php

namespace Calvino\Core;

/**
 * Classe Controller
 * Classe de base pour tous les contrôleurs
 */
abstract class Controller
{
    /**
     * Constructeur
     */
    public function __construct()
    {
        // Initialisation du contrôleur
    }
    
    /**
     * Renvoie une réponse JSON
     *
     * @param mixed $data
     * @param int $status
     * @return array
     */
    protected function json($data, int $status = 200): array
    {
        return [
            'data' => $data,
            'status' => $status
        ];
    }
    
    /**
     * Renvoie une réponse d'erreur
     *
     * @param string $message
     * @param int $status
     * @return array
     */
    protected function error(string $message, int $status = 400): array
    {
        return [
            'error' => true,
            'message' => $message,
            'status' => $status
        ];
    }
    
    /**
     * Renvoie une réponse de succès
     *
     * @param string $message
     * @param mixed $data
     * @return array
     */
    protected function success(string $message, $data = null): array
    {
        $response = [
            'success' => true,
            'message' => $message
        ];
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        return $response;
    }
    
    /**
     * Valide les données d'entrée
     *
     * @param array $rules
     * @return array
     */
    protected function validate(array $rules): array
    {
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            $value = request($field);
            
            // Règle "required"
            if (strpos($rule, 'required') !== false && empty($value)) {
                $errors[$field] = "Le champ {$field} est requis";
                continue;
            }
            
            // Ignorer les autres validations si la valeur est vide et non requise
            if (empty($value)) {
                continue;
            }
            
            // Règle "email"
            if (strpos($rule, 'email') !== false && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $errors[$field] = "Le champ {$field} doit être une adresse email valide";
            }
            
            // Règle "min"
            if (preg_match('/min:(\d+)/', $rule, $matches)) {
                $min = (int) $matches[1];
                if (strlen($value) < $min) {
                    $errors[$field] = "Le champ {$field} doit contenir au moins {$min} caractères";
                }
            }
            
            // Règle "max"
            if (preg_match('/max:(\d+)/', $rule, $matches)) {
                $max = (int) $matches[1];
                if (strlen($value) > $max) {
                    $errors[$field] = "Le champ {$field} ne peut pas dépasser {$max} caractères";
                }
            }
            
            // Règle "numeric"
            if (strpos($rule, 'numeric') !== false && !is_numeric($value)) {
                $errors[$field] = "Le champ {$field} doit être numérique";
            }
            
            // Règle "in"
            if (preg_match('/\bin:([^|]+)/', $rule, $matches)) {
                $allowedValues = explode(',', $matches[1]);
                if (!in_array($value, $allowedValues)) {
                    $errors[$field] = "Le champ {$field} doit être parmi les valeurs suivantes : " . implode(', ', $allowedValues);
                }
            }
        }
        
        return $errors;
    }
} 