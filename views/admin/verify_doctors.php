<?php
require_once '../../includes/session.php';
require_once '../../config/config.php';
require_once '../../models/Medecin.php';

// Vérifier si l'utilisateur est connecté et est un administrateur
requireLogin();
requireRole('admin');

$database = new Database();
$db = $database->getConnection();
$medecin = new Medecin($db);

// Traitement des actions de vérification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier le token CSRF
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $error = "Erreur de sécurité : token CSRF invalide";
    } else {
        if (isset($_POST['action']) && isset($_POST['medecin_id'])) {
            $medecin->id = $_POST['medecin_id'];
            
            if ($_POST['action'] === 'verify') {
                if ($medecin->updateVerificationStatus('verified')) {
                    // Envoyer un email de confirmation
                    $medecin->sendVerificationConfirmationEmail();
                    $success = "Le médecin a été vérifié avec succès.";
                } else {
                    $error = "Une erreur s'est produite lors de la vérification.";
                }
            } elseif ($_POST['action'] === 'reject') {
                if ($medecin->updateVerificationStatus('rejected')) {
                    // Envoyer un email de rejet
                    $medecin->sendRejectionEmail();
                    $success = "Le médecin a été rejeté.";
                } else {
                    $error = "Une erreur s'est produite lors du rejet.";
                }
            }
        }
    }
}

// Récupérer la liste des médecins en attente de vérification
$query = "SELECT m.*, s.nomspecialite 
          FROM medecin m 
          LEFT JOIN specialite s ON m.idspecialite = s.id 
          WHERE m.verification_status = 'pending' 
          ORDER BY m.id DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$pending_doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification des Médecins - MedConnect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-6">Vérification des Médecins</h1>
            
            <?php if (isset($success)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if (empty($pending_doctors)): ?>
                <div class="text-center py-8">
                    <i class="fas fa-check-circle text-green-500 text-5xl mb-4"></i>
                    <p class="text-gray-600">Aucun médecin en attente de vérification.</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Spécialité</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Numéro RPPS</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($pending_doctors as $doctor): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php echo htmlspecialchars($doctor['prenom'] . ' ' . $doctor['nom']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php echo htmlspecialchars($doctor['nomspecialite']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php echo htmlspecialchars($doctor['num']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php echo htmlspecialchars($doctor['email']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php echo htmlspecialchars($doctor['contact']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                            <input type="hidden" name="medecin_id" value="<?php echo $doctor['id']; ?>">
                                            <button type="submit" name="action" value="verify" 
                                                    class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded mr-2">
                                                <i class="fas fa-check"></i> Valider
                                            </button>
                                            <button type="submit" name="action" value="reject" 
                                                    class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded"
                                                    onclick="return confirm('Êtes-vous sûr de vouloir rejeter ce médecin ?')">
                                                <i class="fas fa-times"></i> Rejeter
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html> 