<?php

namespace Calvino\Console\Commands;

use Calvino\Console\AnimatedCommand;

/**
 * Commande pour exécuter une requête SQL
 */
class DbQueryCommand extends AnimatedCommand
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'db:query';
    }
    
    /**
     * {@inheritdoc}
     */
    public function getDescription(): string
    {
        return 'Exécute une requête SQL';
    }
    
    /**
     * {@inheritdoc}
     */
    public function handle(): void
    {
        try {
            // Afficher un titre
            $this->title("EXÉCUTION DE REQUÊTE SQL");
            
            // Vérifier les arguments
            if (empty($this->args[0])) {
                $this->error("Requête SQL requise.");
                echo "Usage: php calvino db:query [requête_sql]\n";
                exit(1);
            }
            
            $sql = implode(' ', $this->args);
            
            // Animation d'initialisation
            
            // Initialiser les services de base de données
            $dbManager = $this->initDatabaseServices();
            
            // Afficher la requête
            echo "\n\033[36mRequête à exécuter:\033[0m\n";
            echo "\033[33m" . $sql . "\033[0m\n\n";
            
            // Confirmation pour les requêtes non-SELECT
            if (stripos(trim($sql), 'SELECT') !== 0) {
                if (!$this->confirm("ATTENTION: Vous êtes sur le point d'exécuter une requête qui pourrait modifier la base de données. Continuer?")) {
                    $this->error("Opération annulée par l'utilisateur.");
                    return;
                }
            }
            
            // Animation d'exécution
            
            // Exécuter la requête
            $result = $dbManager->executeQuery($sql);
            
            if ($result === false) {
                $this->error("Échec de l'exécution de la requête.");
                exit(1);
            }
            
            // Afficher les résultats pour les requêtes SELECT
            if (is_array($result)) {
                if (empty($result)) {
                    echo "\n\033[33mAucun résultat trouvé.\033[0m\n";
                } else {
                    // Utiliser la méthode table pour afficher les résultats
                    $headers = array_keys($result[0]);
                    $data = [];
                    
                    foreach ($result as $row) {
                        $rowData = [];
                        foreach ($headers as $header) {
                            $rowData[] = $row[$header] ?? 'NULL';
                        }
                        $data[] = $rowData;
                    }
                    
                    echo "\n";
                    $this->table($headers, $data);
                    
                    $this->success("Nombre de résultats: " . count($result));
                }
            } else {
                $this->success("Requête exécutée avec succès");
            }
            
        } catch (\Exception $e) {
            $this->error("Erreur: " . $e->getMessage());
            exit(1);
        }
    }
} 