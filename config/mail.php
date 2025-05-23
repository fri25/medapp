<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require_once __DIR__ . '/../vendor/autoload.php';

// Configuration SMTP pour l'envoi d'emails
define('SMTP_HOST', env('MAIL_HOST', 'smtp.gmail.com'));  // Serveur SMTP Gmail
define('SMTP_PORT', env('MAIL_PORT', 587));               // Port SMTP Gmail
define('SMTP_USERNAME', env('MAIL_USERNAME'));  // Email Gmail
define('SMTP_PASSWORD', env('MAIL_PASSWORD'));  // Mot de passe d'application
define('SMTP_FROM_EMAIL', env('MAIL_FROM_ADDRESS'));  // Email d'envoi
define('SMTP_FROM_NAME', env('MAIL_FROM_NAME', 'MedConnect'));
define('SMTP_REPLY_TO', env('MAIL_REPLY_TO'));  // Email de réponse

// Fonction pour envoyer un email
function sendEmail($to, $subject, $message, $isHtml = false) {
    try {
        // Validation des paramètres
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Adresse email invalide : $to");
        }

        if (empty($subject) || empty($message)) {
            throw new Exception("Le sujet et le message sont obligatoires");
        }

        $mail = new PHPMailer(true);

        // Configuration du serveur
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;
        $mail->CharSet = 'UTF-8';

        // Activer le débogage SMTP en mode développement
        if (env('APP_ENV') === 'development') {
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;
            $mail->Debugoutput = function($str, $level) {
                error_log("PHPMailer Debug: $str");
            };
        }

        // Destinataires
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($to);
        if (SMTP_REPLY_TO) {
            $mail->addReplyTo(SMTP_REPLY_TO);
        }

        // Contenu
        $mail->isHTML($isHtml);
        $mail->Subject = $subject;
        $mail->Body = $message;
        if ($isHtml) {
            $mail->AltBody = strip_tags($message);
        }

        // Ajout d'en-têtes de sécurité
        $mail->addCustomHeader('X-Mailer', 'MedConnect Mailer');
        $mail->addCustomHeader('X-Priority', '1');
        $mail->addCustomHeader('X-MSMail-Priority', 'High');
        $mail->addCustomHeader('X-Auto-Response-Suppress', 'OOF, DR, RN, NRN, AutoReply');

        // Envoi de l'email
        $mail->send();
        
        // Journalisation du succès
        error_log("Email envoyé avec succès à : $to");
        return true;
    } catch (Exception $e) {
        // Journalisation détaillée des erreurs
        error_log("Erreur d'envoi d'email : " . $mail->ErrorInfo);
        error_log("Détails de l'erreur : " . $e->getMessage());
        error_log("Trace : " . $e->getTraceAsString());
        
        // En mode développement, on peut relancer l'exception
        if (env('APP_ENV') === 'development') {
            throw $e;
        }
        
        return false;
    }
} 