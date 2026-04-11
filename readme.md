# MedConnect

Application de gestion de consultations médicales en ligne permettant la mise en relation entre patients et professionnels de santé.

## Table des matières

- [Fonctionnalités](#fonctionnalités)
- [Architecture](#architecture)
- [Prérequis](#prérequis)
- [Installation](#installation)
- [Configuration](#configuration)
- [Structure du projet](#structure-du-projet)
- [API et Endpoints](#api-et-endpoints)
- [Sécurité](#sécurité)
- [Dépendances](#dépendances)
- [Licence](#licence)

## Fonctionnalités

### Pour les Patients
- **Inscription et connexion sécurisées**
- **Prise de rendez-vous** avec médecins disponibles
- **Messagerie** avec les professionnels de santé
- **Carnet de santé numérique**
- **Consultations en ligne** via chat
- **Annulation de rendez-vous**

### Pour les Médecins
- **Gestion du profil professionnel**
- **Gestion des disponibilités**
- **Consultation des patients**
- **Messagerie** avec les patients
- **Gestion des rendez-vous**

### Pour les Administrateurs
- **Tableau de bord** de supervision
- **Gestion des utilisateurs** (patients et médecins)
- **Validation des comptes médecins**
- **Statistiques et rapports**

### Fonctionnalités techniques
- **Chatbot** d'assistance
- **Notifications par email** (PHPMailer)
- **Génération de PDF** (TCPDF)
- **Intégration Google API**
- **Interface responsive** (TailwindCSS)

## Architecture

Le projet suit une architecture MVC (Modèle-Vue-Contrôleur) avec une organisation moderne :

```
medapp/
├── app/
│   ├── Controllers/     # Logique métier et contrôleurs
│   ├── Models/          # Modèles de données (User, Patient, Medecin, etc.)
│   └── Views/           # Vues de l'application
├── config/              # Configuration (DB, mail, paths)
├── public/              # Fichiers publics (CSS, JS)
├── views/               # Templates et pages (login, register, etc.)
├── models/              # Classes métier
├── includes/            # Fonctions utilitaires et session
├── api/                 # Endpoints API
├── chatbot/             # Logique du chatbot
├── auth/                # Gestion de l'authentification
├── storage/             # Fichiers stockés
└── vendor/              # Dépendances Composer
```

## Prérequis

- **PHP** >= 7.4
- **MySQL** ou **MariaDB**
- **Composer**
- **Node.js** (pour TailwindCSS)
- **Serveur web** (Apache/Nginx)

## Installation

### 1. Cloner le dépôt
```bash
git clone https://github.com/fri25/medapp.git
cd medapp
```

### 2. Installer les dépendances PHP
```bash
composer install
```

### 3. Installer les dépendances Node.js (optionnel - pour le développement CSS)
```bash
npm install
```

### 4. Configurer l'environnement
```bash
cp .env.example .env
```
Modifiez le fichier `.env` avec vos informations de configuration.

### 5. Créer la base de données
```bash
mysql -u root -p
CREATE DATABASE medapp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
exit;

# Importer le schéma complet
mysql -u root -p medapp < database_complete.sql
```

### 6. Configurer les permissions
Assurez-vous que les dossiers suivants sont accessibles en écriture :
- `storage/`
- `uploads/`
- `tmp/`
- `logs/`

## Configuration

### Fichier `.env`

```env
# Base de données
DB_HOST=localhost
DB_PORT=3306
DB_NAME=medapp
DB_USER=root
DB_PASS=votre_mot_de_passe

# Application
APP_NAME=MedConnect
APP_ENV=local
APP_DEBUG=true

# Email (PHPMailer)
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USER=votre_email@gmail.com
MAIL_PASS=votre_mot_de_passe_app

# Google API
GOOGLE_CLIENT_ID=votre_client_id
GOOGLE_CLIENT_SECRET=votre_client_secret
GOOGLE_REDIRECT_URI=http://localhost/medapp/auth/google_callback.php

# Sécurité
SESSION_LIFETIME=7200
```

## Structure du projet

| Dossier | Description |
|---------|-------------|
| `app/Controllers/` | Logique des contrôleurs métier |
| `app/Models/` | Modèles de données (User, Patient, Medecin, Admin, etc.) |
| `app/Views/` | Vues de l'interface |
| `config/` | Configuration de la base de données, email, chemins |
| `views/` | Templates et pages (login, register, patient/, medecin/, admin/) |
| `models/` | Classes métier supplémentaires |
| `includes/` | Fonctions utilitaires, gestion de session, connexion DB |
| `auth/` | Gestion de l'authentification (login, register, forgot password) |
| `api/` | Endpoints API pour les requêtes AJAX |
| `chatbot/` | Logique du chatbot d'assistance |
| `public/` | Fichiers CSS et JS compilés |
| `storage/` | Fichiers stockés (documents, uploads) |
| `logs/` | Fichiers de log |

## API et Endpoints

### Authentification
| Endpoint | Méthode | Description |
|----------|---------|-------------|
| `auth/login.php` | POST | Connexion utilisateur |
| `auth/register_patient.php` | POST | Inscription patient |
| `auth/register_medecin.php` | POST | Inscription médecin |
| `auth/forgot_password.php` | POST | Mot de passe oublié |
| `auth/reset_password.php` | POST | Réinitialisation mot de passe |

### Rendez-vous
| Endpoint | Méthode | Description |
|----------|---------|-------------|
| `check_disponibilite.php` | POST | Vérifier disponibilité |
| `annuler_rdv.php` | POST | Annuler un rendez-vous |
| `get_medecins.php` | GET | Liste des médecins |

### API JSON
| Endpoint | Méthode | Description |
|----------|---------|-------------|
| `api/check_email.php` | POST | Vérifier email existant |
| `api/check_phone.php` | POST | Vérifier téléphone existant |
| `api/save_message.php` | POST | Sauvegarder un message |
| `api/get_messages.php` | GET | Récupérer messages |

## Sécurité

- **Variables d'environnement** : Les informations sensibles sont stockées dans `.env` (jamais committé)
- **Hashage des mots de passe** : Utilisation de `password_hash()`
- **Protection CSRF** : Tokens CSRF sur les formulaires sensibles
- **Sessions sécurisées** : Gestion des sessions avec régénération d'ID
- **SQL Injection** : Requêtes préparées PDO
- **XSS Protection** : Échappement des sorties HTML

### Bonnes pratiques
- Ne jamais commiter le fichier `.env`
- Utiliser des mots de passe forts pour la base de données
- Configurer HTTPS en production
- Limiter les tentatives de connexion

## Dépendances

### PHP (Composer)
| Package | Version | Usage |
|---------|---------|-------|
| `vlucas/phpdotenv` | ^5.5 | Gestion des variables d'environnement |
| `google/apiclient` | ^2.18 | Intégration Google OAuth |
| `phpmailer/phpmailer` | ^6.8 | Envoi d'emails |
| `tecnickcom/tcpdf` | ^6.6 | Génération de PDF |

### Node.js (NPM)
| Package | Version | Usage |
|---------|---------|-------|
| `tailwindcss` | ^4.1.4 | Framework CSS |
| `autoprefixer` | ^10.4.21 | Préfixage CSS |
| `postcss` | ^8.5.3 | Transformation CSS |

## Modèles de données

### User
- Gestion des utilisateurs (authentification, rôles)

### Patient
- Profil patient, informations de santé

### Medecin
- Profil médecin, spécialités, disponibilités

### Admin
- Gestion administrative

### Message
- Messagerie entre utilisateurs

### Dashboard
- Statistiques et rapports

## Licence

Ce projet est sous licence ISC.

---

**MedConnect Team** - Application de gestion médicale 