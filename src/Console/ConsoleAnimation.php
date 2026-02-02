<?php

namespace Calvino\Console;

/**
 * Bibliothèque de styles console moderne et rapide
 */
class ConsoleAnimation 
{
    // Définition des couleurs ANSI
    const GREEN = "\033[32m";
    const BLUE = "\033[34m";
    const YELLOW = "\033[33m";
    const RED = "\033[31m";
    const CYAN = "\033[36m";
    const MAGENTA = "\033[35m";
    const WHITE = "\033[37m";
    const BLACK = "\033[30m";
    const RESET = "\033[0m";
    const BOLD = "\033[1m";
    const DIM = "\033[2m";

    /**
     * Affiche un message de chargement (instantané pour plus de fluidité)
     */
    public function spinner($message, $duration = 0, $type = 'default') {
        echo self::CYAN . " ℹ " . self::RESET . self::BOLD . $message . self::RESET;
        
        // On réduit drastiquement la durée si elle est spécifiée
        if ($duration > 0) {
            usleep(200000); // Max 200ms pour montrer un petit signe d'activité
        }
        
        echo " " . self::GREEN . "Done" . self::RESET . "\n";
    }

    /**
     * Affiche une barre de progression minimaliste
     */
    public function progressBar($current, $total, $taskName, $style = 'default') {
        $percent = round(($current / $total) * 100);
        $bar_length = 20;
        $completed = floor(($current / $total) * $bar_length);
        
        $bar = str_repeat("■", $completed) . self::DIM . str_repeat("□", $bar_length - $completed) . self::RESET;
        
        printf("\r %s %s [%s] %d%%", self::BLUE . "→" . self::RESET, str_pad($taskName, 20), $bar, $percent);
        
        if ($current == $total) {
            echo " " . self::GREEN . "✓" . self::RESET . "\n";
        }
    }
    
    /**
     * Affiche un titre sobre et élégant
     */
    public function title($title, $color = null) {
        $textColor = $color ?? self::CYAN;
        echo "\n" . self::BOLD . $textColor . "── " . mb_strtoupper($title) . " ──" . self::RESET . "\n\n";
    }
    
    /**
     * Affiche un message de succès moderne
     */
    public function success($message, $symbol = '✓') {
        echo "\n " . self::BG_GREEN_TEXT() . " SUCCESS " . self::RESET . " " . self::BOLD . $message . self::RESET . " " . $symbol . "\n\n";
    }
    
    private static function BG_GREEN_TEXT() {
        return "\033[97;42m";
    }

    /**
     * Affiche un message d'erreur compact
     */
    public function error($message) {
        echo "\n " . self::BG_RED_TEXT() . " ERROR " . self::RESET . " " . self::BOLD . self::RED . $message . self::RESET . "\n\n";
    }

    private static function BG_RED_TEXT() {
        return "\033[97;41m";
    }
    
    /**
     * Affiche un tableau de données minimaliste
     */
    public function table($headers, $data) {
        $columnWidths = [];
        foreach ($headers as $i => $header) {
            $columnWidths[$i] = mb_strlen($header);
            foreach ($data as $row) {
                $columnWidths[$i] = max($columnWidths[$i], mb_strlen($row[$i] ?? ''));
            }
            $columnWidths[$i] += 2;
        }
        
        echo "\n";
        // Header
        foreach ($headers as $i => $header) {
            echo self::BOLD . str_pad(" " . $header, $columnWidths[$i] + 1) . self::RESET . (isset($headers[$i+1]) ? "│" : "");
        }
        echo "\n";
        
        // Separator
        foreach ($headers as $i => $header) {
            echo str_repeat("─", $columnWidths[$i] + 1) . (isset($headers[$i+1]) ? "┼" : "");
        }
        echo "\n";
        
        // Body
        foreach ($data as $row) {
            foreach ($row as $i => $cell) {
                echo str_pad(" " . $cell, $columnWidths[$i] + 1) . (isset($row[$i+1]) ? self::DIM . "│" . self::RESET : "");
            }
            echo "\n";
        }
        echo "\n";
    }
    
    /**
     * Version rapide de wait (instantané)
     */
    public function waitRandom($message, $duration = 0) {
        echo " " . self::YELLOW . "⚡" . self::RESET . " " . $message . "... " . self::GREEN . "Done" . self::RESET . "\n";
    }
    
    /**
     * Version rapide de countdown (instantané)
     */
    public function countdown($seconds, $message = "Démarrage") {
        echo " " . self::BOLD . $message . self::RESET . " " . self::GREEN . "Go!" . self::RESET . "\n";
    }
    
    /**
     * Efface l'écran
     */
    public function clearScreen() {
        echo "\033[2J\033[;H";
    }
}
 