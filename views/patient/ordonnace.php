<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ordonnances - MedConnect</title>
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
        .prescription-card {
            transition: all 0.3s ease;
        }
        .prescription-card:hover {
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
                <a href="rdv.php" class="nav-link block px-4 py-3 rounded-lg text-[#1e40af]">
                    <i class="fas fa-calendar-alt mr-3"></i>Mes Rendez-vous
                </a>
                <a href="ordonnace.php" class="nav-link active block px-4 py-3 rounded-lg text-[#1e40af]">
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
                            <i class="fas fa-prescription text-white text-xl"></i>
                        </div>
                        <h1 class="text-2xl font-bold text-[#1e40af]">Mes Ordonnances</h1>
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
                                <p class="text-sm text-[#3b82f6]">Ordonnances actives</p>
                                <h3 class="text-2xl font-bold text-[#1e40af]">3</h3>
                            </div>
                            <div class="w-12 h-12 rounded-full bg-[#EFF6FF] flex items-center justify-center">
                                <i class="fas fa-prescription text-xl text-[#3b82f6]"></i>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl shadow-lg p-6 glass-effect">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-[#10b981]">Médicaments en cours</p>
                                <h3 class="text-2xl font-bold text-[#1e40af]">5</h3>
                            </div>
                            <div class="w-12 h-12 rounded-full bg-[#ECFDF5] flex items-center justify-center">
                                <i class="fas fa-pills text-xl text-[#10b981]"></i>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl shadow-lg p-6 glass-effect">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-[#f59e0b]">À renouveler</p>
                                <h3 class="text-2xl font-bold text-[#1e40af]">2</h3>
                            </div>
                            <div class="w-12 h-12 rounded-full bg-[#FFFBEB] flex items-center justify-center">
                                <i class="fas fa-clock text-xl text-[#f59e0b]"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Liste des ordonnances -->
                <div class="bg-white rounded-xl shadow-lg p-6 glass-effect">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-semibold text-[#1e40af] flex items-center">
                            <i class="fas fa-prescription mr-2"></i>
                            Mes dernières ordonnances
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

                    <div class="space-y-4">
                        <!-- Exemple d'ordonnance -->
                        <div class="prescription-card bg-[#F8FAFC] rounded-lg p-4 hover:bg-[#F1F5F9]">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    <div class="w-12 h-12 rounded-full bg-[#DBEAFE] flex items-center justify-center">
                                        <i class="fas fa-user-md text-[#3b82f6] text-xl"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-medium text-[#1e40af]">Dr. Martin</h3>
                                        <p class="text-sm text-[#3b82f6]">15 Mars 2024</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4">
                                    <span class="px-3 py-1 bg-[#DCFCE7] text-[#10b981] rounded-full text-sm">
                                        Active
                                    </span>
                                    <button class="text-[#3b82f6] hover:text-[#2563eb]">
                                        <i class="fas fa-download"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Exemple d'ordonnance -->
                        <div class="prescription-card bg-[#F8FAFC] rounded-lg p-4 hover:bg-[#F1F5F9]">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    <div class="w-12 h-12 rounded-full bg-[#DBEAFE] flex items-center justify-center">
                                        <i class="fas fa-user-md text-[#3b82f6] text-xl"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-medium text-[#1e40af]">Dr. Dubois</h3>
                                        <p class="text-sm text-[#3b82f6]">10 Mars 2024</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4">
                                    <span class="px-3 py-1 bg-[#FEF3C7] text-[#f59e0b] rounded-full text-sm">
                                        À renouveler
                                    </span>
                                    <button class="text-[#3b82f6] hover:text-[#2563eb]">
                                        <i class="fas fa-download"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
