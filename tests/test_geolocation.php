<?php

require_once __DIR__ . '/../app/Services/GeoLocationService.php';

use App\Services;

echo "Test du service de géolocalisation\n";
echo "=================================\n\n";

// Test avec différentes adresses IP
$testIPs = [
    '8.8.8.8',        // Google DNS
    '102.244.73.44',  // IP du Cameroun
    '91.198.174.192', // Wikipedia
];

foreach ($testIPs as $ip) {
    echo "Test de l'IP : " . $ip . "\n";
    echo "----------------------------------------\n";
    
    // Premier appel - devrait faire un appel API
    $location = Services\GeoLocationService::getFormattedLocation($ip);
    echo "Premier appel (API) : " . ($location ?? "Erreur") . "\n";
    
    // Deuxième appel immédiat - devrait utiliser le cache
    $location2 = Services\GeoLocationService::getFormattedLocation($ip);
    echo "Deuxième appel (cache) : " . ($location2 ?? "Erreur") . "\n";
    
    // Afficher les données complètes
    $data = Services\GeoLocationService::getLocationData($ip);
    if ($data) {
        echo "\nDonnées complètes :\n";
        echo "- Ville : " . ($data['city'] ?? 'N/A') . "\n";
        echo "- Région : " . ($data['region'] ?? 'N/A') . "\n";
        echo "- Pays : " . ($data['country_name'] ?? 'N/A') . "\n";
        echo "- Code pays : " . ($data['country_code'] ?? 'N/A') . "\n";
        echo "- Latitude : " . ($data['latitude'] ?? 'N/A') . "\n";
        echo "- Longitude : " . ($data['longitude'] ?? 'N/A') . "\n";
    }
    
    echo "\n----------------------------------------\n\n";
} 