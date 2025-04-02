<?php
/**
 * Utilitaire de journalisation dédié aux uploads
 * Permet de diagnostiquer les problèmes d'upload et d'insertion
 */

/**
 * Enregistre un message dans le fichier de log
 *
 * @param string $message Message à enregistrer
 * @param string $level Niveau de log (INFO, WARNING, ERROR, SUCCESS, DEBUG)
 * @return void
 */
function log_upload($message, $level = 'INFO') {
    // Définition du répertoire et du fichier de logs
    $logDir = __DIR__ . '/logs';
    $logFile = $logDir . '/upload.log';

    // Création du répertoire de logs si nécessaire
    if (!file_exists($logDir)) {
        mkdir($logDir, 0755, true);
    }

    // Formation du message avec date et niveau
    $formattedMessage = date('Y-m-d H:i:s') . " [$level] " . $message . PHP_EOL;

    // Écriture dans le fichier de log
    file_put_contents($logFile, $formattedMessage, FILE_APPEND);

    // En développement, on écrit aussi dans le log d'erreurs PHP
    if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
        error_log("UPLOAD_LOG [$level]: " . $message);
    }
}

/**
 * Nettoie le fichier de log
 * Utile avant de débuter une nouvelle série de tests
 *
 * @return void
 */
function clear_upload_log() {
    $logFile = __DIR__ . '/logs/upload.log';
    if (file_exists($logFile)) {
        file_put_contents($logFile, "");
    }
}