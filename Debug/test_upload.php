<?php
// Fichier: test_upload.php
// Test simplifié d'upload et d'insertion en base de données

session_start();
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'upload_log.php';

// Créer le répertoire de logs
if (!file_exists(__DIR__ . '/logs')) {
    mkdir(__DIR__ . '/logs', 0755, true);
}

// Vider le fichier de log
file_put_contents(__DIR__ . '/logs/upload.log', '');

// Afficher les informations de débogage
error_reporting(E_ALL);
ini_set('display_errors', 1);

log_upload("=== DÉBUT DU TEST D'UPLOAD ===");

// Vérifier l'existence du répertoire d'upload
if (!file_exists(UPLOAD_DIR)) {
    log_upload("Création du répertoire d'upload: " . UPLOAD_DIR);
    mkdir(UPLOAD_DIR, 0755, true);
}

// Créer un fichier de test
$testContent = "Ceci est un fichier de test\n";
$testFilename = UPLOAD_DIR . 'test_' . time() . '.txt';

log_upload("Tentative d'écriture de fichier test: " . $testFilename);

// Test d'écriture
$writeResult = file_put_contents($testFilename, $testContent);
log_upload("Résultat d'écriture: " . ($writeResult ? "Succès" : "Échec"));

if ($writeResult) {
    log_upload("Vérification du contenu: " . file_get_contents($testFilename));
    unlink($testFilename);
    log_upload("Fichier test supprimé");
}

// Test d'insertion en base de données
try {
    log_upload("Test d'insertion en base de données");
    $database = new Database();
    $conn = $database->getConnection();

    if (!$conn) {
        log_upload("Échec de connexion à la base de données", "ERROR");
    } else {
        log_upload("Connexion à la base de données réussie");

        // Insertion de test
        $query = "INSERT INTO candidatures (offre_id, etudiant_id, cv, lettre_motivation, date_candidature) 
                 VALUES (1, :etudiant_id, 'test_cv.txt', 'Test de lettre de motivation', NOW())";

        $stmt = $conn->prepare($query);
        $etudiantId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1;
        $stmt->bindParam(':etudiant_id', $etudiantId, PDO::PARAM_INT);

        $result = $stmt->execute();

        if ($result) {
            $insertId = $conn->lastInsertId();
            log_upload("Insertion de test réussie, ID: " . $insertId, "SUCCESS");

            // Supprimer immédiatement l'entrée de test
            $conn->exec("DELETE FROM candidatures WHERE id = " . $insertId);
            log_upload("Entrée de test supprimée");
        } else {
            $errorInfo = $stmt->errorInfo();
            log_upload("Échec de l'insertion de test: " . print_r($errorInfo, true), "ERROR");
        }
    }
} catch (Exception $e) {
    log_upload("Exception lors du test d'insertion: " . $e->getMessage(), "ERROR");
}

log_upload("=== FIN DU TEST D'UPLOAD ===");

// Afficher le contenu du fichier de log
$logContent = file_exists(__DIR__ . '/logs/upload.log') ? file_get_contents(__DIR__ . '/logs/upload.log') : "Aucun fichier de log trouvé";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test d'Upload et de Base de Données</title>
    <style>
        body { font-family: monospace; margin: 20px; line-height: 1.4; }
        h1 { color: #333; }
        pre { background: #f5f5f5; padding: 15px; border-radius: 5px; overflow-x: auto; white-space: pre-wrap; }
        .success { color: green; }
        .error { color: red; font-weight: bold; }
        .info { color: blue; }
    </style>
</head>
<body>
<h1>Test d'Upload et de Base de Données</h1>

<h2>Journal de test</h2>
<pre id="log-content"><?php echo htmlspecialchars($logContent); ?></pre>

<h2>Test d'upload manuel</h2>
<form action="test_upload.php" method="post" enctype="multipart/form-data">
    <p>Sélectionnez un fichier à uploader :</p>
    <input type="file" name="test_file">
    <button type="submit">Uploader</button>
</form>

<?php
// Traitement de l'upload de test
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['test_file'])) {
    echo '<h3>Résultat de l\'upload</h3>';
    echo '<pre>';

    $uploadFile = $_FILES['test_file'];
    echo "Informations du fichier:\n";
    echo "- Nom: " . htmlspecialchars($uploadFile['name']) . "\n";
    echo "- Type: " . htmlspecialchars($uploadFile['type']) . "\n";
    echo "- Taille: " . htmlspecialchars($uploadFile['size']) . " octets\n";
    echo "- Code d'erreur: " . htmlspecialchars($uploadFile['error']) . "\n\n";

    $destination = UPLOAD_DIR . 'test_manual_' . time() . '_' . basename($uploadFile['name']);
    echo "Destination: " . htmlspecialchars($destination) . "\n";

    if (move_uploaded_file($uploadFile['tmp_name'], $destination)) {
        echo "<span class='success'>Fichier uploadé avec succès!</span>\n";
        echo "Le fichier est maintenant disponible à: " . htmlspecialchars($destination) . "\n";
    } else {
        echo "<span class='error'>Échec de l'upload!</span>\n";
        echo "Erreur système: " . error_get_last()['message'] . "\n";
    }

    echo '</pre>';
}
?>
</body>
</html>