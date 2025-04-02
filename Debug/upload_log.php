<?php
// Fichier: upload_log.php
// Utilitaire de journalisation dédié aux uploads

function log_upload($message, $level = 'INFO') {
    $logFile = __DIR__ . '/logs/upload.log';
    $logDir = dirname($logFile);

    // Créer le répertoire de logs si nécessaire
    if (!file_exists($logDir)) {
        mkdir($logDir, 0755, true);
    }

    // Formater le message
    $formattedMessage = date('Y-m-d H:i:s') . " [$level] " . $message . PHP_EOL;

    // Écrire dans le fichier de log
    file_put_contents($logFile, $formattedMessage, FILE_APPEND);
}