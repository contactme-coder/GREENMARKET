-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 28, 2026 at 12:36 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `greenmarket`
--

-- --------------------------------------------------------

--
-- Table structure for table `avis`
--

CREATE TABLE `avis` (
  `id` int(11) NOT NULL,
  `id_client` int(11) NOT NULL,
  `reference_produit` varchar(15) NOT NULL,
  `note` int(11) NOT NULL CHECK (`note` between 1 and 5),
  `commentaire` text DEFAULT NULL,
  `date_avis` datetime DEFAULT current_timestamp(),
  `statut` enum('en_attente','valide','refuse') DEFAULT 'en_attente',
  `id_moderateur` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `badge_producteur`
--

CREATE TABLE `badge_producteur` (
  `id` int(11) NOT NULL,
  `id_producteur` int(11) NOT NULL,
  `badge_nom` varchar(50) NOT NULL,
  `date_obtention` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categorie`
--

CREATE TABLE `categorie` (
  `idcat` int(11) NOT NULL,
  `libelle` varchar(100) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categorie`
--

INSERT INTO `categorie` (`idcat`, `libelle`, `description`) VALUES
(1, 'Fruits & Légumes', 'Produits frais du maraîchage biologique local'),
(2, 'Miel & Apiculture', 'Miels artisanaux, cire et produits de la ruche'),
(3, 'Huiles & Olives', 'Huile d\'olive extra-vierge pressée à froid'),
(4, 'Produits Laitiers', 'Fromages fermiers, lait cru, yaourts artisanaux'),
(5, 'Épices & Aromates', 'Herbes séchées et épices du terroir marocain'),
(6, 'Céréales & Légumineuses', 'Blé, orge, lentilles et légumineuses biologiques'),
(7, 'Conserves & Confitures', 'Confitures maison, conserves de légumes locaux'),
(8, 'Artisanat & Bien-être', 'Savons naturels, cosmétiques bio, huiles essentielles');

-- --------------------------------------------------------

--
-- Table structure for table `commande`
--

CREATE TABLE `commande` (
  `idcom` int(11) NOT NULL,
  `id_client` int(11) NOT NULL,
  `montant_total` decimal(10,2) NOT NULL,
  `methode_paiement` varchar(50) NOT NULL,
  `statut` enum('en_attente','livree','annulee') DEFAULT 'en_attente',
  `date_commande` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `commande_produit`
--

CREATE TABLE `commande_produit` (
  `id` int(11) NOT NULL,
  `idcom` int(11) NOT NULL,
  `reference_produit` varchar(15) NOT NULL,
  `quantite` int(11) NOT NULL,
  `prix_unitaire` decimal(10,2) NOT NULL,
  `statut_livraison` enum('en_attente','livree') DEFAULT 'en_attente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `compte`
--

CREATE TABLE `compte` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `motpasse` varchar(255) NOT NULL,
  `role` enum('client','producteur','admin') NOT NULL DEFAULT 'client',
  `statut` enum('actif','suspendu','en_attente') DEFAULT 'actif',
  `date_inscription` datetime DEFAULT current_timestamp(),
  `question_secrete` varchar(255) DEFAULT NULL,
  `reponse_secrete` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `compte`
--

INSERT INTO `compte` (`id`, `nom`, `email`, `motpasse`, `role`, `statut`, `date_inscription`, `question_secrete`, `reponse_secrete`) VALUES
(1, 'Administrateur', 'admin@greenmarket.ma', '$2y$12$7x.OqV45Yj4glwyVJHZ/r.L9efNZETaED5N96K4ERbRyKS4wQ1QzO', 'admin', 'actif', '2026-06-27 23:06:55', NULL, NULL),
(2, 'Alaiz Mohammed', 'alaiz@greenmarket.ma', '$2y$12$c6HkURujdSwyWV2irDV57uxOY6TXcs17UgF54RdZMNQQg.3itEV2u', 'producteur', 'actif', '2026-06-27 23:06:55', 'Quel est votre premier animal de compagnie ?', 'titi'),
(3, 'Azaanoun Ismail', 'contactmebymymail@gmail.com', '$2y$12$DRHBZ2asNNi2IngYBTNZMeLUKOvr5yO9bRX5ftZLrtBSpIvYXt7Ry', 'client', 'actif', '2026-06-27 23:06:55', 'Quel est le nom de votre école primaire ?', 'victor'),
(4, 'Lamiae El Ftouh', 'LamFet@gmail.com', '$2y$12$DRHBZ2asNNi2IngYBTNZMeLUKOvr5yO9bRX5ftZLrtBSpIvYXt7Ry', 'client', 'actif', '2026-06-27 23:06:55', 'Quel est le nom de votre école primaire ?', 'victor'),
(5, 'Chtoun Ismail', 'chtoun@greenmarket.ma', '$2y$12$c6HkURujdSwyWV2irDV57uxOY6TXcs17UgF54RdZMNQQg.3itEV2u', 'producteur', 'actif', '2026-06-27 23:06:55', 'Quel est votre premier animal de compagnie ?', 'titi'),
(6, 'Akhrif Roumaissae', 'roumaissae@greenmarket.ma', '$2y$12$c6HkURujdSwyWV2irDV57uxOY6TXcs17UgF54RdZMNQQg.3itEV2u', 'producteur', 'actif', '2026-06-27 23:06:55', 'Quel est votre premier animal de compagnie ?', 'titi');

-- --------------------------------------------------------

--
-- Table structure for table `point_fidelite`
--

CREATE TABLE `point_fidelite` (
  `id` int(11) NOT NULL,
  `id_client` int(11) NOT NULL,
  `points` int(11) NOT NULL,
  `source` varchar(50) NOT NULL,
  `date_obtention` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `produit`
--

CREATE TABLE `produit` (
  `reference` varchar(15) NOT NULL,
  `libelle` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `prixu` decimal(10,2) NOT NULL CHECK (`prixu` > 0),
  `quantite` int(11) DEFAULT 0,
  `statut` enum('en_attente','valide','refuse') DEFAULT 'en_attente',
  `dateachat` datetime DEFAULT current_timestamp(),
  `idcateg` int(11) NOT NULL,
  `id_producteur` int(11) NOT NULL,
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `produit`
--

INSERT INTO `produit` (`reference`, `libelle`, `description`, `prixu`, `quantite`, `statut`, `dateachat`, `idcateg`, `id_producteur`, `image`) VALUES
('CHAT-GRI-001', 'Châtaignes Grillées à la Marocaine', 'Châtaignes grillées à la main, prêtes à déguster. Origine Maroc. Un en-cas authentique et savoureux, idéal pour l\'hiver.', 99.99, 1000, 'valide', '2026-06-27 23:06:55', 7, 2, 'https://i.ibb.co/YF45SBK6/Chataignes-grillees-de-Tetouan.png'),
('FIG-CHE-002', 'Figues Séchées Naturelles de Chefchaouen', 'Figues séchées au soleil de la région de Chefchaouen. Naturelles, sans sucre ajouté, elles sont douces et moelleuses.', 100.00, 1000, 'valide', '2026-06-27 23:06:55', 7, 2, 'https://i.ibb.co/rG9pKw36/Figues-de-Chefchaouen.png'),
('ZEM-HOC-001', 'Zembo d\'Al Hoceima', 'Zembo : épice traditionnelle de la région d\'Al Hoceima. Mélange parfumé à base de cumin, coriandre, gingembre et autres herbes authentiques.', 99.00, 1000, 'valide', '2026-06-27 23:06:55', 5, 2, 'https://i.ibb.co/DfmfSXcz/Zembo-d-Al-Hoceima.png');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `avis`
--
ALTER TABLE `avis`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_client` (`id_client`),
  ADD KEY `reference_produit` (`reference_produit`),
  ADD KEY `id_moderateur` (`id_moderateur`);

--
-- Indexes for table `badge_producteur`
--
ALTER TABLE `badge_producteur`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_producteur` (`id_producteur`);

--
-- Indexes for table `categorie`
--
ALTER TABLE `categorie`
  ADD PRIMARY KEY (`idcat`),
  ADD UNIQUE KEY `libelle` (`libelle`);

--
-- Indexes for table `commande`
--
ALTER TABLE `commande`
  ADD PRIMARY KEY (`idcom`),
  ADD KEY `id_client` (`id_client`);

--
-- Indexes for table `commande_produit`
--
ALTER TABLE `commande_produit`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idcom` (`idcom`),
  ADD KEY `reference_produit` (`reference_produit`);

--
-- Indexes for table `compte`
--
ALTER TABLE `compte`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `point_fidelite`
--
ALTER TABLE `point_fidelite`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_client` (`id_client`);

--
-- Indexes for table `produit`
--
ALTER TABLE `produit`
  ADD PRIMARY KEY (`reference`),
  ADD KEY `idcateg` (`idcateg`),
  ADD KEY `id_producteur` (`id_producteur`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `avis`
--
ALTER TABLE `avis`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `badge_producteur`
--
ALTER TABLE `badge_producteur`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categorie`
--
ALTER TABLE `categorie`
  MODIFY `idcat` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `commande`
--
ALTER TABLE `commande`
  MODIFY `idcom` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `commande_produit`
--
ALTER TABLE `commande_produit`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `compte`
--
ALTER TABLE `compte`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `point_fidelite`
--
ALTER TABLE `point_fidelite`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `avis`
--
ALTER TABLE `avis`
  ADD CONSTRAINT `avis_ibfk_1` FOREIGN KEY (`id_client`) REFERENCES `compte` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `avis_ibfk_2` FOREIGN KEY (`reference_produit`) REFERENCES `produit` (`reference`) ON DELETE CASCADE,
  ADD CONSTRAINT `avis_ibfk_3` FOREIGN KEY (`id_moderateur`) REFERENCES `compte` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `badge_producteur`
--
ALTER TABLE `badge_producteur`
  ADD CONSTRAINT `badge_producteur_ibfk_1` FOREIGN KEY (`id_producteur`) REFERENCES `compte` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `commande`
--
ALTER TABLE `commande`
  ADD CONSTRAINT `commande_ibfk_1` FOREIGN KEY (`id_client`) REFERENCES `compte` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `commande_produit`
--
ALTER TABLE `commande_produit`
  ADD CONSTRAINT `commande_produit_ibfk_1` FOREIGN KEY (`idcom`) REFERENCES `commande` (`idcom`) ON DELETE CASCADE,
  ADD CONSTRAINT `commande_produit_ibfk_2` FOREIGN KEY (`reference_produit`) REFERENCES `produit` (`reference`);

--
-- Constraints for table `point_fidelite`
--
ALTER TABLE `point_fidelite`
  ADD CONSTRAINT `point_fidelite_ibfk_1` FOREIGN KEY (`id_client`) REFERENCES `compte` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `produit`
--
ALTER TABLE `produit`
  ADD CONSTRAINT `produit_ibfk_1` FOREIGN KEY (`idcateg`) REFERENCES `categorie` (`idcat`),
  ADD CONSTRAINT `produit_ibfk_2` FOREIGN KEY (`id_producteur`) REFERENCES `compte` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
