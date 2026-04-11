<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Réinitialisation de mot de passe - MedConnect</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #10b981; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0;">
        <h1 style="margin: 0;">Réinitialisation de votre mot de passe</h1>
    </div>
    <div style="background-color: #f9fafb; padding: 20px; border: 1px solid #e5e7eb; border-radius: 0 0 5px 5px;">
        <p>Bonjour <?php echo htmlspecialchars($nom); ?>,</p>
        
        <p>Vous avez demandé la réinitialisation de votre mot de passe sur MedConnect.</p>
        
        <p>Pour définir un nouveau mot de passe, veuillez cliquer sur le bouton ci-dessous :</p>
        
        <div style="text-align: center;">
            <a href="<?php echo htmlspecialchars($reset_link); ?>" 
               style="display: inline-block; background-color: #10b981; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 20px 0;">
                Réinitialiser mon mot de passe
            </a>
        </div>
        
        <p>Si le bouton ne fonctionne pas, vous pouvez copier et coller le lien suivant dans votre navigateur :</p>
        <p style="word-break: break-all; background-color: #f3f4f6; padding: 10px; border-radius: 4px;">
            <?php echo htmlspecialchars($reset_link); ?>
        </p>
        
        <p>Ce lien expirera dans 24 heures pour des raisons de sécurité.</p>
        
        <p>Si vous n'avez pas demandé cette réinitialisation, vous pouvez ignorer cet email.</p>
    </div>
    <div style="text-align: center; margin-top: 20px; font-size: 12px; color: #6b7280;">
        <p>Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
        <p>&copy; <?php echo date('Y'); ?> MedConnect. Tous droits réservés.</p>
    </div>
</body>
</html> 