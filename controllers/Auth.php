<?php
require_once '../config/database.php';
require_once '../models/Patient.php';
require_once '../models/Medecin.php';
require_once '../models/Admin.php';
require_once '../includes/session.php';

class Auth {
    private $database;
    private $db;
    
    public function __construct() {
        $this->database = new Database();
        $this->db = $this->database->getConnection();
    }
    
    // Méthode pour gérer l'authentification
    public function login($email, $password) {
        // Fonction de log locale
        $writeLog = function($message) {
            $log_file = __DIR__ . '/../logs/debug.log';
            $timestamp = date('Y-m-d H:i:s');
            file_put_contents($log_file, "[$timestamp] $message\n", FILE_APPEND);
        };
        
        $writeLog("Tentative de connexion pour l'email : " . $email);
        
        // Vérifier d'abord le type de compte avec toutes les informations nécessaires
        $query = "SELECT 'patient' as type, id, nom, prenom, password, verification_status 
                 FROM patient WHERE email = ? 
                 UNION ALL 
                 SELECT 'medecin' as type, id, nom, prenom, password, verification_status 
                 FROM medecin WHERE email = ? 
                 UNION ALL 
                 SELECT 'admin' as type, id, nom, prenom, password, 'verified' as verification_status 
                 FROM admin WHERE email = ?";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(1, $email);
        $stmt->bindParam(2, $email);
        $stmt->bindParam(3, $email);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $writeLog("Type de compte trouvé : " . $row['type']);
            $writeLog("Statut de vérification : " . $row['verification_status']);
            
            // Vérifier si le compte est vérifié
            if($row['verification_status'] !== 'verified') {
                $writeLog("Compte non vérifié - Type: " . $row['type']);
                return false;
            }
            
            // Vérifier le mot de passe
            if(password_verify($password, $row['password'])) {
                $writeLog("Connexion " . $row['type'] . " réussie");
                $this->createSession($row['id'], $row['nom'], $row['prenom'], $email, $row['type']);
                return true;
            } else {
                $writeLog("Mot de passe incorrect pour le compte " . $row['type']);
            }
        } else {
            $writeLog("Aucun compte trouvé avec cet email");
        }
        
        $writeLog("Échec de la connexion");
        return false;
    }
    
    // Méthode pour créer une session
    private function createSession($user_id, $nom, $prenom, $email, $role) {
        // Ne plus démarrer la session ici car elle est déjà démarrée dans le fichier session.php
        
        // Utiliser la fonction initSession si disponible
        if (function_exists('initSession')) {
            initSession($user_id, $role, $nom, $prenom, $email, 'standard');
        } else {
            // Fallback - Stocker les informations de l'utilisateur dans la session
            $_SESSION['user_id'] = $user_id;
            $_SESSION['nom'] = $nom;
            $_SESSION['prenom'] = $prenom;
            $_SESSION['email'] = $email;
            $_SESSION['role'] = $role;
            $_SESSION['auth_method'] = 'standard';
            $_SESSION['last_activity'] = time();
        }
    }
    
    // Méthode pour vérifier si l'utilisateur est connecté
    public function isLoggedIn() {
        // Ne plus démarrer la session ici car elle est déjà démarrée dans le fichier session.php
        
        // Vérifier si l'ID utilisateur existe dans la session et si la session n'a pas expiré
        if(isset($_SESSION['user_id']) && isset($_SESSION['last_activity'])) {
            // Vérifier si la dernière activité date de moins de 30 minutes
            if(time() - $_SESSION['last_activity'] < 1800) {
                // Mettre à jour le temps de dernière activité
                $_SESSION['last_activity'] = time();
                return true;
            } else {
                // La session a expiré, déconnecter l'utilisateur
                $this->logout();
            }
        }
        
        return false;
    }
    
    // Méthode pour vérifier le rôle de l'utilisateur
    public function checkRole($required_role) {
        // Ne plus démarrer la session ici car elle est déjà démarrée dans le fichier session.php
        
        if(isset($_SESSION['role']) && $_SESSION['role'] == $required_role) {
            return true;
        }
        
        return false;
    }
    
    // Méthode pour déconnecter l'utilisateur
    public function logout() {
        // Ne plus démarrer la session ici car elle est déjà démarrée dans le fichier session.php
        
        // Utiliser la fonction de déconnexion du fichier session.php si disponible
        if (function_exists('logout')) {
            logout();
        } else {
            // Fallback - Détruire toutes les variables de session
            $_SESSION = array();
            
            // Détruire la session
            session_destroy();
        }
    }
    
    // Méthode pour initier la réinitialisation du mot de passe
    public function forgotPassword($email) {
        $patient = new Patient($this->db);
        $patient->email = $email;
        
        $medecin = new Medecin($this->db);
        $medecin->email = $email;
        
        $admin = new Admin($this->db);
        $admin->email = $email;
        
        // Vérifier si l'email existe dans l'une des tables
        if($patient->emailExists()) {
            $token = $patient->generateResetToken();
            $this->sendResetEmail($email, $token);
            return true;
        } elseif($medecin->emailExists()) {
            $token = $medecin->generateResetToken();
            $this->sendResetEmail($email, $token);
            return true;
        } elseif($admin->emailExists()) {
            $token = $admin->generateResetToken();
            $this->sendResetEmail($email, $token);
            return true;
        }
        
        return false;
    }
    
    // Méthode pour envoyer un email de réinitialisation
    private function sendResetEmail($email, $token) {
        $to = $email;
        $subject = "Réinitialisation de mot de passe - MedConnect";
        
        $message = "
        <html>
        <head>
        <title>Réinitialisation de mot de passe</title>
        </head>
        <body>
        <h2>Bonjour,</h2>
        <p>Vous avez demandé la réinitialisation de votre mot de passe. Veuillez cliquer sur le lien ci-dessous pour définir un nouveau mot de passe:</p>
        <p><a href='http://localhost/medapp/views/reset_password.php?token=$token'>Réinitialiser mon mot de passe</a></p>
        <p>Ce lien expirera dans 1 heure.</p>
        <p>Si vous n'avez pas demandé la réinitialisation de votre mot de passe, veuillez ignorer cet email.</p>
        <p>Cordialement,<br>L'équipe MedConnect</p>
        </body>
        </html>
        ";
        
        // Pour envoyer un e-mail HTML, l'en-tête Content-type doit être défini
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From: noreply@medconnect.com' . "\r\n";
        
        // Envoyer l'e-mail
        mail($to, $subject, $message, $headers);
    }
    
    // Méthode pour réinitialiser le mot de passe
    public function resetPassword($token, $new_password) {
        // Vérifier si le token existe et est valide
        $query = "SELECT email, expire_date, used FROM password_reset WHERE token = ? LIMIT 0,1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(1, $token);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $email = $row['email'];
            $expire_date = $row['expire_date'];
            $used = $row['used'];
            
            // Vérifier si le token n'a pas expiré et n'a pas déjà été utilisé
            if(strtotime($expire_date) >= time() && $used == 0) {
                // Mettre à jour le mot de passe
                $patient = new Patient($this->db);
                $patient->email = $email;
                
                $medecin = new Medecin($this->db);
                $medecin->email = $email;
                
                $admin = new Admin($this->db);
                $admin->email = $email;
                
                if($patient->emailExists()) {
                    $patient->password = $new_password;
                    if($patient->updatePassword()) {
                        // Marquer le token comme utilisé
                        $this->markTokenAsUsed($token);
                        return true;
                    }
                } elseif($medecin->emailExists()) {
                    $medecin->password = $new_password;
                    if($medecin->updatePassword()) {
                        // Marquer le token comme utilisé
                        $this->markTokenAsUsed($token);
                        return true;
                    }
                } elseif($admin->emailExists()) {
                    $admin->password = $new_password;
                    if($admin->updatePassword()) {
                        // Marquer le token comme utilisé
                        $this->markTokenAsUsed($token);
                        return true;
                    }
                }
            }
        }
        
        return false;
    }
    
    // Méthode pour marquer un token comme utilisé
    private function markTokenAsUsed($token) {
        $query = "UPDATE password_reset SET used = 1 WHERE token = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(1, $token);
        $stmt->execute();
    }
} 