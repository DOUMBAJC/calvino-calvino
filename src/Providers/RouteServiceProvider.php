<?php

namespace Calvino\Providers;

use Calvino\Core\Application;
use Calvino\Providers\ServiceProvider;

/**
 * Fournisseur de services pour les routes
 */
class RouteServiceProvider extends ServiceProvider
{
    /**
     * Application
     *
     * @var Application
     */
    protected Application $app;

    /**
     * Préfixe pour les routes API
     *
     * @var string
     */
    protected string $apiPrefix = 'api';

    /**
     * Constructeur
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Enregistre les services dans le conteneur
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->bind('routes', $this);
    }

    /**
     * Démarre le service après l'enregistrement
     *
     * @return void
     */
    public function boot(): void
    {
        $this->loadRoutes();
    }

    /**
     * Charge les routes de l'application
     *
     * @return void
     */
    protected function loadRoutes(): void
    {
        $router = $this->app->getRouter();
        
        // Chargement simple si la méthode group n'est pas disponible
        if (!method_exists($router, 'group')) {
            require_once BASE_PATH . '/routes/api.php';
            return;
        }
        
        // Configuration du groupe API avec préfixe
        $router->group([
            'prefix' => $this->apiPrefix,
            'namespace' => '\\Calvino\\Controllers\\Api'
        ], function() use ($router) {
            require_once BASE_PATH . '/routes/api.php';
        });
    }
} 