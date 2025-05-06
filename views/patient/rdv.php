<?php
require_once '../../config/config.php';
require_once '../../includes/session.php';

// Initialisation des messages
$success = "";
$error = "";

// Vérifie que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../index.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Requête pour récupérer les rendez-vous du patient avec infos médecin
$sql = "SELECT r.dateheure, r.statut, m.nom AS nom_medecin, s.nomspecialite
        FROM rendezvous r
        JOIN medecin m ON r.idmedecin = m.id
        LEFT JOIN specialite s ON r.idspecialite = s.id
        WHERE r.idpatient = :idpatient
        ORDER BY r.dateheure DESC
        ";

$stmt = db()->prepare($sql);
$stmt->execute(['idpatient' => $user_id]);
$rendezvous = $stmt->fetchAll(PDO::FETCH_ASSOC);


// prendre rendez vous

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $idpatient = $_SESSION['user_id'];
    $idmedecin = $_POST['idmedecin'] ?? null;
    $idspecialite = $_POST['idspecialite'] ?? null;
    $dateheure = $_POST['dateheure'] ?? null;

    if ($idmedecin && $idspecialite && $dateheure) {
        $sql = "INSERT INTO rendezvous (dateheure, statut, idmedecin, idpatient, idspecialite)
                VALUES (:dateheure, 'en attente', :idmedecin, :idpatient, :idspecialite)";
        $stmt = db()->prepare($sql);
        $result = $stmt->execute([
            'dateheure' => $dateheure,
            'idmedecin' => $idmedecin,
            'idpatient' => $idpatient,
            'idspecialite' => $idspecialite
        ]);

        if ($result) {
            header('Location: rdv.php?success=1');
            exit();
        } else {
            header('Location: rdv.php?error=1');
            exit();
        }
    } else {
        header('Location: rdv.php?error=1');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>    
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Rendez-vous - MedConnect</title>
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
        .appointment-card {
            transition: all 0.3s ease;
        }
        .appointment-card:hover {
            transform: translateY(-5px);
        }
        .search-input {
            transition: all 0.3s ease;
        }
        .search-input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
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
                <a href="carnet.php" class="nav-link block px-4 py-3 rounded-lg text-[#1e40af]">
                    <i class="fas fa-book-medical mr-3"></i>Mon Carnet de Santé
                </a>
                <a href="rdv.php" class="nav-link active block px-4 py-3 rounded-lg text-[#1e40af]">
                    <i class="fas fa-calendar-alt mr-3"></i>Mes Rendez-vous
                </a>
                <a href="ordonnace.php" class="nav-link block px-4 py-3 rounded-lg text-[#1e40af]">
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
                            <i class="fas fa-calendar-alt text-white text-xl"></i>
                        </div>
                        <h1 class="text-2xl font-bold text-[#1e40af]">Mes Rendez-vous</h1>
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
                                <p class="text-sm text-[#3b82f6]">Rendez-vous à venir</p>
                                <h3 class="text-2xl font-bold text-[#1e40af]">3</h3>
                            </div>
                            <div class="w-12 h-12 rounded-full bg-[#EFF6FF] flex items-center justify-center">
                                <i class="fas fa-calendar-check text-xl text-[#3b82f6]"></i>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl shadow-lg p-6 glass-effect">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-[#10b981]">Rendez-vous confirmés</p>
                                <h3 class="text-2xl font-bold text-[#1e40af]">2</h3>
                            </div>
                            <div class="w-12 h-12 rounded-full bg-[#ECFDF5] flex items-center justify-center">
                                <i class="fas fa-check-circle text-xl text-[#10b981]"></i>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl shadow-lg p-6 glass-effect">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-[#f59e0b]">En attente</p>
                                <h3 class="text-2xl font-bold text-[#1e40af]">1</h3>
                            </div>
                            <div class="w-12 h-12 rounded-full bg-[#FFFBEB] flex items-center justify-center">
                                <i class="fas fa-clock text-xl text-[#f59e0b]"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bouton pour afficher/masquer le formulaire -->
                <button onclick="toggleForm()" class="mb-6 bg-[#3b82f6] hover:bg-[#2563eb] text-white px-6 py-3 rounded-lg transition-colors duration-300 flex items-center gap-2">
                    <i class="fas fa-plus"></i>
                    Prendre un rendez-vous
                </button>

                <!-- Formulaire caché par défaut -->
                <div id="formRdv" class="bg-white rounded-xl shadow-lg p-6 mb-8 glass-effect hidden">
                    <form action="" method="POST" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="specialite" class="block text-sm font-medium text-[#1e40af] mb-2">Spécialité :</label>
                                <select name="idspecialite" id="specialite" required class="w-full border border-gray-200 rounded-lg px-4 py-3 focus:outline-none focus:border-[#3b82f6]">
                                    <option value="">-- Choisir une spécialité --</option>
                                    <?php
                                    $specialites = db()->query("SELECT id, nomspecialite FROM specialite")->fetchAll(PDO::FETCH_ASSOC);
                                    foreach ($specialites as $s) {
                                        echo "<option value='{$s['id']}'>{$s['nomspecialite']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <div>
                                <label for="medecin" class="block text-sm font-medium text-[#1e40af] mb-2">Médecin :</label>
                                <select name="idmedecin" id="medecin" required class="w-full border border-gray-200 rounded-lg px-4 py-3 focus:outline-none focus:border-[#3b82f6]">
                                    <option value="">-- Sélectionner une spécialité d'abord --</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label for="dateheure" class="block text-sm font-medium text-[#1e40af] mb-2">Date et Heure :</label>
                            <input type="datetime-local" name="dateheure" id="dateheure" required class="w-full border border-gray-200 rounded-lg px-4 py-3 focus:outline-none focus:border-[#3b82f6]">
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="bg-[#10b981] hover:bg-[#059669] text-white px-6 py-3 rounded-lg transition-colors duration-300 flex items-center gap-2">
                                <i class="fas fa-check"></i>
                                Confirmer
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Liste des rendez-vous -->
                <div class="bg-white rounded-xl shadow-lg p-6 glass-effect">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-semibold text-[#1e40af] flex items-center">
                            <i class="fas fa-calendar-alt mr-2"></i>
                            Mes rendez-vous
                        </h2>
                        <div class="flex gap-4">
                            <div class="relative">
                                <input type="text" 
                                       placeholder="Rechercher..." 
                                       class="search-input border border-gray-200 rounded-lg px-4 py-2 pl-10 focus:outline-none">
                                <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-[#3b82f6]"></i>
                            </div>
                            <button class="bg-[#3b82f6] hover:bg-[#2563eb] text-white px-4 py-2 rounded-lg transition-colors duration-300 flex items-center gap-2">
                                <i class="fas fa-filter"></i>
                                Filtrer
                            </button>
                        </div>
                    </div>

                    <?php if (count($rendezvous) > 0): ?>
                        <div class="space-y-4">
                            <?php foreach ($rendezvous as $rdv): ?>
                                <div class="appointment-card bg-[#F8FAFC] rounded-lg p-4 hover:bg-[#F1F5F9]">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-4">
                                            <div class="w-12 h-12 rounded-full bg-[#DBEAFE] flex items-center justify-center">
                                                <i class="fas fa-user-md text-[#3b82f6] text-xl"></i>
                                            </div>
                                            <div>
                                                <h3 class="font-medium text-[#1e40af]">Dr. <?= htmlspecialchars($rdv['nom_medecin']) ?></h3>
                                                <p class="text-sm text-[#3b82f6]">
                                                    <i class="fas fa-calendar-alt mr-2"></i>
                                                    <?= date('d/m/Y H:i', strtotime($rdv['dateheure'])) ?>
                                                </p>
                                                <p class="text-sm text-[#3b82f6]">
                                                    <i class="fas fa-stethoscope mr-2"></i>
                                                    <?= htmlspecialchars($rdv['nomspecialite']) ?>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-4">
                                            <?php
                                            $statut = strtolower($rdv['statut']);
                                            $color = '';
                                            switch($statut) {
                                                case 'accepté':
                                                    $color = 'bg-[#DCFCE7] text-[#10b981]';
                                                    break;
                                                case 'refusé':
                                                    $color = 'bg-[#FEE2E2] text-[#EF4444]';
                                                    break;
                                                default:
                                                    $color = 'bg-[#FEF3C7] text-[#f59e0b]';
                                            }
                                            ?>
                                            <span class="px-3 py-1 <?= $color ?> rounded-full text-sm">
                                                <?= ucfirst(htmlspecialchars($statut)) ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-8">
                            <div class="w-16 h-16 rounded-full bg-[#FEE2E2] flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-calendar-times text-[#991B1B] text-2xl"></i>
                            </div>
                            <h3 class="text-xl font-semibold text-[#1e40af] mb-2">Aucun rendez-vous</h3>
                            <p class="text-[#3b82f6]">Prenez votre premier rendez-vous en cliquant sur le bouton ci-dessus</p>
                        </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

    <script>
    function toggleForm() {
        const form = document.getElementById('formRdv');
        form.classList.toggle('hidden');
    }

    document.getElementById('specialite').addEventListener('change', function () {
        let specialiteId = this.value;
        let medecinSelect = document.getElementById('medecin');

        if (specialiteId) {
            fetch(`ajax_rdv.php?specialite_id=${specialiteId}`)
                .then(response => response.json())
                .then(data => {
                    medecinSelect.innerHTML = '';
                    if (data.length > 0) {
                        data.forEach(med => {
                            let option = document.createElement('option');
                            option.value = med.id;
                            option.text = med.nom;
                            medecinSelect.appendChild(option);
                        });
                    } else {
                        medecinSelect.innerHTML = '<option>Aucun médecin trouvé</option>';
                    }
                });
        }
    });

    document.getElementById('medecin').addEventListener('change', function () {
        let medecinId = this.value;
        if (medecinId) {
            fetch(`ajax_rdv.php?medecin_id=${medecinId}`)
                .then(response => response.json())
                .then(data => {
                    let specialiteSelect = document.getElementById('specialite');
                    for (let i = 0; i < specialiteSelect.options.length; i++) {
                        if (specialiteSelect.options[i].value == data.id) {
                            specialiteSelect.selectedIndex = i;
                            break;
                        }
                    }
                });
        }
    });
    </script>
</body>
</html>
