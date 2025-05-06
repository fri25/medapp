-- ajouter une table fichemed

CREATE TABLE fichemed (
    id INT AUTO_INCREMENT PRIMARY KEY,
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
    FOREIGN KEY (id_patient) REFERENCES patient(id) ON DELETE CASCADE,
    FOREIGN KEY (id_profil) REFERENCES profilpatient(id) ON DELETE CASCADE,
    FOREIGN KEY (id_carnet) REFERENCES carnetsante(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;