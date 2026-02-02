# Guide de Contribution

Merci de votre intérêt pour contribuer à Calvino Framework ! Ce document vous guide à travers le processus de contribution.

## Code de Conduite

En participant à ce projet, vous acceptez de respecter notre code de conduite. Soyez respectueux et constructif dans vos interactions.

## Comment Contribuer

### Signaler un Bug

1. Vérifiez que le bug n'a pas déjà été signalé dans les [issues](https://github.com/DOUMBAJC/calvino/issues)
2. Ouvrez une nouvelle issue avec un titre descriptif
3. Incluez :
   - Version de PHP
   - Version du framework
   - Description détaillée du problème
   - Étapes pour reproduire
   - Comportement attendu vs comportement actuel

### Proposer une Fonctionnalité

1. Ouvrez une issue pour discuter de la fonctionnalité
2. Expliquez le cas d'usage et les bénéfices
3. Attendez les retours avant de commencer le développement

### Soumettre une Pull Request

1. **Fork** le repository
2. **Créez une branche** : `git checkout -b feature/ma-fonctionnalite`
3. **Committez** vos changements : `git commit -m 'Ajout de ma fonctionnalité'`
4. **Pushez** : `git push origin feature/ma-fonctionnalite`
5. **Ouvrez une Pull Request**

## Standards de Code

### Style PHP

- Suivez PSR-12 pour le style de code
- Utilisez des noms de variables descriptifs en camelCase
- Documentez les méthodes publiques avec PHPDoc

```php
/**
 * Récupère un utilisateur par son ID
 *
 * @param int $id L'identifiant de l'utilisateur
 * @return User|null
 */
public function getUserById(int $id): ?User
{
    return User::find($id);
}
```

### Tests

- Ajoutez des tests pour les nouvelles fonctionnalités
- Assurez-vous que tous les tests passent : `composer test`
- Visez une couverture de code > 80%

### Commits

Utilisez des messages de commit clairs :

```
feat: Ajout du support PostgreSQL
fix: Correction du bug de routage avec paramètres
docs: Mise à jour du guide d'installation
refactor: Amélioration de la performance du QueryBuilder
```

## Développement Local

### Configuration

```bash
git clone https://github.com/DOUMBAJC/calvino.git
cd calvino-framework
composer install
cp .env.example .env
```

### Lancer les Tests

```bash
composer test
```

### Vérifier le Style de Code

```bash
composer cs-check
composer cs-fix
```

## Structure du Projet

```
src/
├── Core/           # Composants principaux
├── Console/        # Commandes CLI
├── Middleware/     # Middlewares de base
├── Providers/      # Service providers
└── Helpers/        # Fonctions utilitaires
```

## Licence

En contribuant, vous acceptez que vos contributions soient sous licence MIT.

## Questions ?

N'hésitez pas à ouvrir une issue pour toute question !
