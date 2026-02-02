<?php

namespace Calvino\Providers;

use Calvino\Core\Application;
use PDO;

/**
 * Fournisseur de services pour la base de données
 */
class DatabaseServiceProvider extends ServiceProvider
{
    /**
     * Application
     *
     * @var Application
     */
    protected Application $app;
    
    /**
     * Connexion PDO
     *
     * @var PDO|null
     */
    private ?PDO $connection = null;

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
        $this->app->bind('db', $this);
    }
    
    /**
     * Démarre le service après l'enregistrement
     *
     * @return void
     */
    public function boot(): void
    {
        // Pas besoin de créer la connexion immédiatement
    }
    
    /**
     * Obtient la connexion PDO
     *
     * @return PDO
     */
    public function getConnection(): PDO
    {
        if ($this->connection === null) {
            $this->connection = $this->connect();
        }
        
        return $this->connection;
    }
    
    /**
     * Crée une connexion PDO
     *
     * @return PDO
     */
    private function connect(): PDO
    {
        $driver = env('DB_CONNECTION', 'mysql');
        $host = env('DB_HOST', 'localhost');
        $port = env('DB_PORT', '3306');
        $database = env('DB_DATABASE', 'pharmacie');
        $username = env('DB_USERNAME', 'root');
        $password = env('DB_PASSWORD', '');
        $charset = env('DB_CHARSET', 'utf8mb4');
        
        // Verifier si l'hôte est un socket Unix 
        if (strpos($host, '/') !== false) {
            $baseDsn = "{$driver}:unix_socket={$host};charset={$charset}";
            $fullDsn = "{$driver}:unix_socket={$host};dbname={$database};charset={$charset}";
        } else {
            $baseDsn = "{$driver}:host={$host};port={$port};charset={$charset}";
            $fullDsn = "{$driver}:host={$host};port={$port};dbname={$database};charset={$charset}";
        }
        
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        try {
            return new PDO($fullDsn, $username, $password, $options);
        } catch (\PDOException $e) {
            // Vérifier si l'erreur est liée à l'absence de la base de données
            if (strpos($e->getMessage(), "Unknown database") !== false || 
                strpos($e->getMessage(), "Base") !== false && strpos($e->getMessage(), "inconnue") !== false) {
                
                // Message d'erreur avec demande de confirmation
                $message = "La base de données '{$database}' n'existe pas.";
                
                if (php_sapi_name() === 'cli') {
                    // En ligne de commande, on demande une confirmation
                    echo $message . " Voulez-vous la créer ? (o/n): ";
                    $handle = fopen("php://stdin", "r");
                    $line = trim(fgets($handle));
                    fclose($handle);
                    
                    if (strtolower($line) !== 'o' && strtolower($line) !== 'oui' && strtolower($line) !== 'y' && strtolower($line) !== 'yes') {
                        throw new \Exception("Opération annulée. La base de données n'a pas été créée.");
                    }
                } else {
                    // Dans une application web, on affiche juste le message d'erreur avec des instructions
                    throw new \Exception($message . " Veuillez créer la base de données manuellement ou utilisez la commande 'php console db:create'");
                }
                
                try {
                    $pdo = new PDO($baseDsn, $username, $password, $options);
                    
                    // Création de la base de données
                    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET {$charset} COLLATE {$charset}_unicode_ci");
                    echo "Base de données '{$database}' créée avec succès." . PHP_EOL;
                    
                    // Exécuter les migrations si nécessaire
                    if (php_sapi_name() === 'cli') {
                        echo "Voulez-vous exécuter les migrations maintenant ? (o/n): ";
                        $handle = fopen("php://stdin", "r");
                        $line = trim(fgets($handle));
                        fclose($handle);
                        
                        if (strtolower($line) === 'o' || strtolower($line) === 'oui' || strtolower($line) === 'y' || strtolower($line) === 'yes') {
                            // On pourrait exécuter les migrations automatiquement
                            echo "Pour exécuter les migrations, utilisez la commande : php calvino migrate" . PHP_EOL;
                            $this->app->make('migrate')->run();
                        }
                    }
                    
                    // Se connecter à la nouvelle base de données
                    return new PDO($fullDsn, $username, $password, $options);
                    
                } catch (\PDOException $innerException) {
                    throw new \Exception("Impossible de créer la base de données : " . $innerException->getMessage());
                }
            }
            
            throw new \Exception("Erreur de connexion à la base de données : " . $e->getMessage());
        }
    }
} 