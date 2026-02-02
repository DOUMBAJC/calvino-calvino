<?php

namespace Calvino\Core;

/**
 * Classe Env
 * Gère le chargement des variables d'environnement
 */
class Env
{
    /**
     * Charge les variables d'environnement à partir d'un fichier
     *
     * @param string $path
     * @return void
     */
    public function load(string $path): void
    {
        if (!file_exists($path)) {
            return;
        }
        
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            // Ignorer les commentaires
            if (strpos(trim($line), '#') === 0) {
                continue;
            }
            
            // Extraire les paires clé=valeur
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            
            // Supprimer les guillemets si présents
            if (strpos($value, '"') === 0 && strrpos($value, '"') === strlen($value) - 1) {
                $value = substr($value, 1, -1);
            } elseif (strpos($value, "'") === 0 && strrpos($value, "'") === strlen($value) - 1) {
                $value = substr($value, 1, -1);
            }
            
            // Traiter les valeurs spéciales
            $value = $this->processValue($value);
            
            // Définir la variable d'environnement
            if (!getenv($name)) {
                putenv("{$name}={$value}");
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
    }
    
    /**
     * Traite les valeurs spéciales
     *
     * @param string $value
     * @return string
     */
    private function processValue(string $value): string
    {
        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return 'true';
            case 'false':
            case '(false)':
                return 'false';
            case 'null':
            case '(null)':
                return '';
            case 'empty':
            case '(empty)':
                return '';
        }
        
        // Remplacer les variables existantes dans la valeur
        if (strpos($value, '${') !== false && strpos($value, '}') !== false) {
            preg_match_all('/\${([^}]+)}/', $value, $matches);
            
            foreach ($matches[0] as $index => $match) {
                $envVar = $matches[1][$index];
                $envValue = getenv($envVar) ?: '';
                $value = str_replace($match, $envValue, $value);
            }
        }
        
        return $value;
    }
} 