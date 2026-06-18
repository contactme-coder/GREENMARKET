-- ============================================================
--  GreenMarket – Base de données complète
--  Filière : DEV 202 | Module : M107 / M201
--  Instructrice : LAFHAL Joairia
-- ============================================================

CREATE DATABASE IF NOT EXISTS greenmarket CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE greenmarket;

-- ============================================================
-- TABLES
-- ============================================================

-- Table unique pour gérer l'authentification (comme dans mng_produit.sql)
CREATE TABLE compte (
   id               INT AUTO_INCREMENT PRIMARY KEY,
   nom              VARCHAR(100) NOT NULL,
   email            VARCHAR(150) NOT NULL UNIQUE,
   motpasse         VARCHAR(255) NOT NULL,
   role             ENUM('client','producteur','admin') NOT NULL DEFAULT 'client',
   statut           ENUM('actif','suspendu','en_attente') DEFAULT 'actif',
   date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Table des catégories
CREATE TABLE categorie (
   idcat       INT AUTO_INCREMENT PRIMARY KEY,
   libelle     VARCHAR(100) NOT NULL UNIQUE,
   description TEXT
);

-- Table des produits (respectant les noms de colonnes de la formatrice)
CREATE TABLE produit (
   reference    VARCHAR(15)    NOT NULL PRIMARY KEY,
   libelle      VARCHAR(150)   NOT NULL,
   description  TEXT,
   prixu        DECIMAL(10,2)  NOT NULL CHECK(prixu > 0),
   quantite     INT            DEFAULT 0,
   statut       ENUM('en_attente','valide','refuse') DEFAULT 'en_attente',
   dateachat    DATETIME       DEFAULT CURRENT_TIMESTAMP,
   idcateg      INT            NOT NULL,
   id_producteur INT           NOT NULL,
   image        VARCHAR(255),
   FOREIGN KEY(idcateg)       REFERENCES categorie(idcat) ON DELETE RESTRICT,
   FOREIGN KEY(id_producteur) REFERENCES compte(id)       ON DELETE CASCADE
);

-- ============================================================
-- COMPTES UTILISATEURS
-- Algorithme : bcrypt (PASSWORD_BCRYPT) — compatible password_verify()
-- ============================================================

-- id=1 | Admin | Mot de passe : admin123
INSERT INTO compte (nom, email, motpasse, role, statut) VALUES (
   'Administrateur',
   'admin@greenmarket.ma',
   '$2y$12$7x.OqV45Yj4glwyVJHZ/r.L9efNZETaED5N96K4ERbRyKS4wQ1QzO',
   'admin',
   'actif'
);

-- id=2 | Producteur de démo | Mot de passe : prod2026
INSERT INTO compte (nom, email, motpasse, role, statut) VALUES (
   'Ferme Bio Amina',
   'amina@greenmarket.ma',
   '$2y$12$c6HkURujdSwyWV2irDV57uxOY6TXcs17UgF54RdZMNQQg.3itEV2u',
   'producteur',
   'actif'
);

-- id=3 | Client de démo | Mot de passe : client2026
INSERT INTO compte (nom, email, motpasse, role, statut) VALUES (
   'Yassine Benali',
   'yassine@gmail.com',
   '$2y$12$DRHBZ2asNNi2IngYBTNZMeLUKOvr5yO9bRX5ftZLrtBSpIvYXt7Ry',
   'client',
   'actif'
);

-- ============================================================
-- CATÉGORIES
-- ============================================================

INSERT INTO categorie (libelle, description) VALUES
('Fruits & Légumes',       'Produits frais du maraîchage biologique local'),
('Miel & Apiculture',      'Miels artisanaux, cire et produits de la ruche'),
('Huiles & Olives',        'Huile d''olive extra-vierge pressée à froid'),
('Produits Laitiers',      'Fromages fermiers, lait cru, yaourts artisanaux'),
('Épices & Aromates',      'Herbes séchées et épices du terroir marocain'),
('Céréales & Légumineuses','Blé, orge, lentilles et légumineuses biologiques'),
('Conserves & Confitures', 'Confitures maison, conserves de légumes locaux'),
('Artisanat & Bien-être',  'Savons naturels, cosmétiques bio, huiles essentielles');

-- ============================================================
-- PRODUITS DE DÉMONSTRATION (liés au producteur id=2)
-- 5 produits "valide" visibles dans le catalogue
-- 1 produit "en_attente" pour démontrer le workflow admin
-- Images : picsum.photos — stable et libre de droits
-- ============================================================

-- Catégorie Miel (idcateg=2)
INSERT INTO produit (reference, libelle, description, prixu, quantite, statut, idcateg, id_producteur, image) VALUES (
   'MIEL-THY-001',
   'Miel de Thym Artisanal',
   'Miel pur récolté à la main dans les collines du Moyen Atlas. Riche en antioxydants, idéal pour renforcer l''immunité. Certifié sans antibiotiques ni traitements chimiques.',
   85.00, 30, 'valide', 2, 2,
   'https://picsum.photos/seed/miel-thym/400/300'
);

-- Catégorie Huiles (idcateg=3)
INSERT INTO produit (reference, libelle, description, prixu, quantite, statut, idcateg, id_producteur, image) VALUES (
   'HUI-OLI-001',
   'Huile d''Olive Extra-Vierge BIO',
   'Huile d''olive pressée à froid, issue de notre oliveraie centenaire de la région de Marrakech. Acidité inférieure à 0.3%. Saveur fruitée et légèrement poivrée en fin de bouche.',
   65.00, 50, 'valide', 3, 2,
   'https://picsum.photos/seed/olive-oil/400/300'
);

-- Catégorie Fruits & Légumes (idcateg=1)
INSERT INTO produit (reference, libelle, description, prixu, quantite, statut, idcateg, id_producteur, image) VALUES (
   'FRU-TOM-001',
   'Tomates Cerises Bio du Terroir',
   'Variété ancienne cultivée sans pesticides dans notre jardin potager biologique. Idéales pour les salades ou à déguster nature. Récoltées à maturité optimale.',
   18.00, 100, 'valide', 1, 2,
   'https://picsum.photos/seed/tomate-bio/400/300'
);

-- Catégorie Épices (idcateg=5)
INSERT INTO produit (reference, libelle, description, prixu, quantite, statut, idcateg, id_producteur, image) VALUES (
   'EPI-SAF-001',
   'Safran Pur de Taliouine',
   'Safran AOP de Taliouine, capitale mondiale du safran. Récolte manuelle des pistils au lever du soleil. Arôme puissant et couleur dorée intense. Conditionné en petits sachets de 1g.',
   120.00, 20, 'valide', 5, 2,
   'https://picsum.photos/seed/safran/400/300'
);

-- Catégorie Produits Laitiers (idcateg=4)
INSERT INTO produit (reference, libelle, description, prixu, quantite, statut, idcateg, id_producteur, image) VALUES (
   'LAI-CHE-001',
   'Fromage de Chèvre Fermier',
   'Fromage frais de chèvre fabriqué artisanalement dans notre ferme. Lait cru non pasteurisé, affiné 7 jours. Saveur douce et légèrement acidulée. Vendu à la pièce (environ 200g).',
   45.00, 40, 'valide', 4, 2,
   'https://picsum.photos/seed/fromage/400/300'
);

-- Produit "en_attente" pour démontrer le workflow de validation admin
INSERT INTO produit (reference, libelle, description, prixu, quantite, statut, idcateg, id_producteur, image) VALUES (
   'CON-FIG-001',
   'Confiture de Figues Maison',
   'Confiture préparée avec des figues fraîches cueillies à la main dans notre figuier centenaire. Recette traditionnelle sans conservateurs, juste des figues, du sucre et du citron.',
   35.00, 60, 'en_attente', 7, 2,
   'https://picsum.photos/seed/confiture/400/300'
);

-- ============================================================
-- RÉCAPITULATIF DES ACCÈS DE DÉMONSTRATION
-- ============================================================
-- Rôle           | Email                       | Mot de passe
-- Admin           | admin@greenmarket.ma        | admin123
-- Producteur      | amina@greenmarket.ma        | prod2026
-- Client          | yassine@gmail.com           | client2026
-- ============================================================
