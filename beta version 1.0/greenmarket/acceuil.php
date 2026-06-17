<?php
session_start();
$vedettes = [];
$cart_count = 0;
try {
    include("prodconnex.php");
    $req = $c->query("SELECT p.*, cat.libelle as nomcat FROM produit p JOIN categorie cat ON p.idcateg = cat.idcat WHERE p.statut = 'valide' ORDER BY p.dateachat DESC LIMIT 6");
    $vedettes = $req->fetchAll(PDO::FETCH_ASSOC);
} catch(Exception $e) { /* page fonctionne même sans BDD */ }
$cart_count = isset($_SESSION['panier']) ? array_sum($_SESSION['panier']) : 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>GreenMarket – Nature's Finest, Delivered Fresh</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">
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
}
html { scroll-behavior: smooth; }
body { font-family: var(--sans); background: var(--ivory); color: var(--text); overflow-x: hidden; -webkit-font-smoothing: antialiased; }

/* ─── NAVBAR ─────────────────────────────────── */
.navbar {
  position: fixed; top: 0; left: 0; right: 0; z-index: 200;
  display: flex; align-items: center; justify-content: space-between;
  padding: 0 60px; height: 72px;
  background: rgba(249,245,239,0.92); backdrop-filter: blur(16px);
  border-bottom: 1px solid rgba(212,197,173,0.35);
  transition: box-shadow 0.3s;
}
.navbar.scrolled { box-shadow: 0 4px 20px rgba(60,50,20,0.08); }
.logo { display: flex; align-items: center; gap: 9px; text-decoration: none; }
.logo-leaf {
  width: 34px; height: 34px; background: var(--olive);
  border-radius: 50% 50% 50% 0; transform: rotate(-45deg);
  display: flex; align-items: center; justify-content: center; flex-shrink: 0; position: relative;
}
.logo-leaf::after {
  content: ''; width: 14px; height: 14px; background: var(--ivory);
  border-radius: 50%; position: absolute;
  top: 50%; left: 50%; transform: translate(-50%,-50%) rotate(45deg);
}
.logo-text { font-family: var(--serif); font-size: 1.4rem; font-weight: 600; color: var(--olive); letter-spacing: 0.02em; }
.logo-text span { color: var(--brown); }
.nav-links { display: flex; align-items: center; gap: 36px; list-style: none; }
.nav-links a {
  font-size: 0.82rem; font-weight: 500; color: var(--text-mid);
  text-decoration: none; letter-spacing: 0.1em; text-transform: uppercase;
}
.nav-links a:hover { color: var(--olive); }
.nav-actions { display: flex; align-items: center; gap: 16px; }
.cart-btn {
  width: 40px; height: 40px; border-radius: 50%; border: 1px solid var(--sand);
  background: transparent; cursor: pointer; display: flex; align-items: center;
  justify-content: center; position: relative; color: var(--text-mid); text-decoration: none;
}
.cart-badge {
  position: absolute; top: -4px; right: -4px; width: 18px; height: 18px;
  background: var(--olive); color: white; font-size: 0.6rem; font-weight: 600;
  border-radius: 50%; display: flex; align-items: center; justify-content: center;
}
.signin-btn {
  height: 38px; padding: 0 22px; background: var(--olive); color: white;
  border: none; border-radius: 100px; font-family: var(--sans); font-size: 0.8rem;
  font-weight: 500; letter-spacing: 0.08em; text-transform: uppercase; cursor: pointer;
  text-decoration: none; display: inline-flex; align-items: center;
}
.signin-btn:hover { background: var(--olive-mid); }
.logout-link { font-size: 0.82rem; font-weight: 600; color: #c95a5a; text-decoration: none; text-transform: uppercase; letter-spacing: 0.08em; }

/* ─── HERO ───────────────────────────────────── */
.hero {
  min-height: 100vh; padding-top: 72px;
  display: grid; grid-template-columns: 1fr 1fr;
  position: relative; overflow: hidden; background: var(--ivory);
}
.hero-left {
  display: flex; flex-direction: column; justify-content: center;
  padding: 80px 60px 80px 80px;
}
.hero-badge {
  display: inline-flex; align-items: center; gap: 7px;
  background: var(--olive-bg); color: var(--olive); border-radius: 100px;
  padding: 6px 16px; font-size: 0.78rem; font-weight: 600; letter-spacing: 0.08em;
  text-transform: uppercase; margin-bottom: 28px; width: fit-content;
  border: 1px solid rgba(92,107,58,0.2);
}
.hero-title {
  font-family: var(--serif); font-size: 4.2rem; line-height: 1.1;
  font-weight: 400; color: var(--text); margin-bottom: 20px;
  letter-spacing: -0.01em;
}
.hero-title em { color: var(--olive); font-style: italic; }
.hero-sub { font-size: 1.05rem; color: var(--text-mid); line-height: 1.7; margin-bottom: 40px; max-width: 420px; font-weight: 300; }
.hero-ctas { display: flex; gap: 14px; flex-wrap: wrap; }
.cta-primary {
  padding: 14px 32px; background: var(--olive); color: white; border: none;
  border-radius: 100px; font-family: var(--sans); font-size: 0.88rem; font-weight: 500;
  letter-spacing: 0.08em; text-transform: uppercase; text-decoration: none;
  display: inline-flex; align-items: center; gap: 8px; cursor: pointer;
  transition: background 0.2s, transform 0.15s;
}
.cta-primary:hover { background: var(--olive-mid); transform: translateY(-1px); }
.cta-secondary {
  padding: 13px 28px; background: transparent; color: var(--olive);
  border: 1.5px solid var(--olive); border-radius: 100px; font-family: var(--sans);
  font-size: 0.88rem; font-weight: 500; letter-spacing: 0.08em; text-transform: uppercase;
  text-decoration: none; display: inline-flex; align-items: center; cursor: pointer;
  transition: background 0.2s, color 0.2s;
}
.cta-secondary:hover { background: var(--olive); color: white; }
.hero-right {
  display: flex; align-items: center; justify-content: center;
  background: linear-gradient(145deg, var(--olive-bg) 0%, var(--cream) 100%);
  position: relative; padding: 60px 40px;
}
.hero-circle {
  width: 420px; height: 420px; background: linear-gradient(145deg, var(--olive) 0%, var(--olive-mid) 100%);
  border-radius: 50%; display: flex; align-items: center; justify-content: center;
  box-shadow: 0 32px 80px rgba(92,107,58,0.3); position: relative;
}
.hero-circle-inner {
  width: 300px; height: 300px; background: rgba(255,255,255,0.12);
  border-radius: 50%; display: flex; align-items: center; justify-content: center;
  backdrop-filter: blur(4px);
}
.hero-circle-icon { font-size: 7rem; filter: drop-shadow(0 4px 12px rgba(0,0,0,0.15)); }
.hero-stat {
  position: absolute; background: white; border-radius: 12px;
  padding: 12px 18px; box-shadow: 0 8px 24px rgba(60,50,20,0.12);
  display: flex; align-items: center; gap: 10px;
}
.hero-stat-1 { top: 90px; right: 60px; }
.hero-stat-2 { bottom: 110px; right: 50px; }
.hero-stat-3 { bottom: 80px; left: 50px; }
.stat-icon { width: 36px; height: 36px; background: var(--olive-bg); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1rem; }
.stat-text-bold { font-weight: 700; font-size: 0.88rem; color: var(--text); }
.stat-text-small { font-size: 0.72rem; color: var(--text-lt); }

/* ─── PRODUITS VEDETTES ───────────────────────── */
.section { padding: 80px 60px; }
.section-header { text-align: center; margin-bottom: 50px; }
.section-label { font-size: 0.78rem; font-weight: 600; letter-spacing: 0.15em; text-transform: uppercase; color: var(--olive-lt); margin-bottom: 10px; }
.section-title { font-family: var(--serif); font-size: 3rem; font-weight: 300; color: var(--text); margin-bottom: 14px; }
.section-title em { color: var(--olive); font-style: italic; }
.section-sub { font-size: 0.95rem; color: var(--text-mid); max-width: 480px; margin: 0 auto; line-height: 1.7; font-weight: 300; }

.products-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 28px; }
.product-card {
  background: var(--white); border-radius: 16px; overflow: hidden;
  border: 1px solid var(--cream2); box-shadow: 0 2px 12px rgba(60,50,20,0.04);
  transition: transform 0.25s, box-shadow 0.25s;
}
.product-card:hover { transform: translateY(-5px); box-shadow: 0 16px 40px rgba(60,50,20,0.1); }
.product-img {
  height: 200px; background-size: cover; background-position: center;
  background-color: var(--cream); position: relative;
}
.product-cat {
  position: absolute; top: 12px; left: 12px;
  background: rgba(249,245,239,0.92); color: var(--olive);
  padding: 4px 12px; border-radius: 100px; font-size: 0.72rem; font-weight: 600;
  letter-spacing: 0.08em; text-transform: uppercase; backdrop-filter: blur(4px);
}
.product-body { padding: 20px; }
.product-name { font-family: var(--serif); font-size: 1.25rem; margin-bottom: 6px; color: var(--text); }
.product-desc { font-size: 0.84rem; color: var(--text-lt); line-height: 1.6; margin-bottom: 16px; }
.product-footer { display: flex; align-items: center; justify-content: space-between; }
.product-price { font-family: var(--serif); font-size: 1.4rem; font-weight: 600; color: var(--olive); }
.btn-add {
  padding: 8px 18px; background: var(--olive-bg); color: var(--olive);
  border: none; border-radius: 100px; font-family: var(--sans); font-size: 0.78rem;
  font-weight: 600; cursor: pointer; text-decoration: none; transition: background 0.2s;
}
.btn-add:hover { background: var(--olive); color: white; }

.empty-products { text-align: center; padding: 60px 20px; grid-column: 1 / -1; }
.empty-products p { color: var(--text-lt); font-size: 1rem; margin-bottom: 20px; }

/* ─── VALEURS ─────────────────────────────────── */
.values-section { background: var(--cream); padding: 80px 60px; }
.values-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 32px; max-width: 1100px; margin: 0 auto; }
.value-card { background: var(--white); padding: 36px 30px; border-radius: 16px; text-align: center; border: 1px solid var(--cream2); }
.value-icon { font-size: 2.5rem; margin-bottom: 16px; display: block; }
.value-title { font-family: var(--serif); font-size: 1.5rem; color: var(--olive); margin-bottom: 10px; }
.value-text { font-size: 0.9rem; color: var(--text-mid); line-height: 1.7; font-weight: 300; }

/* ─── FOOTER ──────────────────────────────────── */
footer { background: var(--text); color: rgba(255,255,255,0.7); padding: 40px 60px; text-align: center; }
footer .footer-logo { font-family: var(--serif); font-size: 1.5rem; color: var(--olive-lt); margin-bottom: 10px; }
footer p { font-size: 0.84rem; }
</style>
</head>
<body>

<!-- ══ NAVBAR ══════════════════════════════════════════════════════════ -->
<nav class="navbar" id="navbar">
  <a href="acceuil.php" class="logo">
    <div class="logo-leaf"></div>
    <div class="logo-text">Green<span>Market</span></div>
  </a>
  <ul class="nav-links">
    <li><a href="acceuil.php">Accueil</a></li>
    <li><a href="catalogue.php">Catalogue</a></li>
    <li><a href="boutique.php">Boutiques</a></li>
  </ul>
  <div class="nav-actions">
    <a href="panier.php" class="cart-btn" title="Panier">
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
      <a href="deconnexion.php" class="logout-link">Déconnexion</a>
    <?php else: ?>
      <a href="authentification.php" class="signin-btn">Se connecter</a>
    <?php endif; ?>
  </div>
</nav>

<!-- ══ HERO ════════════════════════════════════════════════════════════ -->
<section class="hero">
  <div class="hero-left">
    <div class="hero-badge">🌿 Produits 100% Bio &amp; Locaux</div>
    <h1 class="hero-title">La nature au cœur<br>de votre <em>table</em></h1>
    <p class="hero-sub">Découvrez des produits frais, biologiques et sourcés directement auprès de producteurs locaux passionnés au Maroc.</p>
    <div class="hero-ctas">
      <a href="catalogue.php" class="cta-primary">🛒 Découvrir le catalogue</a>
      <a href="boutique.php" class="cta-secondary">Nos boutiques</a>
    </div>
  </div>
  <div class="hero-right">
    <div class="hero-circle">
      <div class="hero-circle-inner">
        <span class="hero-circle-icon">🌱</span>
      </div>
    </div>
    <div class="hero-stat hero-stat-1">
      <div class="stat-icon">✅</div>
      <div>
        <div class="stat-text-bold">Certifié Bio</div>
        <div class="stat-text-small">100% naturel</div>
      </div>
    </div>
    <div class="hero-stat hero-stat-2">
      <div class="stat-icon">🏡</div>
      <div>
        <div class="stat-text-bold">Producteurs Locaux</div>
        <div class="stat-text-small">Maroc &amp; région</div>
      </div>
    </div>
    <div class="hero-stat hero-stat-3">
      <div class="stat-icon">🚚</div>
      <div>
        <div class="stat-text-bold">Livraison Fraîche</div>
        <div class="stat-text-small">Qualité garantie</div>
      </div>
    </div>
  </div>
</section>

<!-- ══ PRODUITS VEDETTES ════════════════════════════════════════════════ -->
<section class="section">
  <div class="section-header">
    <div class="section-label">Nos Produits</div>
    <h2 class="section-title">Sélection de la <em>Saison</em></h2>
    <p class="section-sub">Des produits frais certifiés, cueillis au bon moment et livrés avec soin.</p>
  </div>
  <div class="products-grid">
    <?php if(empty($vedettes)): ?>
      <div class="empty-products">
        <p>Les produits seront affichés ici une fois ajoutés par nos producteurs.</p>
        <a href="catalogue.php" class="btn-add" style="padding:12px 26px;">Voir le catalogue complet</a>
      </div>
    <?php else: ?>
      <?php foreach($vedettes as $p): ?>
      <div class="product-card">
        <div class="product-img" style="background-image: url('<?= htmlspecialchars($p['image']) ?>');">
          <div class="product-cat"><?= htmlspecialchars($p['nomcat']) ?></div>
        </div>
        <div class="product-body">
          <div class="product-name"><?= htmlspecialchars($p['libelle']) ?></div>
          <div class="product-desc"><?= htmlspecialchars(mb_substr($p['description'], 0, 80)) ?>...</div>
          <div class="product-footer">
            <div class="product-price"><?= htmlspecialchars($p['prixu']) ?> DH</div>
            <a href="catalogue.php?action=add&ref=<?= urlencode($p['reference']) ?>" class="btn-add">+ Ajouter</a>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</section>

<!-- ══ VALEURS ══════════════════════════════════════════════════════════ -->
<section class="values-section">
  <div class="section-header">
    <div class="section-label">Nos Engagements</div>
    <h2 class="section-title">Pourquoi choisir <em>GreenMarket</em> ?</h2>
  </div>
  <div class="values-grid">
    <div class="value-card">
      <span class="value-icon">🌿</span>
      <div class="value-title">Bio &amp; Certifié</div>
      <p class="value-text">Tous nos produits sont issus de l'agriculture biologique, sans pesticides ni additifs chimiques.</p>
    </div>
    <div class="value-card">
      <span class="value-icon">🏡</span>
      <div class="value-title">Producteurs Locaux</div>
      <p class="value-text">Nous travaillons directement avec des agriculteurs locaux pour soutenir l'économie marocaine.</p>
    </div>
    <div class="value-card">
      <span class="value-icon">🚚</span>
      <div class="value-title">Fraîcheur Garantie</div>
      <p class="value-text">Nos produits sont récoltés à maturité et livrés rapidement pour préserver toute leur fraîcheur.</p>
    </div>
  </div>
</section>

<!-- ══ FOOTER ═══════════════════════════════════════════════════════════ -->
<footer>
  <div class="footer-logo">GreenMarket</div>
  <p>© 2026 GreenMarket — Plateforme de produits bio &amp; locaux au Maroc</p>
</footer>

<script>
const navbar = document.getElementById('navbar');
window.addEventListener('scroll', () => {
  if(window.scrollY > 20) navbar.classList.add('scrolled');
  else navbar.classList.remove('scrolled');
});
</script>
</body>
</html>
