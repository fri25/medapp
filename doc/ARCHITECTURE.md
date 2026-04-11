# Architecture - MedConnect

## Vue d'ensemble

MedConnect utilise une architecture **MVC (Modèle-Vue-Contrôleur)** avec des éléments de **architecture en couches**. Le projet est organisé pour faciliter la maintenance, les tests et l'évolution.

## Diagramme d'architecture

```
┌─────────────────────────────────────────────────────────────┐
│                         CLIENT                              │
│                  (Navigateur web)                           │
└───────────────────────┬─────────────────────────────────────┘
                        │ HTTP
┌───────────────────────▼─────────────────────────────────────┐
│                      PUBLIC                                 │
│  index.php  ──►  Router  ──►  Controllers                   │
│  /css, /js                                                  │
└───────────────────────┬─────────────────────────────────────┘
                        │
┌───────────────────────▼─────────────────────────────────────┐
│                      APPLICATION                            │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐         │
│  │ Controllers │  │   Models    │  │    Views    │         │
│  │   (Logic)   │◄─┤  (Data)     │  │  (UI/UX)    │         │
│  └──────┬──────┘  └──────┬──────┘  └─────────────┘         │
│         │                │                                  │
│  ┌──────▼──────┐  ┌──────▼──────┐                          │
│  │   Services  │  │  Validators │                          │
│  │  (Business) │  │   (Input)   │                          │
│  └─────────────┘  └─────────────┘                          │
└───────────────────────┬─────────────────────────────────────┘
                        │
┌───────────────────────▼─────────────────────────────────────┐
│                      INFRASTRUCTURE                         │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐         │
│  │   Config    │  │  Database   │  │   Session   │         │
│  │  (Settings) │  │    (PDO)    │  │  (Security) │         │
│  └─────────────┘  └─────────────┘  └─────────────┘         │
│  ┌─────────────┐  ┌─────────────┐                          │
│  │    Mail     │  │    API      │                          │
│  │(PHPMailer)  │  │ (External)  │                          │
│  └─────────────┘  └─────────────┘                          │
└─────────────────────────────────────────────────────────────┘
```

## Couches de l'application

### 1. Couche Présentation (Views)
**Dossiers :** `views/`, `app/Views/`, `public/`

Responsabilités :
- Affichage des données
- Formulaires HTML
- Templates de pages
- Assets (CSS, JS, images)

Organisation :
```
views/
├── components/         # Composants réutilisables
├── emails/            # Templates d'emails
├── patient/           # Pages espace patient
├── medecin/           # Pages espace médecin
└── admin/             # Pages administration
```

### 2. Couche Contrôleurs (Controllers)
**Dossier :** `app/Controllers/`, `controllers/`

Responsabilités :
- Réception des requêtes HTTP
- Appel des services métier
- Retour des réponses
- Gestion des erreurs

Exemple de flux :
```
Requête HTTP
    │
    ▼
┌──────────────┐
│  Controller  │──► Validation entrée
│  (login.php) │
└──────┬───────┘
       │
       ▼
┌──────────────┐
│    Model     │──► Logique métier
│   (User)     │
└──────┬───────┘
       │
       ▼
┌──────────────┐
│    View      │──► Rendu réponse
│ (login.php)  │
└──────────────┘
```

### 3. Couche Modèles (Models)
**Dossier :** `app/Models/`, `models/`

Responsabilités :
- Accès aux données
- Validation métier
- Relations entre entités
- Logique métier spécifique

Modèles principaux :
| Modèle | Description |
|--------|-------------|
| `User` | Authentification et gestion utilisateurs |
| `Patient` | Profil et données patients |
| `Medecin` | Profil et disponibilités médecins |
| `Admin` | Gestion administrative |
| `Message` | Système de messagerie |
| `Dashboard` | Statistiques et rapports |
| `ProfilMedecin` | Détails professionnels |

### 4. Couche Services
**Dossier :** `includes/`, `api/`, `chatbot/`

Responsabilités :
- Services transversaux
- Intégrations externes
- Traitements spécifiques

Services disponibles :
- **Authentification** (`auth/`)
- **Messagerie** (`send_mail.php`)
- **Chatbot** (`chatbot/`)
- **API REST** (`api/`)
- **Vérifications** (`check_*.php`)

### 5. Couche Infrastructure
**Dossier :** `config/`

Responsabilités :
- Configuration centralisée
- Connexions (DB, Mail, API)
- Gestion des erreurs
- Sécurité

## Flux de données

### Authentification
```
1. Formulaire login (views/login.php)
        │
        ▼
2. Validation (includes/validation.php)
        │
        ▼
3. Controller (controllers/AuthController.php)
        │
        ▼
4. Model (models/User.php)
        │
        ▼
5. Database (config/database.php)
        │
        ▼
6. Session (includes/session.php)
        │
        ▼
7. Redirection (vers dashboard approprié)
```

### Prise de rendez-vous
```
1. Formulaire RDV (views/patient/prendre_rdv.php)
        │
        ▼
2. Vérification disponibilité (check_disponibilite.php)
        │
        ▼
3. Création RDV (models/Patient.php)
        │
        ▼
4. Notification email (send_mail.php)
        │
        ▼
5. Confirmation (views/confirmation.php)
```

## Patterns utilisés

### 1. Singleton (Configuration)
```php
// config/config.php
class Config {
    private static $instance = null;
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}
```

### 2. Repository (Accès données)
Les modèles agissent comme repositories pour leurs entités respectives.

### 3. Front Controller
`index.php` sert de point d'entrée unique et route les requêtes.

## Sécurité intégrée

| Couche | Mécanisme |
|--------|-----------|
| Présentation | Échappement HTML, CSRF tokens |
| Contrôleur | Validation entrée, rate limiting |
| Modèle | Requêtes préparées PDO |
| Infrastructure | Variables d'environnement, logs sécurisés |

## Points d'extension

### Ajouter un nouveau module
1. Créer le modèle dans `models/`
2. Créer le contrôleur dans `app/Controllers/`
3. Créer les vues dans `views/nouveau_module/`
4. Ajouter les routes dans `index.php`
5. Créer les migrations SQL

### Intégrer une nouvelle API
1. Créer un service dans `includes/services/`
2. Ajouter la configuration dans `config/`
3. Utiliser via les contrôleurs

## Performance

### Optimisations implémentées
- Autoloader PSR-4 (Composer)
- Connexion DB singleton
- Sessions optimisées
- Cache possible pour les données statiques

### Recommandations
- Utiliser un cache (Redis/Memcached) en production
- Activer OPcache pour PHP
- Compresser les assets CSS/JS
- Utiliser un CDN pour les fichiers statiques

## Tests

Structure recommandée pour les tests :
```
tests/
├── unit/              # Tests unitaires
├── integration/       # Tests d'intégration
└── e2e/              # Tests end-to-end
```

---

**Dernière mise à jour :** Avril 2026
