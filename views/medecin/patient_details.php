<?php
require_once '../../includes/session.php';
require_once '../../config/config.php';

// Vérifier si l'utilisateur est connecté et est un médecin
requireLogin();
requireRole('medecin');

$user_id = $_SESSION['user_id'];
$patient_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$patient_id) {
    header('Location: /medapp/views/medecin/dashboard.php');
    exit;
}

// Récupérer les informations du patient
$stmt = db()->prepare("
    SELECT 
        p.*,
        pp.date_naissance,
        pp.sexe,
        pp.groupe_sanguin,
        pp.allergies,
        pp.antecedents_medicaux,
        pp.traitements_en_cours
    FROM patient p
    LEFT JOIN profilpatient pp ON p.id = pp.idpatient
    WHERE p.id = ?
");
$stmt->execute([$patient_id]);
$patient = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$patient) {
    header('Location: patients.php');
    exit;
}

// Récupérer les derniers rendez-vous
$stmt = db()->prepare("
    SELECT r.*, m.nom as medecin_nom, m.prenom as medecin_prenom
    FROM rendezvous r
    JOIN medecin m ON r.idmedecin = m.id
    WHERE r.idpatient = ?
    ORDER BY r.date_rdv DESC
    LIMIT 5
");
$stmt->execute([$patient_id]);
$rendezvous = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les dernières ordonnances
$stmt = db()->prepare("
    SELECT o.*, m.nom as medecin_nom, m.prenom as medecin_prenom
    FROM ordonnance o
    JOIN medecin m ON o.idmedecin = m.id
    WHERE o.idpatient = ?
    ORDER BY o.date_ordonnance DESC
    LIMIT 5
");
$stmt->execute([$patient_id]);
$ordonnances = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails Patient - MedConnect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex">
        <!-- Barre latérale -->
        <aside class="w-64 bg-blue-700 text-white flex flex-col py-6 px-4">
            <h1 class="text-2xl font-bold mb-10 text-center">MedConnect</h1>
            <nav class="flex-1 space-y-4">
                <a href="dashboard.php" class="block px-4 py-2 rounded hover:bg-blue-600">
                    <i class="fas fa-home mr-2"></i>Tableau de bord
                </a>
                <a href="patients.php" class="block px-4 py-2 rounded bg-blue-800">
                    <i class="fas fa-users mr-2"></i>Mes Patients
                </a>
                <a href="rdv.php" class="block px-4 py-2 rounded hover:bg-blue-600">
                    <i class="fas fa-calendar-alt mr-2"></i>Agenda
                </a>
                <a href="consultations.php" class="block px-4 py-2 rounded hover:bg-blue-600">
                    <i class="fas fa-stethoscope mr-2"></i>Consultations
                </a>
                <a href="ordonnances.php" class="block px-4 py-2 rounded hover:bg-blue-600">
                    <i class="fas fa-prescription mr-2"></i>Ordonnances
                </a>
                <a href="messages.php" class="block px-4 py-2 rounded hover:bg-blue-600">
                    <i class="fas fa-envelope mr-2"></i>Messages
                </a>
                <a href="profile_medecin.php" class="block px-4 py-2 rounded hover:bg-blue-600">
                    <i class="fas fa-user-md mr-2"></i>Mon Profil
                </a>
            </nav>
            <div class="mt-6">
                <a href="../../logout.php" class="block bg-red-500 hover:bg-red-600 text-white text-center px-4 py-2 rounded">
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
                        <a href="patients.php" class="text-blue-600 hover:text-blue-800">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                        <h1 class="text-xl font-bold text-gray-800">
                            <?php echo htmlspecialchars($patient['prenom'] . ' ' . $patient['nom']); ?>
                        </h1>
                    </div>
                    <div class="flex space-x-4">
                        <a href="nouvelle_consultation.php?patient_id=<?php echo $patient_id; ?>" 
                           class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                            <i class="fas fa-plus-circle mr-2"></i>Nouvelle Consultation
                        </a>
                        <a href="nouvelle_ordonnance.php?patient_id=<?php echo $patient_id; ?>" 
                           class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">
                            <i class="fas fa-prescription mr-2"></i>Nouvelle Ordonnance
                        </a>
                    </div>
                </div>
            </header>

            <!-- Contenu principal -->
            <main class="container mx-auto px-4 py-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Informations personnelles -->
                    <div class="md:col-span-1">
                        <div class="bg-white rounded-xl shadow-sm p-6">
                            <h2 class="text-lg font-semibold text-gray-800 mb-4">Informations personnelles</h2>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Date de naissance</label>
                                    <p class="mt-1"><?php echo htmlspecialchars($patient['date_naissance'] ?? 'Non renseigné'); ?></p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Sexe</label>
                                    <p class="mt-1"><?php echo htmlspecialchars($patient['sexe'] ?? 'Non renseigné'); ?></p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Groupe sanguin</label>
                                    <p class="mt-1"><?php echo htmlspecialchars($patient['groupe_sanguin'] ?? 'Non renseigné'); ?></p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Contact</label>
                                    <p class="mt-1"><?php echo htmlspecialchars($patient['contact'] ?? 'Non renseigné'); ?></p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Email</label>
                                    <p class="mt-1"><?php echo htmlspecialchars($patient['email'] ?? 'Non renseigné'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Informations médicales -->
                    <div class="md:col-span-2">
                        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                            <h2 class="text-lg font-semibold text-gray-800 mb-4">Informations médicales</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Allergies</label>
                                    <p class="mt-1"><?php echo htmlspecialchars($patient['allergies'] ?? 'Aucune allergie connue'); ?></p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Antécédents médicaux</label>
                                    <p class="mt-1"><?php echo htmlspecialchars($patient['antecedents_medicaux'] ?? 'Aucun antécédent connu'); ?></p>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-500">Traitements en cours</label>
                                    <p class="mt-1"><?php echo htmlspecialchars($patient['traitements_en_cours'] ?? 'Aucun traitement en cours'); ?></p>
                                </div>
                            </div>
                        </div>

                        <!-- Derniers rendez-vous -->
                        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                            <div class="flex justify-between items-center mb-4">
                                <h2 class="text-lg font-semibold text-gray-800">Derniers rendez-vous</h2>
                                <a href="rdv.php?patient_id=<?php echo $patient_id; ?>" class="text-blue-600 hover:text-blue-800">
                                    Voir tout
                                </a>
                            </div>
                            <div class="space-y-4">
                                <?php foreach ($rendezvous as $rdv): ?>
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <div class="flex justify-between items-center">
                                            <div>
                                                <p class="font-medium"><?php echo date('d/m/Y H:i', strtotime($rdv['date_rdv'])); ?></p>
                                                <p class="text-sm text-gray-500">
                                                    Dr. <?php echo htmlspecialchars($rdv['medecin_prenom'] . ' ' . $rdv['medecin_nom']); ?>
                                                </p>
                                            </div>
                                            <span class="px-3 py-1 rounded-full text-sm <?php 
                                                $statusClass = '';
                                                switch($rdv['statut']) {
                                                    case 'confirme':
                                                        $statusClass = 'bg-green-100 text-green-800';
                                                        break;
                                                    case 'annule':
                                                        $statusClass = 'bg-red-100 text-red-800';
                                                        break;
                                                    default:
                                                        $statusClass = 'bg-yellow-100 text-yellow-800';
                                                }
                                                echo $statusClass;
                                            ?>">
                                                <?php echo ucfirst($rdv['statut']); ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Dernières ordonnances -->
                        <div class="bg-white rounded-xl shadow-sm p-6">
                            <div class="flex justify-between items-center mb-4">
                                <h2 class="text-lg font-semibold text-gray-800">Dernières ordonnances</h2>
                                <a href="ordonnances.php?patient_id=<?php echo $patient_id; ?>" class="text-blue-600 hover:text-blue-800">
                                    Voir tout
                                </a>
                            </div>
                            <div class="space-y-4">
                                <?php foreach ($ordonnances as $ordonnance): ?>
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <div class="flex justify-between items-center">
                                            <div>
                                                <p class="font-medium"><?php echo date('d/m/Y', strtotime($ordonnance['date_ordonnance'])); ?></p>
                                                <p class="text-sm text-gray-500">
                                                    Dr. <?php echo htmlspecialchars($ordonnance['medecin_prenom'] . ' ' . $ordonnance['medecin_nom']); ?>
                                                </p>
                                            </div>
                                            <a href="voir_ordonnance.php?id=<?php echo $ordonnance['id']; ?>" 
                                               class="text-blue-600 hover:text-blue-800">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html> 