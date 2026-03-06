# Console / CLI

Calvino fournit une interface en ligne de commande pour accélérer le développement.

## Utilisation

```bash
composer calvino <commande>
# ou
php vendor/bin/calvino <commande>
```

## Commandes disponibles

### Serveur de développement

```bash
composer calvino serve
# ou
composer dev
```

Démarre le serveur PHP intégré sur `http://localhost:8000`.

### Migrations

```bash
# Exécuter toutes les migrations en attente
composer calvino migrate

# Annuler la dernière migration
composer calvino migrate:rollback

# Annuler toutes les migrations
composer calvino migrate:reset
```

### Seeders

```bash
# Exécuter le seeder principal
composer calvino db:seed
```

### Génération de code

```bash
# Créer un contrôleur
composer calvino make:controller ProductController

# Créer un modèle
composer calvino make:model Product

# Créer une migration
composer calvino make:migration create_products_table
```

### Aide et debug

```bash
# Afficher l'aide
composer calvino help

# Lister toutes les routes (en dev uniquement)
composer calvino route:list
```

## Fichiers générés

### Contrôleur (`make:controller`)

Créé dans `app/Controllers/Api/` :

```php
<?php

namespace App\Controllers\Api;

use Calvino\Core\Controller;
use Calvino\Core\Request;

class ProductController extends Controller
{
    public function index(): array
    {
        return $this->success('Liste récupérée', []);
    }

    public function show(Request $request, string $id): array
    {
        return $this->success('Élément récupéré', []);
    }

    public function store(Request $request): array
    {
        return $this->success('Élément créé', [], 201);
    }

    public function update(Request $request, string $id): array
    {
        return $this->success('Élément mis à jour', []);
    }

    public function destroy(Request $request, string $id): array
    {
        return $this->success('Élément supprimé');
    }
}
```

### Modèle (`make:model`)

Créé dans `app/Models/` :

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

### Migration (`make:migration`)

Créé dans `database/migrations/` avec horodatage :

```php
<?php

class CreateProductsTable extends \App\Core\Migration
{
    public function up(): void
    {
        $this->create('products', function (\App\Core\Schema $table) {
            $table->id();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        $this->drop('products');
    }
}
```

## Tests

```bash
# Exécuter tous les tests
composer test

# Ou directement avec PHPUnit
./vendor/bin/phpunit

# Suite spécifique
./vendor/bin/phpunit --testsuite Unit
./vendor/bin/phpunit --testsuite Feature

# Test spécifique
./vendor/bin/phpunit tests/Unit/ThrottleMiddlewareTest.php
```
