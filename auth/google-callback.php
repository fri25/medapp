<?php
/**
 * Page de callback pour l'authentification Google
 * Traite le code d'autorisation retourné par Google
 */

require_once '../vendor/autoload.php';
require_once '../config/config.php';
require_once '../includes/session.php';

// Vérifier si l'utilisateur est connecté
requireLogin();
requireRole('patient');

$user_id = $_SESSION['user_id'];

// Charger la configuration Google
$config = require '../config/google_config.php';

// Créer le client Google
$client = new Google_Client();
$client->setClientId($config['client_id']);
$client->setClientSecret($config['client_secret']);
$client->setRedirectUri($config['redirect_uri']);

try {
    // Vérifier l'état pour éviter les attaques CSRF
    if (!isset($_GET['state']) || $_GET['state'] !== $_SESSION['google_auth_state']) {
        throw new Exception('État invalide');
    }

    // Échanger le code d'autorisation contre un token
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

    // Stocker le token dans la base de données
    $stmt = db()->prepare("
        INSERT INTO google_tokens (user_id, access_token, refresh_token, expires_at)
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
        access_token = VALUES(access_token),
        refresh_token = VALUES(refresh_token),
        expires_at = VALUES(expires_at)
    ");

    $expires_at = date('Y-m-d H:i:s', time() + $token['expires_in']);
    $stmt->execute([
        $user_id,
        $token['access_token'],
        $token['refresh_token'],
        $expires_at
    ]);

    // Rediriger vers la page de l'agenda avec un message de succès
    $_SESSION['success'] = "Votre agenda a été connecté avec succès à Google Calendar.";
    header('Location: ../views/patient/rdv.php');
    exit;

} catch (Exception $e) {
    // En cas d'erreur, rediriger avec un message d'erreur
    $_SESSION['error'] = "Erreur lors de la connexion à Google Calendar : " . $e->getMessage();
    header('Location: ../views/patient/rdv.php');
    exit;
} 