SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
CREATE DATABASE IF NOT EXISTS greenmarket;
USE greenmarket;

-- Table des comptes
CREATE TABLE `compte` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL UNIQUE,
  `motpasse` varchar(255) NOT NULL,
  `role` enum('client','producteur','admin') NOT NULL DEFAULT 'client',
  `statut` enum('actif','suspendu','en_attente') DEFAULT 'actif',
  `date_inscription` datetime DEFAULT current_timestamp(),
  `question_secrete` varchar(255) DEFAULT NULL,
  `reponse_secrete` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des catégories (MODIFIÉE : Frais / Non Frais)
CREATE TABLE `categorie` (
  `idcat` int(11) NOT NULL AUTO_INCREMENT,
  `libelle` varchar(100) NOT NULL UNIQUE,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`idcat`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des produits
CREATE TABLE `produit` (
  `reference` varchar(15) NOT NULL PRIMARY KEY,
  `libelle` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `prixu` decimal(10,2) NOT NULL CHECK (`prixu` > 0),
  `quantite` int(11) DEFAULT 0,
  `statut` enum('en_attente','valide','refuse') DEFAULT 'en_attente',
  `dateachat` datetime DEFAULT current_timestamp(),
  `idcateg` int(11) NOT NULL,
  `id_producteur` int(11) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  FOREIGN KEY (`idcateg`) REFERENCES `categorie` (`idcat`),
  FOREIGN KEY (`id_producteur`) REFERENCES `compte` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des médias
CREATE TABLE `produit_media` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reference_produit` varchar(15) NOT NULL,
  `type` enum('image','video') NOT NULL,
  `url` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `ordre` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`reference_produit`) REFERENCES `produit` (`reference`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des commandes
CREATE TABLE `commande` (
  `idcom` int(11) NOT NULL AUTO_INCREMENT,
  `id_client` int(11) NOT NULL,
  `montant_total` decimal(10,2) NOT NULL,
  `methode_paiement` varchar(50) NOT NULL,
  `statut` enum('en_attente','livree','annulee') DEFAULT 'en_attente',
  `date_commande` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`idcom`),
  FOREIGN KEY (`id_client`) REFERENCES `compte` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des lignes de commande
CREATE TABLE `commande_produit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idcom` int(11) NOT NULL,
  `reference_produit` varchar(15) NOT NULL,
  `quantite` int(11) NOT NULL,
  `prix_unitaire` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`idcom`) REFERENCES `commande` (`idcom`) ON DELETE CASCADE,
  FOREIGN KEY (`reference_produit`) REFERENCES `produit` (`reference`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des avis
CREATE TABLE `avis` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_client` int(11) NOT NULL,
  `reference_produit` varchar(15) NOT NULL,
  `note` int(11) NOT NULL CHECK (`note` between 1 and 5),
  `commentaire` text DEFAULT NULL,
  `date_avis` datetime DEFAULT current_timestamp(),
  `statut` enum('en_attente','valide','refuse') DEFAULT 'en_attente',
  `id_moderateur` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`id_client`) REFERENCES `compte` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`reference_produit`) REFERENCES `produit` (`reference`) ON DELETE CASCADE,
  FOREIGN KEY (`id_moderateur`) REFERENCES `compte` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des points fidélité
CREATE TABLE `point_fidelite` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_client` int(11) NOT NULL,
  `points` int(11) NOT NULL,
  `source` varchar(50) NOT NULL,
  `date_obtention` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`id_client`) REFERENCES `compte` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des badges producteur
CREATE TABLE `badge_producteur` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_producteur` int(11) NOT NULL,
  `badge_nom` varchar(50) NOT NULL,
  `date_obtention` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`id_producteur`) REFERENCES `compte` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- DONNÉES DE DÉMONSTRATION
-- =====================================================
INSERT INTO `compte` (`nom`, `email`, `motpasse`, `role`, `statut`, `question_secrete`, `reponse_secrete`) VALUES
('Administrateur', 'admin@greenmarket.ma', '$argon2id$v=19$m=65536,t=4,p=1$NnA5aEVvV05Uc29nOFMxYg$25xC2xGot1inyxVnoyB+63GX4xmQa1xjrTPHObjhVLs', 'admin', 'actif', NULL, NULL),
('Alaiz Mohammed', 'alaiz@greenmarket.ma', '$argon2id$v=19$m=65536,t=4,p=1$NnA5aEVvV05Uc29nOFMxYg$25xC2xGot1inyxVnoyB+63GX4xmQa1xjrTPHObjhVLs', 'producteur', 'actif', 'Quel est votre premier animal de compagnie ?', 'titi'),
('Azaanoun Ismail', 'contactmebymymail@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$NnA5aEVvV05Uc29nOFMxYg$25xC2xGot1inyxVnoyB+63GX4xmQa1xjrTPHObjhVLs', 'client', 'actif', 'Quel est le nom de votre école primaire ?', 'victor'),
('Lamiae El Ftouh', 'LamFet@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$NnA5aEVvV05Uc29nOFMxYg$25xC2xGot1inyxVnoyB+63GX4xmQa1xjrTPHObjhVLs', 'client', 'actif', 'Quel est le nom de votre école primaire ?', 'victor'),
('Chtoun Ismail', 'chtoun@greenmarket.ma', '$argon2id$v=19$m=65536,t=4,p=1$NnA5aEVvV05Uc29nOFMxYg$25xC2xGot1inyxVnoyB+63GX4xmQa1xjrTPHObjhVLs', 'producteur', 'actif', 'Quel est votre premier animal de compagnie ?', 'titi'),
('Akhrif Roumaissae', 'roumaissae@greenmarket.ma', '$argon2id$v=19$m=65536,t=4,p=1$NnA5aEVvV05Uc29nOFMxYg$25xC2xGot1inyxVnoyB+63GX4xmQa1xjrTPHObjhVLs', 'producteur', 'actif', 'Quel est votre premier animal de compagnie ?', 'titi');

-- CATÉGORIES (2 SEULEMENT)
INSERT INTO `categorie` (`libelle`, `description`) VALUES
('Frais', 'Produits frais, non transformés, provenant directement du terroir.'),
('Non Frais', 'Produits transformés, séchés ou conditionnés pour une longue conservation.');

-- PRODUITS
-- 1. Zembo d'Al Hoceima (Alaiz Mohammed - id=2) -> Frais (idcat=1)
INSERT INTO `produit` VALUES ('ZEM-HOC-001', 'Zembo d\'Al Hoceima', 'Mélange d\'épices traditionnelles de la région d\'Al Hoceima.', 99.00, 1000, 'valide', NOW(), 1, 2, 'https://i.ibb.co/DfmfSXcz/Zembo-d-Al-Hoceima.png');
-- 2. Figues Séchées (Chtoun Ismail - id=5) -> Frais (idcat=1)
INSERT INTO `produit` VALUES ('FIG-CHE-002', 'Figues Séchées Naturelles de Chefchaouen', 'Figues séchées au soleil, 100% naturelles.', 100.00, 1000, 'valide', NOW(), 1, 5, 'https://i.ibb.co/rG9pKw36/Figues-de-Chefchaouen.png');
-- 3. Châtaignes Grillées (Akhrif Roumaissae - id=6) -> Non Frais (idcat=2)
INSERT INTO `produit` VALUES ('CHAT-GRI-001', 'Châtaignes Grillées à la Marocaine', 'Châtaignes grillées à la main, prêtes à déguster.', 99.99, 1000, 'valide', NOW(), 2, 6, 'https://i.ibb.co/YF45SBK6/Chataignes-grillees-de-Tetouan.png');

INSERT INTO `produit_media` VALUES 
(NULL, 'ZEM-HOC-001', 'image', 'https://i.ibb.co/mF8bQ9Z/Zembo-boite.png', 'Détail de la boîte', 1),
(NULL, 'FIG-CHE-002', 'image', 'https://i.ibb.co/Vv9c8vJ/Figues-assiette.png', 'Présentation sur assiette', 1),
(NULL, 'CHAT-GRI-001', 'image', 'https://i.ibb.co/6P0vZQd/Chataignes-sac.png', 'Le sachet kraft', 1),
(NULL, 'CHAT-GRI-001', 'video', 'https://www.w3schools.com/html/mov_bbb.mp4', 'Démonstration torréfaction', 2);
COMMIT;