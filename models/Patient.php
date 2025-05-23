<?php
require_once 'User.php';

class Patient extends User {
    protected $id_medecin;
    protected $sexe;
    public $verification_token;
    public $verification_token_expires;
    public $reset_token;
    public $reset_token_expires;
    public $remember_token;
    public $remember_token_expires;
    public $verification_status = 'pending';
    
    public function __construct($db) {
        parent::__construct($db);
        $this->table_name = "patient";
        $this->role = "patient";
    }
    
    // Getters et Setters
    public function getIdMedecin() {
        return $this->id_medecin;
    }

    public function setIdMedecin($id_medecin) {
        $this->id_medecin = $id_medecin;
    }

    public function getSexe() {
        return $this->sexe;
    }

    public function setSexe($sexe) {
        $this->sexe = $sexe;
    }

    // Méthode pour générer un token de vérification
    public function generateVerificationToken() {
        // Fonction de log locale
        $writeLog = function($message) {
            $log_file = __DIR__ . '/../logs/debug.log';
            $timestamp = date('Y-m-d H:i:s');
            file_put_contents($log_file, "[$timestamp] $message\n", FILE_APPEND);
        };
        
        // Générer un token unique
        $token = bin2hex(random_bytes(32));
        $writeLog("Nouveau token généré : " . $token);
        
        // Stocker le token et sa date d'expiration
        $this->verification_token = $token;
        $this->verification_token_expires = date('Y-m-d H:i:s', strtotime('+24 hours'));
        
        return $token;
    }
    
    // Implémentation de la méthode register pour les patients
    public function register() {
        // Fonction de log locale
        $writeLog = function($message) {
            $log_file = __DIR__ . '/../logs/debug.log';
            $timestamp = date('Y-m-d H:i:s');
            file_put_contents($log_file, "[$timestamp] $message\n", FILE_APPEND);
        };
        
        // Générer le token de vérification avant l'enregistrement
        $token = $this->generateVerificationToken();
        $writeLog("Token généré pour l'enregistrement : " . $token);
        $writeLog("Expiration du token : " . $this->verification_token_expires);

        $query = "INSERT INTO " . $this->table_name . " 
                  (nom, prenom, datenais, sexe, email, contact, password, role, id_medecin,
                   verification_status, verification_token, verification_token_expires) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $writeLog("Requête SQL : " . $query);
        
        $stmt = $this->db->prepare($query);
        
        // Nettoyage et sécurisation des entrées
        $this->nom = htmlspecialchars(strip_tags($this->nom));
        $this->prenom = htmlspecialchars(strip_tags($this->prenom));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->contact = htmlspecialchars(strip_tags($this->contact));
        
        // Hashage du mot de passe
        $hashed_password = $this->hashPassword($this->password);
        
        // Binding des valeurs
        $stmt->bindParam(1, $this->nom);
        $stmt->bindParam(2, $this->prenom);
        $stmt->bindParam(3, $this->datenais);
        $stmt->bindParam(4, $this->sexe);
        $stmt->bindParam(5, $this->email);
        $stmt->bindParam(6, $this->contact);
        $stmt->bindParam(7, $hashed_password);
        $stmt->bindParam(8, $this->role);
        $stmt->bindParam(9, $this->id_medecin);
        $stmt->bindParam(10, $this->verification_status);
        $stmt->bindParam(11, $this->verification_token);
        $stmt->bindParam(12, $this->verification_token_expires);
        
        if($stmt->execute()) {
            $writeLog("Compte enregistré avec succès");
            return $token; // Retourner le token pour l'email
        } else {
            $writeLog("Erreur lors de l'enregistrement : " . implode(", ", $stmt->errorInfo()));
        }
        
        return false;
    }
    
    // Méthode pour mettre à jour un patient
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET nom = ?, prenom = ?, datenais = ?, email = ?, contact = ? 
                  WHERE id = ?";
        
        $stmt = $this->db->prepare($query);
        
        // Nettoyage et sécurisation des entrées
        $this->nom = htmlspecialchars(strip_tags($this->nom));
        $this->prenom = htmlspecialchars(strip_tags($this->prenom));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->contact = htmlspecialchars(strip_tags($this->contact));
        
        // Binding des valeurs
        $stmt->bindParam(1, $this->nom);
        $stmt->bindParam(2, $this->prenom);
        $stmt->bindParam(3, $this->datenais);
        $stmt->bindParam(4, $this->email);
        $stmt->bindParam(5, $this->contact);
        $stmt->bindParam(6, $this->id);
        
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    // Méthode pour mettre à jour le mot de passe
    public function updatePassword() {
        $query = "UPDATE " . $this->table_name . " SET password = ? WHERE id = ?";
        
        $stmt = $this->db->prepare($query);
        
        // Hashage du nouveau mot de passe
        $hashed_password = $this->hashPassword($this->password);
        
        // Binding des valeurs
        $stmt->bindParam(1, $hashed_password);
        $stmt->bindParam(2, $this->id);
        
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    // Méthode pour confirmer un compte utilisateur
    public function confirmAccount($token) {
        // Fonction de log locale
        $writeLog = function($message) {
            $log_file = __DIR__ . '/../logs/debug.log';
            $timestamp = date('Y-m-d H:i:s');
            file_put_contents($log_file, "[$timestamp] $message\n", FILE_APPEND);
        };
        
        $writeLog("Début de la confirmation du compte patient avec le token : " . $token);
        
        // Vérifier si le token est valide et n'a pas expiré
        $query = "SELECT id, verification_status, verification_token, verification_token_expires FROM " . $this->table_name . " 
                 WHERE verification_token = ? 
                 AND verification_token_expires > NOW() 
                 LIMIT 0,1";
        
        $writeLog("Requête SQL : " . $query);
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(1, $token);
        $stmt->execute();
        
        // Log du nombre de résultats
        $writeLog("Nombre de résultats trouvés : " . $stmt->rowCount());
        
        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $writeLog("Compte trouvé avec l'ID : " . $row['id']);
            $writeLog("Statut actuel : " . $row['verification_status']);
            $writeLog("Token stocké : " . $row['verification_token']);
            $writeLog("Expiration du token : " . $row['verification_token_expires']);
            
            $this->id = $row['id'];
            
            // Vérifier si le compte n'est pas déjà vérifié
            if ($row['verification_status'] === 'verified') {
                $writeLog("Le compte est déjà vérifié");
                return true;
            }
            
            $query = "UPDATE " . $this->table_name . " 
                     SET verification_status = 'verified', 
                         verification_token = NULL, 
                         verification_token_expires = NULL 
                     WHERE id = ?";
            
            $writeLog("Requête de mise à jour : " . $query);
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(1, $this->id);
            
            if($stmt->execute()) {
                $writeLog("Compte patient mis à jour avec succès");
                return true;
            } else {
                $writeLog("Erreur lors de la mise à jour du compte : " . implode(", ", $stmt->errorInfo()));
            }
        } else {
            // Vérifier si le token existe mais a expiré
            $query = "SELECT id, verification_token_expires FROM " . $this->table_name . " WHERE verification_token = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(1, $token);
            $stmt->execute();
            
            if($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $writeLog("Token trouvé mais expiré. Date d'expiration : " . $row['verification_token_expires']);
            } else {
                $writeLog("Aucun compte patient trouvé avec ce token");
            }
        }
        
        return false;
    }
} 