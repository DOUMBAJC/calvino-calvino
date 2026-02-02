# Calvino Framework

![PHP Version](https://img.shields.io/badge/PHP-%5E8.2-blue)
![License](https://img.shields.io/badge/license-MIT-green)
![Version](https://img.shields.io/badge/version-1.0.0-orange)

Un mini-framework PHP moderne et léger pour créer rapidement des APIs REST avec authentification JWT, ORM, migrations et CLI.

## ✨ Fonctionnalités

- 🚀 **Routage HTTP** - Système de routage flexible avec support des groupes et middlewares
- 🔐 **Authentification JWT** - Système d'authentification intégré avec gestion de sessions
- 🗄️ **ORM Simple** - Modèles avec relations (belongsTo, hasMany) et QueryBuilder
- 📦 **Migrations** - Système de migrations de base de données
- 🛠️ **CLI Puissante** - Commandes pour générer controllers, models, migrations
- ⚡ **Léger** - Aucune dépendance lourde, seulement PHP natif
- 🎨 **PSR-4** - Autoloading standard et structure moderne

## 📋 Prérequis

- PHP 8.2 ou supérieur
- Extension PDO
- Extension JSON
- Extension mbstring
- MySQL 5.7+ ou PostgreSQL

## 🚀 Installation

### Via Composer

```bash
composer require calvino/calvino
```

### Créer un Nouveau Projet

```bash
composer create-project calvino/calvino mon-projet
cd mon-projet
```

## ⚡ Démarrage Rapide

### 1. Configuration

Copiez le fichier `.env.example` et configurez votre base de données :

```bash
cp .env.example .env
```

```env
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=ma_base
DB_USERNAME=root
DB_PASSWORD=
```

### 2. Migrations

Créez les tables de base de données :

```bash
composer calvino migrate
```

### 3. Lancer le Serveur

```bash
php -S localhost:8000 -t public
```

Votre API est maintenant accessible sur `http://localhost:8000` 🎉

## 📖 Utilisation

### Définir des Routes

Dans `routes/api.php` :

```php
<?php

use Calvino\Core\Application;

$router = Application::getInstance()->getRouter();

// Route simple
$router->get('/hello', function() {
    return ['message' => 'Hello World!'];
});

// Route avec contrôleur
$router->get('/users', 'UserController@index');

// Groupe avec middleware
$router->group(['middleware' => 'auth'], function() use ($router) {
    $router->get('/profile', 'UserController@profile');
    $router->post('/logout', 'AuthController@logout');
});
```

### Créer un Contrôleur

```bash
composer calvino make:controller ProductController
```

```php
<?php

namespace App\Controllers;

use Calvino\Core\Controller;
use Calvino\Core\Request;

class ProductController extends Controller
{
    public function index(Request $request): array
    {
        return [
            'status' => 'success',
            'data' => []
        ];
    }
}
```

### Créer un Modèle

```bash
composer calvino make:model Product
```

```php
<?php

namespace App\Models;

use Calvino\Core\Model;

class Product extends Model
{
    protected string $table = 'products';
    
    protected array $fillable = ['name', 'price', 'description'];
}
```

### Utiliser l'ORM

```php
// Récupérer tous les produits
$products = Product::all();

// Trouver par ID
$product = Product::find(1);

// Créer
$product = Product::create([
    'name' => 'Laptop',
    'price' => 999.99
]);

// Mettre à jour
$product->price = 899.99;
$product->save();

// Supprimer
$product->delete();

// Requête avec conditions
$products = Product::where('price', 100, '>')->get();
```

### Créer une Migration

```bash
composer calvino make:migration create_products_table
```

```php
<?php

use Calvino\Core\Migration;
use Calvino\Core\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function ($table) {
            $table->id();
            $table->string('name');
            $table->decimal('price', 10, 2);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
```

## 🔧 Commandes CLI

```bash
# Démarrer le serveur de développement
composer calvino serve

# Migrations
composer calvino migrate              # Exécuter les migrations
composer calvino migrate:rollback     # Annuler la dernière migration
composer calvino migrate:reset        # Annuler toutes les migrations

# Génération de code
composer calvino make:controller NomController
composer calvino make:model NomModel
composer calvino make:migration create_table_name

# Aide
composer calvino help
composer calvino route:list
```

## 📚 Documentation

Pour une documentation complète, consultez le dossier [docs/](docs/) :

- [Installation](docs/installation.md)
- [Routage](docs/routing.md)
- [Base de données & ORM](docs/database.md)
- [Authentification](docs/authentication.md)
- [Middlewares](docs/middleware.md)
- [Console/CLI](docs/console.md)

## 🤝 Contribution

Les contributions sont les bienvenues ! Consultez [CONTRIBUTING.md](CONTRIBUTING.md) pour plus de détails.

## 📄 Licence

Ce projet est sous licence MIT. Voir le fichier [LICENSE](LICENSE) pour plus de détails.

## 👨‍💻 Auteur

**DOUMBA Jean Calvain**

- GitHub: [@DOUMBAJC](https://github.com/DOUMBAJC)

## 🙏 Remerciements

Merci à tous les contributeurs qui ont aidé à améliorer ce framework !

---

Fait avec ❤️ par la communauté PHP