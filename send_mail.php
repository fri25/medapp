<?php

    // Envoi de l'email de confirmation
    $confirmation_link = "http://votre_site/confirmation.php?token=$token";
    $subject = "Confirmation de votre inscription";
    $body = "Bonjour $nom, \n\nMerci de vous être inscrit. Veuillez cliquer sur le lien suivant pour confirmer votre inscription : \n$confirmation_link";

    // Envoi de l'email avec PHPMailer
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    require 'vendor/autoload.php';

    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'elfridayemadje5@gmail.com'; // Remplacez avec votre email
        $mail->Password = 'dezv ahmn hwxv nkdk'; // Remplacez avec votre mot de passe d'application
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('elfridayemadje5@gmail.com', 'Admin');
        $mail->addAddress($email);

        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = strip_tags($body);

        $mail->send();
        echo "Un email de confirmation vous a été envoyé.";
    } catch (Exception $e) {
        echo "Erreur lors de l'envoi de l'email : {$mail->ErrorInfo}";
    }

?>