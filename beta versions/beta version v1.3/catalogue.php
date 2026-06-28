<?php
include("preferences.php");
include("prodconnex.php");

if(!isset($_SESSION['panier'])) { $_SESSION['panier'] = []; }

if(isset($_GET['action']) && $_GET['action'] == 'add' && isset($_GET['ref'])) {
    $ref = $_GET['ref'];
    $_SESSION['panier'][$ref] = isset($_SESSION['panier'][$ref]) ? $_SESSION['panier'][$ref] + 1 : 1;
    header("Location: catalogue.php?msgs=ok");
    exit;
}

$where = ["p.statut = 'valide'"];
$params = [];
if(isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search = "%" . trim($_GET['search']) . "%";
    $where[] = "(p.libelle LIKE ? OR p.description LIKE ?)";
    $params[] = $search; $params[] = $search;
}
if(isset($_GET['categorie']) && !empty($_GET['categorie']) && $_GET['categorie'] != 'all') {
    $where[] = "p.idcateg = ?"; $params[] = $_GET['categorie'];
}
if(isset($_GET['min_price']) && is_numeric($_GET['min_price']) && $_GET['min_price'] >= 0) {
    $where[] = "p.prixu >= ?"; $params[] = $_GET['min_price'];
}
if(isset($_GET['max_price']) && is_numeric($_GET['max_price']) && $_GET['max_price'] > 0) {
    $where[] = "p.prixu <= ?"; $params[] = $_GET['max_price'];
}

$sql = "SELECT p.*, c.libelle as nomcat FROM produit p JOIN categorie c ON p.idcateg = c.idcat WHERE " . implode(" AND ", $where) . " ORDER BY p.dateachat DESC";
try {
    $req = $c->prepare($sql);
    $req->execute($params);
    $produits = $req->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) { $produits = []; }

$cat_req = $c->query("SELECT idcat, libelle FROM categorie ORDER BY libelle");
$categories = $cat_req->fetchAll(PDO::FETCH_ASSOC);
$cart_count = array_sum($_SESSION['panier']);
?>
<!DOCTYPE html>
<html lang="<?= $lang_active ?>" dir="<?= $lang_active === 'ar' ? 'rtl' : 'ltr' ?>">
<head>
  <meta charset="UTF-8">
  <title>GreenMarket – <?= tr('cat') ?></title>
  <link rel="icon" type="image/svg+xml" href="favicon.svg">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    :root { --ivory: #f9f5ef; --cream2: #e8dfd0; --olive: #5c6b3a; --text: #1e1e18; --white: #ffffff; }
    body { font-family: sans-serif; background: var(--ivory); color: var(--text); transition: 0.3s; padding-top: 90px; }
    body.dark { background-color: #121212 !important; color: #f9f5ef !important; }
    body.dark .navbar { background: #1e1e1e !important; border-bottom: 1px solid #333; }
    body.dark .navbar a { color: #fff !important; }
    body.dark .product-card { background: #1e1e1e; border-color: #333; }
    .navbar { position: fixed; top: 0; left: 0; right: 0; height: 72px; display: flex; justify-content: space-between; align-items: center; padding: 0 40px; background: rgba(249,245,239,0.95); border-bottom: 1px solid var(--cream2); z-index: 100; }
    .nav-links { display: flex; gap: 20px; list-style: none; }
    .nav-links a { text-decoration: none; color: var(--text); font-weight: bold; text-transform: uppercase; font-size: 13px; }
    .container { padding: 40px; }
    .filters { display: flex; flex-wrap: wrap; gap: 15px; margin-bottom: 30px; background: white; padding: 20px; border-radius: 10px; border: 1px solid var(--cream2); }
    .filters input, .filters select { padding: 8px 12px; border: 1px solid var(--cream2); border-radius: 6px; background: var(--ivory); }
    .filters button { padding: 8px 20px; background: var(--olive); color: white; border: none; border-radius: 6px; cursor: pointer; }
    .products-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 25px; }
    .product-card { background: var(--white); border: 1px solid var(--cream2); border-radius: 12px; overflow: hidden; transition:0.3s; }
    .stars { color: #f4c542; font-size: 14px; }
    .review-form { margin-top: 15px; border-top: 1px solid var(--cream2); padding-top: 15px; }
    .review-form textarea { width: 100%; padding: 8px; border: 1px solid var(--cream2); border-radius: 6px; }
    .review-form select { padding: 5px; }
  </style>
</head>
<body class="<?= $theme_actif ?>">
<nav class="navbar">
  <a href="acceuil.php" style="font-weight:bold; color:var(--olive); font-size:1.3rem; text-decoration:none;">GreenMarket</a>
  <ul class="nav-links">
    <li><a href="acceuil.php"><?= tr('home') ?></a></li>
    <li><a href="catalogue.php"><?= tr('cat') ?></a></li>
    <li><a href="boutique.php"><?= tr('shop') ?></a></li>
  </ul>
  <div style="display:flex; align-items:center; gap:20px;">
    <?php afficher_selecteurs(); ?>
    <a href="panier.php" style="text-decoration:none; color:inherit; font-weight:bold;">🛒 (<?= $cart_count ?>)</a>
  </div>
</nav>
<div class="container">
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
    <button type="submit">Filtrer</button>
  </form>
  <div class="products-grid">
    <?php foreach($produits as $p): 
        $av_req = $c->prepare("SELECT * FROM avis WHERE reference_produit = ? AND statut = 'valide'");
        $av_req->execute([$p['reference']]);
        $avis = $av_req->fetchAll(PDO::FETCH_ASSOC);
        $moyenne = 0;
        if(count($avis) > 0) { $somme = array_sum(array_column($avis, 'note')); $moyenne = round($somme / count($avis), 1); }
    ?>
    <div class="product-card">
      <img src="<?= htmlspecialchars($p['image']) ?>" style="width:100%; height:180px; object-fit:cover;">
      <div style="padding:15px;">
        <h4 style="margin-bottom:5px;"><?= htmlspecialchars($p['libelle']) ?></h4>
        <div class="stars">
            <?php for($i=1; $i<=5; $i++): ?><?= $i <= $moyenne ? '★' : '☆' ?><?php endfor; ?>
            <span style="font-size:12px; color:gray;">(<?= count($avis) ?> avis)</span>
        </div>
        <div style="display:flex; justify-content:space-between; align-items:center; margin-top:10px;">
          <span style="font-weight:bold; color:var(--olive);"><?= htmlspecialchars($p['prixu']) ?> DH</span>
          <a href="catalogue.php?action=add&ref=<?= urlencode($p['reference']) ?>" style="background:var(--olive); color:white; padding:6px 12px; border-radius:6px; text-decoration:none; font-size:12px;">+ Add</a>
        </div>
        <?php if(isset($_SESSION['idu']) && $_SESSION['roleu'] == 'client'): ?>
        <div class="review-form">
            <form method="POST" action="ajouter_avis.php">
                <input type="hidden" name="reference" value="<?= htmlspecialchars($p['reference']) ?>">
                <select name="note" required><option value="">Note</option><option value="5">5 ★</option><option value="4">4 ★</option><option value="3">3 ★</option><option value="2">2 ★</option><option value="1">1 ★</option></select>
                <textarea name="commentaire" placeholder="Votre commentaire..." rows="2" required></textarea>
                <button type="submit" style="background:var(--olive); color:white; border:none; padding:5px 10px; border-radius:4px; cursor:pointer; font-size:12px; margin-top:5px;"><?= tr('submit_review') ?></button>
            </form>
        </div>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
</body>
</html>