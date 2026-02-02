<?php

namespace Calvino\Console\Commands;

use Calvino\Console\AnimatedCommand;

/**
 * Commande pour lister toutes les routes
 */
class RouteListCommand extends AnimatedCommand
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'route:list';
    }
    
    /**
     * {@inheritdoc}
     */
    public function getDescription(): string
    {
        return 'Liste toutes les routes enregistrées';
    }
    
    /**
     * {@inheritdoc}
     */
    public function handle(): void
    {
        $this->title("LISTE DES ROUTES");
        
        $router = $this->app->getRouter();
        $routes = $router->getRoutes();
        
        if (empty($routes)) {
            $this->warning("Aucune route enregistrée.");
            return;
        }
        
        
        $headers = ['Méthode', 'URI', 'Action', 'Middleware'];
        $data = [];
        
        foreach ($routes as $route) {
            $method = $route->getMethod();
            
            // Colorisation de la méthode
            $coloredMethod = $method;
            if ($method === 'GET') $coloredMethod = "\033[32mGET\033[0m";
            elseif ($method === 'POST') $coloredMethod = "\033[33mPOST\033[0m";
            elseif ($method === 'PUT') $coloredMethod = "\033[34mPUT\033[0m";
            elseif ($method === 'DELETE') $coloredMethod = "\033[31mDELETE\033[0m";
            
            // Formatage de l'action
            $action = 'Closure';
            if (!$route->hasClosure()) {
                $controller = $route->getController();
                $methodName = $route->getAction();
                // Simplifier le nom du contrôleur pour l'affichage
                $controllerParts = explode('\\', $controller);
                $shortController = end($controllerParts);
                $action = $shortController . '@' . $methodName;
            }
            
            // Formatage des middlewares
             $middlewares = implode(', ', $route->getMiddlewares());
             if (empty($middlewares)) {
                 $middlewares = '-';
             }
            
            $data[] = [
                $coloredMethod,
                $route->getPath(),
                $action,
                $middlewares
            ];
        }
        
        $this->table($headers, $data);
        $this->newLine();
        $this->info("Total: " . count($routes) . " routes");
        $this->newLine();
    }
}
