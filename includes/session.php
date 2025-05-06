<?php
/**
 * Gestion des sessions et de l'authentification
 * Utilise la variable SESSION_SECRET du fichier .env pour renforcer la sécurité
 */

// Charger la configuration si ce n'est pas déjà fait
if (!function_exists('env')) {
    require_once __DIR__ . '/../config/config.php';
}

// Configurer les options de session avant de la démarrer
$session_name = 'MEDSESSID'; // Nom personnalisé pour masquer l'utilisation de PHP
$secure = false; // Définir à true en production avec HTTPS
$httponly = true; // Empêche l'accès au cookie via JavaScript
$samesite = 'Lax'; // Protection contre les attaques CSRF

// En production, forcer HTTPS
if (env('APP_ENV') === 'production') {
    $secure = true;
    ini_set('session.cookie_secure', 1);
}

// Définir les options de cookie
ini_set('session.use_strict_mode', 1);
ini_set('session.use_cookies', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_httponly', 1);

// Configurer la durée de vie du cookie (30 minutes = 1800 secondes)
ini_set('session.gc_maxlifetime', 1800);
ini_set('session.cookie_lifetime', 1800);

// Paramétrer le cookie SameSite
session_set_cookie_params([
    'lifetime' => 1800,
    'path' => '/',
    'domain' => '', // Auto
    'secure' => $secure,
    'httponly' => $httponly,
    'samesite' => $samesite
]);

// Définir un nom de session personnalisé uniquement si la session n'est pas déjà active
if (session_status() == PHP_SESSION_NONE) {
    session_name($session_name);
    session_start();
    
    // Régénérer l'ID de session périodiquement pour prévenir la fixation de session
    if (!isset($_SESSION['created'])) {
        $_SESSION['created'] = time();
    } else if (time() - $_SESSION['created'] > 600) { // Régénérer toutes les 10 minutes
        // Régénérer l'ID de session
        session_regenerate_id(true);
        $_SESSION['created'] = time();
    }
}

/**
 * Vérifie si l'utilisateur est connecté
 * 
 * @return bool True si l'utilisateur est connecté
 */
function isLoggedIn() {
    // Vérifier si l'utilisateur est connecté
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        return false;
    }

    // Vérifier si la session n'a pas expiré
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
        logout();
        return false;
    }

    // Mettre à jour le timestamp de dernière activité
    $_SESSION['last_activity'] = time();

    return true;
}

/**
 * Vérifie si l'utilisateur a le rôle requis
 * 
 * @param string $role Le rôle requis (admin, medecin, patient)
 * @return bool True si l'utilisateur a le rôle requis
 */
function hasRole($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

/**
 * Exige que l'utilisateur soit connecté
 * Redirige vers la page de connexion si ce n'est pas le cas
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /medapp/views/login.php');
        exit();
    }
}

/**
 * Exige que l'utilisateur ait un rôle spécifique
 * Redirige vers la page d'accueil si ce n'est pas le cas
 * 
 * @param string $role Le rôle requis (admin, medecin, patient)
 */
function requireRole($role) {
    requireLogin();
    if (!hasRole($role)) {
        header('Location: /medapp/index.php');
        exit();
    }
}

/**
 * Déconnecte l'utilisateur
 */
function logout() {
    // Détruire toutes les variables de session
    $_SESSION = array();

    // Détruire le cookie de session
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }

    // Détruire la session
    session_destroy();

    // Rediriger vers la page de connexion
    header('Location: /medapp/views/login.php');
    exit();
}

/**
 * Génère une empreinte du navigateur pour renforcer la sécurité des sessions
 */
function generateBrowserFingerprint() {
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $_SESSION['browser_fingerprint'] = hash_hmac('sha256', $user_agent, env('SESSION_SECRET'));
}

/**
 * Vérifie l'empreinte du navigateur
 * 
 * @return bool True si l'empreinte est valide
 */
function verifyBrowserFingerprint() {
    if (!isset($_SESSION['browser_fingerprint'])) {
        return false;
    }

    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $expected = hash_hmac('sha256', $user_agent, env('SESSION_SECRET'));

    return hash_equals($_SESSION['browser_fingerprint'], $expected);
} 