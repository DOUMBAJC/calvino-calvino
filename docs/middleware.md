# Middlewares

Les middlewares interceptent les requêtes HTTP avant qu'elles n'atteignent le contrôleur.

## Configuration

Les middlewares sont enregistrés dans `config/middlewares.php` :

```php
return [
    // Appliqués à TOUTES les requêtes
    'global' => [
        \Calvino\Middleware\CorsMiddleware::class,
        \Calvino\Middleware\JsonMiddleware::class,
    ],

    // Appliqués par nom sur des routes spécifiques
    'route' => [
        'auth'     => \Calvino\Middleware\AuthMiddleware::class,
        'admin'    => \Calvino\Middleware\AdminMiddleware::class,
        'throttle' => \App\Middleware\ThrottleMiddleware::class,
        'locale'   => \App\Middleware\LocaleMiddleware::class,
    ],
];
```

## Middlewares inclus

### CorsMiddleware (global)
Ajoute les en-têtes CORS à chaque réponse. Configurable via `.env` :

```env
CORS_ALLOWED_ORIGINS=http://localhost:3000
CORS_ALLOWED_METHODS="GET, POST, PUT, DELETE, PATCH, OPTIONS"
CORS_ALLOW_CREDENTIALS=true
```

### JsonMiddleware (global)
Force `Content-Type: application/json` sur toutes les réponses.

### AuthMiddleware (`auth`)
Valide le JWT dans le header `Authorization: Bearer <token>`. Retourne 401 si absent ou invalide.

### AdminMiddleware (`admin`)
Vérifie que l'utilisateur connecté a le rôle `admin`. À utiliser après `auth`. Retourne 403 sinon.

### ThrottleMiddleware (`throttle`)
Limite le nombre de requêtes par IP dans une fenêtre de temps. Protège contre le brute-force.

**Configuration :**
```env
RATE_LIMIT_MAX=5    # Nombre max de tentatives
RATE_LIMIT_DECAY=60 # Fenêtre en secondes
```

**En-têtes retournés :**
```
X-RateLimit-Limit: 5
X-RateLimit-Remaining: 3
Retry-After: 45   # (uniquement si bloqué, HTTP 429)
```

**Réponse si bloqué (HTTP 429) :**
```json
{
    "success": false,
    "message": "Trop de tentatives. Veuillez réessayer dans 45 secondes.",
    "retry_after": 45,
    "status": 429
}
```

### LocaleMiddleware (`locale`)
Détecte la langue depuis l'en-tête `Accept-Language` et configure la traduction automatiquement.

```
Accept-Language: fr-FR,fr;q=0.9,en;q=0.8
```

## Créer un middleware personnalisé

```php
<?php

namespace App\Middleware;

class MyCustomMiddleware
{
    public function handle($request, $next)
    {
        // Logique avant le contrôleur
        if (/* condition non remplie */) {
            http_response_code(403);
            echo json_encode(['message' => 'Accès refusé']);
            exit;
        }

        $response = $next($request);

        // Logique après le contrôleur (optionnel)

        return $response;
    }
}
```

Puis l'enregistrer dans `config/middlewares.php` :

```php
'route' => [
    'my_middleware' => \App\Middleware\MyCustomMiddleware::class,
],
```

Et l'utiliser dans les routes :

```php
$router->get('/protected', 'MyController@index', ['middleware' => 'my_middleware']);

// Ou dans un groupe
$router->group(['middleware' => 'my_middleware'], function () use ($router) {
    // routes...
});
```

## Ordre d'exécution

1. Middlewares globaux (`CorsMiddleware`, `JsonMiddleware`)
2. Middlewares de groupe (`auth`, `admin`)
3. Middlewares de route (`throttle`)
4. Contrôleur

Les middlewares s'exécutent dans l'ordre où ils sont déclarés.
