<?php

namespace Calvino\Console\Commands;

use Calvino\Console\AnimatedCommand;

/**
 * Commande pour créer un nouveau modèle
 */
class MakeModelCommand extends AnimatedCommand
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'make:model';
    }
    
    /**
     * {@inheritdoc}
     */
    public function getDescription(): string
    {
        return 'Crée un nouveau modèle';
    }
    
    /**
     * {@inheritdoc}
     */
    public function handle(): void
    {
        try {
            // Afficher un titre
            $this->title("CRÉATION DE MODÈLE");
            
            // Vérifier les arguments
            if (empty($this->args[0])) {
                $this->error("Nom du modèle requis.");
                echo "Usage: php calvino make:model [nom_modele]\n";
                exit(1);
            }
            
            $modelName = $this->args[0];
            
            // Animation de préparation
            
            // Créer le modèle
            $this->createModel($modelName);
            
        } catch (\Exception $e) {
            $this->error("Erreur: " . $e->getMessage());
            exit(1);
        }
    }
    
    /**
     * Crée un fichier de modèle
     *
     * @param string $modelName
     * @return bool
     */
    protected function createModel(string $modelName): bool
    {
        // S'assurer que le nom commence par une majuscule
        $modelName = ucfirst($modelName);
        
        // Animation de vérification
        
        // Définir le chemin du fichier
        $modelsPath = BASE_PATH . '/app/Models';
        $filePath = $modelsPath . '/' . $modelName . '.php';
        
        // Vérifier si le répertoire existe
        if (!is_dir($modelsPath)) {
            mkdir($modelsPath, 0777, true);
        }
        
        // Vérifier si le fichier existe déjà
        if (file_exists($filePath)) {
            $this->error("Le modèle '{$modelName}' existe déjà.");
            return false;
        }
        
        // Créer le contenu du fichier
        $tableName = $this->pluralize(strtolower($modelName));
        
        $modelContent = $this->getModelTemplate($modelName, $tableName);
        
        // Animation d'écriture
        
        // Écrire dans le fichier
        if (file_put_contents($filePath, $modelContent) !== false) {
            $this->success("Modèle '{$modelName}' créé avec succès");
            echo "Fichier créé: " . $filePath . "\n\n";
            
            // Demander si l'utilisateur veut également créer une migration
            if ($this->confirm("Voulez-vous créer une migration pour ce modèle?")) {
                $dbManager = $this->initDatabaseServices();
                
                $result = $dbManager->createMigrationFile($modelName);
                
                if ($result) {
                    $this->success("Fichier de migration créé avec succès");
                    if (is_string($result)) {
                        echo "Fichier créé: " . $result . "\n";
                    }
                } else {
                    $this->error("Erreur lors de la création du fichier de migration.");
                }
            }
            
            return true;
        }
        
        $this->error("Erreur lors de la création du fichier modèle.");
        return false;
    }
    
    /**
     * Retourne le template pour un modèle
     *
     * @param string $modelName
     * @param string $tableName
     * @return string
     */
    protected function getModelTemplate(string $modelName, string $tableName): string
    {
        $stubPath = BASE_PATH . '/stubs/model.stub';
        
        if (file_exists($stubPath)) {
            $content = file_get_contents($stubPath);
            
            // Remplacements
            $content = str_replace('{{namespace}}', 'App\\Models', $content);
            $content = str_replace('{{class}}', $modelName, $content);
            $content = str_replace('{{table}}', $tableName, $content);
            
            return $content;
        }

        return <<<PHP
<?php

namespace App\Models;

use Calvino\Core\Model;

/**
 * Modèle {$modelName}
 */
class {$modelName} extends Model
{
    /**
     * Table associée au modèle
     *
     * @var string
     */
    protected string \$table = '{$tableName}';
    
    /**
     * Clé primaire
     *
     * @var string
     */
    protected string \$primaryKey = 'id';
    
    /**
     * Champs remplissables
     *
     * @var array
     */
    protected array \$fillable = [];
}
PHP;
    }
    
    /**
     * Convertit un mot au pluriel (simple)
     *
     * @param string $singular
     * @return string
     */
    protected function pluralize(string $singular): string
    {
        $lastChar = substr($singular, -1);
        
        if ($lastChar === 'y') {
            return substr($singular, 0, -1) . 'ies';
        } elseif ($lastChar === 's' || $lastChar === 'x' || $lastChar === 'z' || 
                  substr($singular, -2) === 'ch' || substr($singular, -2) === 'sh') {
            return $singular . 'es';
        } else {
            return $singular . 's';
        }
    }
} 