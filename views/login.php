<?php
require_once '../controllers/Auth.php';

// Traitement du formulaire de connexion
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $auth = new Auth();
    
    // Récupérer les données du formulaire
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    // Tenter la connexion
    if ($auth->login($email, $password)) {
        // Récupérer le rôle depuis la session
        session_start();
        $role = $_SESSION['role'];
        
        // Rediriger vers la page appropriée selon le rôle
        switch ($role) {
            case 'admin':
                header("Location: admin/dashboard.php");
                break;
            case 'medecin':
                header("Location: medecin/dashboard.php");
                break;
            case 'patient':
                header("Location: patient/dashboard.php");
                break;
            default:
                header("Location: index.php");
                break;
        }
        exit;
    } else {
        $message = "Email ou mot de passe incorrect. Veuillez réessayer.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - MedConnect</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8 max-w-md">
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="bg-blue-600 text-white px-6 py-4">
                <h3 class="text-xl font-semibold">Connexion</h3>
            </div>
            <div class="p-6">
                <?php if (isset($message)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_GET['registered']) && $_GET['registered'] == 'success'): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        Inscription réussie ! Vous pouvez maintenant vous connecter.
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_GET['password_reset']) && $_GET['password_reset'] == 'success'): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        Votre mot de passe a été réinitialisé avec succès. Vous pouvez maintenant vous connecter.
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <div class="mb-4">
                        <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email</label>
                        <input type="email" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="email" name="email" required>
                    </div>
                    <div class="mb-6">
                        <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Mot de passe</label>
                        <input type="password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="password" name="password" required>
                    </div>
                    <div class="mb-4">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded w-full focus:outline-none focus:shadow-outline">Se connecter</button>
                    </div>
                </form>
                <div class="text-center mt-4">
                    <p class="text-sm text-gray-600 mb-2"><a href="forgot_password.php" class="text-blue-600 hover:text-blue-800">Mot de passe oublié?</a></p>
                    <p class="text-sm text-gray-600 mb-2">Vous n'avez pas de compte? <a href="register_patient.php" class="text-blue-600 hover:text-blue-800">Inscrivez-vous ici</a></p>
                    <p class="text-sm text-gray-600">Vous êtes médecin? <a href="register_medecin.php" class="text-blue-600 hover:text-blue-800">Inscrivez-vous en tant que médecin</a></p>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 