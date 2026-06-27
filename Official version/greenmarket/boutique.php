<?php
include("preferences.php");
include("prodconnex.php");
try {
    $req = $c->query("SELECT id, nom, email FROM compte WHERE role = 'producteur' AND statut = 'actif'");
    $boutiques = $req->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) { $boutiques = []; }
?>
<!DOCTYPE html>
<html lang="<?= $lang_active ?>" dir="<?= $lang_active === 'ar' ? 'rtl' : 'ltr' ?>">
<head>
  <meta charset="UTF-8"><title>GreenMarket – <?= tr('shop') ?></title>
  <link rel="icon" type="image/svg+xml" href="favicon.svg">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: sans-serif; background: #f9f5ef; color: #1e1e18; transition: 0.3s; padding-top: 90px; }
    body.dark { background-color: #121212 !important; color: #f9f5ef !important; }
    body.dark .navbar { background: #1e1e1e !important; border-bottom: 1px solid #333; }
    body.dark .navbar a { color: #fff !important; }
    body.dark .shop-card { background: #1e1e1e; border-color: #333; color: #fff; }
    .navbar { position: fixed; top: 0; left: 0; right: 0; height: 72px; display: flex; justify-content: space-between; align-items: center; padding: 0 40px; background: rgba(249,245,239,0.95); border-bottom: 1px solid #e8dfd0; z-index: 100; }
    .nav-links { display: flex; gap: 20px; list-style: none; }
    .nav-links a { text-decoration: none; color: #1e1e18; font-weight: bold; text-transform: uppercase; font-size:13px; }
    .container { padding: 40px; }
    .shops-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 25px; margin-top:30px; }
    .shop-card { background: white; border: 1px solid #e8dfd0; padding: 20px; border-radius: 12px; text-align: center; }
  </style>
</head>
<body class="<?= $theme_actif ?>">
<nav class="navbar">
  <a href="acceuil.php" style="font-weight:bold; color:#5c6b3a; font-size:1.3rem; text-decoration:none;">GreenMarket</a>
  <ul class="nav-links"><li><a href="acceuil.php"><?= tr('home') ?></a></li><li><a href="catalogue.php"><?= tr('cat') ?></a></li><li><a href="boutique.php"><?= tr('shop') ?></a></li></ul>
  <div style="display:flex; align-items:center; gap:20px;"><?php afficher_selecteurs(); if(isset($_SESSION['idu'])): ?><a href="deconnexion.php" style="color:#c95a5a; font-weight:bold; text-decoration:none; font-size:13px;"><?= tr('logout') ?></a><?php else: ?><a href="authentification.php" style="text-decoration:none; color:white; background:#5c6b3a; padding:6px 15px; border-radius:20px; font-size:13px;"><?= tr('login') ?></a><?php endif; ?></div>
</nav>
<div class="container">
  <h1><?= tr('prod_by') ?></h1><p style="color:gray;"><?= tr('prod_sub') ?></p>
  <div class="shops-grid">
    <?php if(empty($boutiques)): ?><p><?= tr('empty_shop') ?></p>
    <?php else: foreach($boutiques as $b): ?>
      <div class="shop-card"><div style="font-size:3rem; margin-bottom:10px;">🏪</div><h3><?= htmlspecialchars($b['nom']) ?></h3><p style="color:gray; font-size:13px; margin-top:5px;"><?= htmlspecialchars($b['email']) ?></p></div>
    <?php endforeach; endif; ?>
  </div>
</div>
</body>
</html>