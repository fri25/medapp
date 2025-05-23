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
        SELECT c.*, p.nom as patient_nom, p.prenom as patient_prenom,
               m.nom as medecin_nom, m.prenom as medecin_prenom
        FROM consultation c
        JOIN patient p ON c.id_patient = p.id
        JOIN medecin m ON c.id_medecin = m.id
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
    <title>Consultation - MedConnect</title>
    <style>
        @media print {
            body {
                font-family: Arial, sans-serif;
                line-height: 1.6;
                color: #333;
                margin: 0;
                padding: 20px;
            }
            .header {
                text-align: center;
                margin-bottom: 30px;
                border-bottom: 2px solid #2E7D32;
                padding-bottom: 20px;
            }
            .header h1 {
                color: #2E7D32;
                margin: 0;
                font-size: 24px;
            }
            .header p {
                margin: 5px 0;
                color: #666;
            }
            .section {
                margin-bottom: 20px;
            }
            .section h2 {
                color: #2E7D32;
                font-size: 18px;
                margin-bottom: 10px;
                border-bottom: 1px solid #ddd;
                padding-bottom: 5px;
            }
            .section p {
                margin: 5px 0;
            }
            .footer {
                margin-top: 50px;
                text-align: center;
                font-size: 12px;
                color: #666;
                border-top: 1px solid #ddd;
                padding-top: 20px;
            }
            .no-print {
                display: none;
            }
        }
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #2E7D32;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .print-button:hover {
            background-color: #1B5E20;
        }
    </style>
</head>
<body>
    <button onclick="window.print()" class="print-button no-print">
        <i class="fas fa-print"></i> Imprimer
    </button>

    <div class="header">
        <h1>MedConnect</h1>
        <p>Consultation Médicale</p>
    </div>

    <div class="section">
        <h2>Informations Générales</h2>
        <p><strong>Date de consultation :</strong> <?php echo date('d/m/Y H:i', strtotime($consultation['date_consultation'])); ?></p>
        <p><strong>Médecin :</strong> Dr. <?php echo htmlspecialchars($consultation['medecin_prenom'] . ' ' . $consultation['medecin_nom']); ?></p>
        <p><strong>Patient :</strong> <?php echo htmlspecialchars($consultation['patient_prenom'] . ' ' . $consultation['patient_nom']); ?></p>
    </div>

    <div class="section">
        <h2>Motif de Consultation</h2>
        <p><?php echo nl2br(htmlspecialchars($consultation['motif'])); ?></p>
    </div>

    <?php if (!empty($consultation['antecedents'])): ?>
    <div class="section">
        <h2>Antécédents</h2>
        <p><?php echo nl2br(htmlspecialchars($consultation['antecedents'])); ?></p>
    </div>
    <?php endif; ?>

    <div class="section">
        <h2>Examen Clinique</h2>
        <p><?php echo nl2br(htmlspecialchars($consultation['examen_clinique'])); ?></p>
    </div>

    <div class="section">
        <h2>Diagnostic</h2>
        <p><?php echo nl2br(htmlspecialchars($consultation['diagnostic'])); ?></p>
    </div>

    <?php if (!empty($consultation['traitement'])): ?>
    <div class="section">
        <h2>Traitement Prescrit</h2>
        <p><?php echo nl2br(htmlspecialchars($consultation['traitement'])); ?></p>
    </div>
    <?php endif; ?>

    <?php if (!empty($consultation['recommandations'])): ?>
    <div class="section">
        <h2>Recommandations</h2>
        <p><?php echo nl2br(htmlspecialchars($consultation['recommandations'])); ?></p>
    </div>
    <?php endif; ?>

    <?php if (!empty($consultation['prochain_rdv'])): ?>
    <div class="section">
        <h2>Prochain Rendez-vous</h2>
        <p><?php echo date('d/m/Y H:i', strtotime($consultation['prochain_rdv'])); ?></p>
    </div>
    <?php endif; ?>

    <div class="footer">
        <p>Document généré le <?php echo date('d/m/Y H:i'); ?></p>
        <p>MedConnect - Votre partenaire santé</p>
    </div>

    <script>
        // Impression automatique au chargement de la page
        window.onload = function() {
            // Attendre un court instant pour que la page soit complètement chargée
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html> 