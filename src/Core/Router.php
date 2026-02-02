<?php

namespace Calvino\Core;

/**
 * Classe Router
 * Gère les routes de l'application
 */
class Router
{
    /**
     * Routes enregistrées
     *
     * @var array
     */
    private array $routes = [];
    
    /**
     * Préfixe actuel pour les routes
     *
     * @var string
     */
    private string $prefix = '';
    
    /**
     * Espace de noms actuel pour les contrôleurs
     *
     * @var string
     */
    private string $namespace = '';
    
    /**
     * Middlewares actuels pour les routes
     *
     * @var array
     */
    private array $middlewares = [];
    
    /**
     * Ajoute une route GET
     *
     * @param string $uri
     * @param array|string $action
     * @return Route
     */
    public function get(string $uri, $action): Route
    {
        return $this->addRoute('GET', $uri, $action);
    }
    
    /**
     * Ajoute une route POST
     *
     * @param string $uri
     * @param array|string $action
     * @return Route
     */
    public function post(string $uri, $action): Route
    {
        return $this->addRoute('POST', $uri, $action);
    }
    
    /**
     * Ajoute une route PUT
     *
     * @param string $uri
     * @param array|string $action
     * @return Route
     */
    public function put(string $uri, $action): Route
    {
        return $this->addRoute('PUT', $uri, $action);
    }
    
    /**
     * Ajoute une route DELETE
     *
     * @param string $uri
     * @param array|string $action
     * @return Route
     */
    public function delete(string $uri, $action): Route
    {
        return $this->addRoute('DELETE', $uri, $action);
    }
    
    /**
     * Ajoute une route pour toutes les méthodes HTTP
     *
     * @param string $uri
     * @param array|string $action
     * @return Route
     */
    public function any(string $uri, $action): Route
    {
        return $this->addRoute('ANY', $uri, $action);
    }
    
    
    /**
     * Ajoute une route
     *
     * @param string $method
     * @param string $uri
     * @param array|string $action
     * @return Route
     */
    private function addRoute(string $method, string $uri, $action): Route
    {
        // Ajouter le préfixe si défini
        if ($this->prefix) {
            $uri = rtrim($this->prefix, '/') . '/' . ltrim($uri, '/');
        }
        
        // Traiter l'action
        if (is_string($action)) {
            // Format: 'Controller@method'
            list($controller, $actionMethod) = explode('@', $action);
            
            // Ajouter l'espace de noms si défini
            if ($this->namespace && strpos($controller, '\\') !== 0) {
                $controller = rtrim($this->namespace, '\\') . '\\' . $controller;
            }
            
            $action = [
                'controller' => $controller,
                'method' => $actionMethod
            ];
        }
        
        // Créer la route
        $route = new Route($method, $uri, $action);
        
        // Ajouter les middlewares globaux
        foreach ($this->middlewares as $middleware) {
            $route->middleware($middleware);
        }
        
        $this->routes[] = $route;
        
        return $route;
    }
    
    /**
     * Groupe les routes avec des attributs partagés
     *
     * @param array $attributes
     * @param callable $callback
     * @return void
     */
    public function group(array $attributes, callable $callback): void
    {
        // Sauvegarder les attributs actuels
        $oldPrefix = $this->prefix;
        $oldNamespace = $this->namespace;
        $oldMiddlewares = $this->middlewares;
        
        // Fusionner les nouveaux attributs
        if (isset($attributes['prefix'])) {
            $this->prefix = $this->prefix 
                ? rtrim($this->prefix, '/') . '/' . ltrim($attributes['prefix'], '/')
                : $attributes['prefix'];
        }
        
        if (isset($attributes['namespace'])) {
            $this->namespace = $this->namespace 
                ? rtrim($this->namespace, '\\') . '\\' . trim($attributes['namespace'], '\\')
                : $attributes['namespace'];
        }
        
        if (isset($attributes['middleware'])) {
            $middlewareGroups = config('routes.middleware_groups', []);
            $middlewares = [];
            
            // Si c'est un nom de groupe de middleware
            if (is_string($attributes['middleware']) && isset($middlewareGroups[$attributes['middleware']])) {
                $middlewares = $middlewareGroups[$attributes['middleware']];
            } else {
                $middlewares = is_array($attributes['middleware']) 
                    ? $attributes['middleware'] 
                    : [$attributes['middleware']];
            }
                
            $this->middlewares = array_merge($this->middlewares, $middlewares);
        }
        
        // Exécuter le callback pour ajouter les routes
        $callback();
        
        // Restaurer les attributs précédents
        $this->prefix = $oldPrefix;
        $this->namespace = $oldNamespace;
        $this->middlewares = $oldMiddlewares;
    }
    
    /**
     * Résout une route en fonction de la requête
     *
     * @param Request $request
     * @return Route|null
     */
    public function resolve(Request $request): ?Route
    {
        $method = $request->getMethod();
        $path = $request->getPath();
        
        // Rechercher la route correspondante
        foreach ($this->routes as $route) {
            if ($route->matches($method, $path)) {
                return $route;
            }
        }
        
        return null;
    }
    
    /**
     * Récupère toutes les routes
     *
     * @return array
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }
} 