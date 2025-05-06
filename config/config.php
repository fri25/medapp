<?php
/**
 * Configuration générale de l'application
 * Inclut les paramètres de base de données et les fonctions de connexion sécurisée
 */

// Charger les variables d'environnement
require_once __DIR__ . '/../includes/env_loader.php';

// Activation de la gestion stricte des erreurs
error_reporting(E_ALL);
ini_set('display_errors', env('APP_ENV') === 'development' ? 1 : 0);

// Classe de configuration et gestion de la connexion à la base de données
class Config {
    // Configuration de base de l'application
    private static $config = [
        'database' => [
            'host' => null,
            'dbname' => null,
            'username' => null,
            'password' => null,
            'charset' => 'utf8mb4',
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ]
        ],
        'app' => [
            'log_errors' => true,
            'error_log_path' => '../logs/error.log',
            'session_timeout' => 1800, // 30 minutes
            'env' => null
        ],
        'auth' => [
            'google' => [
                'client_id' => null,
                'client_secret' => null,
                'redirect_uri' => null,
            ]
        ]
    ];

    /**
     * Initialise la configuration avec les variables d'environnement
     */
    public static function init() {
        // Charger les paramètres de base de données depuis .env
        self::$config['database']['host'] = env('DB_HOST');
        self::$config['database']['dbname'] = env('DB_NAME');
        self::$config['database']['username'] = env('DB_USER');
        self::$config['database']['password'] = env('DB_PASS');
        
        // Charger les paramètres de l'application
        self::$config['app']['env'] = env('APP_ENV');
        
        // Charger les paramètres d'authentification Google
        self::$config['auth']['google']['client_id'] = env('GOOGLE_CLIENT_ID');
        self::$config['auth']['google']['client_secret'] = env('GOOGLE_CLIENT_SECRET');
        self::$config['auth']['google']['redirect_uri'] = env('GOOGLE_REDIRECT_URI');
    }

    /**
     * Récupère une valeur de configuration
     * 
     * @param string $key Clé de configuration (utiliser la notation 'section.paramètre')
     * @param mixed $default Valeur par défaut si la clé n'existe pas
     * @return mixed La valeur de configuration ou la valeur par défaut
     */
    public static function get($key, $default = null) {
        $keys = explode('.', $key);
        $value = self::$config;

        foreach ($keys as $segment) {
            if (!isset($value[$segment])) {
                return $default;
            }
            $value = $value[$segment];
        }

        return $value;
    }

    /**
     * Établit une connexion sécurisée à la base de données
     * 
     * @return PDO Instance de connexion à la base de données
     * @throws PDOException Si la connexion échoue
     */
    public static function getDbConnection() {
        try {
            // Validation des paramètres de configuration
            $host = self::get('database.host');
            $dbname = self::get('database.dbname');
            $username = self::get('database.username');
            $password = self::get('database.password');
            $charset = self::get('database.charset', 'utf8mb4');
            $options = self::get('database.options', []);

            if (empty($host) || empty($dbname)) {
                throw new Exception("Configuration de base de données incomplète");
            }

            // Connexion à la base de données
            $dsn = "mysql:host={$host};dbname={$dbname};charset={$charset}";
            $pdo = new PDO($dsn, $username, $password, $options);

            // Test de la connexion
            $pdo->query("SELECT 1");

            return $pdo;
        } catch (PDOException $e) {
            self::logError('Erreur de connexion à la base de données : ' . $e->getMessage());
            
            // En production, ne pas révéler les détails de l'erreur
            if (self::isProduction()) {
                throw new Exception("Impossible de se connecter à la base de données. Veuillez contacter l'administrateur.");
            } else {
                throw $e; // En développement, propager l'erreur complète
            }
        } catch (Exception $e) {
            self::logError('Erreur de configuration : ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Détermine si l'application tourne en mode production
     * 
     * @return bool True si en production
     */
    public static function isProduction() {
        return self::get('app.env') === 'production';
    }

    /**
     * Enregistre une erreur dans le journal
     * 
     * @param string $message Message d'erreur
     * @return void
     */
    public static function logError($message) {
        if (self::get('app.log_errors', true)) {
            $logPath = self::get('app.error_log_path', '../logs/error.log');
            $logDir = dirname($logPath);
            
            // Créer le répertoire de logs s'il n'existe pas
            if (!file_exists($logDir)) {
                mkdir($logDir, 0755, true);
            }
            
            $timestamp = date('Y-m-d H:i:s');
            $logMessage = "[{$timestamp}] {$message}" . PHP_EOL;
            
            error_log($logMessage, 3, $logPath);
        }
    }
}

// Initialiser la configuration
Config::init();

// Fonction raccourci pour récupérer une connexion à la base de données
function db() {
    static $db = null;
    
    if ($db === null) {
        $db = Config::getDbConnection();
    }
    
    return $db;
}

// Fonction raccourci pour récupérer une valeur de configuration
function config($key, $default = null) {
    return Config::get($key, $default);
}


