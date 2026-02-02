<?php

namespace Calvino\Console\Commands;

use Calvino\Console\AnimatedCommand;

/**
 * Commande pour créer un fichier de migration
 */
class MakeMigrationCommand extends AnimatedCommand
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'make:migration';
    }
    
    /**
     * {@inheritdoc}
     */
    public function getDescription(): string
    {
        return 'Crée un fichier de migration';
    }
    
    /**
     * {@inheritdoc}
     */
    public function handle(): void
    {
        try {
            // Afficher un titre
            $this->title("CRÉATION DE FICHIER DE MIGRATION");
            
            // Vérifier les arguments
            if (empty($this->args[0])) {
                $this->error("Nom du modèle requis.");
                echo "Usage: php calvino make:migration [nom_modele]\n";
                exit(1);
            }
            
            $modelName = $this->args[0];
            
            // Animation de préparation
            
            // Initialiser les services de base de données
            $dbManager = $this->initDatabaseServices();
            
            // Animation de génération
            
            // Créer le fichier de migration
            $result = $this->createMigration($modelName);
            
            if ($result) {
                // Message de succès
                $this->success("Fichier de migration créé avec succès");
                
                // Afficher le chemin du fichier
                echo "\nFichier créé: " . $result . "\n";
            } else {
                $this->error("Erreur lors de la création du fichier de migration.");
                exit(1);
            }
        } catch (\Exception $e) {
            $this->error("Erreur: " . $e->getMessage());
            exit(1);
        }
    }
    /**
     * Crée un fichier de migration
     * 
     * @param string $name Nom de la migration
     * @return string|bool Le chemin du fichier créé ou false
     */
    protected function createMigration(string $name)
    {
        $tableName = '';
        $className = '';
        
        // Nettoyer le nom (snake_case)
        $name = strtolower($name);
        
        // Détecter si c'est un pattern "create_xxxx_table"
        if (preg_match('/^create_(.+)_table$/', $name, $matches)) {
            $tableName = $matches[1];
            $className = 'Create' . $this->studly($tableName) . 'Table';
        } else {
            // Sinon on traite comme un nom de modèle
            $tableName = $this->pluralize($name);
            $className = 'Create' . $this->studly($name) . 'Table';
        }
        
        $timestamp = date('YmdHis');
        $fileName = $timestamp . '_create_' . $tableName . '_table.php';
        $path = BASE_PATH . '/database/migrations/' . $fileName;
        
        // S'assurer que le dossier existe
        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }
        
        $stubPath = BASE_PATH . '/stubs/migration.stub';
        $content = '';
        
        if (file_exists($stubPath)) {
            $content = file_get_contents($stubPath);
            $content = str_replace('{{class}}', $className, $content);
            $content = str_replace('{{table}}', $tableName, $content);
        } else {
            // Fallback content avec classe anonyme
            $content = <<<PHP
<?php

use Calvino\Core\Migration;
use Calvino\Core\Schema;

/**
 * Migration: {$className}
 */
return new class extends Migration
{
    /**
     * Exécute la migration
     *
     * @return void
     */
    public function up(): void
    {
        \$this->create('{$tableName}', function (\$table) {
            \$table->id();
            // TODO: Ajouter les colonnes
            \$table->timestamps();
        });
    }

    /**
     * Annule la migration
     *
     * @return void
     */
    public function down(): void
    {
        \$this->dropIfExists('{$tableName}');
    }
};
PHP;
        }
        
        if (file_put_contents($path, $content) !== false) {
            return $path;
        }
        
        return false;
    }

    /**
     * Convertit une chaîne en StudlyCase
     *
     * @param string $value
     * @return string
     */
    protected function studly(string $value): string
    {
        $value = ucwords(str_replace(['-', '_'], ' ', $value));
        return str_replace(' ', '', $value);
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