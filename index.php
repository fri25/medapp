<?php
// Mode débogage forcé pour identifier l'erreur
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Point d'entrée principal de l'application
session_start();

// Afficher un message pour confirmer que le script commence son exécution
echo "<!-- Démarrage du script index.php -->";

try {
    // Inclusion des fichiers nécessaires avec vérification
    if (!file_exists('config/config.php')) {
        throw new Exception("Le fichier config.php est introuvable.");
    }
    require_once 'config/config.php';  
    
    if (!file_exists('includes/session.php')) {
        throw new Exception("Le fichier session.php est introuvable.");
    }
    require_once 'includes/session.php';
    
    echo "<!-- Fichiers chargés avec succès -->";
    
    // Vérification si l'utilisateur est déjà connecté
    if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
        // Redirection en fonction du rôle de l'utilisateur
        switch ($_SESSION['role']) {
            case 'admin':
                header('Location: views/admin/dashboard.php');
                break;
            case 'medecin':
                header('Location: views/medecin/dashboard.php');
                break;
            case 'patient':
                header('Location: views/patient/dashboard.php');
                break;
            default:
                // En cas de rôle non reconnu, déconnexion et redirection vers la page de connexion
                session_destroy();
                header('Location: views/login.php');
                break;
        }
        exit;
    } else {
        // Si l'utilisateur n'est pas connecté, redirection vers la page de connexion
        header('Location: views/login.php');
        exit;
    }
} catch (Exception $e) {
    // Afficher l'erreur précise pour le débogage
    echo '<div style="background-color: #ffdddd; border: 1px solid #ff0000; padding: 10px; margin: 10px;">';
    echo '<h2>Erreur détectée :</h2>';
    echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<h3>Trace :</h3>';
    echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    
    // Information sur le chemin des fichiers
    echo '<h3>Informations système :</h3>';
    echo '<p>Chemin actuel : ' . getcwd() . '</p>';
    echo '<p>Chemin du fichier : ' . __FILE__ . '</p>';
    echo '<p>PHP version : ' . phpversion() . '</p>';
    
    if (function_exists('env')) {
        echo '<p>APP_ENV : ' . env('APP_ENV', 'non défini') . '</p>';
    } else {
        echo '<p>Fonction env() non disponible</p>';
    }
    
    echo '</div>';
    exit;
}
?> 