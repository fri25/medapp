# Guide d'Installation - MedConnect

## Table des matières
1. [Prérequis système](#prérequis-système)
2. [Installation étape par étape](#installation-étape-par-étape)
3. [Configuration du serveur web](#configuration-du-serveur-web)
4. [Configuration de la base de données](#configuration-de-la-base-de-données)
5. [Configuration des emails](#configuration-des-emails)
6. [Configuration Google OAuth](#configuration-google-oauth)
7. [Vérification de l'installation](#vérification-de-linstallation)
8. [Résolution des problèmes](#résolution-des-problèmes)

---

## Prérequis système

### Logiciels requis
- **PHP** >= 7.4 avec extensions :
  - `pdo_mysql`
  - `mbstring`
  - `openssl`
  - `json`
  - `curl`
  - `fileinfo`
  - `gd` (optionnel - pour la manipulation d'images)
- **MySQL** >= 5.7 ou **MariaDB** >= 10.2
- **Composer** >= 2.0
- **Node.js** >= 14.0 (optionnel - pour TailwindCSS)
- **Serveur web** : Apache avec mod_rewrite ou Nginx

### Espace disque
- Minimum 500 Mo pour l'application
- 1 Go+ recommandé pour les fichiers stockés

---

## Installation étape par étape

### 1. Télécharger le projet
```bash
cd /chemin/vers/votre/serveur
git clone https://github.com/fri25/medapp.git
cd medapp
```

### 2. Installer les dépendances PHP
```bash
composer install
```

### 3. Installer les dépendances Node.js (optionnel)
```bash
npm install
```

### 4. Configurer l'environnement
```bash
cp .env.example .env
```

Éditez le fichier `.env` avec vos paramètres :
```env
# Base de données
DB_HOST=localhost
DB_PORT=3306
DB_NAME=medapp
DB_USER=root
DB_PASS=votre_mot_de_passe_sécurisé

# Application
APP_NAME=MedConnect
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost/medapp

# Email
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USER=votre_email@gmail.com
MAIL_PASS=mot_de_passe_application

# Google OAuth
GOOGLE_CLIENT_ID=your_client_id.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=your_client_secret
GOOGLE_REDIRECT_URI=http://localhost/medapp/auth/google_callback.php

# Sécurité
SESSION_LIFETIME=7200
```

---

## Configuration du serveur web

### Apache (.htaccess)
Le fichier `.htaccess` devrait déjà être présent à la racine :
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# Protection des fichiers sensibles
<FilesMatch "^\.env">
    Order allow,deny
    Deny from all
</FilesMatch>

<FilesMatch "\.(sql|log|ini)$">
    Order allow,deny
    Deny from all
</FilesMatch>
```

### Nginx
```nginx
server {
    listen 80;
    server_name localhost;
    root /chemin/vers/medapp;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.env {
        deny all;
    }
}
```

---

## Configuration de la base de données

### 1. Créer la base de données
```bash
mysql -u root -p
```
```sql
CREATE DATABASE medapp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'medapp_user'@'localhost' IDENTIFIED BY 'votre_mot_de_passe';
GRANT ALL PRIVILEGES ON medapp.* TO 'medapp_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 2. Importer le schéma
```bash
mysql -u root -p medapp < database_complete.sql
```

### 3. Vérifier l'importation
```bash
mysql -u root -p medapp -e "SHOW TABLES;"
```

---

## Configuration des emails

### Gmail (recommandé pour le développement)
1. Activez la validation en 2 étapes sur votre compte Google
2. Générez un "Mot de passe d'application" dans les paramètres de sécurité
3. Utilisez ce mot de passe dans le fichier `.env`

Configuration `.env` :
```env
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USER=votre_email@gmail.com
MAIL_PASS=votre_mot_de_passe_application
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@medconnect.local
MAIL_FROM_NAME=MedConnect
```

---

## Configuration Google OAuth

### 1. Créer un projet Google Cloud
1. Allez sur [Google Cloud Console](https://console.cloud.google.com/)
2. Créez un nouveau projet
3. Activez l'API "Google OAuth 2.0"

### 2. Configurer les identifiants OAuth
1. Allez dans "Identifiants" > "Créer des identifiants" > "ID client OAuth"
2. Configurez l'écran de consentement OAuth
3. Ajoutez les URI de redirection autorisées :
   - `http://localhost/medapp/auth/google_callback.php` (développement)
   - `https://votredomaine.com/auth/google_callback.php` (production)

### 3. Copier les identifiants
Récupérez le Client ID et Client Secret, puis ajoutez-les au fichier `.env`.

---

## Vérification de l'installation

### 1. Test de la base de données
Accédez à : `http://localhost/medapp/test_env.php`

Ce fichier vérifie :
- Connexion à la base de données
- Chargement des variables d'environnement
- Configuration PHP

### 2. Test de session
Accédez à : `http://localhost/medapp/test_session.php`

### 3. Test de login
Accédez à : `http://localhost/medapp/test_login.php`

### 4. Page d'accueil
Accédez à : `http://localhost/medapp/`

Vous devriez voir la page de connexion.

---

## Résolution des problèmes

### Erreur "Class 'Dotenv\Dotenv' not found"
```bash
composer install
# ou
composer dump-autoload
```

### Erreur de connexion à la base de données
Vérifiez dans `config/config.php` et `.env` :
- Hôte correct
- Nom de base de données
- Nom d'utilisateur et mot de passe
- Port MySQL (3306 par défaut)

### Erreur 500 - Internal Server Error
Vérifiez les logs :
- `logs/error.log`
- Logs du serveur web

### Sessions qui ne persistent pas
Vérifiez dans `php.ini` :
```ini
session.save_path = "/tmp"
session.gc_maxlifetime = 7200
```

### Emails non envoyés
1. Vérifiez la configuration dans `.env`
2. Testez avec `send_mail.php`
3. Vérifiez les logs d'erreur

### Permissions de dossiers
```bash
chmod 755 storage/
chmod 755 uploads/
chmod 755 tmp/
chmod 755 logs/

# Sur certains systèmes :
chmod -R 775 storage/
chown -R www-data:www-data storage/
```

---

## Prochaines étapes

Après l'installation réussie :
1. Créez un compte administrateur
2. Configurez les premiers médecins
3. Testez le flux patient complet
4. Configurez HTTPS pour la production

Pour plus d'informations, consultez le [README principal](../README.md).
