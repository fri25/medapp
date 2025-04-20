<?php
/**
 * Classe de gestion de l'authentification Google
 */

// Charger les dépendances
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Patient.php';

class GoogleAuth {
    private $client;
    
    /**
     * Constructeur
     * Initialise le client Google
     */
    public function __construct() {
        // Vérifier que l'autoloader de Composer est disponible
        if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
            throw new Exception("Les dépendances ne sont pas installées. Exécutez 'composer install'.");
        }
        
        // Initialiser le client Google
        $this->client = new Google_Client();
        $this->client->setClientId(config('auth.google.client_id'));
        $this->client->setClientSecret(config('auth.google.client_secret'));
        $this->client->setRedirectUri(config('auth.google.redirect_uri'));
        $this->client->addScope('email');
        $this->client->addScope('profile');
    }
    
    /**
     * Génère l'URL de connexion Google
     * 
     * @return string URL de connexion
     */
    public function getAuthUrl() {
        return $this->client->createAuthUrl();
    }
    
    /**
     * Traite le code d'autorisation retourné par Google
     * 
     * @param string $code Code d'autorisation
     * @return array Informations de l'utilisateur
     */
    public function handleCallback($code) {
        try {
            // Échanger le code contre un token d'accès
            $token = $this->client->fetchAccessTokenWithAuthCode($code);
            $this->client->setAccessToken($token);
            
            // Obtenir les informations de l'utilisateur
            $google_oauth = new Google_Service_Oauth2($this->client);
            $user_info = $google_oauth->userinfo->get();
            
            return [
                'email' => $user_info->getEmail(),
                'name' => $user_info->getName(),
                'given_name' => $user_info->getGivenName(),
                'family_name' => $user_info->getFamilyName(),
                'picture' => $user_info->getPicture(),
                'google_id' => $user_info->getId()
            ];
        } catch (Exception $e) {
            Config::logError("Erreur d'authentification Google: " . $e->getMessage());
            throw new Exception("Échec de l'authentification Google. Veuillez réessayer.");
        }
    }
    
    /**
     * Connexion ou inscription d'un utilisateur via Google
     * 
     * @param array $user_info Informations de l'utilisateur Google
     * @return int ID de l'utilisateur
     */
    public function loginOrRegisterUser($user_info) {
        $db = db();
        
        try {
            // Vérifier si l'utilisateur existe déjà (par son Google ID)
            $stmt = $db->prepare("SELECT id, role FROM users WHERE google_id = :google_id");
            $stmt->bindParam(':google_id', $user_info['google_id']);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                // L'utilisateur existe déjà, connexion
                $user = $stmt->fetch();
                $this->setupSession($user['id'], $user['role'], $user_info);
                return $user['id'];
            } else {
                // Vérifier si l'email existe déjà
                $stmt = $db->prepare("SELECT id FROM users WHERE email = :email");
                $stmt->bindParam(':email', $user_info['email']);
                $stmt->execute();
                
                if ($stmt->rowCount() > 0) {
                    // L'email existe déjà mais pas associé à Google
                    // Nous mettons à jour l'utilisateur avec son ID Google
                    $user = $stmt->fetch();
                    $stmt = $db->prepare("UPDATE users SET google_id = :google_id WHERE id = :id");
                    $stmt->bindParam(':google_id', $user_info['google_id']);
                    $stmt->bindParam(':id', $user['id']);
                    $stmt->execute();
                    
                    // Récupérer le rôle de l'utilisateur
                    $stmt = $db->prepare("SELECT role FROM users WHERE id = :id");
                    $stmt->bindParam(':id', $user['id']);
                    $stmt->execute();
                    $role = $stmt->fetch()['role'];
                    
                    $this->setupSession($user['id'], $role, $user_info);
                    return $user['id'];
                } else {
                    // Nouvel utilisateur, inscription en tant que patient par défaut
                    return $this->registerNewUser($user_info);
                }
            }
        } catch (Exception $e) {
            Config::logError("Erreur de connexion/inscription Google: " . $e->getMessage());
            throw new Exception("Erreur lors de la connexion. Veuillez réessayer.");
        }
    }
    
    /**
     * Enregistre un nouvel utilisateur depuis Google
     * 
     * @param array $user_info Informations de l'utilisateur Google
     * @return int ID de l'utilisateur
     */
    private function registerNewUser($user_info) {
        $db = db();
        $role = 'patient'; // Par défaut, les utilisateurs Google sont enregistrés comme patients
        
        try {
            $db->beginTransaction();
            
            // Insérer dans la table utilisateurs
            $stmt = $db->prepare("
                INSERT INTO users (nom, prenom, email, google_id, role, date_creation) 
                VALUES (:nom, :prenom, :email, :google_id, :role, NOW())
            ");
            
            $stmt->bindParam(':nom', $user_info['family_name']);
            $stmt->bindParam(':prenom', $user_info['given_name']);
            $stmt->bindParam(':email', $user_info['email']);
            $stmt->bindParam(':google_id', $user_info['google_id']);
            $stmt->bindParam(':role', $role);
            $stmt->execute();
            
            $user_id = $db->lastInsertId();
            
            // Insérer dans la table patients
            $stmt = $db->prepare("
                INSERT INTO patients (user_id) VALUES (:user_id)
            ");
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            
            $db->commit();
            
            // Configurer la session
            $this->setupSession($user_id, $role, $user_info);
            
            return $user_id;
        } catch (Exception $e) {
            $db->rollBack();
            Config::logError("Erreur d'inscription Google: " . $e->getMessage());
            throw new Exception("Erreur lors de l'inscription. Veuillez réessayer.");
        }
    }
    
    /**
     * Configure la session utilisateur
     * 
     * @param int $user_id ID de l'utilisateur
     * @param string $role Rôle de l'utilisateur
     * @param array $user_info Informations supplémentaires
     */
    private function setupSession($user_id, $role, $user_info) {
        // Utiliser la fonction initSession du fichier session.php
        if (function_exists('initSession')) {
            initSession(
                $user_id,
                $role,
                $user_info['family_name'],
                $user_info['given_name'],
                $user_info['email'],
                'google'
            );
        } else {
            // Fallback si la fonction n'existe pas (pour compatibilité)
            $_SESSION['user_id'] = $user_id;
            $_SESSION['role'] = $role;
            $_SESSION['nom'] = $user_info['family_name'];
            $_SESSION['prenom'] = $user_info['given_name'];
            $_SESSION['email'] = $user_info['email'];
            $_SESSION['auth_method'] = 'google';
            $_SESSION['last_activity'] = time();
        }
    }
} 