<?php
require_once '../../includes/session.php';
// require_once '../config/database.php';

requireLogin();
requireRole('patient');

$user_id = $_SESSION['user_id'];
$nom = $_SESSION['nom'];
$prenom = $_SESSION['prenom'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord Patient - MedConnect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f0f9f5;
        }
        .stat-card {
            transition: all 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .rdv-card {
            transition: all 0.3s ease;
        }
        .rdv-card:hover {
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
                <a href="dashboard.php" class="nav-link active block px-4 py-3 rounded-lg text-[#1e40af]">
                    <i class="fas fa-home mr-3"></i>Tableau de bord
                </a>
                <a href="carnet.php" class="nav-link block px-4 py-3 rounded-lg text-[#1e40af]">
                    <i class="fas fa-book-medical mr-3"></i>Mon Carnet de Santé
                </a>
                <a href="rdv.php" class="nav-link block px-4 py-3 rounded-lg text-[#1e40af]">
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
                            <i class="fas fa-user text-white text-xl"></i>
                        </div>
                        <h1 class="text-2xl font-bold text-[#1e40af]"><?php echo htmlspecialchars($prenom . ' ' . $nom); ?></h1>
                    </div>
                    <div class="text-sm text-[#3b82f6]">
                        <i class="fas fa-calendar-alt mr-2"></i><?php echo date('d/m/Y'); ?>
                    </div>
                </div>
            </header>

            <!-- Contenu principal -->
            <main class="container mx-auto px-4 py-8">
                <!-- Statistiques rapides -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="stat-card bg-white rounded-xl shadow-lg p-6 border-l-4 border-[#3b82f6] glass-effect">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-[#3b82f6]">Prochain RDV</p>
                                <h3 class="text-2xl font-bold text-[#1e40af]">15 Mars</h3>
                            </div>
                            <div class="w-12 h-12 rounded-full bg-[#EFF6FF] flex items-center justify-center">
                                <i class="fas fa-calendar-check text-xl text-[#3b82f6]"></i>
                            </div>
                        </div>
                    </div>
                    <div class="stat-card bg-white rounded-xl shadow-lg p-6 border-l-4 border-[#10b981] glass-effect">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-[#10b981]">Médecin traitant</p>
                                <h3 class="text-2xl font-bold text-[#1e40af]">Dr. Martin</h3>
                            </div>
                            <div class="w-12 h-12 rounded-full bg-[#ECFDF5] flex items-center justify-center">
                                <i class="fas fa-user-md text-xl text-[#10b981]"></i>
                            </div>
                        </div>
                    </div>
                    <div class="stat-card bg-white rounded-xl shadow-lg p-6 border-l-4 border-[#f59e0b] glass-effect">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-[#f59e0b]">Ordonnances</p>
                                <h3 class="text-2xl font-bold text-[#1e40af]">2</h3>
                            </div>
                            <div class="w-12 h-12 rounded-full bg-[#FFFBEB] flex items-center justify-center">
                                <i class="fas fa-prescription text-xl text-[#f59e0b]"></i>
                            </div>
                        </div>
                    </div>
                    <div class="stat-card bg-white rounded-xl shadow-lg p-6 border-l-4 border-[#8b5cf6] glass-effect">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-[#8b5cf6]">Messages non lus</p>
                                <h3 class="text-2xl font-bold text-[#1e40af]">3</h3>
                            </div>
                            <div class="w-12 h-12 rounded-full bg-[#F5F3FF] flex items-center justify-center">
                                <i class="fas fa-envelope text-xl text-[#8b5cf6]"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sections principales -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Section Rendez-vous -->
                    <div class="bg-white rounded-xl shadow-lg p-6 glass-effect">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-xl font-semibold text-[#1e40af]">
                                <i class="fas fa-calendar-alt mr-2"></i>Mes Rendez-vous
                            </h2>
                            <a href="rdv.php" class="bg-[#3b82f6] hover:bg-[#2563eb] text-white px-4 py-2 rounded-lg transition-colors duration-300">
                                <i class="fas fa-plus-circle mr-2"></i>Nouveau RDV
                            </a>
                        </div>
                        <div class="space-y-4">
                            <div class="rdv-card p-4 bg-[#EFF6FF] rounded-lg hover:bg-[#DBEAFE]">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <p class="font-medium text-[#1e40af]">Dr. Martin</p>
                                        <p class="text-sm text-[#3b82f6]">15 Mars 2024 - 14:30</p>
                                    </div>
                                    <span class="px-3 py-1 bg-[#DCFCE7] text-[#10b981] rounded-full text-sm">
                                        Confirmé
                                    </span>
                                </div>
                            </div>
                            <div class="rdv-card p-4 bg-[#EFF6FF] rounded-lg hover:bg-[#DBEAFE]">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <p class="font-medium text-[#1e40af]">Dr. Dubois</p>
                                        <p class="text-sm text-[#3b82f6]">20 Mars 2024 - 10:00</p>
                                    </div>
                                    <span class="px-3 py-1 bg-[#FEF3C7] text-[#f59e0b] rounded-full text-sm">
                                        En attente
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section Documents -->
                    <div class="bg-white rounded-xl shadow-lg p-6 glass-effect">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-xl font-semibold text-[#1e40af]">
                                <i class="fas fa-file-medical mr-2"></i>Mes Documents
                            </h2>
                            <a href="carnet.php" class="bg-[#3b82f6] hover:bg-[#2563eb] text-white px-4 py-2 rounded-lg transition-colors duration-300">
                                <i class="fas fa-upload mr-2"></i>Ajouter
                            </a>
                        </div>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between p-4 bg-[#EFF6FF] rounded-lg hover:bg-[#DBEAFE]">
                                <div class="flex items-center space-x-4">
                                    <div class="w-12 h-12 rounded-full bg-[#DBEAFE] flex items-center justify-center">
                                        <i class="fas fa-file-medical text-[#3b82f6] text-xl"></i>
                                    </div>
                                    <div>
                                        <p class="font-medium text-[#1e40af]">Ordonnance - 10/03/2024</p>
                                        <p class="text-sm text-[#3b82f6]">Dr. Martin</p>
                                    </div>
                                </div>
                                <a href="#" class="text-[#3b82f6] hover:text-[#2563eb]">
                                    <i class="fas fa-download"></i>
                                </a>
                            </div>
                            <div class="flex items-center justify-between p-4 bg-[#EFF6FF] rounded-lg hover:bg-[#DBEAFE]">
                                <div class="flex items-center space-x-4">
                                    <div class="w-12 h-12 rounded-full bg-[#DBEAFE] flex items-center justify-center">
                                        <i class="fas fa-file-alt text-[#3b82f6] text-xl"></i>
                                    </div>
                                    <div>
                                        <p class="font-medium text-[#1e40af]">Analyse sanguine - 05/03/2024</p>
                                        <p class="text-sm text-[#3b82f6]">Laboratoire Central</p>
                                    </div>
                                </div>
                                <a href="#" class="text-[#3b82f6] hover:text-[#2563eb]">
                                    <i class="fas fa-download"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section Rappels et Alertes -->
                <div class="mt-8 bg-white rounded-xl shadow-lg p-6 glass-effect">
                    <h2 class="text-xl font-semibold text-[#1e40af] mb-6">
                        <i class="fas fa-bell mr-2"></i>Rappels et Alertes
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="reminder-card p-4 bg-[#FFFBEB] rounded-lg border border-[#FEF3C7]">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-full bg-[#FEF3C7] flex items-center justify-center">
                                    <i class="fas fa-pills text-[#f59e0b]"></i>
                                </div>
                                <p class="text-sm text-[#92400e]">Renouvellement d'ordonnance dans 5 jours</p>
                            </div>
                        </div>
                        <div class="reminder-card p-4 bg-[#EFF6FF] rounded-lg border border-[#DBEAFE]">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-full bg-[#DBEAFE] flex items-center justify-center">
                                    <i class="fas fa-syringe text-[#3b82f6]"></i>
                                </div>
                                <p class="text-sm text-[#1e40af]">Vaccin contre la grippe recommandé</p>
                            </div>
                        </div>
                        <div class="reminder-card p-4 bg-[#ECFDF5] rounded-lg border border-[#D1FAE5]">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-full bg-[#D1FAE5] flex items-center justify-center">
                                    <i class="fas fa-heartbeat text-[#10b981]"></i>
                                </div>
                                <p class="text-sm text-[#065f46]">Bilan de santé annuel à prévoir</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section Messagerie -->
                <div class="mt-8 bg-white rounded-xl shadow-lg p-6 glass-effect">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-semibold text-[#1e40af]">
                            <i class="fas fa-envelope mr-2"></i>Messagerie
                        </h2>
                        <a href="messages.php" class="bg-[#3b82f6] hover:bg-[#2563eb] text-white px-4 py-2 rounded-lg transition-colors duration-300">
                            Voir tous les messages
                        </a>
                    </div>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-4 bg-[#EFF6FF] rounded-lg hover:bg-[#DBEAFE]">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 rounded-full bg-[#DBEAFE] flex items-center justify-center">
                                    <i class="fas fa-user-md text-[#3b82f6] text-xl"></i>
                                </div>
                                <div>
                                    <p class="font-medium text-[#1e40af]">Dr. Martin</p>
                                    <p class="text-sm text-[#3b82f6]">Confirmation de votre rendez-vous...</p>
                                </div>
                            </div>
                            <span class="text-xs text-[#3b82f6]">Il y a 2h</span>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
