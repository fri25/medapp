<?php
function redirectIfNotLoggedIn() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        header('Location: ../../index.php');
        exit();
    }
}

function redirectIfNotAuthorized($requiredRole) {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== $requiredRole) {
        header('Location: ../../index.php');
        exit();
    }
}

function getDashboardUrl() {
    if (!isset($_SESSION['role'])) {
        return '/medapp/index.php';
    }
    
    switch ($_SESSION['role']) {
        case 'admin':
            return '/medapp/views/admin/dashboard.php';
        case 'medecin':
            return '/medapp/views/medecin/dashboard.php';
        case 'patient':
            return '/medapp/views/patient/dashboard.php';
        default:
            return '/medapp/index.php';
    }
}
?> 