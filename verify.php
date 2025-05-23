<?php
require_once 'config/database.php';
require_once 'models/Medecin.php';
require_once 'models/Patient.php';

// Activer l'affichage des erreurs pour le débogage
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Tester uniquement la connexion à la base de données
    $database = new Database();
    $db = $database->getConnection();
    
    // Si cette ligne s'exécute, la connexion a réussi
    echo "Connexion à la base de données réussie!";
    exit;
} catch (Exception $e) {
    // Afficher l'erreur détaillée
    echo "Erreur de connexion à la base de données : " . $e->getMessage();
    exit;
}

// Vérifier si un token est fourni
if (!isset($_GET['token']) || empty($_GET['token'])) {
    header('Location: ' . BASE_URL . '/views/login.php?error=' . urlencode('Token de vérification manquant'));
    exit;
}

// Nettoyer et valider le token
$token = htmlspecialchars(trim($_GET['token']));
if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
    header('Location: ' . BASE_URL . '/views/login.php?error=' . urlencode('Format de token invalide'));
    exit;
}

try {
    // Créer une instance de la base de données
    $database = new Database();
    $db = $database->getConnection();
    
    // Démarrer une transaction
    $db->beginTransaction();
    
    // Fonction générique pour envoyer un email de confirmation
    function sendConfirmationEmail($db, $userType, $email) {
        if ($userType === 'patient') {
            $user = new Patient($db);
            $user->email = $email;
            $user->sendVerificationConfirmationEmail();
        } else if ($userType === 'medecin') {
            $user = new Medecin($db);
            $user->email = $email;
            $user->sendVerificationConfirmationEmail(); // Assurez-vous que cette méthode existe
        }
    }
    
    // Fonction pour vérifier et mettre à jour un utilisateur
    function verifyUser($db, $userType, $token) {
        $tableName = $userType;
        
        // Vérifier si le token existe et n'est pas expiré
        $check_query = "SELECT id, email, verification_status FROM {$tableName} 
                       WHERE verification_token = ? 
                       AND verification_token_expires > NOW()";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->bindParam(1, $token);
        $check_stmt->execute();
        
        if ($check_stmt->rowCount() > 0) {
            $user_data = $check_stmt->fetch(PDO::FETCH_ASSOC);
            
            // Mettre à jour le statut de vérification
            $update_query = "UPDATE {$tableName} 
                            SET verification_status = 'verified',
                                verification_token = NULL,
                                verification_token_expires = NULL,
                                updated_at = NOW()
                            WHERE id = ?";
            $update_stmt = $db->prepare($update_query);
            $update_stmt->bindParam(1, $user_data['id']);
            
            if ($update_stmt->execute()) {
                // Si c'est un médecin, mettre à jour également la table profilmedecin
                if ($userType === 'medecin') {
                    $update_profil_query = "UPDATE profilmedecin 
                                          SET verification_status = 'verified',
                                              date_verification = NOW()
                                          WHERE id_medecin = ?";
                    $update_profil_stmt = $db->prepare($update_profil_query);
                    $update_profil_stmt->bindParam(1, $user_data['id']);
                    $update_profil_stmt->execute();
                }
                
                // Envoyer l'email de confirmation
                sendConfirmationEmail($db, $userType, $user_data['email']);
                
                return true;
            }
        }
        
        return false;
    }
    
    // Vérifier l'utilisateur (patient ou médecin)
    $userVerified = false;
    
    // Vérifie d'abord s'il s'agit d'un patient
    if (verifyUser($db, 'patient', $token)) {
        $userVerified = true;
    } 
    // Si ce n'est pas un patient, vérifie s'il s'agit d'un médecin
    else if (verifyUser($db, 'medecin', $token)) {
        $userVerified = true;
    }
    
    if ($userVerified) {
        // Valider la transaction
        $db->commit();
        
        // Rediriger vers la page de connexion avec un message de succès
        header('Location: ' . BASE_URL . '/views/login.php?success=' . urlencode('Votre compte a été vérifié avec succès. Vous pouvez maintenant vous connecter.'));
        exit;
    } else {
        // Si aucun compte n'a été mis à jour, le token est invalide ou expiré
        $db->rollBack();
        header('Location: ' . BASE_URL . '/views/login.php?error=' . urlencode('Token de vérification invalide ou expiré'));
        exit;
    }
    
} catch (Exception $e) {
    // En cas d'erreur, annuler la transaction
    if (isset($db)) {
        $db->rollBack();
    }
    
    // Logger l'erreur
    error_log("Erreur de vérification : " . $e->getMessage());
    
    // Rediriger vers la page de connexion avec un message d'erreur
    header('Location: ' . BASE_URL . '/views/login.php?error=' . urlencode('Une erreur est survenue lors de la vérification de votre compte'));
    exit;
}
?>