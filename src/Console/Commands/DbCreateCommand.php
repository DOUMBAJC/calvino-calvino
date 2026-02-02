<?php

namespace Calvino\Console\Commands;

use Calvino\Console\AnimatedCommand;

/**
 * Commande pour créer la base de données
 */
class DbCreateCommand extends AnimatedCommand
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'db:create';
    }
    
    /**
     * {@inheritdoc}
     */
    public function getDescription(): string
    {
        return 'Crée la base de données';
    }
    
    /**
     * {@inheritdoc}
     */
    public function handle(): void
    {
        try {
            // Afficher un titre
            $this->title("CRÉATION DE LA BASE DE DONNÉES");
            
            // Animation de chargement des paramètres
            
            // Récupérer les informations de connexion depuis .env
            $driver = env('DB_CONNECTION', 'mysql');
            $host = env('DB_HOST', 'localhost');
            $port = env('DB_PORT', '3306');
            $database = env('DB_DATABASE', 'pharmacie_manager');
            $username = env('DB_USERNAME', 'root');
            $password = env('DB_PASSWORD', '');
            $charset = env('DB_CHARSET', 'utf8mb4');
            
            // Afficher les paramètres dans un tableau
            $paramsData = [
                ['Driver', $driver],
                ['Host', $host],
                ['Port', $port],
                ['Database', $database],
                ['Username', $username],
                ['Charset', $charset]
            ];
            
            $this->table(['Paramètre', 'Valeur'], $paramsData);
            
            // Vérifier si l'hôte est un chemin de socket Unix
            if (strpos($host, '/') !== false) {
                $baseDsn = "{$driver}:unix_socket={$host};charset={$charset}";
            } else {
                $baseDsn = "{$driver}:host={$host};port={$port};charset={$charset}";
            }
            
            // Animation de connexion
            
            try {
                // Tenter de se connecter sans spécifier la base de données
                $options = [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                    \PDO::ATTR_EMULATE_PREPARES => false,
                    // Réduire le timeout de connexion
                    \PDO::ATTR_TIMEOUT => 3,
                ];
                
                $pdo = new \PDO($baseDsn, $username, $password, $options);
                $this->success("Connexion au serveur MySQL établie");
                
                // Animation de vérification
                
                // Vérifier si la base de données existe déjà
                $stmt = $pdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$database'");
                $dbExists = $stmt->fetch();
                
                if ($dbExists) {
                    $this->error("La base de données '$database' existe déjà.");
                } else {
                    if ($this->confirm("La base de données '$database' n'existe pas. Voulez-vous la créer ?")) {
                        // Animation de création
                        
                        // Création de la base de données
                        $pdo->exec("CREATE DATABASE `{$database}` CHARACTER SET {$charset} COLLATE {$charset}_unicode_ci");
                        $this->success("Base de données '$database' créée avec succès");
                        
                        // Demander si l'utilisateur veut exécuter les migrations
                        if ($this->confirm("Voulez-vous exécuter les migrations maintenant ?")) {
                            echo "Pour exécuter les migrations, utilisez la commande : php calvino migrate\n";
                        }
                    } else {
                        $this->error("Opération annulée. La base de données n'a pas été créée.");
                    }
                }
            } catch (\PDOException $e) {
                $this->error("Erreur de connexion au serveur MySQL: " . $e->getMessage());
                
                // Vérifier si l'erreur est liée au serveur qui n'est pas en cours d'exécution
                if (strpos($e->getMessage(), 'No such file or directory') !== false || 
                    strpos($e->getMessage(), "Can't connect") !== false ||
                    strpos($e->getMessage(), 'Connection refused') !== false) {
                    
                    echo "\nLe serveur MySQL ne semble pas être en cours d'exécution.\n\n";
                    echo "Veuillez démarrer le service MySQL en exécutant une des commandes suivantes :\n";
                    
                    $this->table(['Commande', 'Description'], [
                        ['sudo service mysql start', 'Démarrer MySQL sur Debian/Ubuntu'],
                        ['sudo service mariadb start', 'Démarrer MariaDB sur Debian/Ubuntu'],
                        ['sudo systemctl start mysql', 'Démarrer MySQL sur les systèmes avec systemd'],
                        ['sudo systemctl start mariadb', 'Démarrer MariaDB sur les systèmes avec systemd']
                    ]);
                    
                    echo "\nPuis réessayez la commande : php console db:create\n\n";
                    echo "Si le problème persiste, vérifiez que MySQL est bien installé et que le service est configuré.\n";
                }
                
                // Vérifier si l'erreur est liée aux identifiants
                if (strpos($e->getMessage(), 'Access denied') !== false) {
                    $this->error("Les identifiants de connexion semblent incorrects.");
                    echo "Veuillez vérifier le nom d'utilisateur et le mot de passe dans le fichier .env\n";
                }
            }
            
        } catch (\Exception $e) {
            $this->error("Erreur: " . $e->getMessage());
        }
    }
} 