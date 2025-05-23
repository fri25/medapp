<?php
// Définition des chemins de base
define('BASE_URL', '/medapp');
define('LOGIN_PATH', VIEWS_PATH . '/login.php');

// Fonction pour générer les URLs
function url($path) {
    return BASE_URL . '/' . ltrim($path, '/');
}

// Fonction pour rediriger
function redirect($path, $params = []) {
    $url = url($path);
    if (!empty($params)) {
        $url .= '?' . http_build_query($params);
    }
    header('Location: ' . $url);
    exit;
} 