<?php
require_once '../../includes/session.php';
require_once '../../config/database.php';

requireLogin();
requireRole('patient');

$user_id = $_SESSION['user_id'];
$nom = $_SESSION['nom'];
$prenom = $_SESSION['prenom'];

// Récupérer les consultations du patient
$query = "SELECT c.*, m.nom as medecin_nom, m.prenom as medecin_prenom 
          FROM consultation c 
          JOIN medecin m ON c.id_medecin = m.id 
          WHERE c.id_patient = ? 
          ORDER BY c.date_consultation DESC";
$stmt = db()->prepare($query);
$stmt->execute([$user_id]);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Consultations - MedApp</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f0f9f5;
        }
        .consultation-card {
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            transition: transform 0.2s;
        }
        .consultation-card:hover {
            transform: translateY(-5px);
        }
        .consultation-header {
            background-color: #f8f9fa;
            border-radius: 15px 15px 0 0;
            padding: 15px;
        }
        .consultation-body {
            padding: 20px;
        }
        .badge-custom {
            font-size: 0.9em;
            padding: 8px 12px;
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
                <a href="dashboard.php" class="nav-link block px-4 py-3 rounded-lg text-[#1e40af]">
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
                <a href="consultations.php" class="nav-link active block px-4 py-3 rounded-lg text-[#1e40af]">
                    <i class="fas fa-stethoscope mr-3"></i>Mes Consultations
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
                <a href="./../logout.php" class="block bg-[#FF5252] hover:bg-[#D32F2F] text-white text-center px-4 py-3 rounded-lg transition-colors duration-300">
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
                <div class="bg-white rounded-xl shadow-lg p-6 glass-effect">
                    <h1 class="text-2xl font-bold text-[#1e40af] mb-6">Mes Consultations</h1>
                    
                    <?php if (empty($result)): ?>
                        <div class="alert alert-info bg-[#EFF6FF] text-[#1e40af] p-4 rounded-lg">
                            <i class="fas fa-info-circle me-2"></i>
                            Vous n'avez pas encore de consultations enregistrées.
                        </div>
                    <?php else: ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <?php foreach ($result as $consultation): ?>
                                <div class="consultation-card bg-white rounded-xl shadow-lg overflow-hidden">
                                    <div class="consultation-header">
                                        <div class="flex justify-between items-center">
                                            <h5 class="text-lg font-semibold text-[#1e40af]">
                                                Dr. <?php echo htmlspecialchars($consultation['medecin_prenom'] . ' ' . $consultation['medecin_nom']); ?>
                                            </h5>
                                            <span class="badge-custom bg-[#3b82f6] text-white rounded-full">
                                                <?php echo date('d/m/Y', strtotime($consultation['date_consultation'])); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="consultation-body">
                                        <div class="mb-4">
                                            <h6 class="text-sm font-medium text-[#3b82f6] mb-2">Motif de consultation</h6>
                                            <p class="text-gray-700"><?php echo nl2br(htmlspecialchars($consultation['motif'])); ?></p>
                                        </div>
                                        
                                        <div class="mb-4">
                                            <h6 class="text-sm font-medium text-[#3b82f6] mb-2">Diagnostic</h6>
                                            <p class="text-gray-700"><?php echo nl2br(htmlspecialchars($consultation['diagnostic'])); ?></p>
                                        </div>
                                        
                                        <div class="mb-4">
                                            <h6 class="text-sm font-medium text-[#3b82f6] mb-2">Traitement</h6>
                                            <p class="text-gray-700"><?php echo nl2br(htmlspecialchars($consultation['traitement'])); ?></p>
                                        </div>
                                        
                                        <?php if ($consultation['recommandations']): ?>
                                            <div class="mb-4">
                                                <h6 class="text-sm font-medium text-[#3b82f6] mb-2">Recommandations</h6>
                                                <p class="text-gray-700"><?php echo nl2br(htmlspecialchars($consultation['recommandations'])); ?></p>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if ($consultation['prochain_rdv']): ?>
                                            <div class="mt-4 pt-4 border-t border-gray-200">
                                                <h6 class="text-sm font-medium text-[#3b82f6] mb-2">Prochain rendez-vous</h6>
                                                <p class="text-gray-700">
                                                    <i class="fas fa-calendar-alt me-2"></i>
                                                    <?php echo date('d/m/Y H:i', strtotime($consultation['prochain_rdv'])); ?>
                                                </p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>
</body>
</html> 