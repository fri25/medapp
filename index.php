<?php
// Point d'entrée principal de l'application
session_start();

// Inclusion des fichiers nécessaires
require_once 'config/database.php';
require_once 'includes/session.php';

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
?> 