<?php

/**
 * Récupère une variable d'environnement
 *
 * @param string $key
 * @param mixed|null $default
 * @return mixed
 */
function env(string $key, $default = null)
{
    $value = getenv($key);
    
    if ($value === false) {
        return $default;
    }
    
    switch (strtolower($value)) {
        case 'true':
        case '(true)':
            return true;
        case 'false':
        case '(false)':
            return false;
        case 'null':
        case '(null)':
            return null;
        case 'empty':
        case '(empty)':
            return '';
    }
    
    return $value;
}


/**
 * Récupère une valeur de configuration
 *
 * @param string $key
 * @param mixed|null $default
 * @return mixed
 */
function config(string $key, $default = null)
{
    $keys = explode('.', $key);
    $file = array_shift($keys);
    
    if (!file_exists(BASE_PATH . "/config/{$file}.php")) {
        return $default;
    }
    
    $config = require BASE_PATH . "/config/{$file}.php";
    
    foreach ($keys as $segment) {
        if (!isset($config[$segment])) {
            return $default;
        }
        
        $config = $config[$segment];
    }
    
    return $config;
}


/**
 * Échappe le HTML pour éviter les XSS
 *
 * @param string $value
 * @return string
 */
function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}


/**
 * Récupère l'instance de l'application
 *
 * @return \Calvino\Core\Application
 */
function app(): \Calvino\Core\Application
{
    return \Calvino\Core\Application::getInstance();
}

/**
 * Récupère un paramètre de la requête
 *
 * @param string|null $key
 * @param mixed $default
 * @return mixed
 */
function request(?string $key = null, $default = null)
{
    $request = app()->getRequest();
    
    if ($key === null) {
        return $request;
    }
    
    return $request->input($key, $default);
}

/**
 * Génère une réponse JSON
 *
 * @param mixed $data
 * @param int $status
 * @return void
 */
function json($data, int $status = 200): void
{
    app()->getResponse()
        ->setStatusCode($status)
        ->json($data);
}

/**
 * Récupère une traduction
 *
 * @param string $key
 * @param array $replace
 * @param string|null $locale
 * @return string
 */
function trans(string $key, array $replace = [], ?string $locale = null): string
{
    // Si la locale n'est pas spécifiée, utiliser celle de l'application
    $locale = $locale ?: app()->getLocale();
    
    // Séparation du fichier et de la clé
    $segments = explode('.', $key);
    
    if (count($segments) === 1) {
        // Si pas de fichier spécifié, utiliser 'messages' par défaut
        $file = 'messages';
        $item = $segments[0];
    } else {
        $file = $segments[0];
        $itemPath = array_slice($segments, 1);
    }
    
    // Chemin du fichier de traduction
    $path = BASE_PATH . "/resources/lang/{$locale}/{$file}.php";
    
    // Si le fichier n'existe pas, essayer avec la locale de fallback
    if (!file_exists($path)) {
        $fallbackLocale = config('app.fallback_locale', 'en');
        $path = BASE_PATH . "/resources/lang/{$fallbackLocale}/{$file}.php";
        
        // Si le fichier de fallback n'existe pas non plus, retourner la clé
        if (!file_exists($path)) {
            return $key;
        }
    }
    
    // Charger le fichier de traduction
    $translations = require $path;
    
    // Naviguer dans le tableau de traductions pour les clés imbriquées
    if (count($segments) > 1) {
        $translation = $translations;
        foreach ($itemPath as $segment) {
            if (!isset($translation[$segment])) {
                return $key; // Clé non trouvée, retourner la clé d'origine
            }
            $translation = $translation[$segment];
        }
    } else {
        // Pour les clés simples (fichier par défaut)
        if (!isset($translations[$segments[0]])) {
            return $key;
        }
        $translation = $translations[$segments[0]];
    }
    
    // Vérifier que la traduction est bien une chaîne
    if (!is_string($translation)) {
        return $key; // Si ce n'est pas une chaîne (mais un tableau), retourner la clé d'origine
    }
    
    // Remplacer les placeholders dans la chaîne
    if (!empty($replace)) {
        foreach ($replace as $replaceKey => $value) {
            $translation = str_replace(":{$replaceKey}", $value, $translation);
        }
    }
    
    return $translation;
}

/**
 * Obtient l'instance du service d'authentification
 *
 * @return \Calvino\Core\Auth
 */
function auth()
{
    static $auth = null;
    
    if ($auth === null) {
        // Vérifions si l'application a déjà été initialisée
        try {
            $app = \Calvino\Core\Application::getInstance();
            
            // Vérifier si le service Auth existe déjà dans le conteneur
            if ($app->has('auth')) {
                $auth = $app->make('auth');
            } else {
                // Créer une nouvelle instance et la lier au conteneur
                $auth = new \Calvino\Core\Auth();
                $app->bind('auth', $auth);
            }
        } catch (\Exception $e) {
            // Si une exception se produit (par exemple lors de l'initialisation du routeur),
            // nous créons simplement une nouvelle instance sans l'enregistrer
            $auth = new \Calvino\Core\Auth();
        }
    }
    
    return $auth;
}