# Base de données & ORM

## Configuration

Dans votre fichier `.env` :

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=calvino_db
DB_USERNAME=root
DB_PASSWORD=
DB_CHARSET=utf8mb4
```

## Migrations

### Créer une migration

```bash
composer calvino make:migration create_products_table
```

Cela génère un fichier dans `database/migrations/` :

```php
<?php

class CreateProductsTable extends \App\Core\Migration
{
    public function up(): void
    {
        $this->create('products', function (\App\Core\Schema $table) {
            $table->id();
            $table->string('name');
            $table->decimal('price', 10, 2);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        $this->drop('products');
    }
}
```

### Types de colonnes disponibles

| Méthode | Type SQL |
|---------|----------|
| `id()` | BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY |
| `string('col', 255)` | VARCHAR(255) |
| `text('col')` | TEXT |
| `integer('col')` | INT |
| `unsignedBigInteger('col')` | BIGINT UNSIGNED |
| `decimal('col', 10, 2)` | DECIMAL(10,2) |
| `boolean('col')` | TINYINT(1) |
| `enum('col', ['a','b'])` | ENUM('a','b') |
| `timestamp('col')` | TIMESTAMP |
| `timestamps()` | created_at + updated_at |

### Exécuter les migrations

```bash
# Appliquer toutes les migrations en attente
composer calvino migrate

# Annuler la dernière migration
composer calvino migrate:rollback

# Remettre à zéro (rollback de toutes les migrations)
composer calvino migrate:reset
```

## Seeders

### Créer un seeder

```bash
# Créer manuellement dans database/seeders/
```

Exemple de seeder :

```php
<?php

namespace Database\Seeders;

use App\Models\User;

class UserSeeder
{
    public function run(): void
    {
        User::create([
            'name'      => 'Admin',
            'email'     => 'admin@example.com',
            'password'  => User::hashPassword('MotDePasseSecurise!'),
            'role'      => 'admin',
            'is_active' => 1,
        ]);
    }
}
```

```bash
composer calvino db:seed
```

## ORM — Modèles

### Créer un modèle

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
    protected array $fillable = ['name', 'price', 'description', 'is_active'];
}
```

### Opérations CRUD

```php
// Récupérer tous les enregistrements
$products = Product::all();

// Trouver par ID
$product = Product::find(1);

// Filtrer
$activeProducts = Product::where('is_active', 1)->get();
$expensive      = Product::where('price', 100, '>')->get();

// Premier résultat
$first = Product::where('name', 'Paracétamol')->first();

// Créer
$product = Product::create([
    'name'  => 'Ibuprofène',
    'price' => 5.50,
]);

// Mettre à jour
$product->price = 6.00;
$product->save();

// Supprimer
$product->delete();
```

### Relations

```php
class User extends Model
{
    // Un utilisateur a plusieurs sessions
    public function sessions()
    {
        return $this->hasMany(UserSession::class);
    }
}

class ActivityLog extends Model
{
    // Un log appartient à un utilisateur
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
```

### Traits disponibles

| Trait | Description |
|-------|-------------|
| `Authenticatable` | Login JWT, createToken, verifyPassword, hashPassword |
| `Notifiable` | Envoi de notifications persistantes |
| `LoggableActivity` | Journalisation des actions |

```php
use Calvino\Auth\Authenticatable;
use Calvino\Traits\Notifiable;

class User extends Model
{
    use Authenticatable, Notifiable;
}
```

## Tables du projet

| Table | Description |
|-------|-------------|
| `users` | Utilisateurs du système |
| `user_sessions` | Sessions actives (JWT + refresh token) |
| `notifications` | Notifications persistantes |
| `activity_logs` | Journal d'audit des actions |
| `password_resets` | Tokens de réinitialisation de mot de passe |
