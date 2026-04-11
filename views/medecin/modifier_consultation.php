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

// Traitement du formulaire de modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validation des données requises
        if (empty($_POST['motif'])) {
            throw new Exception("Le motif de consultation est requis.");
        }
        if (empty($_POST['examen_clinique'])) {
            throw new Exception("L'examen clinique est requis.");
        }
        if (empty($_POST['diagnostic'])) {
            throw new Exception("Le diagnostic est requis.");
        }

        // Préparation de la requête
        $stmt = db()->prepare("
            UPDATE consultation SET
                motif = ?,
                antecedents = ?,
                examen_clinique = ?,
                diagnostic = ?,
                traitement = ?,
                recommandations = ?,
                prochain_rdv = ?
            WHERE id = ? AND id_medecin = ?
        ");

        // Exécution de la requête
        $success = $stmt->execute([
            $_POST['motif'],
            $_POST['antecedents'] ?? null,
            $_POST['examen_clinique'],
            $_POST['diagnostic'],
            $_POST['traitement'] ?? null,
            $_POST['recommandations'] ?? null,
            !empty($_POST['prochain_rdv']) ? $_POST['prochain_rdv'] : null,
            $consultation_id,
            $user_id
        ]);

        if ($success) {
            $success_message = "La consultation a été mise à jour avec succès.";
            // Redirection vers la vue de la consultation
            header("Location: voir_consultation.php?id=" . $consultation_id);
            exit;
        } else {
            throw new Exception("Erreur lors de la mise à jour de la consultation.");
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier la Consultation - MedConnect</title>
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
                        <h2 class="text-2xl font-bold text-[#1B5E20]">Modifier la Consultation</h2>
                        <p class="text-[#558B2F]">Consultation du <?php echo date('d/m/Y H:i', strtotime($consultation['date_consultation'])); ?></p>
                    </div>
                    <a href="voir_consultation.php?id=<?php echo $consultation_id; ?>" 
                       class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors duration-300">
                        <i class="fas fa-arrow-left mr-2"></i>Retour
                    </a>
                </div>

                <?php if (isset($error)): ?>
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6">
                        <i class="fas fa-exclamation-circle mr-2"></i><?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="bg-white rounded-xl shadow-lg p-6 space-y-6">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <!-- Informations du patient -->
                    <div class="border-b border-gray-200 pb-4">
                        <h3 class="text-lg font-semibold text-[#1B5E20] mb-2">Patient</h3>
                        <p class="text-gray-700">
                            <?php echo htmlspecialchars($consultation['patient_prenom'] . ' ' . $consultation['patient_nom']); ?>
                        </p>
                    </div>

                    <!-- Motif de consultation -->
                    <div>
                        <label for="motif" class="block text-sm font-medium text-gray-700 mb-2">Motif de consultation</label>
                        <textarea name="motif" id="motif" rows="3" required
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2E7D32] focus:border-transparent"
                                  placeholder="Décrivez le motif de la consultation..."><?php echo htmlspecialchars($consultation['motif']); ?></textarea>
                    </div>

                    <!-- Antécédents -->
                    <div>
                        <label for="antecedents" class="block text-sm font-medium text-gray-700 mb-2">Antécédents</label>
                        <textarea name="antecedents" id="antecedents" rows="3"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2E7D32] focus:border-transparent"
                                  placeholder="Notez les antécédents pertinents..."><?php echo htmlspecialchars($consultation['antecedents'] ?? ''); ?></textarea>
                    </div>

                    <!-- Examen clinique -->
                    <div>
                        <label for="examen_clinique" class="block text-sm font-medium text-gray-700 mb-2">Examen clinique</label>
                        <textarea name="examen_clinique" id="examen_clinique" rows="4" required
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2E7D32] focus:border-transparent"
                                  placeholder="Détaillez l'examen clinique..."><?php echo htmlspecialchars($consultation['examen_clinique']); ?></textarea>
                    </div>

                    <!-- Diagnostic -->
                    <div>
                        <label for="diagnostic" class="block text-sm font-medium text-gray-700 mb-2">Diagnostic</label>
                        <textarea name="diagnostic" id="diagnostic" rows="3" required
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2E7D32] focus:border-transparent"
                                  placeholder="Indiquez le diagnostic..."><?php echo htmlspecialchars($consultation['diagnostic']); ?></textarea>
                    </div>

                    <!-- Traitement -->
                    <div>
                        <label for="traitement" class="block text-sm font-medium text-gray-700 mb-2">Traitement prescrit</label>
                        <textarea name="traitement" id="traitement" rows="3"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2E7D32] focus:border-transparent"
                                  placeholder="Décrivez le traitement prescrit..."><?php echo htmlspecialchars($consultation['traitement'] ?? ''); ?></textarea>
                    </div>

                    <!-- Recommandations -->
                    <div>
                        <label for="recommandations" class="block text-sm font-medium text-gray-700 mb-2">Recommandations</label>
                        <textarea name="recommandations" id="recommandations" rows="3"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2E7D32] focus:border-transparent"
                                  placeholder="Ajoutez des recommandations pour le patient..."><?php echo htmlspecialchars($consultation['recommandations'] ?? ''); ?></textarea>
                    </div>

                    <!-- Prochain rendez-vous -->
                    <div>
                        <label for="prochain_rdv" class="block text-sm font-medium text-gray-700 mb-2">Prochain rendez-vous</label>
                        <input type="datetime-local" name="prochain_rdv" id="prochain_rdv"
                               value="<?php echo $consultation['prochain_rdv'] ? date('Y-m-d\TH:i', strtotime($consultation['prochain_rdv'])) : ''; ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2E7D32] focus:border-transparent">
                    </div>

                    <!-- Boutons d'action -->
                    <div class="flex justify-end space-x-4">
                        <a href="voir_consultation.php?id=<?php echo $consultation_id; ?>" 
                           class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition-colors duration-300">
                            Annuler
                        </a>
                        <button type="submit" 
                                class="bg-[#2E7D32] hover:bg-[#1B5E20] text-white px-6 py-2 rounded-lg transition-colors duration-300">
                            <i class="fas fa-save mr-2"></i>Enregistrer
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        // Validation du formulaire
        document.querySelector('form').addEventListener('submit', function(e) {
            const requiredFields = ['motif', 'examen_clinique', 'diagnostic'];
            let isValid = true;

            requiredFields.forEach(field => {
                const element = document.getElementById(field);
                if (!element.value.trim()) {
                    element.classList.add('border-red-500');
                    isValid = false;
                } else {
                    element.classList.remove('border-red-500');
                }
            });

            if (!isValid) {
                e.preventDefault();
                alert('Veuillez remplir tous les champs obligatoires.');
            }
        });

        // Réinitialisation des styles de validation lors de la modification
        document.querySelectorAll('input, textarea, select').forEach(element => {
            element.addEventListener('input', function() {
                this.classList.remove('border-red-500');
            });
        });
    </script>
</body>
</html> 