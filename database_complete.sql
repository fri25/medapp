-- Création de la base de données
CREATE DATABASE IF NOT EXISTS medappdb;
USE medappdb;

-- Table des spécialités
CREATE TABLE IF NOT EXISTS specialite (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nomspecialite VARCHAR(100) NOT NULL UNIQUE
);

-- Table des médecins
CREATE TABLE IF NOT EXISTS medecin (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    datenais DATE NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    contact VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'medecin',
    num VARCHAR(20) NOT NULL,
    idspecialite INT,
    verification_status ENUM('pending', 'verified', 'rejected') DEFAULT 'pending',
    verification_token VARCHAR(64) DEFAULT NULL,
    verification_token_expires DATETIME DEFAULT NULL,
    reset_token VARCHAR(64) DEFAULT NULL,
    reset_token_expires DATETIME DEFAULT NULL,
    remember_token VARCHAR(64) DEFAULT NULL,
    remember_token_expires DATETIME DEFAULT NULL,
    FOREIGN KEY (idspecialite) REFERENCES specialite(id)
);

-- Table des patients
CREATE TABLE IF NOT EXISTS patient (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    datenais DATE NOT NULL,
    sexe ENUM('M', 'F', 'A') NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    contact VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'patient',
    id_medecin INT DEFAULT NULL,
    verification_status ENUM('pending', 'verified', 'rejected') DEFAULT 'pending',
    verification_token VARCHAR(64) DEFAULT NULL,
    verification_token_expires DATETIME DEFAULT NULL,
    reset_token VARCHAR(64) DEFAULT NULL,
    reset_token_expires DATETIME DEFAULT NULL,
    remember_token VARCHAR(64) DEFAULT NULL,
    remember_token_expires DATETIME DEFAULT NULL,
    FOREIGN KEY (id_medecin) REFERENCES medecin(id)
);

-- Table des carnets de santé
CREATE TABLE IF NOT EXISTS carnetsante (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_patient INT NOT NULL,
    groupesanguin VARCHAR(10),
    taille DECIMAL(5,2),
    poids DECIMAL(5,2),
    allergie TEXT,
    electrophorese TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_patient) REFERENCES patient(id)
);

-- Table des profils patients
CREATE TABLE IF NOT EXISTS profilpatient (
    id INT PRIMARY KEY AUTO_INCREMENT,
    adresse TEXT,
    profession VARCHAR(100),
    idpatient INT NOT NULL,
    idcarnetsante INT NOT NULL,
    FOREIGN KEY (idpatient) REFERENCES patient(id),
    FOREIGN KEY (idcarnetsante) REFERENCES carnetsante(id)
);

-- Table des fiches médicales
CREATE TABLE IF NOT EXISTS fichemed (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_patient INT NOT NULL,
    id_profil INT NOT NULL,
    id_carnet INT NOT NULL,
    lieu_naissance VARCHAR(100),
    situation_familiale VARCHAR(20),
    enfants INT,
    grossesses INT,
    num_secu VARCHAR(20),
    groupe_sanguin VARCHAR(10),
    medecin_traitant VARCHAR(100),
    Assurance VARCHAR(100),
    antecedents_familiaux TEXT,
    maladies_infantiles TEXT,
    antecedents_medicaux TEXT,
    antecedents_chirurgicaux TEXT,
    allergies TEXT,
    intolerance_medicament TEXT,
    traitement_regulier TEXT,
    vaccins TEXT,
    FOREIGN KEY (id_patient) REFERENCES patient(id),
    FOREIGN KEY (id_profil) REFERENCES profilpatient(id),
    FOREIGN KEY (id_carnet) REFERENCES carnetsante(id)
);

-- Table des administrateurs
CREATE TABLE IF NOT EXISTS admin (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    datenais DATE NOT NULL,
    email VARCHAR(100) NOT NULL,
    contact VARCHAR(20) NOT NULL,
    password VARCHAR(500) NOT NULL,
    role VARCHAR(100) NOT NULL
);

-- Table des rendez-vous
CREATE TABLE IF NOT EXISTS rendezvous (
    id INT PRIMARY KEY AUTO_INCREMENT,
    dateheure DATETIME NOT NULL,
    statut ENUM('en attente', 'accepté', 'refusé') DEFAULT 'en attente',
    idmedecin INT NOT NULL,
    idpatient INT NOT NULL,
    idspecialite INT NOT NULL,
    FOREIGN KEY (idmedecin) REFERENCES medecin(id),
    FOREIGN KEY (idpatient) REFERENCES patient(id),
    FOREIGN KEY (idspecialite) REFERENCES specialite(id)
);

-- Table des consultations
CREATE TABLE IF NOT EXISTS consultation (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_patient INT NOT NULL,
    id_medecin INT NOT NULL,
    date_consultation DATETIME NOT NULL,
    motif TEXT NOT NULL,
    antecedents TEXT,
    examen_clinique TEXT NOT NULL,
    diagnostic TEXT NOT NULL,
    traitement TEXT,
    recommandations TEXT,
    prochain_rdv DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_patient) REFERENCES patient(id),
    FOREIGN KEY (id_medecin) REFERENCES medecin(id)
);

-- Table des profils médecins
CREATE TABLE IF NOT EXISTS profilmedecin (
    id INT PRIMARY KEY AUTO_INCREMENT,
    adresse TEXT,
    profession VARCHAR(100),
    imgdiplome TEXT,
    disponibilite TEXT,
    idmedecin INT NOT NULL,
    FOREIGN KEY (idmedecin) REFERENCES medecin(id)
);

-- Table des messages
CREATE TABLE IF NOT EXISTS messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    contenu TEXT NOT NULL,
    date_envoi DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    sender_type ENUM('patient', 'medecin') NOT NULL,
    lu BOOLEAN DEFAULT FALSE,
    INDEX idx_sender (sender_id, sender_type),
    INDEX idx_receiver (receiver_id),
    INDEX idx_date (date_envoi)
);

-- Table des statuts de frappe
CREATE TABLE IF NOT EXISTS typing_status (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    receiver_id INT NOT NULL,
    sender_type ENUM('patient', 'medecin') NOT NULL,
    is_typing TINYINT(1) NOT NULL DEFAULT 0,
    last_updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_typing_status (user_id, receiver_id, sender_type),
    INDEX idx_last_updated (last_updated)
);

-- Table des ordonnances
CREATE TABLE IF NOT EXISTS ordonnance (
    id INT PRIMARY KEY AUTO_INCREMENT,
    idmedecin INT NOT NULL,
    idpatient INT NOT NULL,
    date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    date_validite DATE NOT NULL,
    medicaments TEXT NOT NULL,
    posologie TEXT NOT NULL,
    quantite TEXT NOT NULL,
    duree_medicament TEXT NOT NULL,
    duree_traitement VARCHAR(50) NOT NULL,
    instructions TEXT,
    signature VARCHAR(255),
    renouvellement TINYINT(1) DEFAULT 0,
    nombre_renouvellements INT DEFAULT 0,
    statut ENUM('active', 'expiree', 'annulee') NOT NULL DEFAULT 'active',
    signature_medecin VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (idmedecin) REFERENCES medecin(id),
    FOREIGN KEY (idpatient) REFERENCES patient(id)
);

-- Table des médicaments
CREATE TABLE IF NOT EXISTS medicament (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_ordonnance INT NOT NULL,
    nom_medicament VARCHAR(255) NOT NULL,
    dosage VARCHAR(100) NOT NULL,
    frequence VARCHAR(100) NOT NULL,
    date_debut DATE NOT NULL,
    date_fin DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_ordonnance) REFERENCES ordonnance(id)
);

-- Table des vaccins
CREATE TABLE IF NOT EXISTS vaccins (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_patient INT NOT NULL,
    nom_vaccin VARCHAR(255) NOT NULL,
    date_vaccination DATE,
    date_rappel DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_patient) REFERENCES patient(id)
);

-- Table des dossiers médicaux
CREATE TABLE IF NOT EXISTS dossiers_medicaux (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_patient INT NOT NULL,
    antecedents TEXT,
    allergies TEXT,
    traitements TEXT,
    derniere_maj TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_patient) REFERENCES patient(id)
);

-- Table des sessions
CREATE TABLE IF NOT EXISTS sessions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    session_id VARCHAR(255) NOT NULL,
    user_id INT NOT NULL,
    user_agent VARCHAR(255),
    ip_address VARCHAR(45),
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des tentatives de connexion
CREATE TABLE IF NOT EXISTS login_attempts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(100) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    success TINYINT(1) NOT NULL DEFAULT 0
);

-- Table des réinitialisations de mot de passe
CREATE TABLE IF NOT EXISTS password_reset (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(100) NOT NULL,
    token VARCHAR(255) NOT NULL,
    expire_date DATETIME NOT NULL,
    used TINYINT(1) DEFAULT 0
);

-- Table des tokens Google
CREATE TABLE IF NOT EXISTS google_tokens (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    access_token TEXT NOT NULL,
    refresh_token TEXT,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table des hôpitaux
CREATE TABLE IF NOT EXISTS hopitaux (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(100),
    localisation TEXT
);

-- Table des pharmacies
CREATE TABLE IF NOT EXISTS pharmacie (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(100),
    localisation TEXT
);

-- Table des prix de consultation
CREATE TABLE IF NOT EXISTS prixconsultation (
    id INT PRIMARY KEY AUTO_INCREMENT,
    prix DECIMAL(10,2)
);

-- Table des utilisateurs
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    password VARCHAR(255),
    google_id VARCHAR(100),
    role ENUM('patient', 'medecin', 'admin') NOT NULL,
    auth_method ENUM('standard', 'google') DEFAULT 'standard',
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insertion des spécialités de base
INSERT INTO specialite (nomspecialite) VALUES
('Cardiologie'),
('Gynécologie'),
('Neurologie'),
('Ophtalmologie'),
('ORL'),
('Pédiatrie'),
('Psychiatrie'),
('Radiologie'),
('Urologie');

-- Insertion des données de test pour les médecins
INSERT INTO medecin (nom, prenom, datenais, email, contact, num, password, role, idspecialite) VALUES
('Martin', 'Marc', '2010-04-29', 'test2@gmail.com', '0157866959', '12345', '$2y$10$KGG6i8iZhvTrZJkT1sgcg.Lx.Lyc.lvIqzO6kyufWk4IUnmgjHCC2', 'medecin', 7),
('FAFA', 'BAKE', '2000-03-03', 'bake@gmail.com', '0157866959', '1234567890', '$2y$10$PXQbkcvvwbuIfb8UvPa97eBHLlx6xG8VGmNdstu4ipEhKrsc/Y0qm', 'medecin', 10),
('BALAAM', 'CHARLESSE', '2006-05-25', 'chao@gmail.com', '0157866959', '1234567890', '$2y$10$alROKemqZ2.dy13g4yGoVukHXEDpisgCXOC37f.UZPoQNP/jwxN3m', 'medecin', 8);

-- Insertion des données de test pour les carnets de santé
INSERT INTO carnetsante (id_patient, groupesanguin, taille, poids, allergie, electrophorese) VALUES
(21, '0+', 165.00, 34.00, 'tomate', 'O'),
(1, '0+', 165.00, 34.00, 'fleur pollen', '+'),
(26, '0+', 170.00, 65.00, 'Polléne', '+');

-- Insertion des données de test pour les pharmacies
INSERT INTO pharmacie (nom, localisation) VALUES
('Pharmacie Saint Michel', 'Cotonou, Carrefour Zogbo, à côté de l\'église Saint Michel'),
('Pharmacie de la Paix', 'Abomey-Calavi, Tankpè, en face du supermarché Leader Price'),
('Pharmacie des Lagunes', 'Porto-Novo, Rue du marché central, quartier Djègan-Kpèvi'),
('Pharmacie Universitaire', 'Cotonou, Campus UAC, Faculté des Sciences de la Santé'),
('Pharmacie Etoile du Sud', 'Parakou, Quartier Zongo, à 200m du rond-point Bio Guèra'),
('Pharmacie Soleil', 'Bohicon, Route de Dassa, à proximité de la station Total'),
('Pharmacie le Bon Samaritain', 'Djougou, Rue du Lycée, face à la mairie'),
('Pharmacie Centrale de Natitingou', 'Natitingou, Rue principale, à côté du commissariat'),
('Pharmacie Médicale', 'Ouidah, Quartier Pahou, près de l\'hôpital Saint Camille'),
('Pharmacie Renaissance', 'Lokossa, Place de l\'Indépendance, face à l\'ancienne poste'); 