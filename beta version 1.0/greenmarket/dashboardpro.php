<?php
session_start();
// Vérification stricte de la session
if(!isset($_SESSION) || empty($_SESSION) || $_SESSION['roleu'] !== 'producteur'){
    header("Location: authentification.php");
    exit;
}

include("prodconnex.php");

try {
    // Récupération dynamique des vrais produits de ce producteur depuis la BDD
    $req = $c->prepare("SELECT p.*, c.libelle as nomcat FROM produit p JOIN categorie c ON p.idcateg = c.idcat WHERE p.id_producteur = ? ORDER BY p.dateachat DESC");
    $req->execute([$_SESSION['idu']]);
    $tab_prod = $req->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) { die("Erreur : ".$e->getMessage()); }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>GreenMarket – Tableau de Bord Producteur</title>
  <style>
    /* Mets ici le style CSS complet de ton dashboardpro.html d'origine */
    body { font-family: 'Jost', sans-serif; background: #f9f5ef; margin: 0; padding: 20px; }
    .navbar { display: flex; justify-content: space-between; background: #5c6b3a; color: white; padding: 15px 30px; align-items: center; }
    .navbar a { color: white; text-decoration: none; font-weight: bold; }
    .dashboard-container { max-width: 1200px; margin: 30px auto; background: white; padding: 30px; border-radius: 12px; }
    .stock-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    .stock-table th, .stock-table td { padding: 12px; border-bottom: 1px solid #e8dfd0; text-align: left; }
    .stock-table th { background: #f2ebe0; }
    .btn-add { display: inline-block; padding: 10px 20px; background: #5c6b3a; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; }
  </style>
</head>
<body>

<nav class="navbar">
  <div class="logo">Green<span>Market</span></div>
  <div class="user-menu">
    <span style="margin-right:20px;">Bienvenue, <strong><?= htmlspecialchars($_SESSION['nomu']) ?></strong> (Producteur)</span>
    <a href="deconnexion.php" style="color: #ff9999;">Déconnexion</a>
  </div>
</nav>

<div class="dashboard-container">
  <div class="dashboard-header">
    <h2>Gestion de votre catalogue de produits locaux</h2>
    <p>Ajoutez, modifiez ou supprimez vos produits en temps réel.</p>
  </div>

  <?php if(isset($_GET['msgs'])): ?>
      <div style="color: green; background: #edf0e4; padding: 12px; margin: 15px 0; border-radius: 6px; font-weight: bold;">
          <?= htmlspecialchars($_GET['msgs']) ?>
      </div>
  <?php endif; ?>
  <?php if(isset($_GET['msgerr'])): ?>
      <div style="color: red; background: #fdeced; padding: 12px; margin: 15px 0; border-radius: 6px; font-weight: bold;">
          <?= htmlspecialchars($_GET['msgerr']) ?>
      </div>
  <?php endif; ?>

  <div style="margin-top: 20px; text-align: right;">
      <a href="ajouterprod.php" class="btn-add">+ Ajouter un nouveau produit</a>
  </div>

  <table class="stock-table">
    <thead>
      <tr>
        <th>Référence</th>
        <th>Image</th>
        <th>Nom du produit</th>
        <th>Catégorie</th>
        <th>Quantité en Stock</th>
        <th>Prix Unitaire</th>
        <th>Statut</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if(empty($tab_prod)): ?>
          <tr><td colspan="8" style="text-align:center; color: #9a7455;">Vous n'avez pas encore de produits en ligne.</td></tr>
      <?php else: ?>
          <?php foreach($tab_prod as $p): ?>
          <tr>
            <td><strong><?= htmlspecialchars($p['reference']) ?></strong></td>
            <td><img src="<?= htmlspecialchars($p['image']) ?>" style="width:50px; height:50px; object-fit:cover; border-radius:6px;" alt="produit"></td>
            <td style="font-weight: 500;"><?= htmlspecialchars($p['libelle']) ?></td>
            <td><?= htmlspecialchars($p['nomcat']) ?></td>
            <td><?= htmlspecialchars($p['quantite']) ?> unités</td>
            <td><?= htmlspecialchars($p['prixu']) ?> DH</td>
            <td>
              <span style="color: <?= $p['statut'] == 'valide' ? 'green' : 'orange' ?>; font-weight: bold;">
                <?= htmlspecialchars($p['statut']) ?>
              </span>
            </td>
            <td>
              <a href="modifierprod.php?refp=<?= urlencode($p['reference']) ?>" style="color: #748249; margin-right: 15px; font-weight: bold;">Modifier</a>
              <a href="supprimerprod.php?refp=<?= urlencode($p['reference']) ?>" class="btn-delete" style="color: #c95a5a; font-weight: bold;">Supprimer</a>
            </td>
          </tr>
          <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<script src="script_dash.js"></script>
</body>
</html>