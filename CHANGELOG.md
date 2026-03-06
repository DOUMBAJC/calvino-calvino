# Changelog

Toutes les modifications notables de ce projet sont documentées dans ce fichier.

Le format suit [Keep a Changelog](https://keepachangelog.com/fr/1.0.0/),
et ce projet adhère au [Versionnement Sémantique](https://semver.org/lang/fr/).

---

## [Unreleased]

### Ajouté
- **ThrottleMiddleware** — Protection anti-brute force sur les routes publiques (`/auth/login`, `/auth/forgot-password`, `/auth/reset-password`). Configurable via `RATE_LIMIT_MAX` et `RATE_LIMIT_DECAY` dans `.env`. Retourne les en-têtes `X-RateLimit-Limit`, `X-RateLimit-Remaining` et `Retry-After` (HTTP 429).
- **Réinitialisation de mot de passe** — Nouveau `PasswordResetController` avec trois endpoints :
  - `POST /auth/forgot-password` : génère un token sécurisé (SHA-256) et notifie l'utilisateur
  - `POST /auth/reset-password` : valide le token et met à jour le mot de passe
  - `GET /auth/reset-password/verify` : vérifie la validité d'un token sans le consommer
- **Migration `password_resets`** — Table de stockage des tokens de reset (hashés, avec expiration).
- **Pagination dans `UserController`** — La route `GET /users` supporte désormais les paramètres `page`, `per_page` (max 100), `search`, `role` et `status`. La réponse inclut un objet `pagination` avec `current_page`, `per_page`, `total`, `total_pages`, `from`, `to`.
- **Routes manquantes exposées** — Ajout des routes notifications et journaux d'activité dans `routes/api.php` (précédemment sans routes définies).
- **Route `GET /auth/me`** — Raccourci pour récupérer l'utilisateur courant.
- **Route `POST /auth/password/force-change`** — Changement forcé de mot de passe par défaut.
- **Documentation complète** — Création des fichiers manquants référencés dans le README :
  - `docs/routing.md`
  - `docs/database.md`
  - `docs/authentication.md`
  - `docs/middleware.md`
  - `docs/console.md`
- **Tests unitaires PHPUnit** — Suite de tests couvrant la logique pure :
  - `ThrottleMiddlewareTest` — Rate limiting, expiration, isolation par IP
  - `UserModelTest` — Hachage, détection mot de passe par défaut, logique de session
  - `ActivityLogControllerTest` — Filtrage, tri, pagination, statistiques
  - `PasswordResetControllerTest` — Génération de token, hash, expiration, construction du lien
  - `UserControllerPaginationTest` — Pagination, filtres, bornes de per_page/page
- **Configuration PHPUnit** — Ajout de `phpunit.xml` et `tests/bootstrap.php`.
- **Traductions** — Ajout de la clé `password_reset` dans `resources/lang/fr/api.php` et `resources/lang/en/api.php`.
- **Variables d'environnement** — `.env.example` enrichi avec `JWT_REFRESH_TTL`, `RATE_LIMIT_MAX`, `RATE_LIMIT_DECAY` et la section `MAIL_*`.
- **Script `db:seed`** — Ajout dans `composer.json`.

### Modifié
- **Route `/debug/routes`** — Sécurisée : n'est plus accessible qu'en environnement `local` ou `development` (`APP_ENV`). En production, la route n'existe simplement pas.
- **`config/middlewares.php`** — Ajout des middlewares nommés `throttle` et `locale`.
- **`UserController`** — Refactoring : pagination, filtres, typage strict (`declare(strict_types=1)`), méthode `getPdoConnection()` extraite.

### Sécurité
- Protection brute-force sur la connexion (ThrottleMiddleware)
- Route de debug masquée en production
- Tokens de reset de mot de passe stockés hashés (SHA-256) en base de données
- Le token brut n'est jamais persisté — seul son hash SHA-256 est enregistré
- Réponse identique qu'un email existe ou non (protection contre l'énumération)

---

## [1.0.0] — 2025-04-15

### Ajouté
- Routage HTTP avec groupes et middlewares
- Authentification JWT avec refresh token et gestion multi-sessions (max 3)
- ORM simple avec relations `hasMany` / `belongsTo`
- Système de migrations
- CLI : `make:controller`, `make:model`, `make:migration`, `migrate`, `serve`
- Notifications persistantes en base de données
- Journal d'activité (audit log)
- Géolocalisation par IP lors des connexions
- Internationalisation FR/EN
- Middlewares : CORS, JSON, Auth, Admin
- Seeders de base (`UserSeeder`)
- Structure PSR-4 avec Composer
