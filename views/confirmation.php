<?php
require_once '../config/database.php';
require_once '../includes/session.php';
require_once '../models/Medecin.php';
require_once '../models/Patient.php';

// Activer l'affichage des erreurs pour le débogage
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configurer le logging
$log_file = __DIR__ . '/../logs/debug.log';
if (!file_exists(dirname($log_file))) {
    mkdir(dirname($log_file), 0777, true);
}

function writeLog($message) {
    global $log_file;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[$timestamp] $message\n", FILE_APPEND);
}

// Définir le chemin de base de l'application
if (!defined('BASE_URL')) {
    define('BASE_URL', '');
}

// Vérifier si un token est fourni
if (!isset($_GET['token']) || empty($_GET['token'])) {
    writeLog("Erreur : Token manquant");
    header("Location: login.php?error=missing_token");
    exit;
}

// Nettoyer et valider le token
$token = htmlspecialchars(trim($_GET['token']));
writeLog("Token reçu : " . $token);

if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
    writeLog("Erreur : Format de token invalide");
    header("Location: login.php?error=invalid_token_format");
    exit;
}

try {
    // Créer une instance de la base de données
    $database = new Database();
    $db = $database->getConnection();
    writeLog("Connexion à la base de données établie");
    
    // Essayer d'abord de confirmer un compte médecin
    $medecin = new Medecin($db);
    writeLog("Tentative de confirmation du compte médecin");
    
    if ($medecin->confirmAccount($token)) {
        writeLog("Compte médecin confirmé avec succès");
        header("Location: login.php?verified=success&type=medecin");
        exit;
    }
    
    // Si ce n'est pas un médecin, essayer avec un patient
    $patient = new Patient($db);
    writeLog("Tentative de confirmation du compte patient");
    
    if ($patient->confirmAccount($token)) {
        writeLog("Compte patient confirmé avec succès");
        header("Location: login.php?verified=success&type=patient");
        exit;
    }
    
    // Si aucun compte n'a été trouvé avec ce token
    writeLog("Aucun utilisateur trouvé avec ce token");
    header("Location: login.php?error=invalid_token");
    exit;
    
} catch (Exception $e) {
    writeLog("Erreur de vérification : " . $e->getMessage());
    if (isset($db)) {
        $db->rollBack();
    }
    header("Location: login.php?error=verification_error");
    exit;
}
?>