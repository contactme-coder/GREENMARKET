<?php
include("preferences.php");
$vedettes = [];
$cart_count = 0;
try {
    include("prodconnex.php");
    $req = $c->query("SELECT p.*, cat.libelle as nomcat FROM produit p JOIN categorie cat ON p.idcateg = cat.idcat WHERE p.statut = 'valide' ORDER BY p.dateachat DESC LIMIT 6");
    $vedettes = $req->fetchAll(PDO::FETCH_ASSOC);
} catch(Exception $e) { }
$cart_count = isset($_SESSION['panier']) ? array_sum($_SESSION['panier']) : 0;
?>
<!DOCTYPE html>
<html lang="<?= $lang_active ?>" dir="<?= $lang_active === 'ar' ? 'rtl' : 'ltr' ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>GreenMarket</title>
<link rel="icon" type="image/svg+xml" href="favicon.svg">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
:root {
  --ivory: #f9f5ef; --cream: #f2ebe0; --cream2: #e8dfd0; --sand: #d4c5ad;
  --olive: #5c6b3a; --olive-mid: #748249; --olive-bg: #edf0e4;
  --text: #1e1e18; --text-mid: #4a4a3a; --text-lt: #8a8a74; --white: #ffffff;
}
body { font-family: 'Jost', sans-serif; background: var(--ivory); color: var(--text); transition: 0.3s; }
body.dark { background-color: #121212 !important; color: #f9f5ef !important; }
body.dark .navbar { background: rgba(30,30,30,0.95) !important; border-bottom: 1px solid #333; }
body.dark .navbar a, body.dark .nav-links a { color: #f9f5ef !important; }
body.dark .product-card { background: #1e1e1e; border-color: #333; }
body.dark .product-name { color: #fff; }
body.dark .hero-right { background: #222; }

.navbar { position: fixed; top: 0; left: 0; right: 0; z-index: 200; display: flex; align-items: center; justify-content: space-between; padding: 0 60px; height: 72px; background: rgba(249,245,239,0.92); backdrop-filter: blur(16px); border-bottom: 1px solid var(--cream2); }
.logo { display: flex; align-items: center; gap: 9px; text-decoration: none; }
.logo-leaf { width: 34px; height: 34px; background: var(--olive); border-radius: 50% 50% 50% 0; transform: rotate(-45deg); }
.logo-text { font-family: 'Cormorant Garamond', serif; font-size: 1.4rem; font-weight: 600; color: var(--olive); }
.nav-links { display: flex; align-items: center; gap: 36px; list-style: none; }
.nav-links a { font-size: 0.82rem; font-weight: 500; color: var(--text-mid); text-decoration: none; text-transform: uppercase; }
.nav-actions { display: flex; align-items: center; gap: 16px; }
.cart-btn { text-decoration: none; color: var(--text-mid); font-size: 1.2rem; position: relative; }
.cart-badge { position: absolute; top: -5px; right: -8px; background: var(--olive); color: white; font-size: 10px; border-radius: 50%; padding: 2px 5px; }
.signin-btn { padding: 8px 22px; background: var(--olive); color: white; border-radius: 100px; text-decoration: none; font-size: 0.8rem; font-weight: 500; text-transform: uppercase; }
.hero { min-height: 100vh; padding-top: 72px; display: grid; grid-template-columns: 1fr 1fr; }
.hero-left { padding: 80px 60px; display: flex; flex-direction: column; justify-content: center; }
.hero-title { font-family: 'Cormorant Garamond', serif; font-size: 4.2rem; margin-bottom: 20px; }
.hero-right { background: var(--olive-bg); display: flex; align-items: center; justify-content: center; }
.products-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 28px; padding: 60px; }
.product-card { background: var(--white); border-radius: 16px; overflow: hidden; border: 1px solid var(--cream2); }
.product-img { height: 200px; background-size: cover; background-position: center; }
</style>
</head>
<body class="<?= $theme_actif ?>">
<nav class="navbar" id="navbar">
  <a href="acceuil.php" class="logo">
    <div class="logo-leaf"></div>
    <div class="logo-text">Green<span>Market</span></div>
  </a>
  <ul class="nav-links">
    <li><a href="acceuil.php"><?= tr('home') ?></a></li>
    <li><a href="catalogue.php"><?= tr('cat') ?></a></li>
    <li><a href="boutique.php"><?= tr('shop') ?></a></li>
  </ul>
  <div class="nav-actions">
    <?php afficher_selecteurs(); ?>
    <a href="panier.php" class="cart-btn">🧺<?php if($cart_count > 0): ?><span class="cart-badge"><?= $cart_count ?></span><?php endif; ?></a>
    <?php if(isset($_SESSION['idu'])): ?>
      <a href="<?= $_SESSION['roleu'] === 'producteur' ? 'dashboardpro.php' : ($_SESSION['roleu'] === 'admin' ? 'dashboradadmis.php' : 'dashboard.php') ?>" class="signin-btn"><?= tr('acc') ?></a>
      <a href="deconnexion.php" style="color:#c95a5a; text-decoration:none; font-weight:600; font-size:0.8rem;"><?= tr('logout') ?></a>
    <?php else: ?>
      <a href="authentification.php" class="signin-btn"><?= tr('login') ?></a>
    <?php endif; ?>
  </div>
</nav>
<section class="hero">
  <div class="hero-left">
    <h1 class="hero-title">
      <?= $lang_active === 'ar' ? 'الطبيعة في قلب <br><em>مائدتك</em>' : ($lang_active === 'en' ? 'Nature at the heart <br>of your <em>table</em>' : 'La nature au cœur <br>de votre <em>table</em>') ?>
    </h1>
    <div>
      <a href="catalogue.php" class="signin-btn" style="padding:15px 30px; font-size:1rem;">🛒 <?= tr('cat') ?></a>
    </div>
  </div>
  <div class="hero-right"><span style="font-size:7rem;">🌱</span></div>
</section>
<section class="products-grid">
    <?php foreach($vedettes as $p): ?>
    <div class="product-card">
      <div class="product-img" style="background-image: url('<?= htmlspecialchars($p['image']) ?>');"></div>
      <div style="padding:20px;">
        <div class="product-name"><?= htmlspecialchars($p['libelle']) ?></div>
        <div style="display:flex; justify-content:space-between; margin-top:15px; align-items:center;">
          <span style="font-weight:bold; color:var(--olive);"><?= htmlspecialchars($p['prixu']) ?> DH</span>
          <a href="catalogue.php?action=add&ref=<?= urlencode($p['reference']) ?>" class="signin-btn" style="padding:5px 12px;">+</a>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
</section>
</body>
</html>