-- Export SQL généré le 2025-05-26 08:51:00

SET FOREIGN_KEY_CHECKS=0;

-- Structure de la table `activity_logs`
DROP TABLE IF EXISTS `activity_logs`;
CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT 'ID de l''utilisateur qui a effectué l''action',
  `action` varchar(100) NOT NULL COMMENT 'Type d''action effectuée (login, logout, create, update, delete)',
  `module` varchar(100) NOT NULL COMMENT 'Module concerné (auth, products, sales, inventory, etc.)',
  `description` text NOT NULL COMMENT 'Description détaillée de l''action',
  `old_values` text DEFAULT NULL COMMENT 'Anciennes valeurs (pour les mises à jour)',
  `new_values` text DEFAULT NULL COMMENT 'Nouvelles valeurs (pour les créations/mises à jour)',
  `ip_address` varchar(45) DEFAULT NULL COMMENT 'Adresse IP de l''utilisateur',
  `user_agent` varchar(255) DEFAULT NULL COMMENT 'User agent du navigateur',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=312 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure de la table `categories`
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT 'Nom de la catégorie',
  `icon` enum('fa-capsules','fa-syringe','fa-bandage','fa-pills','fa-heart','fa-ambulance','fa-flask','fa-prescription','fa-user-md','fa-stethoscope','fa-plus-circle') NOT NULL COMMENT 'Icone de la catégorie',
  `description` text DEFAULT NULL COMMENT 'Description de la catégorie',
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Statut actif ou inactif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure de la table `customers`
DROP TABLE IF EXISTS `customers`;
CREATE TABLE `customers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT 'Nom du client',
  `email` varchar(255) DEFAULT NULL COMMENT 'Adresse email',
  `phone` varchar(20) DEFAULT NULL COMMENT 'Numéro de téléphone',
  `address` text DEFAULT NULL COMMENT 'Adresse physique',
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Statut actif ou inactif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure de la table `manufacturers`
DROP TABLE IF EXISTS `manufacturers`;
CREATE TABLE `manufacturers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT 'Nom du fabricant',
  `contact_name` varchar(255) DEFAULT NULL COMMENT 'Nom de la personne à contacter',
  `phone` varchar(20) DEFAULT NULL COMMENT 'Numéro de téléphone',
  `email` varchar(255) DEFAULT NULL COMMENT 'Adresse email',
  `address` text DEFAULT NULL COMMENT 'Adresse physique',
  `website` varchar(255) DEFAULT NULL COMMENT 'Site web',
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Statut actif ou inactif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure de la table `migrations`
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `executed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure de la table `notifications`
DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT 'ID de l''utilisateur destinataire',
  `title` varchar(255) NOT NULL COMMENT 'Titre de la notification',
  `message` text NOT NULL COMMENT 'Contenu de la notification',
  `type` enum('info','warning','error','success') NOT NULL DEFAULT 'info' COMMENT 'Type de notification (info, warning, error, success)',
  `is_read` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Statut de lecture (0=non lu, 1=lu)',
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Données supplémentaires au format JSON' CHECK (json_valid(`data`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Date de création',
  PRIMARY KEY (`id`),
  KEY `notifications_user_id_index` (`user_id`),
  KEY `notifications_is_read_index` (`is_read`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=66 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure de la table `products`
DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL COMMENT 'ID de la catégorie',
  `manufacturer_id` int(11) NOT NULL COMMENT 'ID du fabricant/fournisseur',
  `name` varchar(255) NOT NULL COMMENT 'Nom du produit',
  `reference` varchar(100) NOT NULL COMMENT 'Référence du produit',
  `barcode` varchar(100) DEFAULT NULL COMMENT 'Code-barres du produit',
  `description` text DEFAULT NULL COMMENT 'Description du produit',
  `price` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Prix de vente',
  `discount_price` decimal(10,2) DEFAULT NULL COMMENT 'Prix promotionnel',
  `stock` int(11) NOT NULL DEFAULT 0 COMMENT 'Quantité en stock',
  `stock_alert` int(11) DEFAULT NULL COMMENT 'Seuil d''alerte de stock',
  `storage_location` varchar(255) DEFAULT NULL COMMENT 'Emplacement de stockage',
  `dosage` varchar(100) DEFAULT NULL COMMENT 'Dosage du produit',
  `requires_prescription` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Nécessite une ordonnance',
  `is_eco_friendly` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Produit éco-responsable',
  `usage_instructions` text DEFAULT NULL COMMENT 'Conseils d''utilisation',
  `expiry_date` date NOT NULL COMMENT 'Date d''expiration',
  `image` varchar(255) DEFAULT NULL COMMENT 'Chemin de l''image',
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Statut actif ou inactif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  KEY `manufacturer_id` (`manufacturer_id`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `products_ibfk_2` FOREIGN KEY (`manufacturer_id`) REFERENCES `manufacturers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure de la table `sale_details`
DROP TABLE IF EXISTS `sale_details`;
CREATE TABLE `sale_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sale_id` int(11) NOT NULL COMMENT 'ID de la vente',
  `product_id` int(11) NOT NULL COMMENT 'ID du produit',
  `quantity` int(11) NOT NULL DEFAULT 1 COMMENT 'Quantité',
  `unit_price` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Prix unitaire',
  `discount` decimal(5,2) NOT NULL DEFAULT 0.00 COMMENT 'Remise',
  `total_price` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Prix total',
  `payment_status` enum('pending','partial','paid','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `sale_id` (`sale_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `sale_details_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `sale_details_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=120 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure de la table `sales`
DROP TABLE IF EXISTS `sales`;
CREATE TABLE `sales` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_number` varchar(50) NOT NULL COMMENT 'Numéro de facture',
  `customer_id` int(11) DEFAULT NULL COMMENT 'ID du client',
  `user_id` int(11) NOT NULL COMMENT 'ID de l''utilisateur',
  `sale_date` datetime NOT NULL DEFAULT current_timestamp() COMMENT 'Date de la vente',
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Montant total',
  `discount` decimal(5,2) NOT NULL DEFAULT 0.00 COMMENT 'Remise',
  `payment_method` enum('cash','card','transfer','check') NOT NULL DEFAULT 'cash' COMMENT 'Mode de paiement',
  `payment_status` enum('pending','partial','paid','cancelled') NOT NULL DEFAULT 'pending' COMMENT 'Statut du paiement',
  `note` text DEFAULT NULL COMMENT 'Note',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoice_number` (`invoice_number`),
  KEY `customer_id` (`customer_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `sales_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure de la table `transactions`
DROP TABLE IF EXISTS `transactions`;
CREATE TABLE `transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sale_id` int(11) NOT NULL COMMENT 'ID de la vente associée',
  `amount` decimal(10,2) NOT NULL COMMENT 'Montant de la transaction',
  `type` enum('payment','refund','adjustment') NOT NULL DEFAULT 'payment' COMMENT 'Type de transaction',
  `payment_method` enum('cash','card','transfer','check') NOT NULL DEFAULT 'cash' COMMENT 'Mode de paiement',
  `status` enum('completed','pending','failed','cancelled') NOT NULL DEFAULT 'completed' COMMENT 'Statut de la transaction',
  `reference` varchar(100) DEFAULT NULL COMMENT 'Référence de la transaction (ex: numéro de chèque)',
  `created_by` int(11) NOT NULL COMMENT 'ID de l''utilisateur ayant créé la transaction',
  `note` text DEFAULT NULL COMMENT 'Note sur la transaction',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `index_sale_id` (`sale_id`),
  KEY `index_status` (`status`),
  KEY `index_type` (`type`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure de la table `user_sessions`
DROP TABLE IF EXISTS `user_sessions`;
CREATE TABLE `user_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT 'ID de l''utilisateur',
  `session_id` varchar(100) NOT NULL COMMENT 'Identifiant unique de la session',
  `token` text DEFAULT NULL COMMENT 'Token JWT actif',
  `refresh_token` text DEFAULT NULL COMMENT 'Refresh token actif',
  `ip_address` varchar(45) DEFAULT NULL COMMENT 'Adresse IP',
  `user_agent` varchar(255) DEFAULT NULL COMMENT 'User agent du navigateur',
  `device_name` varchar(255) DEFAULT NULL COMMENT 'Nom de l''appareil',
  `device_type` varchar(50) DEFAULT NULL COMMENT 'Type d''appareil (mobile, desktop, tablet)',
  `location` varchar(255) DEFAULT NULL COMMENT 'Localisation approximative',
  `last_activity` timestamp NULL DEFAULT NULL COMMENT 'Dernière activité',
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Session active ou non',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `index_user_id` (`user_id`),
  KEY `index_session_id` (`session_id`),
  KEY `index_is_active` (`is_active`),
  CONSTRAINT `user_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Structure de la table `users`
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT 'Nom complet de l''utilisateur',
  `email` varchar(255) NOT NULL COMMENT 'Adresse email unique',
  `password` varchar(255) NOT NULL COMMENT 'Mot de passe crypté',
  `role` enum('admin','manager','pharmacist','cashier') NOT NULL DEFAULT 'pharmacist' COMMENT 'Rôle de l''utilisateur',
  `phone` varchar(20) DEFAULT NULL COMMENT 'Numéro de téléphone',
  `address` text DEFAULT NULL COMMENT 'Adresse physique',
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Statut actif ou inactif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS=1;
