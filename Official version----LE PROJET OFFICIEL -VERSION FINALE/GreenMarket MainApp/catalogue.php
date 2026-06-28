<?php
include("preferences.php");
include("prodconnex.php");

// Gestion du panier avec Cookie (CORRIGÉ)
if(isset($_GET['action']) && $_GET['action'] == 'add' && isset($_GET['ref'])) {
    $ref = $_GET['ref'];
    $panier = isset($_COOKIE['panier']) ? json_decode($_COOKIE['panier'], true) : [];
    $panier[$ref] = isset($panier[$ref]) ? $panier[$ref] + 1 : 1;
    // CORRECTION : On écrit le cookie ET on force la mise à jour dans le script actuel
    setcookie('panier', json_encode($panier), time() + (86400 * 30), "/");
    $_COOKIE['panier'] = json_encode($panier); 
    header("Location: catalogue.php?msgs=ok");
    exit;
}

// ... (le reste du fichier, filtres, etc., identique à ma réponse précédente) ...
$where = ["p.statut = 'valide'"]; $params = [];
if(isset($_GET['search']) && !empty(trim($_GET['search']))) { $search = "%".trim($_GET['search'])."%"; $where[] = "(p.libelle LIKE ? OR p.description LIKE ?)"; $params[]=$search; $params[]=$search; }
if(isset($_GET['categorie']) && !empty($_GET['categorie']) && $_GET['categorie'] != 'all') { $where[] = "p.idcateg = ?"; $params[] = $_GET['categorie']; }
if(isset($_GET['min_price']) && is_numeric($_GET['min_price'])) { $where[] = "p.prixu >= ?"; $params[] = $_GET['min_price']; }
if(isset($_GET['max_price']) && is_numeric($_GET['max_price'])) { $where[] = "p.prixu <= ?"; $params[] = $_GET['max_price']; }

$sql = "SELECT p.*, c.libelle as nomcat FROM produit p JOIN categorie c ON p.idcateg = c.idcat WHERE " . implode(" AND ", $where) . " ORDER BY p.dateachat DESC";
try { $req = $c->prepare($sql); $req->execute($params); $produits = $req->fetchAll(PDO::FETCH_ASSOC); } catch(PDOException $e) { $produits = []; }
$cat_req = $c->query("SELECT idcat, libelle FROM categorie ORDER BY libelle"); $categories = $cat_req->fetchAll(PDO::FETCH_ASSOC);

// Lecture du cookie pour le badge dans la barre
$panier = isset($_COOKIE['panier']) ? json_decode($_COOKIE['panier'], true) : [];
$cart_count = array_sum($panier);

include("header.php");
?>
<!-- ... Le reste du code HTML / CSS / Boucle produits reste identique à ma réponse précédente ... -->
<!-- Je vous l'inclus dans le fichier complet ci-dessous -->
<style>
.container { padding: 100px 40px 40px; max-width: 1200px; margin:0 auto; }
.filters { display: flex; flex-wrap: wrap; gap: 15px; margin-bottom: 30px; background: var(--white); padding: 20px; border-radius: 12px; border: 1px solid var(--cream2); box-shadow: 0 4px 15px rgba(0,0,0,0.04); }
.filters input, .filters select { padding: 8px 12px; border: 1px solid var(--cream2); border-radius: 6px; background: var(--ivory); font-family: inherit; }
.filters button { padding: 8px 20px; background: var(--olive); color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; transition:0.3s; }
.filters button:hover { background: var(--terracotta); }
.products-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 25px; }
.product-card { background: var(--white); border: 1px solid var(--cream2); border-radius: 12px; overflow: hidden; transition:0.3s; perspective: 1000px;}
.product-card:hover { transform: rotateY(3deg) translateY(-8px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
.stars { color: #f4c542; font-size: 14px; }
.badge-fresh { position:absolute; top:10px; right:10px; background:#27ae60; color:white; padding:4px 12px; border-radius:20px; font-size:11px; font-weight:bold;}
.badge-nonfresh { position:absolute; top:10px; right:10px; background:#f39c12; color:white; padding:4px 12px; border-radius:20px; font-size:11px; font-weight:bold;}
</style>
<div class="container">
  <h1 style="font-family:'Cormorant Garamond', serif; font-size:2.5rem; color:var(--olive); margin-bottom:20px;">Nos Produits Authentiques</h1>
  <form method="GET" class="filters">
    <input type="text" name="search" placeholder="🔍 Rechercher..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
    <select name="categorie">
      <option value="all">Toutes catégories</option>
      <?php foreach($categories as $cat): ?>
        <option value="<?= $cat['idcat'] ?>" <?= (isset($_GET['categorie']) && $_GET['categorie'] == $cat['idcat']) ? 'selected' : '' ?>><?= htmlspecialchars($cat['libelle']) ?></option>
      <?php endforeach; ?>
    </select>
    <input type="number" name="min_price" placeholder="Prix min" step="0.01" value="<?= htmlspecialchars($_GET['min_price'] ?? '') ?>">
    <input type="number" name="max_price" placeholder="Prix max" step="0.01" value="<?= htmlspecialchars($_GET['max_price'] ?? '') ?>">
    <button type="submit"><i class="fa-solid fa-sliders"></i> Filtrer</button>
  </form>
  <div class="products-grid">
    <?php foreach($produits as $p): 
        $av_req = $c->prepare("SELECT * FROM avis WHERE reference_produit = ? AND statut = 'valide'");
        $av_req->execute([$p['reference']]); $avis = $av_req->fetchAll(PDO::FETCH_ASSOC);
        $moyenne = 0; if(count($avis) > 0) { $somme = array_sum(array_column($avis, 'note')); $moyenne = round($somme / count($avis), 1); }
        $is_fresh = ($p['idcateg'] == 1);
    ?>
    <div class="product-card" style="position:relative;">
      <span class="<?= $is_fresh ? 'badge-fresh' : 'badge-nonfresh' ?>">
        <?= $is_fresh ? '🌿 Frais' : '📦 Non Frais' ?>
      </span>
      <a href="produit.php?ref=<?= urlencode($p['reference']) ?>">
        <img src="<?= htmlspecialchars($p['image']) ?>" style="width:100%; height:180px; object-fit:cover;">
      </a>
      <div style="padding:15px;">
        <h4 style="margin-bottom:5px;"><a href="produit.php?ref=<?= urlencode($p['reference']) ?>" style="text-decoration:none; color:inherit;"><?= htmlspecialchars($p['libelle']) ?></a></h4>
        <div class="stars"><?php for($i=1; $i<=5; $i++): ?><?= $i <= $moyenne ? '★' : '☆' ?><?php endfor; ?> <span style="font-size:12px; color:gray;">(<?= count($avis) ?>)</span></div>
        <div style="display:flex; justify-content:space-between; align-items:center; margin-top:10px;">
          <span style="font-weight:bold; color:var(--olive);"><?= htmlspecialchars($p['prixu']) ?> DH</span>
          <a href="catalogue.php?action=add&ref=<?= urlencode($p['reference']) ?>" style="background:var(--olive); color:white; padding:6px 12px; border-radius:6px; text-decoration:none; font-size:12px; transition:0.3s;"><i class="fa-solid fa-cart-plus"></i></a>
        </div>
        <?php if(isset($_SESSION['idu']) && $_SESSION['roleu'] == 'client'): ?>
        <div class="review-form">
            <form method="POST" action="ajouter_avis.php">
                <input type="hidden" name="reference" value="<?= htmlspecialchars($p['reference']) ?>">
                <div style="display:flex; gap:5px; margin-bottom:5px;">
                    <select name="note" required style="padding:5px;"><option value="">Note</option><option value="5">5 ★</option><option value="4">4 ★</option><option value="3">3 ★</option><option value="2">2 ★</option><option value="1">1 ★</option></select>
                </div>
                <textarea name="commentaire" placeholder="Votre commentaire..." rows="2" required></textarea>
                <button type="submit" style="background:var(--olive); color:white; border:none; padding:5px 10px; border-radius:4px; cursor:pointer; font-size:12px; margin-top:5px;"><i class="fa-regular fa-paper-plane"></i> <?= tr('submit_review') ?></button>
            </form>
        </div>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<?php include("footer.php"); ?>