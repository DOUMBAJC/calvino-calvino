<?php

return [
    // Messages généraux
    'welcome' => 'Bienvenue sur notre API',
    'not_found' => 'Ressource non trouvée',
    'unauthorized' => 'Non autorisé',
    'forbidden' => 'Accès interdit',
    'validation_error' => 'Erreur de validation',
    'server_error' => 'Erreur serveur',
    'success' => 'Opération réussie',
    'created' => 'Ressource créée avec succès',
    'updated' => 'Ressource mise à jour avec succès',
    'deleted' => 'Ressource supprimée avec succès',
    
    // Messages d'authentification
    'auth' => [
        'login_success' => 'Connexion réussie',
        'login_failed' => 'Identifiants incorrects',
        'logout_success' => 'Déconnexion réussie',
        'token_invalid' => 'Jeton d\'authentification invalide',
        'token_expired' => 'Jeton d\'authentification expiré',
        'token_required' => 'Jeton d\'authentification requis',
        'account_locked' => 'Compte verrouillé',
        'account_inactive' => 'Compte inactif',
        'password_reset_sent' => 'Email de réinitialisation de mot de passe envoyé',
        'password_reset_success' => 'Mot de passe réinitialisé avec succès',
    ],
    
    // Messages pour les produits
    'products' => [
        'created' => 'Produit créé avec succès',
        'updated' => 'Produit mis à jour avec succès',
        'deleted' => 'Produit supprimé avec succès',
        'not_found' => 'Produit non trouvé',
        'out_of_stock' => 'Produit en rupture de stock',
        'low_stock' => 'Stock faible pour ce produit',
        'expired' => 'Produit expiré',
        'expiring_soon' => 'Produit expirant bientôt',
    ],
    
    // Messages pour les commandes
    'orders' => [
        'created' => 'Commande créée avec succès',
        'updated' => 'Commande mise à jour avec succès',
        'deleted' => 'Commande supprimée avec succès',
        'not_found' => 'Commande non trouvée',
        'processed' => 'Commande traitée avec succès',
        'shipped' => 'Commande expédiée',
        'delivered' => 'Commande livrée',
        'cancelled' => 'Commande annulée',
        'payment_required' => 'Paiement requis pour cette commande',
        'payment_received' => 'Paiement reçu pour cette commande',
    ],
    
    // Messages pour les utilisateurs
    'users' => [
        'created' => 'Utilisateur créé avec succès',
        'updated' => 'Utilisateur mis à jour avec succès',
        'deleted' => 'Utilisateur supprimé avec succès',
        'not_found' => 'Utilisateur non trouvé',
        'password_changed' => 'Mot de passe changé avec succès',
        'profile_updated' => 'Profil mis à jour avec succès',
    ],
    
    // Messages pour les fournisseurs
    'suppliers' => [
        'created' => 'Fournisseur créé avec succès',
        'updated' => 'Fournisseur mis à jour avec succès',
        'deleted' => 'Fournisseur supprimé avec succès',
        'not_found' => 'Fournisseur non trouvé',
    ],
    
    // Messages pour les catégories
    'categories' => [
        'created' => 'Catégorie créée avec succès',
        'updated' => 'Catégorie mise à jour avec succès',
        'deleted' => 'Catégorie supprimée avec succès',
        'not_found' => 'Catégorie non trouvée',
    ],
    
    // Messages pour les factures
    'invoices' => [
        'created' => 'Facture créée avec succès',
        'updated' => 'Facture mise à jour avec succès',
        'deleted' => 'Facture supprimée avec succès',
        'not_found' => 'Facture non trouvée',
        'paid' => 'Facture payée',
        'partially_paid' => 'Facture partiellement payée',
        'overdue' => 'Facture en retard de paiement',
        'sent' => 'Facture envoyée au client',
    ],
    
    // Messages d'erreur
    'errors' => [
        'default' => 'Une erreur est survenue',
        'connection' => 'Erreur de connexion',
        'database' => 'Erreur de base de données',
        'file_upload' => 'Erreur lors du téléchargement du fichier',
        'file_too_large' => 'Le fichier est trop volumineux',
        'invalid_format' => 'Format invalide',
        'required_field' => 'Ce champ est requis',
    ],
]; 