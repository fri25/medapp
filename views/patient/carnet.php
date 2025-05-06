<?php
// Connexion à la base de données
require_once '../../config/config.php';
require_once '../../includes/session.php';
requireLogin();
requireRole('patient');

// Récupération des données du patient connecté
$id_patient = $_SESSION['user_id'];


$sql_info = "SELECT 
    p.nom,
    p.prenom,
    p.datenais,
    pf.adresse,
    pf.profession,
    cs.groupesanguin,
    cs.taille,
    cs.poids,
    cs.allergie,
    cs.electrophorese
FROM patient p
JOIN profilpatient pf ON pf.idpatient = p.id
JOIN carnetsante cs ON cs.id = pf.idcarnetsante
WHERE p.id = :id";

$stmt_info = db()->prepare($sql_info);
$stmt_info->execute([':id' => $id_patient]);
$infos = $stmt_info->fetch(PDO::FETCH_ASSOC);

// Fonction de raccourci pour affichage sécurisé
function val($key) {
    global $infos;
    return htmlspecialchars($infos[$key] ?? '');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = $_POST;

    $sql_profil = "SELECT id, idcarnetsante FROM profilpatient WHERE idpatient = :id_patient";
    $stmt_profil = db()->prepare($sql_profil);
    $stmt_profil->execute([':id_patient' => $id_patient]);
    $profil = $stmt_profil->fetch(PDO::FETCH_ASSOC);

    if (!$profil) {
        echo "❌ Aucun profil trouvé pour ce patient.";
        exit;
    }

    $id_profil = $profil['id'];
    $id_carnet = $profil['idcarnetsante'];

    // 🔽 Ici on place le code d'insertion
    $sql = "INSERT INTO fichemed (
        id_patient, id_profil, id_carnet, lieu_naissance, situation_familiale, enfants, grossesses, num_secu, groupe_sanguin,
        medecin_traitant, antecedents_familiaux, maladies_infantiles, antecedents_medicaux,
        antecedents_chirurgicaux, allergies, intolerance_medicament,
        traitement_regulier, vaccins
    ) VALUES (
        :id_patient, :id_profil, :id_carnet, :lieu_naissance, :situation_familiale, :enfants, :grossesses, :num_secu, :groupe_sanguin,
        :medecin_traitant, :antecedents_familiaux, :maladies_infantiles, :antecedents_medicaux,
        :antecedents_chirurgicaux, :allergies, :intolerance_medicament,
        :traitement_regulier, :vaccins
    )";

    $stmt = db()->prepare($sql);

    try {
        $stmt->execute([
            ':id_patient' => $id_patient,
            ':id_profil' => $id_profil,
            ':id_carnet' => $id_carnet,
            ':lieu_naissance' => $data['lieu_naissance'],
            ':situation_familiale' => $data['situation_familiale'],
            ':enfants' => $data['enfants'],
            ':grossesses' => $data['grossesses'],
            ':num_secu' => $data['num_secu'],
            ':groupe_sanguin' => $data['groupe_sanguin'],
            ':medecin_traitant' => $data['medecin_traitant'],
            ':antecedents_familiaux' => $data['antecedents_familiaux'],
            ':maladies_infantiles' => $data['maladies_infantiles'],
            ':antecedents_medicaux' => $data['antecedents_medicaux'],
            ':antecedents_chirurgicaux' => $data['antecedents_chirurgicaux'],
            ':allergies' => $data['allergies'],
            ':intolerance_medicament' => $data['intolerance_medicament'],
            ':traitement_regulier' => $data['traitement_regulier'],
            ':vaccins' => $data['vaccins'],
        ]);

    } catch (PDOException $e) {
        echo "❌ Erreur lors de l'enregistrement : " . $e->getMessage();
    }
}


?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Carnet de Santé - MedConnect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f0f9f5;
        }
        .nav-link {
            transition: all 0.3s ease;
        }
        .nav-link:hover {
            background-color: rgba(59, 130, 246, 0.1);
            transform: translateX(5px);
        }
        .nav-link.active {
            background-color: rgba(59, 130, 246, 0.2);
            border-left: 4px solid #3b82f6;
        }
        .glass-effect {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .health-card {
            transition: all 0.3s ease;
        }
        .health-card:hover {
            transform: translateY(-5px);
        }
        .fade-in {
            animation: fadeIn 0.6s ease-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="bg-gradient-to-br from-[#EFF6FF] to-[#DBEAFE] min-h-screen">
    <div class="min-h-screen flex">
        <!-- Barre latérale -->
        <aside class="w-64 bg-white shadow-lg flex flex-col py-6 px-4">
            <div class="flex items-center justify-center mb-10">
                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-[#3b82f6] to-[#60a5fa] flex items-center justify-center">
                    <i class="fas fa-heartbeat text-white text-xl"></i>
                </div>
                <h1 class="text-2xl font-bold text-[#1e40af] ml-3">MedConnect</h1>
            </div>
            <nav class="flex-1 space-y-2">
                <a href="dashboard.php" class="nav-link block px-4 py-3 rounded-lg text-[#1e40af]">
                    <i class="fas fa-home mr-3"></i>Tableau de bord
                </a>
                <a href="carnet.php" class="nav-link active block px-4 py-3 rounded-lg text-[#1e40af]">
                    <i class="fas fa-book-medical mr-3"></i>Mon Carnet de Santé
                </a>
                <a href="rdv.php" class="nav-link block px-4 py-3 rounded-lg text-[#1e40af]">
                    <i class="fas fa-calendar-alt mr-3"></i>Mes Rendez-vous
                </a>
                <a href="ordonnance.php" class="nav-link block px-4 py-3 rounded-lg text-[#1e40af]">
                    <i class="fas fa-prescription mr-3"></i>Mes Ordonnances
                </a>
                <a href="listes_pharmacie.php" class="nav-link block px-4 py-3 rounded-lg text-[#1e40af]">
                    <i class="fas fa-pills mr-3"></i>Ma Pharmacie
                </a>
                <a href="messages.php" class="nav-link block px-4 py-3 rounded-lg text-[#1e40af]">
                    <i class="fas fa-envelope mr-3"></i>Messages
                </a>
                <a href="profile_patient.php" class="nav-link block px-4 py-3 rounded-lg text-[#1e40af]">
                    <i class="fas fa-user mr-3"></i>Mon Profil
                </a>
            </nav>
            <div class="mt-6">
                <a href="../logout.php" class="block bg-[#FF5252] hover:bg-[#D32F2F] text-white text-center px-4 py-3 rounded-lg transition-colors duration-300">
                    <i class="fas fa-sign-out-alt mr-2"></i>Déconnexion
                </a>
            </div>
        </aside>

        <!-- Contenu principal -->
        <div class="flex-1">
            <!-- En-tête -->
            <header class="bg-white shadow-sm">
                <div class="container mx-auto px-4 py-4 flex justify-between items-center">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-[#3b82f6] to-[#60a5fa] flex items-center justify-center">
                            <i class="fas fa-book-medical text-white text-xl"></i>
                        </div>
                        <h1 class="text-2xl font-bold text-[#1e40af]">Mon Carnet de Santé</h1>
                    </div>
                    <div class="text-sm text-[#3b82f6]">
                        <i class="fas fa-calendar-alt mr-2"></i><?php echo date('d/m/Y'); ?>
                    </div>
                </div>
            </header>

            <!-- Contenu principal -->
            <main class="container mx-auto px-4 py-8">
                <!-- Statistiques -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-white rounded-xl shadow-lg p-6 glass-effect">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-[#3b82f6]">Dernier examen</p>
                                <h3 class="text-2xl font-bold text-[#1e40af]">15 Mars 2024</h3>
                            </div>
                            <div class="w-12 h-12 rounded-full bg-[#EFF6FF] flex items-center justify-center">
                                <i class="fas fa-stethoscope text-xl text-[#3b82f6]"></i>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl shadow-lg p-6 glass-effect">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-[#10b981]">IMC</p>
                                <h3 class="text-2xl font-bold text-[#1e40af]">22.5</h3>
                            </div>
                            <div class="w-12 h-12 rounded-full bg-[#ECFDF5] flex items-center justify-center">
                                <i class="fas fa-weight text-xl text-[#10b981]"></i>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl shadow-lg p-6 glass-effect">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-[#f59e0b]">Allergies</p>
                                <h3 class="text-2xl font-bold text-[#1e40af]">2</h3>
                            </div>
                            <div class="w-12 h-12 rounded-full bg-[#FFFBEB] flex items-center justify-center">
                                <i class="fas fa-allergies text-xl text-[#f59e0b]"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Informations médicales -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Informations de base -->
                    <div class="bg-white rounded-xl shadow-lg p-6 glass-effect fade-in">
                        <h2 class="text-xl font-semibold text-[#1e40af] mb-6 flex items-center">
                            <i class="fas fa-user-circle mr-2"></i>
                            Informations de base
                        </h2>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-[#3b82f6]">Groupe sanguin</span>
                                <span class="font-medium text-[#1e40af]">O+</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-[#3b82f6]">Taille</span>
                                <span class="font-medium text-[#1e40af]">175 cm</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-[#3b82f6]">Poids</span>
                                <span class="font-medium text-[#1e40af]">70 kg</span>
                            </div>
                        </div>
                    </div>

                    <!-- Allergies -->
                    <div class="bg-white rounded-xl shadow-lg p-6 glass-effect fade-in">
                        <h2 class="text-xl font-semibold text-[#1e40af] mb-6 flex items-center">
                            <i class="fas fa-allergies mr-2"></i>
                            Allergies
                        </h2>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-[#3b82f6]">Pollen</span>
                                <span class="px-3 py-1 bg-[#FEF3C7] text-[#f59e0b] rounded-full text-sm">Saisonnière</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-[#3b82f6]">Pénicilline</span>
                                <span class="px-3 py-1 bg-[#FEE2E2] text-[#EF4444] rounded-full text-sm">Médicamenteuse</span>
                            </div>
                        </div>
                    </div>

                    <!-- Antécédents médicaux -->
                    <div class="bg-white rounded-xl shadow-lg p-6 glass-effect fade-in">
                        <h2 class="text-xl font-semibold text-[#1e40af] mb-6 flex items-center">
                            <i class="fas fa-history mr-2"></i>
                            Antécédents médicaux
                        </h2>
                        <div class="space-y-4">
                            <div class="p-4 bg-[#F8FAFC] rounded-lg">
                                <h3 class="font-medium text-[#1e40af] mb-2">Appendicectomie</h3>
                                <p class="text-sm text-[#3b82f6]">2018</p>
                            </div>
                            <div class="p-4 bg-[#F8FAFC] rounded-lg">
                                <h3 class="font-medium text-[#1e40af] mb-2">Fracture du tibia</h3>
                                <p class="text-sm text-[#3b82f6]">2020</p>
                            </div>
                        </div>
                    </div>

                    <!-- Vaccinations -->
                    <div class="bg-white rounded-xl shadow-lg p-6 glass-effect fade-in">
                        <h2 class="text-xl font-semibold text-[#1e40af] mb-6 flex items-center">
                            <i class="fas fa-syringe mr-2"></i>
                            Vaccinations
                        </h2>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-[#3b82f6]">COVID-19</span>
                                <span class="px-3 py-1 bg-[#DCFCE7] text-[#10b981] rounded-full text-sm">À jour</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-[#3b82f6]">Tétanos</span>
                                <span class="px-3 py-1 bg-[#DCFCE7] text-[#10b981] rounded-full text-sm">À jour</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-[#3b82f6]">Grippe</span>
                                <span class="px-3 py-1 bg-[#FEF3C7] text-[#f59e0b] rounded-full text-sm">À renouveler</span>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Bouton de chat flottant -->
    <button class="fixed bottom-4 right-4 bg-[#3b82f6] hover:bg-[#2563eb] text-white rounded-full w-14 h-14 text-2xl shadow-lg transition-colors duration-300 flex items-center justify-center">
        <i class="fas fa-comments"></i>
    </button>
</body>
</html>
