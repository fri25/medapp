<?php
// Configuration Google OAuth2
require_once __DIR__ . '/../includes/env_loader.php';

return [
    'client_id' => env('GOOGLE_CLIENT_ID', 'VOTRE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_CLIENT_SECRET', 'VOTRE_CLIENT_SECRET'),
    'redirect_uri' => env('GOOGLE_REDIRECT_URI', 'http://localhost/medapp/auth/google-callback.php'),
    'scopes' => [
        'https://www.googleapis.com/auth/calendar',
        'https://www.googleapis.com/auth/calendar.events'
    ]
]; 
