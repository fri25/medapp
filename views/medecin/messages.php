<?php
require_once '../../config/database.php';
require_once '../../models/Message.php';
require_once '../../includes/session.php';

// Vérifier si l'utilisateur est connecté et est un médecin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'medecin') {
    header("Location: ../login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();
$message = new Message($db);

// Récupérer la liste des patients qui ont échangé avec le médecin
$query = "SELECT DISTINCT u.id, u.nom, u.prenom, 
          (SELECT COUNT(*) FROM messages m2 
           WHERE m2.sender_id = u.id 
           AND m2.receiver_id = :medecin_id1 
           AND m2.lu = 0) as messages_non_lus
          FROM users u 
          JOIN messages m ON (m.sender_id = u.id OR m.receiver_id = u.id)
          WHERE u.role = 'patient' 
          AND (m.sender_id = :medecin_id2 OR m.receiver_id = :medecin_id3)
          ORDER BY messages_non_lus DESC, u.nom ASC";

$stmt = $db->prepare($query);
$stmt->bindParam(':medecin_id1', $_SESSION['user_id']);
$stmt->bindParam(':medecin_id2', $_SESSION['user_id']);
$stmt->bindParam(':medecin_id3', $_SESSION['user_id']);
$stmt->execute();
$patients = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Traitement de l'envoi de message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['envoyer'])) {
    $message->contenu = $_POST['contenu'];
    $message->sender_id = $_SESSION['user_id'];
    $message->receiver_id = $_POST['receiver_id'];
    $message->sender_type = 'medecin';

    if ($message->envoyer()) {
        $success = "Message envoyé avec succès !";
    } else {
        $error = "Erreur lors de l'envoi du message.";
    }
}

// Récupérer les messages si un patient est sélectionné
$conversation = null;
if (isset($_GET['patient_id'])) {
    $stmt = $message->getConversation($_SESSION['user_id'], $_GET['patient_id']);
    $conversation = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Marquer les messages comme lus
    foreach ($conversation as $msg) {
        if ($msg['receiver_id'] == $_SESSION['user_id'] && $msg['lu'] == 0) {
            $message->marquerCommeLu($msg['id']);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messagerie - MedConnect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <?php include_once '../../views/components/styles.php'; ?>
    <style>
        .message-bubble {
            position: relative;
            transition: all 0.3s ease;
        }
        
        .message-bubble:hover {
            transform: translateY(-2px);
        }
        
        .message-bubble::before {
            content: '';
            position: absolute;
            bottom: -8px;
            width: 0;
            height: 0;
            border-style: solid;
        }
        
        .message-bubble.sent::before {
            right: 10px;
            border-width: 8px 0 0 8px;
            border-color: transparent transparent transparent #2E7D32;
        }
        
        .message-bubble.received::before {
            left: 10px;
            border-width: 8px 8px 0 0;
            border-color: #E8F5E9 transparent transparent transparent;
        }

        .patient-list-item {
            transition: all 0.3s ease;
        }

        .patient-list-item:hover {
            background-color: #E8F5E9;
            transform: translateX(5px);
        }

        .patient-list-item.active {
            background-color: #C8E6C9;
            border-left: 4px solid #2E7D32;
        }

        .message-input {
            transition: all 0.3s ease;
        }

        .message-input:focus {
            box-shadow: 0 0 0 2px rgba(46, 125, 50, 0.2);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-[#F1F8E9] to-[#E8F5E9] min-h-screen">
    <?php include_once '../../views/components/header.php'; ?>

    <div class="container mx-auto px-4 py-8">
        <div class="max-w-5xl mx-auto">
            <div class="flex items-center justify-between mb-8">
                <h1 class="text-3xl font-bold text-[#1B5E20]">
                    <i class="fas fa-comments mr-3"></i>Messagerie
                </h1>
                <a href="dashboard.php" class="btn-secondary">
                    <i class="fas fa-arrow-left mr-2"></i>Retour au tableau de bord
                </a>
            </div>

            <?php if (isset($success)): ?>
                <div class="bg-green-100 border-l-4 border-[#2E7D32] text-[#1B5E20] p-4 mb-4 rounded-r-lg">
                    <i class="fas fa-check-circle mr-2"></i><?php echo $success; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded-r-lg">
                    <i class="fas fa-exclamation-circle mr-2"></i><?php echo $error; ?>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-xl shadow-lg overflow-hidden glass-effect">
                <div class="grid grid-cols-12">
                    <!-- Liste des patients -->
                    <div class="col-span-4 border-r border-gray-200">
                        <div class="p-4 bg-[#E8F5E9]">
                            <h2 class="text-lg font-semibold text-[#1B5E20]">
                                <i class="fas fa-users mr-2"></i>Patients
                            </h2>
                        </div>
                        <div class="overflow-y-auto h-[600px]">
                            <?php foreach ($patients as $patient): ?>
                                <a href="?patient_id=<?php echo $patient['id']; ?>" 
                                   class="patient-list-item block p-4 border-b border-gray-100 <?php echo (isset($_GET['patient_id']) && $_GET['patient_id'] == $patient['id']) ? 'active' : ''; ?>">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-[#2E7D32] to-[#81C784] flex items-center justify-center">
                                                    <i class="fas fa-user text-white"></i>
                                                </div>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm font-medium text-[#1B5E20]">
                                                    <?php echo htmlspecialchars($patient['nom'] . ' ' . $patient['prenom']); ?>
                                                </p>
                                            </div>
                                        </div>
                                        <?php if ($patient['messages_non_lus'] > 0): ?>
                                            <span class="bg-[#2E7D32] text-white text-xs font-bold px-2 py-1 rounded-full">
                                                <?php echo $patient['messages_non_lus']; ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Zone de conversation -->
                    <div class="col-span-8">
                        <?php if (isset($_GET['patient_id'])): ?>
                            <!-- Messages -->
                            <div class="h-[500px] overflow-y-auto p-4 bg-[#F1F8E9]">
                                <?php if ($conversation): ?>
                                    <?php foreach ($conversation as $msg): ?>
                                        <div class="mb-4 <?php echo $msg['sender_id'] == $_SESSION['user_id'] ? 'text-right' : ''; ?>">
                                            <div class="message-bubble inline-block max-w-[70%] rounded-lg p-3 <?php echo $msg['sender_id'] == $_SESSION['user_id'] ? 'sent bg-[#2E7D32] text-white' : 'received bg-[#E8F5E9] text-[#1B5E20]'; ?>">
                                                <p class="text-sm"><?php echo htmlspecialchars($msg['contenu']); ?></p>
                                                <p class="text-xs mt-1 <?php echo $msg['sender_id'] == $_SESSION['user_id'] ? 'text-[#C8E6C9]' : 'text-[#558B2F]'; ?>">
                                                    <?php echo date('d/m/Y H:i', strtotime($msg['date_envoi'])); ?>
                                                </p>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-center text-[#558B2F] mt-8">
                                        <i class="fas fa-comment-slash text-4xl mb-2"></i>
                                        <p>Aucun message dans cette conversation.</p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Formulaire d'envoi -->
                            <div class="border-t border-gray-200 p-4 bg-white">
                                <form method="POST" class="flex gap-2">
                                    <input type="hidden" name="receiver_id" value="<?php echo $_GET['patient_id']; ?>">
                                    <textarea name="contenu" rows="1" class="message-input flex-1 border border-gray-200 rounded-lg px-4 py-2 focus:outline-none focus:border-[#2E7D32]" placeholder="Écrivez votre message..."></textarea>
                                    <button type="submit" name="envoyer" class="btn-primary">
                                        <i class="fas fa-paper-plane"></i>
                                    </button>
                                </form>
                            </div>
                        <?php else: ?>
                            <div class="h-[600px] flex flex-col items-center justify-center text-[#558B2F]">
                                <i class="fas fa-comments text-6xl mb-4"></i>
                                <p class="text-lg">Sélectionnez un patient pour commencer la conversation</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-resize textarea
        const textarea = document.querySelector('textarea');
        if (textarea) {
            textarea.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = (this.scrollHeight) + 'px';
            });
        }

        // Scroll to bottom of messages
        const messagesContainer = document.querySelector('.overflow-y-auto');
        if (messagesContainer) {
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }

        // Auto-scroll to bottom when new messages arrive
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.addedNodes.length) {
                    messagesContainer.scrollTop = messagesContainer.scrollHeight;
                }
            });
        });

        if (messagesContainer) {
            observer.observe(messagesContainer, { childList: true });
        }
    </script>
</body>
</html> 