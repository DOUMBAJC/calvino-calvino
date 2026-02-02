# Installation de Calvino Framework

Ce guide vous accompagne dans l'installation et la configuration de Calvino Framework.

## Prérequis Système

Avant de commencer, assurez-vous que votre système répond aux exigences suivantes :

- **PHP** : Version 8.2 ou supérieure
- **Extensions PHP** :
  - PDO (pour la base de données)
  - JSON
  - mbstring
- **Base de données** : MySQL 5.7+, MariaDB 10.2+, ou PostgreSQL 9.6+
- **Composer** : Gestionnaire de dépendances PHP

### Vérifier votre version de PHP

```bash
php -v
```

### Vérifier les extensions PHP

```bash
php -m | grep -E 'pdo|json|mbstring'
```

## Installation

### Option 1 : Nouveau Projet (Recommandé)

Créez un nouveau projet basé sur Calvino Framework :

```bash
composer create-project calvino/calvino mon-projet
cd mon-projet
```

### Option 2 : Ajouter à un Projet Existant

Ajoutez Calvino Framework à un projet PHP existant :

```bash
composer require calvino/calvino
```

## Configuration

### 1. Variables d'Environnement

Copiez le fichier d'exemple et configurez vos paramètres :

```bash
cp .env.example .env
```

Éditez le fichier `.env` :

```env
# Application
APP_NAME="Mon API"
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_TIMEZONE=UTC

# Base de données
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=ma_base_de_donnees
DB_USERNAME=root
DB_PASSWORD=

# JWT Authentication
JWT_SECRET=votre_cle_secrete_tres_longue_et_aleatoire
JWT_EXPIRATION=3600
JWT_REFRESH_EXPIRATION=604800

# CORS
CORS_ALLOWED_ORIGINS=http://localhost:3000
```

### 2. Générer une Clé JWT

Pour sécuriser l'authentification, générez une clé secrète forte :

```bash
php -r "echo bin2hex(random_bytes(64)) . PHP_EOL;"
```

Copiez le résultat dans `JWT_SECRET` de votre fichier `.env`.

### 3. Créer la Base de Données

Créez manuellement votre base de données :

```sql
CREATE DATABASE ma_base_de_donnees CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 4. Exécuter les Migrations

Créez les tables nécessaires :

```bash
composer calvino migrate
```

## Structure du Projet

Après l'installation, votre projet aura la structure suivante :

```
mon-projet/
├── app/
│   ├── Controllers/     # Vos contrôleurs
│   ├── Models/          # Vos modèles
│   └── Middleware/      # Middlewares personnalisés
├── bootstrap/
│   ├── app.php          # Bootstrap de l'application
│   └── autoload.php     # Autoloader
├── config/
│   ├── app.php          # Configuration principale
│   ├── database.php     # Configuration BDD
│   └── routes.php       # Configuration des routes
├── database/
│   └── migrations/      # Fichiers de migration
├── public/
│   └── index.php        # Point d'entrée
├── routes/
│   └── api.php          # Définition des routes
├── .env                 # Variables d'environnement
├── .env.example         # Exemple de configuration
└── composer.json        # Dépendances
```

## Lancer le Serveur de Développement

### Serveur PHP Intégré

```bash
php -S localhost:8000 -t public
```

Votre API est accessible sur `http://localhost:8000`.

### Avec Docker (Optionnel)

Si vous préférez utiliser Docker :

```dockerfile
FROM php:8.2-cli
RUN docker-php-ext-install pdo pdo_mysql
WORKDIR /app
COPY . /app
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
```

```bash
docker build -t mon-api .
docker run -p 8000:8000 mon-api
```

## Vérification de l'Installation

Testez que tout fonctionne correctement :

```bash
curl http://localhost:8000
```

Vous devriez recevoir une réponse JSON :

```json
{
  "status": "success",
  "message": "Bienvenue sur votre API Calvino"
}
```

## Prochaines Étapes

Maintenant que Calvino Framework est installé :

1. 📖 Consultez le [guide de routage](routing.md)
2. 🗄️ Apprenez à utiliser [l'ORM](database.md)
3. 🔐 Configurez [l'authentification](authentication.md)
4. 🛠️ Explorez les [commandes CLI](console.md)

## Dépannage

### Erreur : "Class not found"

Régénérez l'autoloader Composer :

```bash
composer dump-autoload
```

### Erreur de connexion à la base de données

Vérifiez vos paramètres dans `.env` et que la base de données existe.

### Permission refusée sur bin/calvino

Rendez le script exécutable :

```bash
chmod +x bin/calvino
```

### Port 8000 déjà utilisé

Changez le port :

```bash
php -S localhost:8080 -t public
```

## Support

Si vous rencontrez des problèmes :

- 📝 Consultez la [documentation complète](../README.md)
- 🐛 Ouvrez une [issue sur GitHub](https://github.com/DOUMBAJC/calvino-framework/issues)
- 💬 Rejoignez notre communauté
