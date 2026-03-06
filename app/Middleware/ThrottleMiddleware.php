<?php

declare(strict_types=1);

namespace App\Middleware;

/**
 * ThrottleMiddleware
 * Protège les routes contre les abus (brute-force, spam).
 * Utilise un stockage fichier pour comptabiliser les tentatives par IP.
 */
class ThrottleMiddleware
{
    /**
     * Nombre maximum de requêtes autorisées dans la fenêtre de temps
     */
    private int $maxAttempts;

    /**
     * Durée de la fenêtre de temps en secondes
     */
    private int $decaySeconds;

    /**
     * Répertoire de stockage des compteurs
     */
    private string $storageDir;

    public function __construct(int $maxAttempts = 5, int $decaySeconds = 60)
    {
        $this->maxAttempts  = (int) (env('RATE_LIMIT_MAX', $maxAttempts));
        $this->decaySeconds = (int) (env('RATE_LIMIT_DECAY', $decaySeconds));
        $this->storageDir   = dirname(__DIR__, 2) . '/storage/throttle';

        if (!is_dir($this->storageDir)) {
            mkdir($this->storageDir, 0755, true);
        }
    }

    /**
     * Exécute le middleware de limitation de débit
     */
    public function handle($request, $next)
    {
        $ip  = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $key = $this->buildKey($ip);

        if ($this->tooManyAttempts($key)) {
            $retryAfter = $this->getRetryAfter($key);
            http_response_code(429);
            header('Retry-After: ' . $retryAfter);
            header('X-RateLimit-Limit: ' . $this->maxAttempts);
            header('X-RateLimit-Remaining: 0');
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Trop de tentatives. Veuillez réessayer dans ' . $retryAfter . ' secondes.',
                'retry_after' => $retryAfter,
                'status' => 429
            ]);
            exit;
        }

        $this->incrementAttempts($key);

        $remaining = $this->maxAttempts - $this->getAttempts($key);
        header('X-RateLimit-Limit: ' . $this->maxAttempts);
        header('X-RateLimit-Remaining: ' . max(0, $remaining));

        return $next($request);
    }

    /**
     * Vérifie si la limite de tentatives est atteinte
     */
    private function tooManyAttempts(string $key): bool
    {
        return $this->getAttempts($key) >= $this->maxAttempts;
    }

    /**
     * Incrémente le compteur de tentatives
     */
    private function incrementAttempts(string $key): void
    {
        $file = $this->storageDir . '/' . $key . '.json';
        $data = $this->readData($file);
        $now  = time();

        // Supprimer les entrées expirées
        $data['attempts'] = array_filter(
            $data['attempts'] ?? [],
            fn($timestamp) => ($now - $timestamp) < $this->decaySeconds
        );

        $data['attempts'][] = $now;
        file_put_contents($file, json_encode($data), LOCK_EX);
    }

    /**
     * Retourne le nombre de tentatives actives dans la fenêtre de temps
     */
    private function getAttempts(string $key): int
    {
        $file = $this->storageDir . '/' . $key . '.json';
        $data = $this->readData($file);
        $now  = time();

        $active = array_filter(
            $data['attempts'] ?? [],
            fn($timestamp) => ($now - $timestamp) < $this->decaySeconds
        );

        return count($active);
    }

    /**
     * Retourne le nombre de secondes avant de pouvoir réessayer
     */
    private function getRetryAfter(string $key): int
    {
        $file = $this->storageDir . '/' . $key . '.json';
        $data = $this->readData($file);
        $now  = time();

        $oldest = min($data['attempts'] ?? [$now]);
        return max(1, $this->decaySeconds - ($now - $oldest));
    }

    /**
     * Construit une clé unique pour l'IP
     */
    private function buildKey(string $ip): string
    {
        return 'throttle_' . md5($ip);
    }

    /**
     * Lit les données depuis le fichier de comptage
     */
    private function readData(string $file): array
    {
        if (!file_exists($file)) {
            return ['attempts' => []];
        }

        $content = file_get_contents($file);
        $data    = json_decode($content ?: '{}', true);

        return is_array($data) ? $data : ['attempts' => []];
    }
}
