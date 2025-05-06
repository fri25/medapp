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
    <title>Tableau de bord Médecin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <?php include_once '../../views/components/styles.php'; ?>
    <style>
        .stat-card {
            transition: all 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .patient-card {
            transition: all 0.3s ease;
        }
        .patient-card:hover {
            transform: translateX(5px);
        }
        .reminder-card {
            transition: all 0.3s ease;
        }
        .reminder-card:hover {
            transform: scale(1.02);
        }
        .nav-link {
            transition: all 0.3s ease;
        }
        .nav-link:hover {
            background-color: rgba(46, 125, 50, 0.1);
            transform: translateX(5px);
        }
        .nav-link.active {
            background-color: rgba(46, 125, 50, 0.2);
            border-left: 4px solid #2E7D32;
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
                <a href="dashboard.php" class="nav-link active block px-4 py-3 rounded-lg text-[#1B5E20]">
                    <i class="fas fa-home mr-3"></i>Tableau de bord
                </a>
                <a href="patients.php" class="nav-link block px-4 py-3 rounded-lg text-[#1B5E20]">
                    <i class="fas fa-users mr-3"></i>Mes Patients
                </a>
                <a href="rdv.php" class="nav-link block px-4 py-3 rounded-lg text-[#1B5E20]">
                    <i class="fas fa-calendar-alt mr-3"></i>Agenda
                </a>
                <a href="consultations.php" class="nav-link block px-4 py-3 rounded-lg text-[#1B5E20]">
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
                <!-- Statistiques rapides -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="stat-card bg-white rounded-xl shadow-lg p-6 border-l-4 border-[#2E7D32] glass-effect">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-[#558B2F]">Rendez-vous aujourd'hui</p>
                                <h3 class="text-2xl font-bold text-[#1B5E20]">8</h3>
                            </div>
                            <div class="w-12 h-12 rounded-full bg-[#E8F5E9] flex items-center justify-center">
                                <i class="fas fa-calendar-check text-xl text-[#2E7D32]"></i>
                            </div>
                        </div>
                    </div>
                    <div class="stat-card bg-white rounded-xl shadow-lg p-6 border-l-4 border-[#4CAF50] glass-effect">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-[#558B2F]">Patients actifs</p>
                                <h3 class="text-2xl font-bold text-[#1B5E20]">24</h3>
                            </div>
                            <div class="w-12 h-12 rounded-full bg-[#E8F5E9] flex items-center justify-center">
                                <i class="fas fa-users text-xl text-[#4CAF50]"></i>
                            </div>
                        </div>
                    </div>
                    <div class="stat-card bg-white rounded-xl shadow-lg p-6 border-l-4 border-[#81C784] glass-effect">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-[#558B2F]">Consultations du jour</p>
                                <h3 class="text-2xl font-bold text-[#1B5E20]">12</h3>
                            </div>
                            <div class="w-12 h-12 rounded-full bg-[#E8F5E9] flex items-center justify-center">
                                <i class="fas fa-stethoscope text-xl text-[#81C784]"></i>
                            </div>
                        </div>
                    </div>
                    <div class="stat-card bg-white rounded-xl shadow-lg p-6 border-l-4 border-[#A5D6A7] glass-effect">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-[#558B2F]">Messages non lus</p>
                                <h3 class="text-2xl font-bold text-[#1B5E20]">3</h3>
                            </div>
                            <div class="w-12 h-12 rounded-full bg-[#E8F5E9] flex items-center justify-center">
                                <i class="fas fa-envelope text-xl text-[#A5D6A7]"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sections principales -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Section Patients -->
                    <div class="bg-white rounded-xl shadow-lg p-6 glass-effect">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-xl font-semibold text-[#1B5E20]">
                                <i class="fas fa-users mr-2"></i>Mes Patients
                            </h2>
                            <a href="patients.php" class="btn-primary">
                                <i class="fas fa-plus-circle mr-2"></i>Nouveau patient
                            </a>
                        </div>
                        <div class="space-y-4">
                            <div class="patient-card flex items-center justify-between p-4 bg-[#F1F8E9] rounded-lg hover:bg-[#E8F5E9]">
                                <div class="flex items-center space-x-4">
                                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-[#2E7D32] to-[#81C784] flex items-center justify-center">
                                        <i class="fas fa-user text-white"></i>
                                    </div>
                                    <div>
                                        <p class="font-medium text-[#1B5E20]">Jean Dupont</p>
                                        <p class="text-sm text-[#558B2F]">Dernière visite: 15/03/2024</p>
                                    </div>
                                </div>
                                <a href="#" class="text-[#2E7D32] hover:text-[#1B5E20]">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Section Agenda -->
                    <div class="bg-white rounded-xl shadow-lg p-6 glass-effect">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-xl font-semibold text-[#1B5E20]">
                                <i class="fas fa-calendar-alt mr-2"></i>Mon Agenda
                            </h2>
                            <a href="rdv.php" class="btn-primary">
                                <i class="fas fa-plus-circle mr-2"></i>Nouveau RDV
                            </a>
                        </div>
                        <div class="space-y-4">
                            <div class="p-4 bg-[#F1F8E9] rounded-lg hover:bg-[#E8F5E9]">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <p class="font-medium text-[#1B5E20]">Marie Martin</p>
                                        <p class="text-sm text-[#558B2F]">09:00 - 09:30</p>
                                    </div>
                                    <span class="px-3 py-1 bg-[#C8E6C9] text-[#1B5E20] rounded-full text-sm">
                                        Confirmé
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section Rappels -->
                <div class="mt-8 bg-white rounded-xl shadow-lg p-6 glass-effect">
                    <h2 class="text-xl font-semibold text-[#1B5E20] mb-6">
                        <i class="fas fa-bell mr-2"></i>Rappels importants
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="reminder-card p-4 bg-[#FFF3E0] rounded-lg border border-[#FFE0B2]">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-full bg-[#FFE0B2] flex items-center justify-center">
                                    <i class="fas fa-exclamation-circle text-[#E65100]"></i>
                                </div>
                                <p class="text-sm text-[#E65100]">3 rappels de vaccins à effectuer</p>
                            </div>
                        </div>
                        <div class="reminder-card p-4 bg-[#E3F2FD] rounded-lg border border-[#BBDEFB]">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-full bg-[#BBDEFB] flex items-center justify-center">
                                    <i class="fas fa-file-medical text-[#1565C0]"></i>
                                </div>
                                <p class="text-sm text-[#1565C0]">5 dossiers à mettre à jour</p>
                            </div>
                        </div>
                        <div class="reminder-card p-4 bg-[#FFEBEE] rounded-lg border border-[#FFCDD2]">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-full bg-[#FFCDD2] flex items-center justify-center">
                                    <i class="fas fa-clock text-[#C62828]"></i>
                                </div>
                                <p class="text-sm text-[#C62828]">2 rendez-vous en attente de confirmation</p>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html> 