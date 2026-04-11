<?php
require_once 'config/database.php';
$database = new Database();
$db = $database->getConnection();

$emails_to_verify = [
    'melvineyemadje@gmail.com',
    'test2@gmail.com',
    'bake@gmail.com',
    'chao@gmail.com'
];

foreach ($emails_to_verify as $email) {
    try {
        // Vérifier dans la table patient
        $query = "UPDATE patient SET verification_status = 'verified', verification_token = NULL, verification_token_expires = NULL WHERE email = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            echo "Patient $email vérifié.\n";
        }

        // Vérifier dans la table medecin
        $query = "UPDATE medecin SET verification_status = 'verified', verification_token = NULL, verification_token_expires = NULL WHERE email = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            echo "Médecin $email vérifié.\n";
        }
    } catch (Exception $e) {
        echo "Erreur pour $email : " . $e->getMessage() . "\n";
    }
}
echo "Opération terminée.\n";
