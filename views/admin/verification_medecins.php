<?php
require_once '../../includes/session.php';
requireRole('admin');
require_once '../config/database.php';
require_once '../models/ProfilMedecin.php';

$db = new Database();
$profilMedecin = new ProfilMedecin($db->getConnection());

// Traiter les actions de vérification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_medecin = $_POST['id_medecin'];
    $action = $_POST['action'];
    $commentaire = $_POST['commentaire'] ?? null;

    if ($action === 'verify') {
        $profilMedecin->updateVerificationStatus($id_medecin, 'verified', $commentaire);
    } elseif ($action === 'reject') {
        $profilMedecin->updateVerificationStatus($id_medecin, 'rejected', $commentaire);
    }

    header('Location: verification_medecins.php?success=1');
    exit();
}

// Récupérer tous les profils médecins en attente de vérification
$query = "SELECT m.*, p.* FROM medecin m 
          LEFT JOIN profilmedecin p ON m.id = p.id_medecin 
          WHERE m.verification_status = 'pending' 
          ORDER BY p.created_at DESC";
$stmt = $db->getConnection()->prepare($query);
$stmt->execute();
$medecins = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification des Médecins - MedApp</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- En-tête -->
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <h1 class="text-3xl font-bold text-gray-900">Vérification des Médecins</h1>
            </div>
        </header>

        <!-- Message de succès -->
        <?php if (isset($_GET['success'])): ?>
        <div class="max-w-7xl mx-auto mt-4 px-4 sm:px-6 lg:px-8">
            <div class="bg-green-50 border-l-4 border-green-400 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-green-700">
                            L'action a été effectuée avec succès.
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Liste des médecins -->
        <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <div class="px-4 py-6 sm:px-0">
                <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                    <div class="px-4 py-5 sm:px-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Médecins en attente de vérification</h3>
                        <p class="mt-1 max-w-2xl text-sm text-gray-500">Vérifiez les informations et les diplômes des médecins.</p>
                    </div>
                    <div class="border-t border-gray-200">
                        <div class="bg-white shadow overflow-hidden sm:rounded-md">
                            <ul class="divide-y divide-gray-200">
                                <?php foreach ($medecins as $medecin): ?>
                                <li>
                                    <div class="px-4 py-4 sm:px-6">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0">
                                                    <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                    </svg>
                                                </div>
                                                <div class="ml-4">
                                                    <h4 class="text-lg font-medium text-gray-900"><?php echo htmlspecialchars($medecin['nom'] . ' ' . $medecin['prenom']); ?></h4>
                                                    <p class="text-sm text-gray-500"><?php echo htmlspecialchars($medecin['email']); ?></p>
                                                </div>
                                            </div>
                                            <div class="flex space-x-2">
                                                <button onclick="showVerificationModal(<?php echo $medecin['id']; ?>, 'verify')" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                                    Vérifier
                                                </button>
                                                <button onclick="showVerificationModal(<?php echo $medecin['id']; ?>, 'reject')" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                    Rejeter
                                                </button>
                                            </div>
                                        </div>
                                        <div class="mt-4">
                                            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                                                <div class="sm:col-span-1">
                                                    <dt class="text-sm font-medium text-gray-500">Spécialité</dt>
                                                    <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($medecin['specialite'] ?? 'Non spécifiée'); ?></dd>
                                                </div>
                                                <div class="sm:col-span-1">
                                                    <dt class="text-sm font-medium text-gray-500">Années d'expérience</dt>
                                                    <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($medecin['annees_experience'] ?? 'Non spécifiée'); ?></dd>
                                                </div>
                                                <div class="sm:col-span-1">
                                                    <dt class="text-sm font-medium text-gray-500">Hôpital actuel</dt>
                                                    <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($medecin['hopital_actuel'] ?? 'Non spécifié'); ?></dd>
                                                </div>
                                                <div class="sm:col-span-1">
                                                    <dt class="text-sm font-medium text-gray-500">Adresse du cabinet</dt>
                                                    <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($medecin['adresse_cabinet'] ?? 'Non spécifiée'); ?></dd>
                                                </div>
                                                <div class="sm:col-span-2">
                                                    <dt class="text-sm font-medium text-gray-500">Horaires de travail</dt>
                                                    <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($medecin['horaires_travail'] ?? 'Non spécifiés'); ?></dd>
                                                </div>
                                                <div class="sm:col-span-2">
                                                    <dt class="text-sm font-medium text-gray-500">Diplôme</dt>
                                                    <dd class="mt-1 text-sm text-gray-900">
                                                        <?php if (isset($medecin['diplome'])): ?>
                                                        <a href="../uploads/diplomes/<?php echo htmlspecialchars($medecin['diplome']); ?>" target="_blank" class="text-indigo-600 hover:text-indigo-900">
                                                            Voir le diplôme
                                                        </a>
                                                        <?php else: ?>
                                                        Non fourni
                                                        <?php endif; ?>
                                                    </dd>
                                                </div>
                                            </dl>
                                        </div>
                                    </div>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal de vérification -->
    <div id="verificationModal" class="fixed z-10 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <form id="verificationForm" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <input type="hidden" name="id_medecin" id="medecinId">
                    <input type="hidden" name="action" id="actionType">
                    <div>
                        <div class="mt-3 text-center sm:mt-5">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Confirmation de vérification
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500" id="modalDescription">
                                    Êtes-vous sûr de vouloir <span id="actionText"></span> ce médecin ?
                                </p>
                            </div>
                            <div class="mt-4">
                                <label for="commentaire" class="block text-sm font-medium text-gray-700">Commentaire (optionnel)</label>
                                <textarea name="commentaire" id="commentaire" rows="3" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:col-start-2 sm:text-sm">
                            Confirmer
                        </button>
                        <button type="button" onclick="hideVerificationModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:col-start-1 sm:text-sm">
                            Annuler
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function showVerificationModal(medecinId, action) {
            document.getElementById('medecinId').value = medecinId;
            document.getElementById('actionType').value = action;
            document.getElementById('actionText').textContent = action === 'verify' ? 'vérifier' : 'rejeter';
            document.getElementById('verificationModal').classList.remove('hidden');
        }

        function hideVerificationModal() {
            document.getElementById('verificationModal').classList.add('hidden');
        }
    </script>
</body>
</html> 