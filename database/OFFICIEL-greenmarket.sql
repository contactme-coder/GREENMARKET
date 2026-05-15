-- ============================================
-- GreenMarket - Base de données
-- Filière : DEV 202 | Module : M201
-- ============================================

CREATE TABLE acteur(
   id_utilisateur INT AUTO_INCREMENT,
   nom VARCHAR(100) NOT NULL,
   prenom VARCHAR(100) NOT NULL,
   email VARCHAR(150) NOT NULL,
   mot_de_passe VARCHAR(255) NOT NULL,
   role ENUM('client','producteur','admin') NOT NULL,
   statut ENUM('actif','suspendu','en_attente') DEFAULT 'actif',
   date_inscription DATETIME NOT NULL,
   id_utilisateur_1 INT,
   PRIMARY KEY(id_utilisateur),
   UNIQUE(email),
   FOREIGN KEY(id_utilisateur_1) REFERENCES acteur(id_utilisateur)
);

CREATE TABLE Boutique(
   id_boutique INT AUTO_INCREMENT,
   nom_boutique VARCHAR(100) NOT NULL,
   description TEXT,
   logo VARCHAR(255),
   id_utilisateur INT NOT NULL,
   PRIMARY KEY(id_boutique),
   FOREIGN KEY(id_utilisateur) REFERENCES acteur(id_utilisateur)
);

CREATE TABLE Categorie(
   id_categorie INT AUTO_INCREMENT,
   description TEXT,
   nom VARCHAR(50) NOT NULL,
   PRIMARY KEY(id_categorie),
   UNIQUE(nom)
);

CREATE TABLE Produit(
   id_produit INT AUTO_INCREMENT,
   photo_url VARCHAR(255),
   nom VARCHAR(150) NOT NULL,
   description TEXT,
   prix DECIMAL(10,2) NOT NULL CHECK(prix > 0),
   stock INT DEFAULT 0,
   statut ENUM('en_attente','valide','refuse') DEFAULT 'en_attente',
   date_ajout DATETIME NOT NULL,
   id_boutique INT NOT NULL,
   id_admin INT,
   id_categorie INT NOT NULL,
   PRIMARY KEY(id_produit),
   FOREIGN KEY(id_boutique) REFERENCES Boutique(id_boutique),
   FOREIGN KEY(id_admin) REFERENCES acteur(id_utilisateur),
   FOREIGN KEY(id_categorie) REFERENCES Categorie(id_categorie)
);

CREATE TABLE Panier(
   id_panier INT AUTO_INCREMENT,
   date_creation DATETIME NOT NULL,
   id_client INT NOT NULL,
   PRIMARY KEY(id_panier),
   FOREIGN KEY(id_client) REFERENCES acteur(id_utilisateur)
);

CREATE TABLE infoCommande(
   id_commande INT AUTO_INCREMENT,
   date_commande DATETIME NOT NULL,
   statut ENUM('en_attente','confirmee','expediee','livree','annulee') DEFAULT 'en_attente',
   total DECIMAL(10,2) NOT NULL,
   adresse_livraison TEXT NOT NULL,
   id_panier INT NOT NULL,
   PRIMARY KEY(id_commande),
   UNIQUE(id_panier),
   FOREIGN KEY(id_panier) REFERENCES Panier(id_panier)
);

CREATE TABLE Notification(
   id_notification INT AUTO_INCREMENT,
   type ENUM('commande','statut','produit','avis') NOT NULL,
   message TEXT NOT NULL,
   lu BOOLEAN DEFAULT FALSE,
   date_creation DATETIME NOT NULL,
   id_commande INT,
   id_utilisateur INT NOT NULL,
   PRIMARY KEY(id_notification),
   FOREIGN KEY(id_commande) REFERENCES infoCommande(id_commande),
   FOREIGN KEY(id_utilisateur) REFERENCES acteur(id_utilisateur)
);

CREATE TABLE Avis(
   id_avis BIGINT AUTO_INCREMENT,
   note TINYINT NOT NULL CHECK(note BETWEEN 1 AND 5),
   commentaire TEXT,
   date_avis DATETIME NOT NULL,
   reponse TEXT,
   statut ENUM('visible','masque') DEFAULT 'visible',
   id_client INT NOT NULL,
   id_produit INT NOT NULL,
   PRIMARY KEY(id_avis),
   FOREIGN KEY(id_client) REFERENCES acteur(id_utilisateur),
   FOREIGN KEY(id_produit) REFERENCES Produit(id_produit)
);

CREATE TABLE Facture(
   Id_Facture INT AUTO_INCREMENT,
   montant_total DECIMAL(19,4) NOT NULL,
   moment DATETIME NOT NULL,
   PRIMARY KEY(Id_Facture)
);

CREATE TABLE Paiement(
   id_paiement INT AUTO_INCREMENT,
   mode VARCHAR(20) NOT NULL,
   date_paiement DATETIME NOT NULL,
   statut ENUM('en_attente','complete','rembourse') DEFAULT 'en_attente',
   montant DECIMAL(10,2) NOT NULL,
   Id_Facture INT NOT NULL,
   id_commande INT NOT NULL,
   PRIMARY KEY(id_paiement),
   UNIQUE(Id_Facture),
   UNIQUE(id_commande),
   FOREIGN KEY(Id_Facture) REFERENCES Facture(Id_Facture),
   FOREIGN KEY(id_commande) REFERENCES infoCommande(id_commande)
);

CREATE TABLE FAVORI(
   id_client INT,
   id_produit INT,
   heure_de_favorisation DATETIME,
   PRIMARY KEY(id_client, id_produit),
   FOREIGN KEY(id_client) REFERENCES acteur(id_utilisateur),
   FOREIGN KEY(id_produit) REFERENCES Produit(id_produit)
);

CREATE TABLE AJOUTER(
   id_produit INT,
   id_panier INT,
   quantite INT NOT NULL CHECK(quantite > 0),
   PRIMARY KEY(id_produit, id_panier),
   FOREIGN KEY(id_produit) REFERENCES Produit(id_produit),
   FOREIGN KEY(id_panier) REFERENCES Panier(id_panier)
);

CREATE TABLE ASSOCIER(
   id_boutique INT,
   Id_Facture INT,
   PRIMARY KEY(id_boutique, Id_Facture),
   FOREIGN KEY(id_boutique) REFERENCES Boutique(id_boutique),
   FOREIGN KEY(Id_Facture) REFERENCES Facture(Id_Facture)
);
