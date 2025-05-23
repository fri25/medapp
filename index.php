<?php
// Ajout des en-têtes de sécurité
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Content-Security-Policy: default-src 'self' https: 'unsafe-inline' 'unsafe-eval'; img-src 'self' https: data:;");

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
    
    if (!file_exists('includes/routing.php')) {
        throw new Exception("Le fichier routing.php est introuvable.");
    }
    require_once 'includes/routing.php';
    
    echo "<!-- Fichiers chargés avec succès -->";
    
    // Redirection si l'utilisateur est déjà connecté
    if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
        // Redirection vers le dashboard uniquement si on est sur la page d'accueil
        if ($_SERVER['REQUEST_URI'] === '/medapp/' || $_SERVER['REQUEST_URI'] === '/medapp/index.php') {
            $dashboard_url = getDashboardUrl();
            header('Location: ' . $dashboard_url);
            exit();
        }
    }
} catch (Exception $e) {
    // Journalisation de l'erreur
    error_log("Erreur dans index.php : " . $e->getMessage());
    
    // Afficher l'erreur précise pour le débogage
    echo '<div style="background-color: #ffdddd; border: 1px solid #ff0000; padding: 10px; margin: 10px;">';
    echo '<h2>Erreur détectée :</h2>';
    echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
    
    // En mode développement, afficher plus de détails
    if (env('APP_ENV') === 'development') {
        echo '<h3>Trace :</h3>';
        echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
        
        // Information sur le chemin des fichiers
        echo '<h3>Informations système :</h3>';
        echo '<p>Chemin actuel : ' . htmlspecialchars(getcwd()) . '</p>';
        echo '<p>Chemin du fichier : ' . htmlspecialchars(__FILE__) . '</p>';
        echo '<p>PHP version : ' . htmlspecialchars(phpversion()) . '</p>';
        
        if (function_exists('env')) {
            echo '<p>APP_ENV : ' . htmlspecialchars(env('APP_ENV', 'non défini')) . '</p>';
        } else {
            echo '<p>Fonction env() non disponible</p>';
        }
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
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/waypoints/4.0.1/jquery.waypoints.min.js"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .hero-pattern {
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            position: relative;
            overflow: hidden;
        }
        .hero-pattern::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%2322c55e' fill-opacity='0.1'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            opacity: 0.5;
        }
        .service-card {
            @apply bg-white p-10 rounded-[2rem] transition-all duration-300 relative overflow-hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        .service-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, #22c55e, #16a34a);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }
        .service-card:hover::before {
            transform: scaleX(1);
        }
        .gradient-bg {
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
        }
        .btn-primary {
            @apply text-[#22c55e] hover:text-[#16a34a] transition-all duration-300 flex items-center justify-center gap-3 font-medium text-lg;
        }
        .btn-secondary {
            @apply text-[#22c55e] hover:text-[#16a34a] transition-all duration-300 flex items-center justify-center gap-3 font-medium text-lg;
        }
        .icon {
            @apply w-24 h-24 rounded-[1.5rem] bg-[#dcfce7] flex items-center justify-center mb-8;
        }
        .icon i {
            @apply text-4xl text-[#22c55e];
        }
        .testimonial-card {
            @apply bg-white p-8 rounded-2xl shadow-lg;
        }
        .testimonial-avatar {
            @apply w-16 h-16 rounded-full object-cover border-4 border-[#22c55e];
        }
    </style>
</head>
<body class="bg-gradient-to-br from-[#f0fdf4] to-[#dcfce7] min-h-screen">
    <?php include_once 'views/components/header.php'; ?>

    <main class="flex-grow">
        <!-- Section héro -->
        <section class="hero-pattern py-32 relative z-10">
            <div class="container mx-auto px-6 flex flex-col-reverse lg:flex-row items-center justify-between">
                <!-- Texte -->
                <div class="lg:w-1/2 text-center lg:text-left" data-aos="fade-right">
                    <h1 class="text-5xl md:text-6xl font-extrabold leading-tight text-[#14532d] mb-6">
                        Votre santé,<br>
                        <span class="text-[#22c55e]">notre priorité</span>
                    </h1>
                    <p class="text-xl text-[#166534] mb-8 leading-relaxed max-w-xl">
                        MedConnect vous permet de consulter des médecins qualifiés en ligne, 
                        de prendre rendez-vous facilement et de suivre votre dossier médical en toute sécurité.
                    </p>

                    <div class="flex flex-col sm:flex-row sm:space-x-6 space-y-4 sm:space-y-0 justify-center lg:justify-start">
                        <a href="#services" class="bg-[#22c55e] text-white px-8 py-4 rounded-full text-lg font-semibold shadow-md hover:bg-[#16a34a] transition">
                            Découvrez nos services
                        </a>
                        <a href="register_medecin.php" class="text-[#22c55e] border border-[#22c55e] px-8 py-4 rounded-full text-lg font-semibold hover:bg-[#dcfce7] transition">
                            Connectez-vous
                        </a>
                    </div>
                </div>

                <!-- Image -->
                <div class="lg:w-1/2 mb-10 lg:mb-0" data-aos="fade-left">
                    <img src="./assets/images/home.jpg" 
                        alt="Équipe médicale illustration" 
                        class="w-full max-w-lg mx-auto h-auto rounded-3xl shadow-2xl transform hover:scale-105 transition-transform duration-500">
                </div>
            </div>
        </section>

        <!-- Section statistiques -->
        <section class="py-12 bg-white">
            <div class="container mx-auto px-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center">
                    <div class="p-6">
                        <div class="text-4xl font-bold text-[#22c55e] mb-2">
                            <span class="counter" data-target="1500">0</span>+
                        </div>
                        <div class="text-[#166534]">Médecins qualifiés</div>
                    </div>
                    <div class="p-6">
                        <div class="text-4xl font-bold text-[#22c55e] mb-2">
                            <span class="counter" data-target="20000">0</span>+
                        </div>
                        <div class="text-[#166534]">Patients satisfaits</div>
                    </div>
                    <div class="p-6">
                        <div class="text-4xl font-bold text-[#22c55e] mb-2">
                            <span class="counter" data-target="30">0</span>+
                        </div>
                        <div class="text-[#166534]">Spécialités médicales</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Section services -->
        <section id="services" class="py-24 bg-gradient-to-b from-white via-[#f0fdf4] to-[#dcfce7]">
            <div class="container mx-auto px-4 max-w-7xl">
                <div class="text-center mb-20">
                    <h2 class="text-4xl font-bold text-[#166534] mb-6">Une expérience médicale révolutionnaire</h2>
                    <p class="text-xl text-[#166534] max-w-3xl mx-auto">
                        Découvrez comment nous transformons votre parcours de soins avec des solutions innovantes et personnalisées.
                    </p>
                    <hr class="border-t-4 border-[#22c55e] w-24 mx-auto mt-8 rounded-full">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-12 max-w-6xl mx-auto">
                    <!-- Carte 1 -->
                    <div class="service-card p-8 bg-white rounded-2xl shadow-lg hover:shadow-2xl transition-shadow duration-300">
                        <div class="icon text-4xl text-[#22c55e] mb-6 text-center animate-pulse">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <h3 class="text-2xl font-semibold text-[#166534] mb-4 text-center">Consultations intelligentes</h3>
                        <p class="text-[#166534] text-lg leading-relaxed text-center">
                            Planifiez vos rendez-vous en quelques clics avec notre système de prise de rendez-vous intelligent qui s'adapte à vos disponibilités.
                        </p>
                        <ul class="mt-6 space-y-3">
                            <li class="flex items-center text-[#166534]">
                                <i class="fas fa-check-circle mr-3 text-[#22c55e]"></i>
                                Disponible 24h/24
                            </li>
                            <li class="flex items-center text-[#166534]">
                                <i class="fas fa-check-circle mr-3 text-[#22c55e]"></i>
                                Rappels automatiques
                            </li>
                        </ul>
                    </div>

                    <!-- Carte 2 -->
                    <div class="service-card p-8 bg-white rounded-2xl shadow-lg hover:shadow-2xl transition-shadow duration-300">
                        <div class="icon text-4xl text-[#22c55e] mb-6 text-center animate-pulse">
                            <i class="fas fa-video"></i>
                        </div>
                        <h3 class="text-2xl font-semibold text-[#166534] mb-4 text-center">Téléconsultation premium</h3>
                        <p class="text-[#166534] text-lg leading-relaxed text-center">
                            Consultez des médecins qualifiés depuis chez vous avec une expérience de téléconsultation haute qualité et sécurisée.
                        </p>
                        <ul class="mt-6 space-y-3">
                            <li class="flex items-center text-[#166534]">
                                <i class="fas fa-check-circle mr-3 text-[#22c55e]"></i>
                                Vidéo HD sécurisée
                            </li>
                            <li class="flex items-center text-[#166534]">
                                <i class="fas fa-check-circle mr-3 text-[#22c55e]"></i>
                                Support technique 24/7
                            </li>
                        </ul>
                    </div>

                    <!-- Carte 3 -->
                    <div class="service-card p-8 bg-white rounded-2xl shadow-lg hover:shadow-2xl transition-shadow duration-300">
                        <div class="icon text-4xl text-[#22c55e] mb-6 text-center animate-pulse">
                            <i class="fas fa-file-medical"></i>
                        </div>
                        <h3 class="text-2xl font-semibold text-[#166534] mb-4 text-center">Dossier médical digital</h3>
                        <p class="text-[#166534] text-lg leading-relaxed text-center">
                            Accédez à votre dossier médical complet et partagez-le en toute sécurité avec vos médecins.
                        </p>
                        <ul class="mt-6 space-y-3">
                            <li class="flex items-center text-[#166534]">
                                <i class="fas fa-check-circle mr-3 text-[#22c55e]"></i>
                                Historique complet
                            </li>
                            <li class="flex items-center text-[#166534]">
                                <i class="fas fa-check-circle mr-3 text-[#22c55e]"></i>
                                Partage sécurisé
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- Section médecins -->
        <section id="doctors" class="py-24 bg-white">
            <div class="container mx-auto px-4">
                <div class="text-center mb-20">
                    <h2 class="text-4xl font-bold text-[#166534] mb-6">Nos médecins experts</h2>
                    <p class="text-xl text-[#166534] max-w-3xl mx-auto">
                        Une équipe de médecins qualifiés dans différentes spécialités, prêts à vous accompagner.
                    </p>
                    <hr class="border-t-4 border-[#22c55e] w-24 mx-auto mt-8 rounded-full">
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                    <!-- Médecin 1 -->
                    <div class="bg-white rounded-2xl overflow-hidden shadow-lg hover:shadow-2xl transition-shadow duration-300">
                        <img src="https://randomuser.me/api/portraits/women/44.jpg" alt="Dr. Emilie Laurent" class="w-full h-64 object-cover">
                        <div class="p-6">
                            <h3 class="text-xl font-semibold text-[#166534] mb-1">Dr. Emilie Laurent</h3>
                            <p class="text-[#22c55e] mb-3">Cardiologue</p>
                            <div class="flex items-center mb-3">
                                <div class="text-yellow-400 flex">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star-half-alt"></i>
                                </div>
                                <span class="text-[#166534] ml-2">4.8/5</span>
                            </div>
                            <button class="w-full py-3 bg-[#22c55e] text-white rounded-full hover:bg-[#16a34a] transition duration-300">
                                Voir le profil
                            </button>
                        </div>
                    </div>
                    
                    <!-- Médecin 2 -->
                    <div class="bg-white rounded-2xl overflow-hidden shadow-lg hover:shadow-2xl transition-shadow duration-300">
                        <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Dr. Thomas Petit" class="w-full h-64 object-cover">
                        <div class="p-6">
                            <h3 class="text-xl font-semibold text-[#166534] mb-1">Dr. Thomas Petit</h3>
                            <p class="text-[#22c55e] mb-3">Dermatologue</p>
                            <div class="flex items-center mb-3">
                                <div class="text-yellow-400 flex">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                </div>
                                <span class="text-[#166534] ml-2">5.0/5</span>
                            </div>
                            <button class="w-full py-3 bg-[#22c55e] text-white rounded-full hover:bg-[#16a34a] transition duration-300">
                                Voir le profil
                            </button>
                        </div>
                    </div>
                    
                    <!-- Médecin 3 -->
                    <div class="bg-white rounded-2xl overflow-hidden shadow-lg hover:shadow-2xl transition-shadow duration-300">
                        <img src="https://randomuser.me/api/portraits/women/68.jpg" alt="Dr. Sophie Martin" class="w-full h-64 object-cover">
                        <div class="p-6">
                            <h3 class="text-xl font-semibold text-[#166534] mb-1">Dr. Sophie Martin</h3>
                            <p class="text-[#22c55e] mb-3">Pédiatre</p>
                            <div class="flex items-center mb-3">
                                <div class="text-yellow-400 flex">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="far fa-star"></i>
                                </div>
                                <span class="text-[#166534] ml-2">4.1/5</span>
                            </div>
                            <button class="w-full py-3 bg-[#22c55e] text-white rounded-full hover:bg-[#16a34a] transition duration-300">
                                Voir le profil
                            </button>
                        </div>
                    </div>
                    
                    <!-- Médecin 4 -->
                    <div class="bg-white rounded-2xl overflow-hidden shadow-lg hover:shadow-2xl transition-shadow duration-300">
                        <img src="https://randomuser.me/api/portraits/men/86.jpg" alt="Dr. Nicolas Dubois" class="w-full h-64 object-cover">
                        <div class="p-6">
                            <h3 class="text-xl font-semibold text-[#166534] mb-1">Dr. Nicolas Dubois</h3>
                            <p class="text-[#22c55e] mb-3">Généraliste</p>
                            <div class="flex items-center mb-3">
                                <div class="text-yellow-400 flex">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star-half-alt"></i>
                                </div>
                                <span class="text-[#166534] ml-2">4.7/5</span>
                            </div>
                            <button class="w-full py-3 bg-[#22c55e] text-white rounded-full hover:bg-[#16a34a] transition duration-300">
                                Voir le profil
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Section témoignages -->
        <section id="testimonials" class="py-24 bg-white">
            <div class="container mx-auto px-4">
                <div class="text-center mb-20">
                    <h2 class="text-4xl font-bold text-[#166534] mb-6">Ce que disent nos patients</h2>
                    <p class="text-xl text-[#166534] max-w-3xl mx-auto">
                        Découvrez les expériences de nos patients avec MedConnect.
                    </p>
                    <hr class="border-t-4 border-[#22c55e] w-24 mx-auto mt-8 rounded-full">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
                    <!-- Témoignage 1 -->
                    <div class="bg-white rounded-2xl shadow-lg p-8 hover:shadow-xl transition-shadow duration-300">
                        <div class="flex items-center mb-6">
                            <img src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=150&h=150&fit=crop" 
                                alt="Kossiwa Mensah" 
                                class="w-16 h-16 rounded-full object-cover border-4 border-[#22c55e]">
                            <div class="ml-4">
                                <h4 class="text-lg font-semibold text-[#166534]">Kossiwa Mensah</h4>
                                <p class="text-[#166534]">Patient, Cotonou</p>
                            </div>
                        </div>
                        <div class="flex mb-4">
                            <i class="fas fa-star text-yellow-400"></i>
                            <i class="fas fa-star text-yellow-400"></i>
                            <i class="fas fa-star text-yellow-400"></i>
                            <i class="fas fa-star text-yellow-400"></i>
                            <i class="fas fa-star text-yellow-400"></i>
                        </div>
                        <p class="text-[#166534] text-lg italic">
                            "MedConnect a révolutionné ma façon de consulter. Plus besoin de me déplacer, je peux prendre rendez-vous en quelques clics. Le service est excellent et les médecins très professionnels."
                        </p>
                    </div>

                    <!-- Témoignage 2 -->
                    <div class="bg-white rounded-2xl shadow-lg p-8 hover:shadow-xl transition-shadow duration-300">
                        <div class="flex items-center mb-6">
                            <img src="https://images.unsplash.com/photo-1580489944761-15a19d654956?w=150&h=150&fit=crop" 
                                alt="Aminata Diallo" 
                                class="w-16 h-16 rounded-full object-cover border-4 border-[#22c55e]">
                            <div class="ml-4">
                                <h4 class="text-lg font-semibold text-[#166534]">Aminata Diallo</h4>
                                <p class="text-[#166534]">Patient, Porto-Novo</p>
                            </div>
                        </div>
                        <div class="flex mb-4">
                            <i class="fas fa-star text-yellow-400"></i>
                            <i class="fas fa-star text-yellow-400"></i>
                            <i class="fas fa-star text-yellow-400"></i>
                            <i class="fas fa-star text-yellow-400"></i>
                            <i class="fas fa-star-half-alt text-yellow-400"></i>
                        </div>
                        <p class="text-[#166534] text-lg italic">
                            "La téléconsultation est vraiment pratique. J'ai pu consulter un spécialiste sans avoir à me déplacer. Le suivi est excellent et les médecins sont très à l'écoute."
                        </p>
                    </div>

                    <!-- Témoignage 3 -->
                    <div class="bg-white rounded-2xl shadow-lg p-8 hover:shadow-xl transition-shadow duration-300">
                        <div class="flex items-center mb-6">
                            <img src="https://images.unsplash.com/photo-1560250097-0b93528c311a?w=150&h=150&fit=crop" 
                                alt="Kofi Agbeko" 
                                class="w-16 h-16 rounded-full object-cover border-4 border-[#22c55e]">
                            <div class="ml-4">
                                <h4 class="text-lg font-semibold text-[#166534]">Kofi Agbeko</h4>
                                <p class="text-[#166534]">Patient, Abomey-Calavi</p>
                            </div>
                        </div>
                        <div class="flex mb-4">
                            <i class="fas fa-star text-yellow-400"></i>
                            <i class="fas fa-star text-yellow-400"></i>
                            <i class="fas fa-star text-yellow-400"></i>
                            <i class="fas fa-star text-yellow-400"></i>
                            <i class="fas fa-star text-yellow-400"></i>
                        </div>
                        <p class="text-[#166534] text-lg italic">
                            "Le suivi de mon dossier médical est beaucoup plus simple. Je peux accéder à mes résultats et prescriptions en un clic. La plateforme est vraiment bien pensée."
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Section FAQ -->
        <section id="faq" class="py-24 bg-gradient-to-b from-white via-[#f0fdf4] to-[#dcfce7]">
            <div class="container mx-auto px-4 max-w-4xl">
                <div class="text-center mb-20">
                    <h2 class="text-4xl font-bold text-[#166534] mb-6">Questions fréquentes</h2>
                    <p class="text-xl text-[#166534] max-w-3xl mx-auto">
                        Retrouvez les réponses aux questions les plus courantes sur nos services.
                    </p>
                    <hr class="border-t-4 border-[#22c55e] w-24 mx-auto mt-8 rounded-full">
                </div>

                <div class="space-y-6">
                    <!-- Question 1 -->
                    <div class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl transition-shadow duration-300">
                        <button class="flex justify-between items-center w-full text-left" onclick="toggleFAQ(this)">
                            <h3 class="text-xl font-semibold text-[#166534]">Comment prendre rendez-vous avec un médecin ?</h3>
                            <i class="fas fa-chevron-down text-[#22c55e] transition-transform duration-300"></i>
                        </button>
                        <div class="mt-4 text-[#166534] hidden">
                            <p>Pour prendre rendez-vous, il suffit de créer un compte patient, de choisir un médecin parmi notre réseau et de sélectionner un créneau disponible. Notre système de prise de rendez-vous est disponible 24h/24 et 7j/7.</p>
                        </div>
                    </div>


                    <!-- Question 3 -->
                    <div class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl transition-shadow duration-300">
                        <button class="flex justify-between items-center w-full text-left" onclick="toggleFAQ(this)">
                            <h3 class="text-xl font-semibold text-[#166534]">Les consultations sont-elles remboursées ?</h3>
                            <i class="fas fa-chevron-down text-[#22c55e] transition-transform duration-300"></i>
                        </button>
                        <div class="mt-4 text-[#166534] hidden">
                            <p>Oui, les consultations sont remboursées par l'Assurance Maladie dans les mêmes conditions qu'une consultation en cabinet. Nous vous fournissons une feuille de soins électronique que vous pouvez transmettre à votre mutuelle pour le remboursement complémentaire.</p>
                        </div>
                    </div>

                    <!-- Question 4 -->
                    <div class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl transition-shadow duration-300">
                        <button class="flex justify-between items-center w-full text-left" onclick="toggleFAQ(this)">
                            <h3 class="text-xl font-semibold text-[#166534]">Comment accéder à mon dossier médical ?</h3>
                            <i class="fas fa-chevron-down text-[#22c55e] transition-transform duration-300"></i>
                        </button>
                        <div class="mt-4 text-[#166534] hidden">
                            <p>Votre dossier médical est accessible depuis votre espace patient après connexion. Vous y trouverez l'historique de vos consultations, vos ordonnances, vos résultats d'analyses et tous vos documents médicaux partagés par vos médecins.</p>
                        </div>
                    </div>

                    <!-- Question 5 -->
                    <div class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl transition-shadow duration-300">
                        <button class="flex justify-between items-center w-full text-left" onclick="toggleFAQ(this)">
                            <h3 class="text-xl font-semibold text-[#166534]">Comment devenir médecin sur MedConnect ?</h3>
                            <i class="fas fa-chevron-down text-[#22c55e] transition-transform duration-300"></i>
                        </button>
                        <div class="mt-4 text-[#166534] hidden">
                            <p>Pour rejoindre notre réseau de médecins, créez un compte médecin et fournissez les documents nécessaires (diplômes, numéro proffesionnel.). Notre équipe vérifiera vos informations et vous accompagnera dans la mise en place de votre espace de consultation.</p>
                        </div>
                    </div>
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

        // Fonction pour gérer l'accordéon FAQ
        function toggleFAQ(button) {
            const content = button.nextElementSibling;
            const icon = button.querySelector('i');
            
            // Toggle la classe hidden
            content.classList.toggle('hidden');
            
            // Rotation de l'icône
            icon.style.transform = content.classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(180deg)';
        }
    </script>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 1000,
            once: true
        });

        // Fonction pour formater les nombres avec des espaces
        function formatNumber(number) {
            return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, " ");
        }

        // Fonction pour animer les compteurs
        function animateCounter(element) {
            const target = parseInt(element.getAttribute('data-target'));
            const duration = 2000; // 2 secondes
            const step = target / (duration / 16); // 60fps
            let current = 0;

            const timer = setInterval(() => {
                current += step;
                if (current >= target) {
                    element.textContent = formatNumber(target);
                    clearInterval(timer);
                } else {
                    element.textContent = formatNumber(Math.floor(current));
                }
            }, 16);
        }

        // Initialiser les compteurs avec Waypoints
        $('.counter').each(function() {
            const element = this;
            new Waypoint({
                element: element,
                handler: function() {
                    animateCounter(element);
                    this.destroy(); // Ne déclencher qu'une seule fois
                },
                offset: '90%'
            });
        });

        // Fonction pour gérer l'accordéon FAQ
        function toggleFAQ(button) {
            const content = button.nextElementSibling;
            const icon = button.querySelector('i');
            
            // Toggle la classe hidden
            content.classList.toggle('hidden');
            
            // Rotation de l'icône
            icon.style.transform = content.classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(180deg)';
        }
    </script>

</body>
</html> 