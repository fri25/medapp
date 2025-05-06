<?php
require_once '../../includes/session.php';

// Vérifier si l'utilisateur est connecté
requireLogin();

// Vérifier si l'utilisateur a le rôle requis
requireRole('medecin');

// Accès aux informations de l'utilisateur connecté
$user_id = $_SESSION['user_id'];
$nom = $_SESSION['nom'];
$prenom = $_SESSION['prenom'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultations - MedConnect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <?php include_once '../../views/components/styles.php'; ?>
    <style>
        .consultation-card {
            transition: all 0.3s ease;
        }
        
        .consultation-card:hover {
            transform: translateY(-5px);
        }
        
        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        .status-scheduled {
            background-color: #E8F5E9;
            color: #2E7D32;
        }
        
        .status-completed {
            background-color: #C8E6C9;
            color: #1B5E20;
        }
        
        .status-cancelled {
            background-color: #FFEBEE;
            color: #C62828;
        }
        
        .filter-btn {
            transition: all 0.3s ease;
        }
        
        .filter-btn:hover {
            transform: translateY(-2px);
        }
        
        .filter-btn.active {
            background-color: #2E7D32;
            color: white;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-[#F1F8E9] to-[#E8F5E9] min-h-screen">
    <div class="min-h-screen flex">
        <!-- Barre latérale -->
        <aside class="w-64 bg-white shadow-lg flex flex-col py-6 px-4">
            <div class="flex items-center justify-center mb-10">
                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-[#2E7D32] to-[#81C784] flex items-center justify-center">
                    <i class="fas fa-heartbeat text-white text-xl"></i>
                </div>
                <h1 class="text-2xl font-bold text-[#1B5E20] ml-3">MedConnect</h1>
            </div>
            <nav class="flex-1 space-y-2">
                <a href="dashboard.php" class="nav-link block px-4 py-3 rounded-lg text-[#1B5E20]">
                    <i class="fas fa-home mr-3"></i>Tableau de bord
                </a>
                <a href="patients.php" class="nav-link block px-4 py-3 rounded-lg text-[#1B5E20]">
                    <i class="fas fa-users mr-3"></i>Mes Patients
                </a>
                <a href="rdv.php" class="nav-link block px-4 py-3 rounded-lg text-[#1B5E20]">
                    <i class="fas fa-calendar-alt mr-3"></i>Agenda
                </a>
                <a href="consultations.php" class="nav-link active block px-4 py-3 rounded-lg text-[#1B5E20]">
                    <i class="fas fa-stethoscope mr-3"></i>Consultations
                </a>
                <a href="ordonnances.php" class="nav-link block px-4 py-3 rounded-lg text-[#1B5E20]">
                    <i class="fas fa-prescription mr-3"></i>Ordonnances
                </a>
                <a href="messages.php" class="nav-link block px-4 py-3 rounded-lg text-[#1B5E20]">
                    <i class="fas fa-envelope mr-3"></i>Messages
                </a>
                <a href="profile_medecin.php" class="nav-link block px-4 py-3 rounded-lg text-[#1B5E20]">
                    <i class="fas fa-user-md mr-3"></i>Mon Profil
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
                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-[#2E7D32] to-[#81C784] flex items-center justify-center">
                            <i class="fas fa-user-md text-white text-xl"></i>
                        </div>
                        <h1 class="text-2xl font-bold text-[#1B5E20]">Dr. <?php echo htmlspecialchars($prenom . ' ' . $nom); ?></h1>
                    </div>
                    <div class="text-sm text-[#558B2F]">
                        <i class="fas fa-calendar-alt mr-2"></i><?php echo date('d/m/Y'); ?>
                    </div>
                </div>
            </header>

            <!-- Contenu principal -->
            <main class="container mx-auto px-4 py-8">
                <!-- En-tête de la page -->
                <div class="flex justify-between items-center mb-8">
                    <div>
                        <h2 class="text-2xl font-bold text-[#1B5E20]">Consultations</h2>
                        <p class="text-[#558B2F]">Gérez vos consultations et suivez vos patients</p>
                    </div>
                    <button class="btn-primary">
                        <i class="fas fa-plus-circle mr-2"></i>Nouvelle consultation
                    </button>
                </div>

                <!-- Filtres -->
                <div class="bg-white rounded-xl shadow-lg p-6 mb-8 glass-effect">
                    <div class="flex flex-wrap gap-4">
                        <button class="filter-btn active px-4 py-2 rounded-lg bg-[#E8F5E9] text-[#1B5E20]">
                            <i class="fas fa-calendar-check mr-2"></i>Toutes
                        </button>
                        <button class="filter-btn px-4 py-2 rounded-lg bg-[#E8F5E9] text-[#1B5E20]">
                            <i class="fas fa-clock mr-2"></i>À venir
                        </button>
                        <button class="filter-btn px-4 py-2 rounded-lg bg-[#E8F5E9] text-[#1B5E20]">
                            <i class="fas fa-check-circle mr-2"></i>Terminées
                        </button>
                        <button class="filter-btn px-4 py-2 rounded-lg bg-[#E8F5E9] text-[#1B5E20]">
                            <i class="fas fa-times-circle mr-2"></i>Annulées
                        </button>
                    </div>
                </div>

                <!-- Liste des consultations -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Consultation 1 -->
                    <div class="consultation-card bg-white rounded-xl shadow-lg p-6 glass-effect">
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-[#2E7D32] to-[#81C784] flex items-center justify-center">
                                    <i class="fas fa-user text-white"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-[#1B5E20]">Marie Martin</h3>
                                    <p class="text-sm text-[#558B2F]">Consultation générale</p>
                                </div>
                            </div>
                            <span class="status-badge status-scheduled">
                                <i class="fas fa-clock mr-1"></i>Planifiée
                            </span>
                        </div>
                        <div class="space-y-3">
                            <div class="flex items-center text-[#558B2F]">
                                <i class="fas fa-calendar-alt w-6"></i>
                                <span>15 Mars 2024 - 14:30</span>
                            </div>
                            <div class="flex items-center text-[#558B2F]">
                                <i class="fas fa-clock w-6"></i>
                                <span>30 minutes</span>
                            </div>
                            <div class="flex items-center text-[#558B2F]">
                                <i class="fas fa-comment-medical w-6"></i>
                                <span>Suivi de traitement</span>
                            </div>
                        </div>
                        <div class="mt-4 flex justify-end space-x-2">
                            <button class="btn-secondary">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn-primary">
                                <i class="fas fa-file-medical mr-2"></i>Dossier
                            </button>
                        </div>
                    </div>

                    <!-- Consultation 2 -->
                    <div class="consultation-card bg-white rounded-xl shadow-lg p-6 glass-effect">
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-[#2E7D32] to-[#81C784] flex items-center justify-center">
                                    <i class="fas fa-user text-white"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-[#1B5E20]">Jean Dupont</h3>
                                    <p class="text-sm text-[#558B2F]">Consultation spécialisée</p>
                                </div>
                            </div>
                            <span class="status-badge status-completed">
                                <i class="fas fa-check-circle mr-1"></i>Terminée
                            </span>
                        </div>
                        <div class="space-y-3">
                            <div class="flex items-center text-[#558B2F]">
                                <i class="fas fa-calendar-alt w-6"></i>
                                <span>14 Mars 2024 - 10:00</span>
                            </div>
                            <div class="flex items-center text-[#558B2F]">
                                <i class="fas fa-clock w-6"></i>
                                <span>45 minutes</span>
                            </div>
                            <div class="flex items-center text-[#558B2F]">
                                <i class="fas fa-comment-medical w-6"></i>
                                <span>Bilan de santé</span>
                            </div>
                        </div>
                        <div class="mt-4 flex justify-end space-x-2">
                            <button class="btn-secondary">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn-primary">
                                <i class="fas fa-file-medical mr-2"></i>Dossier
                            </button>
                        </div>
                    </div>

                    <!-- Consultation 3 -->
                    <div class="consultation-card bg-white rounded-xl shadow-lg p-6 glass-effect">
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-[#2E7D32] to-[#81C784] flex items-center justify-center">
                                    <i class="fas fa-user text-white"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-[#1B5E20]">Sophie Bernard</h3>
                                    <p class="text-sm text-[#558B2F]">Consultation urgente</p>
                                </div>
                            </div>
                            <span class="status-badge status-cancelled">
                                <i class="fas fa-times-circle mr-1"></i>Annulée
                            </span>
                        </div>
                        <div class="space-y-3">
                            <div class="flex items-center text-[#558B2F]">
                                <i class="fas fa-calendar-alt w-6"></i>
                                <span>13 Mars 2024 - 16:00</span>
                            </div>
                            <div class="flex items-center text-[#558B2F]">
                                <i class="fas fa-clock w-6"></i>
                                <span>30 minutes</span>
                            </div>
                            <div class="flex items-center text-[#558B2F]">
                                <i class="fas fa-comment-medical w-6"></i>
                                <span>Urgence</span>
                            </div>
                        </div>
                        <div class="mt-4 flex justify-end space-x-2">
                            <button class="btn-secondary">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn-primary">
                                <i class="fas fa-file-medical mr-2"></i>Dossier
                            </button>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Gestion des filtres
        document.querySelectorAll('.filter-btn').forEach(button => {
            button.addEventListener('click', () => {
                document.querySelectorAll('.filter-btn').forEach(btn => {
                    btn.classList.remove('active');
                });
                button.classList.add('active');
            });
        });
    </script>
</body>
</html> 