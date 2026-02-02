<?php
/**
 * Fichier routeur pour le serveur de développement PHP
 * À utiliser avec php -S localhost:8000 public/router.php
 */

// Si le fichier existe et est un fichier, le serveur
if (file_exists($_SERVER['DOCUMENT_ROOT'] . $_SERVER['REQUEST_URI']) && 
    is_file($_SERVER['DOCUMENT_ROOT'] . $_SERVER['REQUEST_URI'])) {
    // Si c'est un fichier PHP, on le retourne
    return false;
}

// Sinon, on redirige vers index.php
require_once __DIR__ . '/index.php'; 