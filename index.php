<?php
// Mode débogage forcé pour identifier l'erreur
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Définir le chemin racine pour les liens dans header et footer
$root_path = '';

// Point d'entrée principal de l'application
// La session sera démarrée dans le fichier session.php, ne pas la démarrer ici

// Afficher un message pour confirmer que le script commence son exécution
echo "<!-- Démarrage du script index.php -->";

try {
    // Inclusion des fichiers nécessaires avec vérification
    if (!file_exists('config/config.php')) {
        throw new Exception("Le fichier config.php est introuvable.");
    }
    require_once 'config/config.php';  
    
    if (!file_exists('includes/session.php')) {
        throw new Exception("Le fichier session.php est introuvable.");
    }
    require_once 'includes/session.php';
    
    echo "<!-- Fichiers chargés avec succès -->";
    
    // Redirection si l'utilisateur est déjà connecté
    if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
        // Préparation de la redirection, mais on l'exécutera après l'affichage de la page
        $redirect = true;
        $redirect_url = '';
        
        // Redirection en fonction du rôle de l'utilisateur
        switch ($_SESSION['role']) {
            case 'admin':
                $redirect_url = 'views/admin/dashboard.php';
                break;
            case 'medecin':
                $redirect_url = 'views/medecin/dashboard.php';
                break;
            case 'patient':
                $redirect_url = 'views/patient/dashboard.php';
                break;
            default:
                // En cas de rôle non reconnu, déconnexion et redirection vers la page de connexion
                session_destroy();
                $redirect_url = 'views/login.php';
                break;
        }
    } else {
        $redirect = false;
    }
} catch (Exception $e) {
    // Afficher l'erreur précise pour le débogage
    echo '<div style="background-color: #ffdddd; border: 1px solid #ff0000; padding: 10px; margin: 10px;">';
    echo '<h2>Erreur détectée :</h2>';
    echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<h3>Trace :</h3>';
    echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    
    // Information sur le chemin des fichiers
    echo '<h3>Informations système :</h3>';
    echo '<p>Chemin actuel : ' . getcwd() . '</p>';
    echo '<p>Chemin du fichier : ' . __FILE__ . '</p>';
    echo '<p>PHP version : ' . phpversion() . '</p>';
    
    if (function_exists('env')) {
        echo '<p>APP_ENV : ' . env('APP_ENV', 'non défini') . '</p>';
    } else {
        echo '<p>Fonction env() non disponible</p>';
    }
    
    echo '</div>';
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedConnect - Votre santé, notre priorité</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
        .hero-pattern {
            background-color: #F1F8E9;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%232E7D32' fill-opacity='0.1'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
        .service-card {
            @apply bg-white p-8 rounded-xl transition duration-300;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        .gradient-bg {
            background: linear-gradient(135deg, #2E7D32 0%, #1B5E20 100%);
        }
        .btn-primary {
            @apply bg-[#2E7D32] hover:bg-[#1B5E20] text-white px-6 py-3 rounded-lg transition-colors duration-300 flex items-center justify-center gap-2;
        }
        .btn-secondary {
            @apply bg-white hover:bg-gray-50 text-[#2E7D32] border-2 border-[#2E7D32] px-6 py-3 rounded-lg transition-colors duration-300 flex items-center justify-center gap-2;
        }
        .icon {
            @apply w-16 h-16 rounded-full bg-[#E8F5E9] flex items-center justify-center mb-6;
        }
        .icon i {
            @apply text-2xl text-[#2E7D32];
        }
    </style>
</head>
<body class="bg-gradient-to-br from-[#F1F8E9] to-[#E8F5E9] min-h-screen">
    <?php include_once 'views/components/header.php'; ?>

    <main class="flex-grow">
        <!-- Section héro -->
        <section class="hero-pattern py-20">
            <div class="container mx-auto px-4 flex flex-col md:flex-row items-center">
                <div class="md:w-1/2 mb-10 md:mb-0">
                    <h1 class="text-4xl md:text-5xl font-bold text-[#1B5E20] leading-tight mb-6">
                        Votre santé, <br><span class="text-[#2E7D32]">notre priorité</span>
                    </h1>
                    <p class="text-lg text-[#558B2F] mb-8">
                        MedConnect vous permet de consulter des médecins qualifiés en ligne, 
                        de prendre rendez-vous facilement et de suivre votre dossier médical en toute sécurité.
                    </p>
                    <div class="flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4">
                        <a href="views/register_patient.php" class="btn-primary">
                            <i class="fas fa-user-plus"></i>
                            Créer un compte patient
                        </a>
                        <a href="views/register_medecin.php" class="btn-secondary">
                            <i class="fas fa-user-md"></i>
                            Vous êtes médecin ?
                        </a>
                    </div>
                </div>
                <div class="md:w-1/2">
                    <img src="https://img.freepik.com/free-vector/online-doctor-consultation-illustration_88138-414.jpg" 
                         alt="Illustration MedConnect" 
                         class="w-full h-auto rounded-xl shadow-lg transform hover:scale-105 transition-transform duration-300">
                </div>
            </div>
        </section>

        <!-- Section services -->
        <section id="services" class="py-16">
            <div class="container mx-auto px-4">
                <div class="text-center mb-16">
                    <h2 class="text-3xl font-bold text-[#1B5E20] mb-4">Nos services</h2>
                    <p class="text-[#558B2F] max-w-2xl mx-auto">
                        Découvrez les services que nous proposons pour améliorer votre expérience de soins de santé.
                    </p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="service-card">
                        <div class="icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-[#1B5E20] mb-3">Rendez-vous en ligne</h3>
                        <p class="text-[#558B2F]">
                            Prenez rendez-vous avec votre médecin en quelques clics, sans attente téléphonique.
                        </p>
                    </div>
                    
                    <div class="service-card">
                        <div class="icon">
                            <i class="fas fa-video"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-[#1B5E20] mb-3">Téléconsultation</h3>
                        <p class="text-[#558B2F]">
                            Consultez un médecin depuis chez vous par vidéoconférence sécurisée.
                        </p>
                    </div>
                    
                    <div class="service-card">
                        <div class="icon">
                            <i class="fas fa-file-medical"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-[#1B5E20] mb-3">Dossier médical</h3>
                        <p class="text-[#558B2F]">
                            Accédez à votre dossier médical complet et partagez-le en toute sécurité avec vos médecins.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Section CTA -->
        <section class="py-16 gradient-bg">
            <div class="container mx-auto px-4 text-center">
                <h2 class="text-3xl font-bold text-white mb-6">Prêt à prendre soin de votre santé ?</h2>
                <p class="text-white/90 max-w-2xl mx-auto mb-8">
                    Inscrivez-vous gratuitement et commencez à utiliser MedConnect dès aujourd'hui.
                </p>
                <div class="flex flex-col sm:flex-row justify-center space-y-4 sm:space-y-0 sm:space-x-4">
                    <a href="views/register_patient.php" class="btn-primary">
                        <i class="fas fa-user-plus"></i>
                        Créer un compte
                    </a>
                    <a href="views/login.php" class="btn-secondary">
                        <i class="fas fa-sign-in-alt"></i>
                        Se connecter
                    </a>
                </div>
            </div>
        </section>
    </main>

    <?php include_once 'views/components/footer.php'; ?>

    <!-- Script JS -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const menuButton = document.querySelector('.md\\:hidden');
            const menuItems = document.querySelector('.md\\:flex');
            
            menuButton.addEventListener('click', function() {
                menuItems.classList.toggle('hidden');
            });
            
            <?php if(isset($redirect) && $redirect): ?>
            setTimeout(function() {
                window.location.href = '<?php echo $redirect_url; ?>';
            }, 100);
            <?php endif; ?>
        });
    </script>
</body>
</html> 