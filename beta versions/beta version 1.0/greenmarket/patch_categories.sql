-- ============================================================
-- PATCH GreenMarket – À exécuter dans phpMyAdmin
-- Objectif : Insérer les catégories pour remplir le <select>
--            de ajouterprod.php
-- ============================================================

USE greenmarket;

-- Insertion des catégories de base
INSERT IGNORE INTO categorie (libelle, description) VALUES
('Fruits & Légumes',   'Produits frais issus du maraîchage biologique local'),
('Miel & Apiculture',  'Miels artisanaux, cire et produits de la ruche'),
('Huiles & Olives',    'Huile d''olive extra-vierge pressée à froid, tapenade'),
('Produits Laitiers',  'Fromages fermiers, lait cru, yaourts artisanaux'),
('Épices & Aromates',  'Herbes séchées, épices du terroir marocain'),
('Céréales & Légumineuses', 'Blé, orge, lentilles et légumineuses biologiques'),
('Conserves & Confitures',  'Confitures maison, conserves de légumes locaux'),
('Artisanat & Bien-être',   'Savons naturels, cosmétiques bio, produits artisanaux');

-- ============================================================
-- CORRECTION de la table compte si des colonnes erronées existent
-- (pour que boutique.php fonctionne correctement)
-- La table correcte utilise : nom, email, role (sans le 'u')
-- ============================================================

-- Optionnel : valider un produit de test pour voir le catalogue
-- UPDATE produit SET statut = 'valide' WHERE reference = 'VOTRE_REF';
