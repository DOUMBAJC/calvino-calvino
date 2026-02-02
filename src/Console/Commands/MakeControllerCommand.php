<?php

namespace Calvino\Console\Commands;

use Calvino\Console\AnimatedCommand;

/**
 * Commande pour créer un nouveau contrôleur
 */
class MakeControllerCommand extends AnimatedCommand
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'make:controller';
    }
    
    /**
     * {@inheritdoc}
     */
    public function getDescription(): string
    {
        return 'Crée un nouveau contrôleur';
    }
    
    /**
     * {@inheritdoc}
     */
    public function handle(): void
    {
        try {
            // Afficher un titre
            $this->title("CRÉATION DE CONTRÔLEUR");
            
            // Vérifier les arguments
            if (empty($this->args[0])) {
                $this->error("Nom du contrôleur requis.");
                echo "Usage: php calvino make:controller [nom_controleur]\n";
                exit(1);
            }
            
            $controllerName = $this->args[0];
            
            // Animation de préparation
            
            // Créer le contrôleur
            $this->createController($controllerName);
            
        } catch (\Exception $e) {
            $this->error("Erreur: " . $e->getMessage());
            exit(1);
        }
    }
    
    /**
     * Crée un fichier de contrôleur
     *
     * @param string $controllerName
     * @return bool
     */
    protected function createController(string $controllerName): bool
    {
        // S'assurer que le nom commence par une majuscule et finit par Controller
        $controllerName = ucfirst($controllerName);
        if (!str_ends_with($controllerName, 'Controller')) {
            $controllerName .= 'Controller';
        }
        
        // Animation de vérification
        
        // Définir le chemin du fichier
        $controllersPath = BASE_PATH . '/app/Controllers';
        $filePath = $controllersPath . '/' . $controllerName . '.php';
        
        // Vérifier si le répertoire existe
        if (!is_dir($controllersPath)) {
            mkdir($controllersPath, 0777, true);
        }
        
        // Vérifier si le fichier existe déjà
        if (file_exists($filePath)) {
            $this->error("Le contrôleur '{$controllerName}' existe déjà.");
            return false;
        }
        
        // Créer le contenu du fichier
        $controllerContent = $this->getControllerTemplate($controllerName);
        
        // Animation d'écriture
        
        // Écrire dans le fichier
        if (file_put_contents($filePath, $controllerContent) !== false) {
            $this->success("Contrôleur '{$controllerName}' créé avec succès");
            echo "Fichier créé: " . $filePath . "\n\n";
            return true;
        }
        
        $this->error("Erreur lors de la création du fichier contrôleur.");
        return false;
    }
    
    /**
     * Retourne le template pour un contrôleur
     *
     * @param string $controllerName
     * @return string
     */
    protected function getControllerTemplate(string $controllerName): string
    {
        $stubPath = BASE_PATH . '/stubs/controller.stub';
        
        if (file_exists($stubPath)) {
            $content = file_get_contents($stubPath);
            
            // Remplacements
            $content = str_replace('{{namespace}}', 'App\\Controllers', $content);
            $content = str_replace('{{class}}', $controllerName, $content);
            
            // Déterminer le nom de la ressource (ex: UserController -> User)
            $resource = str_replace('Controller', '', $controllerName);
            $content = str_replace('{{resource}}', $resource, $content);
            
            return $content;
        }
        
        // Fallback si le stub n'existe pas
        return <<<PHP
<?php

namespace App\Controllers;

use Calvino\Core\Controller;
use Calvino\Core\Request;
use Calvino\Core\Response;

class {$controllerName} extends Controller
{
    public function index(Request \$request)
    {
        return Response::json(['message' => 'Index']);
    }
}
PHP;
    }
}
