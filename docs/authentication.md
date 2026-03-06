# Authentification

Calvino utilise l'authentification **JWT (JSON Web Token)** avec gestion multi-sessions et refresh token.

## Configuration

```env
JWT_SECRET=votre-cle-secrete-min-32-caracteres
JWT_TTL=86400          # Token d'accès : 24h
JWT_REFRESH_TTL=2592000 # Refresh token : 30 jours
```

## Flux d'authentification

### 1. Connexion

```http
POST /auth/login
Content-Type: application/json

{
    "email": "admin@example.com",
    "password": "MonMotDePasse"
}
```

Réponse :

```json
{
    "success": true,
    "token": "eyJ...",
    "refresh_token": "eyJ...",
    "session_id": "abc123...",
    "token_type": "Bearer",
    "user": {
        "id": 1,
        "name": "Admin",
        "email": "admin@example.com",
        "role": "admin"
    },
    "default_password": false
}
```

> Si `default_password: true`, le front-end doit rediriger vers la page de changement de mot de passe.

### 2. Utiliser le token

Inclure le token dans toutes les requêtes protégées :

```http
Authorization: Bearer eyJ...
```

### 3. Renouveler le token

```http
POST /auth/refresh-token
Content-Type: application/json

{
    "refresh_token": "eyJ..."
}
```

### 4. Déconnexion

```http
POST /auth/logout
Authorization: Bearer eyJ...
```

## Réinitialisation de mot de passe

### 1. Demander un lien de reset

```http
POST /auth/forgot-password
Content-Type: application/json

{
    "email": "user@example.com"
}
```

La réponse est toujours la même (pour éviter l'énumération d'emails) :

```json
{
    "success": true,
    "message": "Si cet email existe, un lien de réinitialisation a été envoyé."
}
```

L'utilisateur reçoit une **notification interne** contenant le lien de reset valable **60 minutes**.

### 2. Vérifier la validité d'un token (optionnel)

```http
GET /auth/reset-password/verify?token=abc123...
```

### 3. Réinitialiser le mot de passe

```http
POST /auth/reset-password
Content-Type: application/json

{
    "token": "abc123...",
    "password": "NouveauMotDePasse123!",
    "password_confirmation": "NouveauMotDePasse123!"
}
```

## Gestion des sessions

Un utilisateur peut avoir **jusqu'à 3 sessions simultanées**. À la 4ème connexion, la session la plus ancienne est automatiquement révoquée.

### Lister les sessions actives

```http
GET /auth/sessions
Authorization: Bearer eyJ...
```

### Déconnecter une session spécifique

```http
POST /auth/sessions/logout/{sessionId}
Authorization: Bearer eyJ...
```

### Déconnecter toutes les autres sessions

```http
POST /auth/sessions/logout-others
Authorization: Bearer eyJ...
```

## Rôles et permissions

| Rôle | Description | Accès admin |
|------|-------------|-------------|
| `admin` | Administrateur | Oui |
| `manager` | Gestionnaire | Non |
| `pharmacist` | Pharmacien | Non |
| `cashier` | Caissier | Non |

### Middlewares associés

- `auth` — Vérifie la présence et la validité du JWT
- `admin` — Vérifie que l'utilisateur a le rôle `admin`
- `throttle` — Limite le nombre de requêtes (anti-brute force)

## Modèle User — méthodes d'authentification

```php
// Vérifier le mot de passe
$user->verifyPassword('monmotdepasse'); // bool

// Générer un token JWT
$token = $user->createToken($sessionId);

// Générer un refresh token
$refresh = $user->createRefreshToken($sessionId);

// Hacher un mot de passe
$hash = User::hashPassword('monmotdepasse');

// Vérifier si c'est un mot de passe par défaut
$isDefault = User::isDefaultPassword('PHSAB12'); // true

// Vérifier si le compte est bloqué
$user->isBlocked(); // bool
```
