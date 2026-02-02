<?php

namespace Calvino\Core;

use Calvino\Middleware\Middleware;

/**
 * Classe Application
 * Représente le cœur de l'application
 */
class Application
{
    /**
     * La version du framework
     * 
     * @var string
     */
    public const VERSION = '1.0.0';

    /**
     * Instance singleton de l'application
     *
     * @var Application
     */
    private static $instance;

    /**
     * Router de l'application
     *
     * @var Router
     */
    private Router $router;
    
    /**
     * Requête actuelle
     *
     * @var Request
     */
    private Request $request;
    
    /**
     * Réponse à envoyer
     *
     * @var Response
     */
    private Response $response;
    
    /**
     * Conteneur de services
     *
     * @var array
     */
    private array $services = [];
    
    /**
     * Fournisseurs de services enregistrés
     *
     * @var array
     */
    private array $serviceProviders = [];

    /**
     * Middlewares globaux
     *
     * @var array
     */
    private array $middlewares = [];

    /**
     * La locale actuelle de l'application
     * 
     * @var string
     */
    protected $locale;

    /**
     * Constructeur
     */
    private function __construct()
    {
        $this->router = new Router();
        $this->request = new Request();
        $this->response = new Response();
        
        // Charger les routes
        $this->loadRoutes();
        
        // Initialiser la locale
        $this->locale = config('app.locale', 'fr');
    }

    /**
     * Retourne l'instance de l'application
     *
     * @return Application
     */
    public static function getInstance(): Application
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }
    
    /**
     * Charge les fichiers de routes
     *
     * @return void
     */
    private function loadRoutes(): void
    {
        // Les routes seront chargées par le RouteServiceProvider
        // Ne rien faire ici pour éviter le double chargement
    }
    
    /**
     * Enregistre un fournisseur de services
     *
     * @param string $providerClass
     * @return void
     */
    public function register(string $providerClass): void
    {
        // Éviter les doublons
        if (in_array($providerClass, $this->serviceProviders)) {
            return;
        }
        
        $provider = new $providerClass($this);
        $provider->register();
        
        $this->serviceProviders[] = $providerClass;
        
        // Lancer la méthode boot si disponible
        if (method_exists($provider, 'boot')) {
            $provider->boot();
        }
    }
    
    /**
     * Enregistre une instance de service dans le conteneur
     *
     * @param string $name
     * @param mixed $instance
     * @return void
     */
    public function bind(string $name, $instance): void
    {
        $this->services[$name] = $instance;
    }
    
    /**
     * Récupère un service du conteneur
     *
     * @param string $name
     * @return mixed
     */
    public function make(string $name)
    {
        if (!isset($this->services[$name])) {
            throw new \Exception("Service [{$name}] not found in the container");
        }
        
        return $this->services[$name];
    }
    
    /**
     * Vérifie si un service existe dans le conteneur
     *
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool
    {
        return isset($this->services[$name]);
    }
    
    /**
     * Résout un service du conteneur qui pourrait être une closure
     *
     * @param string $name
     * @return mixed
     */
    public function resolve(string $name)
    {
        $service = $this->make($name);
        
        // Si le service est une closure, on l'exécute
        if ($service instanceof \Closure) {
            return $service($this);
        }
        
        // Sinon on retourne le service tel quel
        return $service;
    }
    
    /**
     * Ajoute un middleware global à l'application
     *
     * @param Middleware $middleware
     * @return self
     */
    public function addMiddleware(Middleware $middleware): self
    {
        $this->middlewares[] = $middleware;
        return $this;
    }
    
    /**
     * Obtient tous les middlewares globaux
     *
     * @return array
     */
    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }
    
    /**
     * Exécute l'application
     *
     * @return void
     */
    public function run(): void
    {
        try {
            $request = $this->request;
            
            // Exécuter les middlewares globaux
            $this->processGlobalMiddlewares($request);
            
            // Vérifier si la route existe
            $route = $this->router->resolve($request);
            
            if (!$route) {
                $this->response->setStatusCode(404);
                $this->response->json([
                    'error' => true,
                    'message' => 'Route not found',
                    'status' => 404
                ]);
                return;
            }
            
            // Exécuter les middlewares de la route
            $middlewares = $route->getMiddlewares();
            foreach ($middlewares as $middleware) {
                $middlewareClass = $this->resolveMiddleware($middleware);
                $middlewareInstance = new $middlewareClass();
                $middlewareInstance->handle($request, function() {});
            }
            
            // Exécuter le contrôleur ou la fonction anonyme
            $result = null;
            
            if ($route->hasClosure()) {
                // Cas d'une fonction anonyme
                $closure = $route->getClosure();
                $result = $closure();
            } else {
                // Cas d'un contrôleur
                $controller = $route->getController();
                $action = $route->getAction();
                $params = $route->getParams();
                
                if (empty($controller)) {
                    throw new \Exception("Contrôleur non défini pour la route " . $route->getPath());
                }
                
                $controllerInstance = new $controller();
                // Ajouter l'objet Request aux paramètres pour le contrôleur
                array_unshift($params, $this->request);
                $result = call_user_func_array([$controllerInstance, $action], $params);
            }
            
            // Envoyer la réponse
            if (is_array($result)) {
                $this->response->json($result);
            } else {
                $this->response->send($result);
            }
        } catch (\Exception $e) {
            $statusCode = $e->getCode() ?: 500;
            $this->response->setStatusCode($statusCode);
                $this->response->json([
                    'error' => true,
                    'message' => $e->getMessage(),
                    'status' => $statusCode
                ]);
        }
    }
    
    /**
     * Exécute les middlewares globaux
     *
     * @param Request $request
     * @return void
     */
    private function processGlobalMiddlewares(Request $request): void
    {
        if (empty($this->middlewares)) {
            return;
        }
        
        // Créer une chaîne de middlewares
        $next = function ($request) {
            return $request;
        };
        
        // Exécuter les middlewares en ordre inverse
        for ($i = count($this->middlewares) - 1; $i >= 0; $i--) {
            $middleware = $this->middlewares[$i];
            $current = $next;
            
            $next = function ($request) use ($middleware, $current) {
                return $middleware->handle($request, $current);
            };
        }
        
        // Exécuter la chaîne
        $next($request);
    }

    /**
     * Obtenir le routeur
     *
     * @return Router
     */
    public function getRouter(): Router
    {
        return $this->router;
    }
    
    /**
     * Obtenir la requête
     *
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }
    
    /**
     * Obtenir la réponse
     *
     * @return Response
     */
    public function getResponse(): Response
    {
        return $this->response;
    }

    /**
     * Définit la locale de l'application
     * 
     * @param string $locale
     * @return void
     */
    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
    }

    /**
     * Récupère la locale de l'application
     * 
     * @return string
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * Résout un middleware en classe complète
     *
     * @param string $middleware
     * @return string
     */
    private function resolveMiddleware(string $middleware): string
    {
        // Si c'est déjà une classe complète
        if (class_exists($middleware)) {
            return $middleware;
        }
        
        // Vérifier dans les groupes de middlewares configurés
        $middlewareGroups = config('routes.middleware_groups', []);
        if (isset($middlewareGroups[$middleware]) && is_array($middlewareGroups[$middleware]) && !empty($middlewareGroups[$middleware])) {
            // Retourner le premier middleware du groupe
            return $middlewareGroups[$middleware][0];
        }
        
        // Essayer avec le namespace par défaut
        $middlewareClass = "\\Calvino\\Middleware\\{$middleware}";
        if (class_exists($middlewareClass)) {
            return $middlewareClass;
        }
        
        // Si on ajoute "Middleware" au nom
        $middlewareClass = "\\Calvino\\Middleware\\{$middleware}Middleware";
        if (class_exists($middlewareClass)) {
            return $middlewareClass;
        }
        
        // Si rien ne fonctionne, retourner le middleware tel quel
        return $middleware;
    }
} 