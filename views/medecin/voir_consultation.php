<?php
require_once '../../includes/session.php';
require_once '../../config/config.php';

// Vérifier si l'utilisateur est connecté et est un médecin
requireLogin();
requireRole('medecin');

$user_id = $_SESSION['user_id'];
$consultation_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$consultation_id) {
    header('Location: consultations.php');
    exit;
}

// Récupérer les détails de la consultation
try {
    $stmt = db()->prepare("
        SELECT c.*, p.nom as patient_nom, p.prenom as patient_prenom
        FROM consultation c
        JOIN patient p ON c.id_patient = p.id
        WHERE c.id = ? AND c.id_medecin = ?
    ");
    $stmt->execute([$consultation_id, $user_id]);
    $consultation = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$consultation) {
        header('Location: consultations.php');
        exit;
    }
} catch (PDOException $e) {
    $error = "Erreur lors de la récupération des détails de la consultation.";
    error_log($e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails de la Consultation - MedConnect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <?php include_once '../../views/components/styles.php'; ?>
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
            </nav>
        </aside>

        <!-- Contenu principal -->
        <main class="flex-1 p-8">
            <div class="max-w-4xl mx-auto">
                <div class="flex justify-between items-center mb-8">
                    <div>
                        <h2 class="text-2xl font-bold text-[#1B5E20]">Détails de la Consultation</h2>
                        <p class="text-[#558B2F]">Consultation du <?php echo date('d/m/Y H:i', strtotime($consultation['date_consultation'])); ?></p>
                    </div>
                    <div class="flex space-x-4">
                        <a href="modifier_consultation.php?id=<?php echo $consultation_id; ?>" 
                           class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg transition-colors duration-300">
                            <i class="fas fa-edit mr-2"></i>Modifier
                        </a>
                        <a href="imprimer_consultation.php?id=<?php echo $consultation_id; ?>" 
                           class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors duration-300">
                            <i class="fas fa-print mr-2"></i>Imprimer
                        </a>
                        <a href="consultations.php" 
                           class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors duration-300">
                            <i class="fas fa-arrow-left mr-2"></i>Retour
                        </a>
                    </div>
                </div>

                <?php if (isset($error)): ?>
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6">
                        <i class="fas fa-exclamation-circle mr-2"></i><?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <div class="bg-white rounded-xl shadow-lg p-6 space-y-6">
                    <!-- Informations du patient -->
                    <div class="border-b border-gray-200 pb-4">
                        <h3 class="text-lg font-semibold text-[#1B5E20] mb-2">Patient</h3>
                        <p class="text-gray-700">
                            <?php echo htmlspecialchars($consultation['patient_prenom'] . ' ' . $consultation['patient_nom']); ?>
                        </p>
                    </div>

                    <!-- Motif de consultation -->
                    <div class="border-b border-gray-200 pb-4">
                        <h3 class="text-lg font-semibold text-[#1B5E20] mb-2">Motif de consultation</h3>
                        <p class="text-gray-700 whitespace-pre-wrap">
                            <?php echo htmlspecialchars($consultation['motif']); ?>
                        </p>
                    </div>

                    <!-- Antécédents -->
                    <?php if (!empty($consultation['antecedents'])): ?>
                    <div class="border-b border-gray-200 pb-4">
                        <h3 class="text-lg font-semibold text-[#1B5E20] mb-2">Antécédents</h3>
                        <p class="text-gray-700 whitespace-pre-wrap">
                            <?php echo htmlspecialchars($consultation['antecedents']); ?>
                        </p>
                    </div>
                    <?php endif; ?>

                    <!-- Examen clinique -->
                    <div class="border-b border-gray-200 pb-4">
                        <h3 class="text-lg font-semibold text-[#1B5E20] mb-2">Examen clinique</h3>
                        <p class="text-gray-700 whitespace-pre-wrap">
                            <?php echo htmlspecialchars($consultation['examen_clinique']); ?>
                        </p>
                    </div>

                    <!-- Diagnostic -->
                    <div class="border-b border-gray-200 pb-4">
                        <h3 class="text-lg font-semibold text-[#1B5E20] mb-2">Diagnostic</h3>
                        <p class="text-gray-700 whitespace-pre-wrap">
                            <?php echo htmlspecialchars($consultation['diagnostic']); ?>
                        </p>
                    </div>

                    <!-- Traitement -->
                    <?php if (!empty($consultation['traitement'])): ?>
                    <div class="border-b border-gray-200 pb-4">
                        <h3 class="text-lg font-semibold text-[#1B5E20] mb-2">Traitement prescrit</h3>
                        <p class="text-gray-700 whitespace-pre-wrap">
                            <?php echo htmlspecialchars($consultation['traitement']); ?>
                        </p>
                    </div>
                    <?php endif; ?>

                    <!-- Recommandations -->
                    <?php if (!empty($consultation['recommandations'])): ?>
                    <div class="border-b border-gray-200 pb-4">
                        <h3 class="text-lg font-semibold text-[#1B5E20] mb-2">Recommandations</h3>
                        <p class="text-gray-700 whitespace-pre-wrap">
                            <?php echo htmlspecialchars($consultation['recommandations']); ?>
                        </p>
                    </div>
                    <?php endif; ?>

                    <!-- Prochain rendez-vous -->
                    <?php if (!empty($consultation['prochain_rdv'])): ?>
                    <div>
                        <h3 class="text-lg font-semibold text-[#1B5E20] mb-2">Prochain rendez-vous</h3>
                        <p class="text-gray-700">
                            <?php echo date('d/m/Y H:i', strtotime($consultation['prochain_rdv'])); ?>
                        </p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html> 