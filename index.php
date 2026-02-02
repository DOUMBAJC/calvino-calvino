<?php
/**
 * Point d'entrée principal pour l'hébergement partagé
 * Redirige vers le dossier public
 */

// Définir le chemin vers le dossier public
$publicPath = __DIR__ . '/public';

// Vérifier si le fichier demandé existe dans le dossier public
$requestUri = $_SERVER['REQUEST_URI'];
$filePath = $publicPath . $requestUri;

// Si le fichier existe et est accessible directement, le servir
if (is_file($filePath)) {
    // Déterminer le type MIME
    $extension = pathinfo($filePath, PATHINFO_EXTENSION);
    switch ($extension) {
        case 'css':
            header('Content-Type: text/css');
            break;
        case 'js':
            header('Content-Type: application/javascript');
            break;
        case 'json':
            header('Content-Type: application/json');
            break;
        case 'png':
            header('Content-Type: image/png');
            break;
        case 'jpg':
        case 'jpeg':
            header('Content-Type: image/jpeg');
            break;
        case 'svg':
            header('Content-Type: image/svg+xml');
            break;
    }
    
    // Servir le fichier
    readfile($filePath);
    exit;
}

// Sinon, inclure le fichier index.php du dossier public
require_once $publicPath . '/index.php';