<?php
session_start();
include("prodconnex.php");

if(!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = [];
}

if(isset($_GET['action']) && $_GET['action'] == 'add' && isset($_GET['ref'])) {
    $ref = $_GET['ref'];
    if(isset($_SESSION['panier'][$ref])) {
        $_SESSION['panier'][$ref]++;
    } else {
        $_SESSION['panier'][$ref] = 1;
    }
    header("Location: catalogue.php?msgs=Produit ajouté au panier avec succès !");
    exit;
}

try {
    $req = $c->query("SELECT p.*, c.libelle as nomcat FROM produit p JOIN categorie c ON p.idcateg = c.idcat WHERE p.statut = 'valide' ORDER BY p.dateachat DESC");
    $produits = $req->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) { die("Erreur : " . $e->getMessage()); }

$cart_count = array_sum($_SESSION['panier']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>GreenMarket – Catalogue</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght=0,300;0,400;0,600;1,300;1,400&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
    :root {
      --ivory:     #f9f5ef;
      --cream:     #f2ebe0;
      --cream2:    #e8dfd0;
      --sand:      #d4c5ad;
      --olive:     #5c6b3a;
      --olive-mid: #748249;
      --olive-lt:  #a3b37a;
      --olive-bg:  #edf0e4;
      --brown:     #6b4c2a;
      --brown-lt:  #9a7455;
      --text:      #1e1e18;
      --text-mid:  #4a4a3a;
      --text-lt:   #8a8a74;
      --white:     #ffffff;
      --serif: 'Cormorant Garamond', Georgia, serif;
      --sans:  'Jost', sans-serif;
      --shadow-sm: 0 2px 12px rgba(60,50,20,0.07);
    }
    body {
      font-family: var(--sans);
      background: var(--ivory);
      color: var(--text);
      -webkit-font-smoothing: antialiased;
    }
    .navbar {
      position: fixed; top: 0; left: 0; right: 0; z-index: 200;
      display: flex; align-items: center; justify-content: space-between;
      padding: 0 60px; height: 72px;
      background: rgba(249,245,239,0.90);
      backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px);
      border-bottom: 1px solid rgba(212,197,173,0.35);
    }
    .logo { display: flex; align-items: center; gap: 9px; text-decoration: none; }
    .logo-leaf {
      width: 34px; height: 34px; background: var(--olive);
      border-radius: 50% 50% 50% 0; transform: rotate(-45deg);
      display: flex; align-items: center; justify-content: center;
    }
    .logo-leaf::after {
      content: ''; width: 14px; height: 14px; background: var(--ivory);
      border-radius: 50%; transform: rotate(45deg) translate(-1px, -1px);
    }
    .logo-text { font-family: var(--serif); font-size: 1.4rem; font-weight: 600; color: var(--olive); }
    .logo-text span { color: var(--brown); }
    .nav-links { display: flex; gap: 30px; list-style: none; }
    .nav-links a { text-decoration: none; color: var(--text-mid); font-size: 0.95rem; font-weight: 400; }
    .nav-actions { display: flex; align-items: center; gap: 16px; }
    .cart-btn {
      text-decoration: none; width: 40px; height: 40px; border-radius: 50%;
      border: 1px solid var(--sand); background: transparent; position: relative;
      display: flex; align-items: center; justify-content: center; color: var(--text-mid);
    }
    .cart-badge {
      position: absolute; top: -5px; right: -5px; background: var(--brown); color: white;
      font-size: 11px; width: 18px; height: 18px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
    }
    .signin-btn {
      padding: 10px 24px; background: var(--olive); color: var(--white);
      border: none; border-radius: 20px; font-size: 0.85rem; font-weight: 500; text-decoration: none;
    }
    .catalog-container { max-width: 1200px; margin: 0 auto; padding: 120px 20px 60px 20px; }
    .catalog-header { margin-bottom: 40px; text-align: center; }
    .catalog-header h1 { font-family: var(--serif); font-size: 2.8rem; font-weight: 300; color: var(--text); }
    .catalog-header h1 em { font-style: italic; color: var(--olive); }
    .catalog-header p { color: var(--text-lt); margin-top: 10px; font-size: 1.1rem; }
    .products-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 30px; }
    .product-card { background: var(--white); border-radius: 12px; overflow: hidden; box-shadow: var(--shadow-sm); border: 1px solid rgba(212,197,173,0.25); }
    .product-badge { position: absolute; top: 15px; left: 15px; background: rgba(249,245,239,0.95); padding: 4px 12px; border-radius: 15px; font-size: 11px; font-weight: 500; color: var(--olive); text-transform: uppercase; letter-spacing: 0.05em; }
    .product-title { font-family: var(--serif); font-size: 1.35rem; font-weight: 600; color: var(--text); margin-bottom: 8px; }
    .product-desc { font-size: 0.88rem; color: var(--text-lt); line-height: 1.5; margin-bottom: 15px; height: 45px; overflow: hidden; }
    .product-price { font-family: var(--sans); font-size: 1.15rem; font-weight: 600; color: var(--brown); }
  </style>
</head>
<body>

<nav class="navbar" id="navbar">
  <a href="acceuil.php" class="logo">
    <div class="logo-leaf"></div>
    <div class="logo-text">Green<span>Market</span></div>
  </a>
  <ul class="nav-links">
    <li><a href="acceuil.php">Accueil</a></li>
    <li><a href="catalogue.php" style="color: var(--olive); font-weight: 500;">Catalogue</a></li>
    <li><a href="boutique.php">Boutiques</a></li>
  </ul>
  <div class="nav-actions">
    <a href="panier.php" class="cart-btn">
      🧺
      <?php if($cart_count > 0): ?>
        <span class="cart-badge"><?= $cart_count ?></span>
      <?php endif; ?>
    </a>
    <?php if(isset($_SESSION['idu'])): ?>
      <?php if($_SESSION['roleu'] == 'admin'): ?>
        <a href="dashboradadmis.php" class="signin-btn">Admin</a>
      <?php elseif($_SESSION['roleu'] == 'producteur'): ?>
        <a href="dashboardpro.php" class="signin-btn">Dashboard</a>
      <?php else: ?>
        <a href="dashboard.php" class="signin-btn">Mon Espace</a>
      <?php endif; ?>
      <a href="deconnexion.php" style="color:#c95a5a; font-weight:bold; text-decoration:none; margin-left:15px; font-size:0.85rem; text-transform:uppercase;">Déconnexion</a>
    <?php else: ?>
      <a href="authentification.php" class="signin-btn">Se connecter</a>
    <?php endif; ?>
  </div>
</nav>

<div class="catalog-container">
  <?php if(isset($_GET['msgs'])): ?>
      <div style="background: var(--olive-bg); color: var(--olive); padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center; font-weight:bold;"><?= htmlspecialchars($_GET['msgs']) ?></div>
  <?php endif; ?>

  <div class="catalog-header">
    <h1>Notre <em>Catalogue</em></h1>
    <p>Découvrez nos produits issus de l'agriculture biologique locale.</p>
  </div>

  <div class="products-grid">
    <?php if(empty($produits)): ?>
        <p style="grid-column: 1/-1; text-align: center; color: var(--text-lt);">Aucun produit disponible pour le moment.</p>
    <?php else: ?>
        <?php foreach($produits as $p): ?>
        <div class="product-card">
          <div class="product-img" style="background-image:url('<?= htmlspecialchars($p['image']) ?>'); height:200px; background-size:cover; position: relative;">
            <div class="product-badge"><?= htmlspecialchars($p['nomcat']) ?></div>
          </div>
          <div class="product-info" style="padding:15px;">
            <h3 class="product-title"><?= htmlspecialchars($p['libelle']) ?></h3>
            <p class="product-desc"><?= htmlspecialchars($p['description']) ?></p>
            <div class="product-footer" style="display:flex; justify-content:space-between; align-items:center;">
              <div class="product-price"><?= htmlspecialchars($p['prixu']) ?> DH</div>
              <a href="catalogue.php?action=add&ref=<?= urlencode($p['reference']) ?>" style="background:#5c6b3a; color:white; padding:8px 15px; border-radius:20px; text-decoration:none; font-size:12px; font-weight: 500;">+ Ajouter</a>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

</body>
</html>