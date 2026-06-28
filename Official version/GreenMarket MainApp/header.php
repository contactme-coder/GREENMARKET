<?php
// GESTION SÉCURISÉE DU PANIER VIA COOKIE
$panier = isset($_COOKIE['panier']) ? json_decode($_COOKIE['panier'], true) : [];
if (!is_array($panier)) {
    $panier = []; // Protection contre les erreurs de décodage JSON
}
$cart_count = array_sum($panier);
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
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
/* ---- RESET & ROOT ---- */
*, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
:root {
  --ivory: #f9f5ef; --cream: #f2ebe0; --cream2: #e8dfd0; --sand: #d4c5ad;
  --olive: #5c6b3a; --olive-mid: #748249; --olive-bg: #edf0e4;
  --terracotta: #c95a5a; --gold: #d4af37;
  --text: #1e1e18; --text-mid: #4a4a3a; --text-lt: #8a8a74; --white: #ffffff;
}
body { 
  font-family: 'Jost', sans-serif; background: var(--ivory); color: var(--text); transition: 0.3s;
  background-image: radial-gradient(circle at 10% 20%, rgba(212, 197, 173, 0.2) 0%, transparent 20%);
}
body.dark { background-color: #121212 !important; color: #f9f5ef !important; }
body.dark .navbar { background: rgba(30,30,30,0.95) !important; border-bottom: 1px solid #333; }
body.dark .navbar a, body.dark .nav-links a { color: #f9f5ef !important; }
body.dark .product-card { background: #1e1e1e; border-color: #333; color:#fff; }
body.dark .dashboard-stat { background: #1e1e1e !important; color:#fff; }

/* ---- NAVBAR (Responsive) ---- */
.navbar { 
  position: fixed; top: 0; left: 0; right: 0; z-index: 200; 
  display: flex; align-items: center; justify-content: space-between; 
  padding: 0 40px; height: 76px; 
  background: rgba(249,245,239,0.92); backdrop-filter: blur(16px); 
  border-bottom: 1px solid var(--cream2); 
  box-shadow: 0 4px 20px rgba(0,0,0,0.04);
}
.logo { display: flex; align-items: center; gap: 9px; text-decoration: none; }
.logo-leaf { width: 34px; height: 34px; background: var(--olive); border-radius: 50% 50% 50% 0; transform: rotate(-45deg); }
.logo-text { font-family: 'Cormorant Garamond', serif; font-size: 1.4rem; font-weight: 600; color: var(--olive); }
.nav-links { display: flex; align-items: center; gap: 36px; list-style: none; }
.nav-links a { font-size: 0.82rem; font-weight: 500; color: var(--text-mid); text-decoration: none; text-transform: uppercase; letter-spacing: 0.5px; position:relative; transition:0.3s;}
.nav-links a::after { content:''; position:absolute; bottom:-4px; left:0; width:0; height:2px; background:var(--olive); transition:0.3s;}
.nav-links a:hover::after { width:100%; }
.nav-actions { display: flex; align-items: center; gap: 16px; flex-wrap: wrap; }

.user-info { display: flex; align-items: center; gap: 12px; font-size: 13px; font-weight: 500; }
.user-info .date-time { font-size: 11px; color: var(--text-lt); }

.cart-btn { text-decoration: none; color: var(--text-mid); font-size: 1.2rem; position: relative; transition:0.3s; }
.cart-btn:hover { transform: scale(1.1); color: var(--olive); }
.cart-badge { position: absolute; top: -8px; right: -12px; background: var(--olive); color: white; font-size: 10px; border-radius: 50%; padding: 2px 6px; font-weight:bold; box-shadow: 0 2px 4px rgba(0,0,0,0.2);}
.signin-btn { 
  padding: 8px 22px; background: var(--olive); color: white; border-radius: 100px; 
  text-decoration: none; font-size: 0.8rem; font-weight: 500; text-transform: uppercase; transition:0.3s;
}
.signin-btn:hover { background: var(--terracotta); transform: translateY(-2px) scale(1.05); box-shadow: 0 4px 15px rgba(0,0,0,0.15); }

/* ---- MENU BURGER (Mobile) ---- */
.burger { display: none; flex-direction: column; gap: 5px; cursor: pointer; }
.burger span { display: block; width: 25px; height: 3px; background: var(--text); transition:0.3s; }
body.dark .burger span { background: #f9f5ef; }

@media(max-width: 992px){ 
  .navbar { padding: 0 20px; } 
  .nav-links { gap: 20px; } 
}
@media(max-width: 768px){ 
  .burger { display: flex; }
  .nav-links { 
    display: none; position: absolute; top: 76px; left: 0; right: 0; 
    background: var(--white); flex-direction: column; padding: 20px; gap: 15px; 
    border-bottom: 1px solid var(--cream2); text-align: center; 
  }
  body.dark .nav-links { background: #1e1e1e; border-color: #333; }
  .nav-links.open { display: flex; }
  .nav-actions { width: 100%; justify-content: center; flex-wrap: wrap; gap: 10px; margin-top: 10px; }
  .user-info { flex-direction: column; align-items: center; gap: 2px; }
  .nav-actions .signin-btn { font-size: 0.7rem; padding: 5px 15px; }
  .nav-actions select { font-size: 11px; padding: 3px 5px; }
}
</style>
</head>
<body class="<?= $theme_actif ?>" onload="startTime()">
<nav class="navbar" id="navbar">
  <a href="acceuil.php" class="logo">
    <div class="logo-leaf"></div>
    <div class="logo-text">Green<span>Market</span></div>
  </a>
  <ul class="nav-links" id="navLinks">
    <li><a href="acceuil.php"><i class="fa-solid fa-house" style="margin-right:5px;"></i> <?= tr('home') ?></a></li>
    <li><a href="catalogue.php"><i class="fa-solid fa-list" style="margin-right:5px;"></i> <?= tr('cat') ?></a></li>
    <li><a href="boutique.php"><i class="fa-solid fa-store" style="margin-right:5px;"></i> <?= tr('shop') ?></a></li>
    <li><a href="notre_histoire.php"><i class="fa-solid fa-book"></i> Notre Histoire</a></li>
    <li><a href="about.php"><i class="fa-solid fa-address-card"></i> About</a></li>
  </ul>
  <div class="nav-actions">
    <?php afficher_selecteurs(); ?>
    <a href="panier.php" class="cart-btn"><i class="fa-solid fa-cart-shopping"></i><?php if($cart_count > 0): ?><span class="cart-badge"><?= $cart_count ?></span><?php endif; ?></a>
    <?php if(isset($_SESSION['idu'])): ?>
      <div class="user-info">
        <span style="font-weight:bold; color:var(--olive);"><i class="fa-regular fa-user"></i> <?= htmlspecialchars($_SESSION['nomu']) ?></span>
        <div class="date-time" id="clock"></div>
      </div>
      <a href="<?= $_SESSION['roleu'] === 'producteur' ? 'dashboardpro.php' : ($_SESSION['roleu'] === 'admin' ? 'dashboradadmis.php' : 'dashboard.php') ?>" class="signin-btn"><?= tr('acc') ?></a>
      <a href="deconnexion.php" style="color:#c95a5a; text-decoration:none; font-weight:600; font-size:0.8rem;"><i class="fa-solid fa-right-from-bracket"></i> <?= tr('logout') ?></a>
    <?php else: ?>
      <a href="authentification.php" class="signin-btn"><i class="fa-solid fa-user"></i> <?= tr('login') ?></a>
    <?php endif; ?>
    <div class="burger" onclick="toggleMenu()">
      <span></span><span></span><span></span>
    </div>
  </div>
</nav>

<script>
// Toggle menu burger
function toggleMenu() {
    document.getElementById('navLinks').classList.toggle('open');
}
// Affichage horloge
function startTime() {
    const today = new Date();
    let h = today.getHours(); let m = today.getMinutes(); let s = today.getSeconds();
    m = checkTime(m); s = checkTime(s);
    document.getElementById('clock').innerHTML = "📅 " + today.toLocaleDateString() + " | 🕐 " + h + ":" + m + ":" + s;
    setTimeout(startTime, 1000);
}
function checkTime(i) { if (i < 10) {i = "0" + i}; return i; }
</script>