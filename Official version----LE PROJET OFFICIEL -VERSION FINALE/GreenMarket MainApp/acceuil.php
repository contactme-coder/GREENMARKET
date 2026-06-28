<?php
include("preferences.php");
include("prodconnex.php");

$vedettes = [];
$cart_count = 0;
try {
    $req = $c->query("SELECT p.*, cat.libelle as nomcat FROM produit p JOIN categorie cat ON p.idcateg = cat.idcat WHERE p.statut = 'valide' ORDER BY p.dateachat DESC LIMIT 6");
    $vedettes = $req->fetchAll(PDO::FETCH_ASSOC);
} catch(Exception $e) { }

$panier = isset($_COOKIE['panier']) ? json_decode($_COOKIE['panier'], true) : [];
$cart_count = array_sum($panier);

// On inclut la barre de navigation (header)
include("header.php");
?>

<style>
/* --- SECTION HÉRO (Aucun bouton de connexion) --- */
.hero { 
  min-height: 100vh; 
  padding-top: 76px; 
  display: grid; 
  grid-template-columns: 1fr 1fr; 
  /* Image de fond : marché traditionnel marocain avec épices et fruits secs */
  background-image: url('https://images.unsplash.com/photo-1563245372-f21724e3a7a3?q=80&w=2070&auto=format&fit=crop'); 
  background-size: cover; 
  background-position: center; 
}
.hero-left { 
  padding: 80px 60px; 
  display: flex; 
  flex-direction: column; 
  justify-content: center; 
  backdrop-filter: blur(4px); 
  background: rgba(249,245,239,0.75); /* Fond semi-transparent pour lisibilité */
}
.hero-title { 
  font-family: 'Cormorant Garamond', serif; 
  font-size: 4.2rem; 
  margin-bottom: 20px; 
  color: var(--olive); 
  line-height: 1.1;
}
.hero-right { 
  display: flex; 
  align-items: center; 
  justify-content: center; 
  background: rgba(92,107,58,0.3); 
}

/* --- SECTION PRODUITS EN VEDETTE --- */
.products-grid { 
  display: grid; 
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); 
  gap: 28px; 
  padding: 60px 40px; 
  max-width: 1200px; 
  margin:0 auto; 
}
.product-card { 
  background: var(--white); 
  border-radius: 16px; 
  overflow: hidden; 
  border: 1px solid var(--cream2); 
  transition: transform 0.3s, box-shadow 0.3s; 
  perspective: 1000px;
}
.product-card:hover { 
  transform: rotateY(3deg) translateY(-8px); 
  box-shadow: 0 10px 20px rgba(0,0,0,0.1); 
}
.product-img { height: 200px; background-size: cover; background-position: center; }

/* --- THEME SOMBRE --- */
body.dark .hero-left { background: rgba(30,30,30,0.85); }
body.dark .hero-title { color: #a3c07a; }

/* --- RESPONSIVE --- */
@media(max-width: 992px){ .hero { grid-template-columns: 1fr; } .hero-left { padding: 60px 40px; } .hero-title { font-size: 3rem; } .hero-right { display:none; } }
@media(max-width: 576px){ .hero-left { padding: 40px 20px; } .hero-title { font-size: 2.5rem; } }
</style>

<!-- SECTION HÉRO -->
<section class="hero">
  <div class="hero-left">
    <h1 class="hero-title">
      <?= $lang_active === 'ar' ? 'الطبيعة في قلب <br><em>مائدتك</em>' : ($lang_active === 'en' ? 'Nature at the heart <br>of your <em>table</em>' : 'La nature au cœur <br>de votre <em>table</em>') ?>
    </h1>
    <div>
      <!-- Un seul bouton propre : Voir le catalogue -->
      <a href="catalogue.php" class="signin-btn" style="padding:15px 35px; font-size:1rem; display:inline-block; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
        <i class="fa-solid fa-bag-shopping"></i> <?= tr('cat') ?>
      </a>
    </div>
  </div>
  <div class="hero-right"><span style="font-size:7rem; opacity:0.8;">🌱</span></div>
</section>

<!-- SECTION PRODUITS EN VEDETTE -->
<section class="products-grid">
    <?php foreach($vedettes as $p): ?>
    <div class="product-card">
      <a href="produit.php?ref=<?= urlencode($p['reference']) ?>">
        <div class="product-img" style="background-image: url('<?= htmlspecialchars($p['image']) ?>');"></div>
      </a>
      <div style="padding:20px;">
        <div class="product-name" style="font-weight:500; font-size:1.1rem; margin-bottom:10px;"><?= htmlspecialchars($p['libelle']) ?></div>
        <div style="display:flex; justify-content:space-between; align-items:center;">
          <span style="font-weight:bold; color:var(--olive); font-size:1.1rem;"><?= htmlspecialchars($p['prixu']) ?> DH</span>
          <a href="catalogue.php?action=add&ref=<?= urlencode($p['reference']) ?>" class="signin-btn" style="padding:6px 14px; font-size:0.8rem;">
            <i class="fa-solid fa-cart-plus"></i>
          </a>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
</section>

<?php include("footer.php"); ?>