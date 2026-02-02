<?php

namespace Calvino\Services;

/**
 * Service de géolocalisation
 * Fournit des méthodes pour obtenir des informations de localisation à partir d'une adresse IP
 */
class GeoLocationService
{
    /**
     * URL de base de l'API ip-api.com
     */
    private const API_BASE_URL = 'http://ip-api.com/json/';
    
    /**
     * Durée de mise en cache en secondes (24 heures)
     */
    private const CACHE_DURATION = 86400; // 24 heures en secondes
    
    /**
     * Nombre maximum de tentatives en cas d'erreur
     */
    private const MAX_RETRIES = 3;
    
    /**
     * Délai initial entre les tentatives (en secondes)
     */
    private const INITIAL_RETRY_DELAY = 2;
    
    /**
     * Dossier de cache
     */
    private const CACHE_DIR = __DIR__ . '/../../cache/geolocation/';
    
    /**
     * Récupère les données du cache
     * 
     * @param string $ip Adresse IP
     * @return array|null Données du cache ou null si non trouvé ou expiré
     */
    private static function getFromCache(string $ip): ?array
    {
        $cacheFile = self::CACHE_DIR . md5($ip) . '.json';
        
        if (!file_exists($cacheFile)) {
            return null;
        }

        $content = file_get_contents($cacheFile);
        $data = json_decode($content, true);

        if (!$data || !isset($data['timestamp']) || !isset($data['data'])) {
            return null;
        }

        // Vérifier si le cache est expiré
        if (time() - $data['timestamp'] > self::CACHE_DURATION) {
            unlink($cacheFile);
            return null;
        }

        return $data['data'];
    }
    
    /**
     * Sauvegarde les données dans le cache
     * 
     * @param string $ip Adresse IP
     * @param array $data Données à mettre en cache
     * @return void
     */
    private static function saveToCache(string $ip, array $data): void
    {
        // Créer le dossier de cache s'il n'existe pas
        if (!is_dir(self::CACHE_DIR)) {
            mkdir(self::CACHE_DIR, 0777, true);
        }

        $cacheFile = self::CACHE_DIR . md5($ip) . '.json';
        $cacheData = [
            'timestamp' => time(),
            'data' => $data
        ];

        file_put_contents($cacheFile, json_encode($cacheData));
    }
    
    /**
     * Effectue une requête API avec gestion des retries
     * 
     * @param string $url URL de l'API
     * @param int $attempt Numéro de la tentative actuelle
     * @return array|null Données de l'API ou null en cas d'erreur
     */
    private static function makeApiRequest(string $url, int $attempt = 1): ?array
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 429 && $attempt < self::MAX_RETRIES) {
            $delay = self::INITIAL_RETRY_DELAY * $attempt;
            error_log("Limite de requêtes atteinte. Attente de {$delay} secondes avant la prochaine tentative ({$attempt}/" . self::MAX_RETRIES . ")...");
            sleep($delay);
            return self::makeApiRequest($url, $attempt + 1);
        }

        if ($httpCode !== 200 || !$response) {
            $errorMessage = match ($httpCode) {
                429 => "Limite de requêtes atteinte. Veuillez réessayer plus tard.",
                403 => "Accès refusé à l'API de géolocalisation.",
                404 => "Adresse IP non trouvée.",
                default => "Erreur HTTP {$httpCode} lors de la requête API."
            };
            error_log($errorMessage);
            return null;
        }

        $data = json_decode($response, true);
        
        if (!$data || $data['status'] === 'fail') {
            error_log("Erreur dans la réponse de l'API : " . ($data['message'] ?? 'Raison inconnue'));
            return null;
        }

        return $data;
    }
    
    /**
     * Récupère les informations de localisation pour une adresse IP
     * 
     * @param string|null $ip Adresse IP (null pour utiliser l'adresse IP actuelle)
     * @return array|null Informations de localisation ou null en cas d'erreur
     */
    public static function getLocationData(?string $ip = null): ?array
    {
        try {
            $ip = $ip ?: $_SERVER['REMOTE_ADDR'] ?? null;
            
            if (!$ip) {
                error_log("Aucune adresse IP fournie ou détectée.");
                return null;
            }

            // Vérifier le cache
            $cachedData = self::getFromCache($ip);
            if ($cachedData !== null) {
                return $cachedData;
            }
            
            // Faire la requête API avec gestion des retries
            $url = self::API_BASE_URL . $ip;
            $data = self::makeApiRequest($url);
            
            if ($data) {
                // Adapter le format des données pour correspondre à l'ancien format
                $formattedData = [
                    'city' => $data['city'] ?? null,
                    'region' => $data['regionName'] ?? null,
                    'country_name' => $data['country'] ?? null,
                    'country_code' => $data['countryCode'] ?? null,
                    'latitude' => $data['lat'] ?? null,
                    'longitude' => $data['lon'] ?? null
                ];
                self::saveToCache($ip, $formattedData);
                return $formattedData;
            }
            
            return null;
        } catch (\Exception $e) {
            error_log("Exception lors de la récupération des données de localisation : " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Récupère une chaîne formatée de localisation pour une adresse IP
     * 
     * @param string|null $ip Adresse IP (null pour utiliser l'adresse IP actuelle)
     * @return string|null Chaîne formatée de localisation ou null en cas d'erreur
     */
    public static function getFormattedLocation(?string $ip = null): ?string
    {
        $data = self::getLocationData($ip);
        
        if (!$data) {
            return null;
        }
        
        $location = [];
        
        if (!empty($data['city'])) {
            $location[] = $data['city'];
        }
        
        if (!empty($data['region'])) {
            $location[] = $data['region'];
        }
        
        if (!empty($data['country_name'])) {
            $location[] = $data['country_name'];
        }
        
        return !empty($location) ? implode(', ', $location) : null;
    }
}
