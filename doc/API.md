# Guide API - MedConnect

## Introduction

Cette documentation décrit les endpoints API disponibles dans MedConnect. Les API suivent une architecture RESTful et retournent des réponses au format JSON.

**URL de base :** `http://localhost/medapp/`

## Authentification

### Session basée
La plupart des API nécessitent une session active. Les cookies de session sont gérés automatiquement par le navigateur.

### Vérification de session
Les endpoints protégés vérifient la session via `includes/session.php`.

---

## Endpoints

### 1. Authentification

#### POST `auth/login.php`
Connexion utilisateur.

**Requête :**
```json
{
    "email": "user@example.com",
    "password": "motdepasse",
    "user_type": "patient"
}
```

**Réponse succès (200) :**
```json
{
    "success": true,
    "message": "Connexion réussie",
    "redirect": "/views/patient/home.php"
}
```

**Réponse erreur (401) :**
```json
{
    "success": false,
    "message": "Identifiants incorrects"
}
```

---

#### POST `auth/register_patient.php`
Inscription d'un nouveau patient.

**Requête :**
```json
{
    "prenom": "Jean",
    "nom": "Dupont",
    "email": "jean@example.com",
    "password": "motdepasse123",
    "confirm_password": "motdepasse123",
    "telephone": "0612345678",
    "date_naissance": "1990-05-15",
    "adresse": "123 Rue de Paris",
    "sexe": "M"
}
```

**Réponse succès (201) :**
```json
{
    "success": true,
    "message": "Inscription réussie",
    "redirect": "/views/login.php"
}
```

---

#### POST `auth/register_medecin.php`
Inscription d'un nouveau médecin.

**Requête :**
```json
{
    "prenom": "Marie",
    "nom": "Martin",
    "email": "dr.martin@example.com",
    "password": "motdepasse123",
    "confirm_password": "motdepasse123",
    "telephone": "0612345678",
    "specialite": "Cardiologie",
    "numero_ordre": "12345",
    "adresse": "456 Avenue Lyon",
    "ville": "Lyon"
}
```

**Réponse succès (201) :**
```json
{
    "success": true,
    "message": "Inscription réussie, en attente de validation",
    "redirect": "/views/login.php"
}
```

---

#### POST `auth/forgot_password.php`
Demande de réinitialisation de mot de passe.

**Requête :**
```json
{
    "email": "user@example.com",
    "user_type": "patient"
}
```

**Réponse succès (200) :**
```json
{
    "success": true,
    "message": "Un email de réinitialisation a été envoyé"
}
```

---

### 2. Utilisateurs

#### POST `check_users.php`
Vérification de l'existence d'un utilisateur.

**Requête :**
```json
{
    "email": "user@example.com"
}
```

**Réponse :**
```json
{
    "exists": true,
    "user_type": "patient"
}
```

---

#### POST `api/check_email.php`
Vérification de disponibilité d'un email (AJAX).

**Requête :**
```json
{
    "email": "test@example.com"
}
```

**Réponse :**
```json
{
    "available": true
}
```

---

#### POST `api/check_phone.php`
Vérification de disponibilité d'un numéro de téléphone.

**Requête :**
```json
{
    "phone": "0612345678"
}
```

**Réponse :**
```json
{
    "available": false,
    "message": "Ce numéro est déjà utilisé"
}
```

---

### 3. Médecins

#### GET `get_medecins.php`
Récupération de la liste des médecins.

**Paramètres :**
- `specialite` (optionnel) : Filtre par spécialité
- `ville` (optionnel) : Filtre par ville
- `disponible` (optionnel) : `true` pour les médecins disponibles

**Requête :**
```
GET /get_medecins.php?specialite=Cardiologie&ville=Lyon
```

**Réponse :**
```json
{
    "success": true,
    "medecins": [
        {
            "id": 1,
            "nom": "Dr. Martin",
            "prenom": "Marie",
            "specialite": "Cardiologie",
            "ville": "Lyon",
            "adresse": "456 Avenue Lyon",
            "telephone": "0612345678",
            "disponible": true
        }
    ],
    "count": 1
}
```

---

#### POST `check_medecins.php`
Vérification de l'existence d'un médecin par son numéro d'ordre.

**Requête :**
```json
{
    "numero_ordre": "12345"
}
```

**Réponse :**
```json
{
    "exists": false
}
```

---

### 4. Rendez-vous

#### POST `check_disponibilite.php`
Vérification des disponibilités d'un médecin.

**Requête :**
```json
{
    "medecin_id": 1,
    "date": "2026-04-15"
}
```

**Réponse :**
```json
{
    "success": true,
    "disponibilites": [
        {
            "heure_debut": "09:00",
            "heure_fin": "09:30",
            "disponible": true
        },
        {
            "heure_debut": "09:30",
            "heure_fin": "10:00",
            "disponible": false
        }
    ]
}
```

---

#### POST `annuler_rdv.php`
Annulation d'un rendez-vous.

**Requête :**
```json
{
    "rdv_id": 15,
    "raison": "Indisponibilité"
}
```

**Réponse succès :**
```json
{
    "success": true,
    "message": "Rendez-vous annulé avec succès"
}
```

**Réponse erreur :**
```json
{
    "success": false,
    "message": "Rendez-vous non trouvé ou déjà annulé"
}
```

---

### 5. Messagerie

#### POST `api/save_message.php`
Sauvegarde d'un message.

**Requête :**
```json
{
    "sender_id": 1,
    "receiver_id": 2,
    "message": "Bonjour, j'ai une question...",
    "conversation_id": 5
}
```

**Réponse :**
```json
{
    "success": true,
    "message_id": 25,
    "created_at": "2026-04-11 10:30:00"
}
```

---

#### GET `api/get_messages.php`
Récupération des messages d'une conversation.

**Paramètres :**
- `conversation_id` (requis) : ID de la conversation
- `limit` (optionnel) : Nombre maximum de messages (défaut: 50)
- `offset` (optionnel) : Offset pour la pagination

**Requête :**
```
GET /api/get_messages.php?conversation_id=5&limit=20
```

**Réponse :**
```json
{
    "success": true,
    "messages": [
        {
            "id": 1,
            "sender_id": 1,
            "sender_name": "Jean Dupont",
            "message": "Bonjour docteur",
            "created_at": "2026-04-11 10:00:00",
            "lu": true
        },
        {
            "id": 2,
            "sender_id": 2,
            "sender_name": "Dr. Martin",
            "message": "Bonjour, comment puis-je vous aider ?",
            "created_at": "2026-04-11 10:05:00",
            "lu": false
        }
    ],
    "total": 2
}
```

---

### 6. Chatbot

#### POST `chatbot/chat.php`
Interaction avec le chatbot.

**Requête :**
```json
{
    "message": "Comment prendre rendez-vous ?",
    "user_id": 1,
    "context": "patient"
}
```

**Réponse :**
```json
{
    "success": true,
    "response": "Pour prendre rendez-vous, allez dans l'espace patient > Prendre rendez-vous, puis sélectionnez un médecin et un créneau disponible.",
    "actions": [
        {
            "type": "redirect",
            "url": "/views/patient/prendre_rdv.php"
        }
    ]
}
```

---

### 7. Vérification

#### GET `verify.php`
Vérification de compte par token.

**Paramètres :**
- `token` (requis) : Token de vérification
- `type` (requis) : Type de vérification (`email`, `password_reset`)

**Requête :**
```
GET /verify.php?token=abc123&type=email
```

**Réponse :**
```json
{
    "success": true,
    "message": "Email vérifié avec succès",
    "redirect": "/views/login.php"
}
```

---

## Codes de statut HTTP

| Code | Description |
|------|-------------|
| 200 | Succès |
| 201 | Créé avec succès |
| 400 | Requête invalide |
| 401 | Non authentifié |
| 403 | Accès interdit |
| 404 | Ressource non trouvée |
| 422 | Validation échouée |
| 500 | Erreur serveur |

---

## Gestion des erreurs

Toutes les erreurs suivent ce format :

```json
{
    "success": false,
    "message": "Description de l'erreur",
    "errors": {
        "field": ["Message d'erreur spécifique"]
    }
}
```

---

## Exemples d'utilisation

### JavaScript (Fetch)

```javascript
// Connexion
fetch('/medapp/auth/login.php', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
    },
    body: JSON.stringify({
        email: 'user@example.com',
        password: 'motdepasse',
        user_type: 'patient'
    })
})
.then(response => response.json())
.then(data => {
    if (data.success) {
        window.location.href = data.redirect;
    } else {
        alert(data.message);
    }
});

// Récupérer les médecins
fetch('/medapp/get_medecins.php?specialite=Cardiologie')
    .then(response => response.json())
    .then(data => {
        console.log(data.medecins);
    });
```

### PHP (cURL)

```php
<?php
// Prise de rendez-vous
$data = [
    'medecin_id' => 1,
    'date' => '2026-04-15',
    'heure' => '10:00'
];

$ch = curl_init('/medapp/check_disponibilite.php');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);

$response = curl_exec($ch);
$result = json_decode($response, true);

curl_close($ch);

if ($result['success']) {
    foreach ($result['disponibilites'] as $dispo) {
        echo $dispo['heure_debut'] . ' - ' . ($dispo['disponible'] ? 'Dispo' : 'Occupé') . "\n";
    }
}
```

---

## Limites et bonnes pratiques

### Rate Limiting
- 100 requêtes par minute par IP
- 1000 requêtes par heure par utilisateur authentifié

### Bonnes pratiques
1. Toujours vérifier la réponse `success`
2. Gérer les erreurs avec des messages utilisateur appropriés
3. Ne jamais exposer les tokens ou mots de passe
4. Utiliser HTTPS en production
5. Implémenter un mécanisme de retry pour les erreurs 500

---

**Dernière mise à jour :** Avril 2026
