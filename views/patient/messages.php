<?php
require_once '../../config/database.php';
require_once '../../models/Message.php';
require_once '../../includes/session.php';

// Vérifier si l'utilisateur est connecté et est un patient
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    header("Location: ../login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();
$message = new Message($db);

// Récupérer la liste des médecins
$query = "SELECT m.id, m.nom, m.prenom, pm.profession as specialite 
          FROM medecin m 
          LEFT JOIN profilmedecin pm ON m.id = pm.idmedecin";
$stmt = $db->prepare($query);
$stmt->execute();
$medecins = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Traitement de l'envoi de message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['envoyer'])) {
    $message->contenu = $_POST['contenu'];
    $message->sender_id = $_SESSION['user_id'];
    $message->receiver_id = $_POST['receiver_id'];
    $message->sender_type = 'patient';

    if ($message->envoyer()) {
        $success = "Message envoyé avec succès !";
    } else {
        $error = "Erreur lors de l'envoi du message.";
    }
}

// Récupérer les messages si un médecin est sélectionné
$conversation = null;
if (isset($_GET['medecin_id'])) {
    $stmt = $message->getConversation($_SESSION['user_id'], $_GET['medecin_id']);
    $conversation = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messagerie - MedConnect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f0f9f5;
        }
        .nav-link {
            transition: all 0.3s ease;
        }
        .nav-link:hover {
            background-color: rgba(59, 130, 246, 0.1);
            transform: translateX(5px);
        }
        .nav-link.active {
            background-color: rgba(59, 130, 246, 0.2);
            border-left: 4px solid #3b82f6;
        }
        .glass-effect {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .message-input {
            transition: all 0.3s ease;
        }
        .message-input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
        }
        .message-bubble {
            transition: all 0.3s ease;
        }
        .message-bubble:hover {
            transform: translateY(-2px);
        }
        .doctor-item {
            transition: all 0.3s ease;
        }
        .doctor-item:hover {
            background-color: rgba(59, 130, 246, 0.1);
        }
        .doctor-item.active {
            background-color: rgba(59, 130, 246, 0.2);
            border-left: 4px solid #3b82f6;
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
                <a href="listes_pharmacie.php" class="nav-link block px-4 py-3 rounded-lg text-[#1e40af]">
                    <i class="fas fa-pills mr-3"></i>Ma Pharmacie
                </a>
                <a href="messages.php" class="nav-link active block px-4 py-3 rounded-lg text-[#1e40af]">
                    <i class="fas fa-envelope mr-3"></i>Messages
                </a>
                <a href="profile_patient.php" class="nav-link block px-4 py-3 rounded-lg text-[#1e40af]">
                    <i class="fas fa-user mr-3"></i>Mon Profil
                </a>
            </nav>
            <div class="mt-6">
                <a href="../logout.php" class="block bg-[#FF5252] hover:bg-[#D32F2F] text-white text-center px-4 py-3 rounded-lg transition-colors duration-300">
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
                        <h1 class="text-2xl font-bold text-[#1e40af]">Messagerie</h1>
                    </div>
                    <div class="text-sm text-[#3b82f6]">
                        <i class="fas fa-calendar-alt mr-2"></i><?php echo date('d/m/Y'); ?>
                    </div>
                </div>
            </header>

            <!-- Contenu principal -->
            <main class="container mx-auto px-4 py-8">
                <?php if (isset($success)): ?>
                    <div class="bg-[#DCFCE7] text-[#065f46] px-4 py-3 rounded-lg mb-6 flex items-center">
                        <i class="fas fa-check-circle mr-2"></i>
                        <?php echo $success; ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($error)): ?>
                    <div class="bg-[#FEE2E2] text-[#991B1B] px-4 py-3 rounded-lg mb-6 flex items-center">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <div class="bg-white rounded-xl shadow-lg overflow-hidden glass-effect">
                    <div class="grid grid-cols-12 h-[calc(100vh-12rem)]">
                        <!-- Liste des médecins -->
                        <div class="col-span-4 border-r border-gray-200">
                            <div class="p-4 bg-[#F8FAFC]">
                                <h2 class="text-lg font-semibold text-[#1e40af] flex items-center">
                                    <i class="fas fa-user-md mr-2"></i>
                                    Médecins
                                </h2>
                            </div>
                            <div class="overflow-y-auto h-[calc(100%-4rem)]">
                                <?php foreach ($medecins as $medecin): ?>
                                    <a href="?medecin_id=<?php echo $medecin['id']; ?>" 
                                       class="doctor-item block p-4 border-b border-gray-100 <?php echo (isset($_GET['medecin_id']) && $_GET['medecin_id'] == $medecin['id']) ? 'active' : ''; ?>">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-[#3b82f6] to-[#60a5fa] flex items-center justify-center">
                                                    <i class="fas fa-user-md text-white"></i>
                                                </div>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm font-medium text-[#1e40af]">
                                                    Dr. <?php echo htmlspecialchars($medecin['nom'] . ' ' . $medecin['prenom']); ?>
                                                </p>
                                                <p class="text-xs text-[#3b82f6]">
                                                    <?php echo htmlspecialchars($medecin['specialite']); ?>
                                                </p>
                                            </div>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Zone de conversation -->
                        <div class="col-span-8 flex flex-col">
                            <?php if (isset($_GET['medecin_id'])): ?>
                                <!-- Messages -->
                                <div class="flex-1 overflow-y-auto p-6 bg-[#F8FAFC]">
                                    <?php if ($conversation): ?>
                                        <?php foreach ($conversation as $msg): ?>
                                            <div class="mb-4 <?php echo $msg['sender_id'] == $_SESSION['user_id'] ? 'text-right' : ''; ?>">
                                                <div class="message-bubble inline-block max-w-[70%] rounded-lg p-4 <?php echo $msg['sender_id'] == $_SESSION['user_id'] ? 'bg-[#3b82f6] text-white' : 'bg-white text-[#1e40af] shadow-sm'; ?>">
                                                    <p class="text-sm"><?php echo htmlspecialchars($msg['contenu']); ?></p>
                                                    <p class="text-xs mt-2 <?php echo $msg['sender_id'] == $_SESSION['user_id'] ? 'text-blue-100' : 'text-gray-500'; ?>">
                                                        <?php echo date('d/m/Y H:i', strtotime($msg['date_envoi'])); ?>
                                                    </p>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="flex items-center justify-center h-full text-[#3b82f6]">
                                            <div class="text-center">
                                                <i class="fas fa-comments text-4xl mb-2"></i>
                                                <p>Aucun message dans cette conversation.</p>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Formulaire d'envoi -->
                                <div class="border-t border-gray-200 p-4 bg-white">
                                    <form method="POST" class="flex gap-3">
                                        <input type="hidden" name="receiver_id" value="<?php echo $_GET['medecin_id']; ?>">
                                        <textarea name="contenu" rows="1" 
                                                  class="message-input flex-1 border border-gray-200 rounded-lg px-4 py-3 focus:outline-none resize-none" 
                                                  placeholder="Écrivez votre message..."></textarea>
                                        <button type="submit" name="envoyer" 
                                                class="bg-[#3b82f6] hover:bg-[#2563eb] text-white px-6 py-3 rounded-lg transition-colors duration-300 flex items-center justify-center">
                                            <i class="fas fa-paper-plane"></i>
                                        </button>
                                    </form>
                                </div>
                            <?php else: ?>
                                <div class="flex-1 flex items-center justify-center bg-[#F8FAFC]">
                                    <div class="text-center text-[#3b82f6]">
                                        <i class="fas fa-comments text-4xl mb-2"></i>
                                        <p>Sélectionnez un médecin pour commencer la conversation</p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Auto-resize textarea
        const textarea = document.querySelector('textarea');
        if (textarea) {
            textarea.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = Math.min(this.scrollHeight, 150) + 'px';
            });
        }

        // Scroll to bottom of messages
        const messagesContainer = document.querySelector('.overflow-y-auto');
        if (messagesContainer) {
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }

        // Focus textarea on load
        if (textarea) {
            textarea.focus();
        }
    </script>
</body>
</html> 