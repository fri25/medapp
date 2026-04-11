<?php
require_once '../../includes/session.php';
requireRole('medecin');
require_once '../config/database.php';
require_once '../models/ProfilMedecin.php';

$db = new Database();
$profilMedecin = new ProfilMedecin($db->getConnection());
$profil = $profilMedecin->getProfilByMedecinId($_SESSION['user_id']);

// Traiter le formulaire de soumission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier le token CSRF
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $_SESSION['error'] = "Erreur de sécurité : token CSRF invalide";
        header('Location: profil.php');
        exit();
    }

    $data = [
        'id_medecin' => $_SESSION['user_id'],
        'diplome' => $_FILES['diplome']['name'] ? $_FILES['diplome']['name'] : $profil['diplome'],
        'specialite' => $_POST['specialite'],
        'annees_experience' => $_POST['annees_experience'],
        'hopital_actuel' => $_POST['hopital_actuel'],
        'adresse_cabinet' => $_POST['adresse_cabinet'],
        'horaires_travail' => $_POST['horaires_travail']
    ];

    // Gérer l'upload du diplôme
    if ($_FILES['diplome']['name']) {
        $target_dir = "../uploads/diplomes/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_extension = strtolower(pathinfo($_FILES["diplome"]["name"], PATHINFO_EXTENSION));
        $new_filename = "diplome_" . $_SESSION['user_id'] . "_" . time() . "." . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        if (move_uploaded_file($_FILES["diplome"]["tmp_name"], $target_file)) {
            $data['diplome'] = $new_filename;
        }
    }

    if ($profil) {
        $profilMedecin->updateProfil($_SESSION['user_id'], $data);
    } else {
        $profilMedecin->createProfil($data);
    }
    
    header('Location: profil.php?success=1');
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Médecin - MedApp</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- En-tête -->
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <h1 class="text-3xl font-bold text-gray-900">Profil Médecin</h1>
            </div>
        </header>

        <!-- Message d'avertissement -->
        <?php if (isset($_SESSION['warning_message'])): ?>
        <div class="max-w-7xl mx-auto mt-4 px-4 sm:px-6 lg:px-8">
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700">
                            <?php echo $_SESSION['warning_message']; ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <?php unset($_SESSION['warning_message']); endif; ?>

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
                            Votre profil a été mis à jour avec succès.
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Formulaire -->
        <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <div class="px-4 py-6 sm:px-0">
                <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                    <form method="POST" enctype="multipart/form-data" class="space-y-8 divide-y divide-gray-200 p-6">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <div class="space-y-8 divide-y divide-gray-200">
                            <div>
                                <div>
                                    <h3 class="text-lg leading-6 font-medium text-gray-900">Informations professionnelles</h3>
                                    <p class="mt-1 text-sm text-gray-500">Ces informations seront utilisées pour vérifier votre compte.</p>
                                </div>

                                <div class="mt-6 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                                    <div class="sm:col-span-4">
                                        <label for="specialite" class="block text-sm font-medium text-gray-700">Spécialité</label>
                                        <div class="mt-1">
                                            <input type="text" name="specialite" id="specialite" value="<?php echo $profil['specialite'] ?? ''; ?>" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" required>
                                        </div>
                                    </div>

                                    <div class="sm:col-span-4">
                                        <label for="annees_experience" class="block text-sm font-medium text-gray-700">Années d'expérience</label>
                                        <div class="mt-1">
                                            <input type="number" name="annees_experience" id="annees_experience" value="<?php echo $profil['annees_experience'] ?? ''; ?>" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" required>
                                        </div>
                                    </div>

                                    <div class="sm:col-span-4">
                                        <label for="hopital_actuel" class="block text-sm font-medium text-gray-700">Hôpital actuel</label>
                                        <div class="mt-1">
                                            <input type="text" name="hopital_actuel" id="hopital_actuel" value="<?php echo $profil['hopital_actuel'] ?? ''; ?>" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" required>
                                        </div>
                                    </div>

                                    <div class="sm:col-span-4">
                                        <label for="adresse_cabinet" class="block text-sm font-medium text-gray-700">Adresse du cabinet</label>
                                        <div class="mt-1">
                                            <textarea name="adresse_cabinet" id="adresse_cabinet" rows="3" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" required><?php echo $profil['adresse_cabinet'] ?? ''; ?></textarea>
                                        </div>
                                    </div>

                                    <div class="sm:col-span-4">
                                        <label for="horaires_travail" class="block text-sm font-medium text-gray-700">Horaires de travail</label>
                                        <div class="mt-1">
                                            <textarea name="horaires_travail" id="horaires_travail" rows="3" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" required><?php echo $profil['horaires_travail'] ?? ''; ?></textarea>
                                        </div>
                                    </div>

                                    <div class="sm:col-span-4">
                                        <label for="diplome" class="block text-sm font-medium text-gray-700">Diplôme</label>
                                        <div class="mt-1">
                                            <input type="file" name="diplome" id="diplome" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300" accept=".pdf,.jpg,.jpeg,.png" <?php echo !isset($profil['diplome']) ? 'required' : ''; ?>>
                                            <?php if (isset($profil['diplome'])): ?>
                                            <p class="mt-2 text-sm text-gray-500">Diplôme actuel : <?php echo $profil['diplome']; ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="pt-5">
                            <div class="flex justify-end">
                                <button type="submit" class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Enregistrer
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html> 