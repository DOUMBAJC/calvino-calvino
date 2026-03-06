# Routage

## Définir des routes

Toutes les routes se définissent dans `routes/api.php`.

```php
use Calvino\Core\Application;

$router = Application::getInstance()->getRouter();

// GET simple
$router->get('/hello', function () {
    return ['message' => 'Hello World!'];
});

// Avec contrôleur
$router->get('/users', 'UserController@index');
$router->post('/users', 'UserController@store');
$router->put('/users/{id}', 'UserController@update');
$router->delete('/users/{id}', 'UserController@destroy');
```

## Paramètres de route

Les paramètres dynamiques sont entourés d'accolades et passés en argument au contrôleur :

```php
$router->get('/users/{id}', 'UserController@show');

// Dans le contrôleur :
public function show(Request $request, string $id): array
{
    $user = User::find((int) $id);
    // ...
}
```

## Groupes de routes

Regroupez les routes partageant un même middleware ou préfixe :

```php
$router->group([
    'middleware' => 'auth',
], function () use ($router) {
    $router->get('/profile', 'AuthController@profile');
    $router->post('/logout', 'AuthController@logout');

    // Groupe imbriqué (admin seulement)
    $router->group(['middleware' => 'admin'], function () use ($router) {
        $router->get('/users', 'UserController@index');
    });
});
```

## Middlewares par route

Appliquer un middleware directement sur une route :

```php
$router->post('/auth/login', 'AuthController@login', ['middleware' => 'throttle']);
```

## Routes disponibles

| Méthode | URI | Middleware | Description |
|---------|-----|------------|-------------|
| GET | `/` | — | Health check |
| POST | `/auth/login` | throttle | Connexion |
| POST | `/auth/refresh-token` | — | Refresh JWT |
| POST | `/auth/forgot-password` | throttle | Demande reset mdp |
| POST | `/auth/reset-password` | throttle | Reset mdp |
| GET | `/auth/reset-password/verify` | — | Vérifie un token reset |
| POST | `/auth/logout` | auth | Déconnexion |
| GET | `/auth/profile` | auth | Profil utilisateur |
| PUT | `/auth/profile` | auth | Mise à jour du profil |
| POST | `/auth/password` | auth | Changement de mdp |
| GET | `/auth/sessions` | auth | Liste des sessions |
| POST | `/auth/sessions/logout/{id}` | auth | Déconnexion d'une session |
| POST | `/auth/sessions/logout-others` | auth | Déconnexion des autres sessions |
| GET | `/notifications` | auth | Liste des notifications |
| GET | `/users` | auth + admin | Liste des utilisateurs (paginée) |
| GET | `/activity-logs` | auth + admin | Journaux d'activité |

## Route de debug (local uniquement)

La route `GET /debug/routes` n'est disponible que si `APP_ENV` vaut `local` ou `development`. Elle est automatiquement masquée en production.
