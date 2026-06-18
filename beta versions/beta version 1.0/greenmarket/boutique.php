<?php
session_start();
include("prodconnex.php");

try {
    $req = $c->query("SELECT id, nomu, emailu FROM compte WHERE roleu = 'producteur' OR role = 'producteur'");
    $boutiques = $req->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $boutiques = [];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>GreenMarket – Boutiques Locales & Durables</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght=0,300;0,400;0,600;1,300;1,400&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    :root {
      --ivory: #f9f5ef;
      --cream: #f2ebe0;
      --cream2: #e8dfd0;
      --sand: #d4c5ad;
      --olive: #5c6b3a;
      --olive-mid: #748249;
      --olive-lt: #a3b37a;
      --olive-bg: #edf0e4;
      --brown: #6b4c2a;
      --brown-lt: #9a7455;
      --text: #1e1e18;
      --text-mid: #4a4a3a;
      --text-lt: #8a8a74;
      --white: #ffffff;
      --serif: 'Cormorant Garamond', Georgia, serif;
      --sans: 'Jost', sans-serif;
      --shadow-sm: 0 2px 12px rgba(60,50,20,0.07);
    }
    body { font-family: var(--sans); background: var(--ivory); color: var(--text); -webkit-font-smoothing: antialiased; }
    .navbar { position: fixed; top: 0; left: 0; right: 0; z-index: 200; display: flex; align-items: center; justify-content: space-between; padding: 0 60px; height: 72px; background: rgba(249,245,239,0.90); backdrop-filter: blur(16px); border-bottom: 1px solid rgba(212,197,173,0.35); }
    .logo { display: flex; align-items: center; gap: 9px; text-decoration: none; }
    .logo-leaf { width: 34px; height: 34px; background: var(--olive); border-radius: 50% 50% 50% 0; transform: rotate(-45deg); display: flex; align-items: center; justify-content: center; }
    .logo-leaf::after { content: ''; width: 14px; height: 14px; background: var(--ivory); border-radius: 50%; transform: rotate(45deg) translate(-1px, -1px); }
    .logo-text { font-family: var(--serif); font-size: 1.4rem; font-weight: 600; color: var(--olive); }
    .logo-text span { color: var(--brown); }
    .nav-links { display: flex; gap: 30px; list-style: none; }
    .nav-links a { text-decoration: none; color: var(--text-mid); font-size: 0.95rem; }
    .nav-actions { display: flex; align-items: center; gap: 16px; }
    .cart-btn { text-decoration: none; width: 40px; height: 40px; border-radius: 50%; border: 1px solid var(--sand); background: transparent; position: relative; display: flex; align-items: center; justify-content: center; color: var(--text-mid); }
    .cart-badge { position: absolute; top: -5px; right: -5px; background: var(--brown); color: white; font-size: 11px; width: 18px; height: 18px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
    .signin-btn { padding: 10px 24px; background: var(--olive); color: var(--white); border: none; border-radius: 20px; font-size: 0.85rem; font-weight: 500; text-decoration: none; }
    .container { max-width: 1200px; margin: 0 auto; padding: 120px 20px 60px 20px; }
    .shops-header { text-align: center; margin-bottom: 50px; }
    .shops-header h1 { font-family: var(--serif); font-size: 2.8rem; font-weight: 300; }
    .shops-header h1 em { font-style: italic; color: var(--olive); }
    .shops-header p { color: var(--text-lt); margin-top: 10px; }
    .shops-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 30px; }
    .shop-card { background: var(--white); border-radius: 16px; pading: 25px; box-shadow: var(--shadow-sm); border: 1px solid rgba(212,197,173,0.25); text-align: center; display: flex; flex-direction: column; align-items: center; padding: 30px 20px; }
    .shop-avatar { width: 80px; height: 80px; background: var(--olive-bg); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; color: var(--olive); margin-bottom: 20px; }
    .shop-name { font-family: var(--serif); font-size: 1.5rem; font-weight: 600; color: var(--text); margin-bottom: 5px; }
    .shop-meta { font-size: 0.85rem; color: var(--text-lt); margin-bottom: 20px; }
    .view-shop-btn { padding: 10px 24px; border: 1px solid var(--olive); color: var(--olive); border-radius: 20px; text-decoration: none; font-size: 0.85rem; font-weight: 500; transition: all 0.2s; }
    .view-shop-btn:hover { background: var(--olive); color: white; }
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
    <li><a href="catalogue.php">Catalogue</a></li>
    <li><a href="boutique.php" style="color: var(--olive); font-weight: 500;">Boutiques</a></li>
  </ul>
  <div class="nav-actions">
    <a href="panier.php" class="cart-btn">
      🧺
      <?php if(isset($_SESSION['panier']) && array_sum($_SESSION['panier']) > 0): ?>
        <span class="cart-badge"><?= array_sum($_SESSION['panier']) ?></span>
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
      <a href="authentification.php" class="signin-btn" id=\"signInBtn\">Se connecter</a>
    <?php endif; ?>
  </div>
</nav>

<div class="container">
  <div class="shops-header">
    <h1>Nos <em>Producteurs</em> Locaux</h1>
    <p>Achetez directement depuis les fermes et coopératives éco-responsables de la région.</p>
  </div>

  <div class="shops-grid">
    <?php if(empty($boutiques)): ?>
        <p style="grid-column:1/-1; text-align:center; color:var(--text-lt);">Aucune boutique disponible.</p>
    <?php else: ?>
        <?php foreach($boutiques as $b): ?>
        <div class="shop-card">
          <div class="shop-avatar">
            <i class="fa-solid fa-store"></i>
          </div>
          <h3 class="shop-name"><?= htmlspecialchars($b['nomu']) ?></h3>
          <div class="shop-meta">
            <i class="fa-regular fa-envelope"></i> <?= htmlspecialchars($b['emailu']) ?>
          </div>
          <a href="catalogue.php" class="view-shop-btn">Voir ses Produits</a>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

</body>
</html>