<?php

namespace Calvino\Console\Commands;

use Calvino\Console\AnimatedCommand;

/**
 * Commande pour créer un nouveau middleware
 */
class MakeMiddlewareCommand extends AnimatedCommand
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'make:middleware';
    }
    
    /**
     * {@inheritdoc}
     */
    public function getDescription(): string
    {
        return 'Crée un nouveau middleware';
    }
    
    /**
     * {@inheritdoc}
     */
    public function handle(): void
    {
        try {
            // Afficher un titre
            $this->title("CRÉATION DE MIDDLEWARE");
            
            // Vérifier les arguments
            if (empty($this->args[0])) {
                $this->error("Nom du middleware requis.");
                echo "Usage: php calvino make:middleware [nom_middleware]\n";
                exit(1);
            }
            
            $middlewareName = $this->args[0];
            
            // Animation de préparation
            
            // Créer le middleware
            $this->createMiddleware($middlewareName);
            
        } catch (\Exception $e) {
            $this->error("Erreur: " . $e->getMessage());
            exit(1);
        }
    }
    
    /**
     * Crée un fichier de middleware
     *
     * @param string $middlewareName
     * @return bool
     */
    protected function createMiddleware(string $middlewareName): bool
    {
        // S'assurer que le nom commence par une majuscule
        $middlewareName = ucfirst($middlewareName);
        
        // Animation de vérification
        
        // Définir le chemin du fichier
        $middlewarePath = BASE_PATH . '/app/Middleware';
        $filePath = $middlewarePath . '/' . $middlewareName . '.php';
        
        // Vérifier si le répertoire existe
        if (!is_dir($middlewarePath)) {
            mkdir($middlewarePath, 0777, true);
        }
        
        // Vérifier si le fichier existe déjà
        if (file_exists($filePath)) {
            $this->error("Le middleware '{$middlewareName}' existe déjà.");
            return false;
        }
        
        // Créer le contenu du fichier
        $middlewareContent = $this->getMiddlewareTemplate($middlewareName);
        
        // Animation d'écriture
        
        // Écrire dans le fichier
        if (file_put_contents($filePath, $middlewareContent) !== false) {
            $this->success("Middleware '{$middlewareName}' créé avec succès");
            echo "Fichier créé: " . $filePath . "\n";
            echo "\033[90mN'oubliez pas d'enregistrer votre middleware dans config/app.php ou config/routes.php\033[0m\n\n";
            return true;
        }
        
        $this->error("Erreur lors de la création du fichier middleware.");
        return false;
    }
    
    /**
     * Retourne le template pour un middleware
     *
     * @param string $middlewareName
     * @return string
     */
    protected function getMiddlewareTemplate(string $middlewareName): string
    {
        return <<<PHP
<?php

namespace App\Middleware;

use Calvino\Http\Request;
use Calvino\Http\Response;

class {$middlewareName}
{
    /**
     * Gère la requête entrante
     *
     * @param Request \$request
     * @param callable \$next
     * @return mixed
     */
    public function handle(Request \$request, callable \$next)
    {
        // Logique de middleware ici
        // if (...) {
        //     return Response::json(['error' => 'Unauthorized'], 401);
        // }
        
        return \$next(\$request);
    }
}
PHP;
    }
}
