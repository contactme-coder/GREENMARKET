<?php
include("preferences.php");
include("prodconnex.php");
if(!isset($_SESSION['panier'])) { $_SESSION['panier'] = []; }
if(isset($_GET['action']) && $_GET['action'] == 'remove' && isset($_GET['ref'])) {
    unset($_SESSION['panier'][$_GET['ref']]);
    header("Location: panier.php"); exit;
}
$cart_items = []; $total = 0;
if(!empty($_SESSION['panier'])) {
    $refs = implode("','", array_keys($_SESSION['panier']));
    try {
        $req = $c->query("SELECT * FROM produit WHERE reference IN ('$refs')");
        while($row = $req->fetch(PDO::FETCH_ASSOC)) {
            $row['qte_panier'] = $_SESSION['panier'][$row['reference']];
            $row['sous_total'] = $row['prixu'] * $row['qte_panier'];
            $total += $row['sous_total'];
            $cart_items[] = $row;
        }
        $_SESSION['total_panier'] = $total;
    } catch(PDOException $e) { }
} else { $_SESSION['total_panier'] = 0; }
?>
<!DOCTYPE html>
<html lang="<?= $lang_active ?>" dir="<?= $lang_active === 'ar' ? 'rtl' : 'ltr' ?>">
<head>
  <meta charset="UTF-8"><title>GreenMarket – <?= tr('cart') ?></title>
  <link rel="icon" type="image/svg+xml" href="favicon.svg">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: sans-serif; background: #f9f5ef; color: #1e1e18; transition: 0.3s; padding-top: 90px; }
    body.dark { background-color: #121212 !important; color: #f9f5ef !important; }
    body.dark .navbar { background: #1e1e1e !important; border-bottom: 1px solid #333; }
    body.dark .navbar a { color: #fff !important; }
    body.dark .cart-item, body.dark .summary-box { background: #1e1e1e; border-color: #333; }
    .navbar { position: fixed; top: 0; left: 0; right: 0; height: 72px; display: flex; justify-content: space-between; align-items: center; padding: 0 40px; background: rgba(249,245,239,0.95); border-bottom: 1px solid #e8dfd0; z-index: 100; }
    .nav-links { display: flex; gap: 20px; list-style: none; }
    .nav-links a { text-decoration: none; color: #1e1e18; font-weight: bold; text-transform: uppercase; font-size:13px; }
    .container { padding: 40px; display: grid; grid-template-columns: 2fr 1fr; gap: 30px; }
    .cart-item { background: white; border: 1px solid #e8dfd0; padding: 15px; margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center; border-radius: 8px; }
    .summary-box { background: white; border: 1px solid #e8dfd0; padding: 20px; border-radius: 8px; height: fit-content; }
  </style>
</head>
<body class="<?= $theme_actif ?>">
<nav class="navbar">
  <a href="acceuil.php" style="font-weight:bold; color:#5c6b3a; font-size:1.3rem; text-decoration:none;">GreenMarket</a>
  <ul class="nav-links"><li><a href="acceuil.php"><?= tr('home') ?></a></li><li><a href="catalogue.php"><?= tr('cat') ?></a></li><li><a href="boutique.php"><?= tr('shop') ?></a></li></ul>
  <div style="display:flex; align-items:center; gap:20px;"><?php afficher_selecteurs(); ?><span style="font-weight:bold;">🛒</span></div>
</nav>
<div class="container">
  <div><h2><?= tr('cart') ?></h2><br>
    <?php if(empty($cart_items)): ?><p><?= tr('empty_cart') ?></p>
    <?php else: foreach($cart_items as $item): ?>
      <div class="cart-item"><div><strong><?= htmlspecialchars($item['libelle']) ?></strong><div style="font-size:12px; color:gray;">QTY: <?= $item['qte_panier'] ?></div></div>
      <div><strong><?= number_format($item['sous_total'], 2) ?> DH</strong></div>
      <a href="panier.php?action=remove&ref=<?= urlencode($item['reference']) ?>" style="color:#c95a5a; text-decoration:none; font-size:13px; font-weight:bold;"><?= tr('remove') ?></a></div>
    <?php endforeach; endif; ?>
  </div>
  <div class="summary-box"><h3><?= tr('summary') ?></h3><br>
    <div style="display:flex; justify-content:space-between; margin-bottom:15px;"><span><?= tr('total') ?></span><span style="font-weight:bold; color:#5c6b3a; font-size:1.3rem;"><?= number_format($total, 2) ?> DH</span></div>
    <?php if(!empty($cart_items)): ?><a href="paiement.php" style="display:block; text-align:center; background:#5c6b3a; color:white; padding:10px; border-radius:6px; text-decoration:none; font-weight:bold; text-transform:uppercase; font-size:13px;">Passer au paiement</a><?php endif; ?>
  </div>
</div>
</body>
</html>