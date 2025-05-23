<?php
require_once 'User.php';

class ProfilMedecin {
    private $db;
    private $table = 'profilmedecin';

    public function __construct($db) {
        $this->db = $db;
    }

    public function getProfilByMedecinId($id_medecin) {
        $query = "SELECT * FROM {$this->table} WHERE id_medecin = :id_medecin";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id_medecin', $id_medecin);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createProfil($data) {
        $query = "INSERT INTO {$this->table} (id_medecin, diplome, specialite, annees_experience, 
                  hopital_actuel, adresse_cabinet, horaires_travail) 
                  VALUES (:id_medecin, :diplome, :specialite, :annees_experience, 
                  :hopital_actuel, :adresse_cabinet, :horaires_travail)";
        
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':id_medecin' => $data['id_medecin'],
            ':diplome' => $data['diplome'],
            ':specialite' => $data['specialite'],
            ':annees_experience' => $data['annees_experience'],
            ':hopital_actuel' => $data['hopital_actuel'],
            ':adresse_cabinet' => $data['adresse_cabinet'],
            ':horaires_travail' => $data['horaires_travail']
        ]);
    }

    public function updateProfil($id_medecin, $data) {
        $query = "UPDATE {$this->table} SET 
                  diplome = :diplome,
                  specialite = :specialite,
                  annees_experience = :annees_experience,
                  hopital_actuel = :hopital_actuel,
                  adresse_cabinet = :adresse_cabinet,
                  horaires_travail = :horaires_travail
                  WHERE id_medecin = :id_medecin";
        
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':id_medecin' => $id_medecin,
            ':diplome' => $data['diplome'],
            ':specialite' => $data['specialite'],
            ':annees_experience' => $data['annees_experience'],
            ':hopital_actuel' => $data['hopital_actuel'],
            ':adresse_cabinet' => $data['adresse_cabinet'],
            ':horaires_travail' => $data['horaires_travail']
        ]);
    }

    public function updateVerificationStatus($id_medecin, $status, $commentaire = null) {
        $query = "UPDATE {$this->table} SET 
                  verification_status = :status,
                  date_verification = NOW(),
                  commentaire_admin = :commentaire
                  WHERE id_medecin = :id_medecin";
        
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':id_medecin' => $id_medecin,
            ':status' => $status,
            ':commentaire' => $commentaire
        ]);
    }

    public function isProfileComplete($id_medecin) {
        $profil = $this->getProfilByMedecinId($id_medecin);
        if (!$profil) {
            return false;
        }

        $required_fields = ['diplome', 'specialite', 'annees_experience', 'hopital_actuel', 'adresse_cabinet', 'horaires_travail'];
        foreach ($required_fields as $field) {
            if (empty($profil[$field])) {
                return false;
            }
        }
        return true;
    }
} 