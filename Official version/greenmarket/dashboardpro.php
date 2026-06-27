<?php
include("preferences.php");
if(!isset($_SESSION['idu']) || $_SESSION['roleu'] !== 'producteur'){ header("Location: authentification.php"); exit; }
include("prodconnex.php");
try {
    $req = $c->prepare("SELECT p.*, c.libelle as nomcat FROM produit p JOIN categorie c ON p.idcateg = c.idcat WHERE p.id_producteur = ? ORDER BY p.dateachat DESC");
    $req->execute([$_SESSION['idu']]); $tab_prod = $req->fetchAll(PDO::FETCH_ASSOC);
    $reqv = $c->prepare("SELECT cp.*, cm.date_commande, cm.methode_paiement, cm.statut as statut_cmd, p.libelle FROM commande_produit cp JOIN commande cm ON cp.idcom = cm.idcom JOIN produit p ON cp.reference_produit = p.reference WHERE p.id_producteur = ? ORDER BY cm.date_commande DESC");
    $reqv->execute([$_SESSION['idu']]); $ventes = $reqv->fetchAll(PDO::FETCH_ASSOC);
    $nb_ventes = count($ventes); $ca_producteur = 0; foreach($ventes as $v) $ca_producteur += $v['quantite'] * $v['prix_unitaire'];
} catch(PDOException $e) { die("Erreur : ".$e->getMessage()); }

// Badges
$badge_req = $c->prepare("SELECT badge_nom FROM badge_producteur WHERE id_producteur = ?");
$badge_req->execute([$_SESSION['idu']]); $badges = $badge_req->fetchAll(PDO::FETCH_COLUMN);
if($nb_ventes >= 10 && !in_array('Bronze', $badges)) { $c->prepare("INSERT INTO badge_producteur (id_producteur, badge_nom) VALUES (?, 'Bronze')")->execute([$_SESSION['idu']]); $badges[] = 'Bronze'; }
if($nb_ventes >= 50 && !in_array('Argent', $badges)) { $c->prepare("INSERT INTO badge_producteur (id_producteur, badge_nom) VALUES (?, 'Argent')")->execute([$_SESSION['idu']]); $badges[] = 'Argent'; }
if($nb_ventes >= 100 && !in_array('Or', $badges)) { $c->prepare("INSERT INTO badge_producteur (id_producteur, badge_nom) VALUES (?, 'Or')")->execute([$_SESSION['idu']]); $badges[] = 'Or'; }
?>
<!DOCTYPE html>
<html lang="<?= $lang_active ?>" dir="<?= $lang_active === 'ar' ? 'rtl' : 'ltr' ?>">
<head>
  <meta charset="UTF-8"><title>GreenMarket – <?= tr('dash') ?></title>
  <link rel="icon" type="image/svg+xml" href="favicon.svg">
  <link rel="preconnect" href="https://fonts.googleapis.com"><link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,400&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
    body { font-family:'Jost', sans-serif; background:#f9f5ef; color:#1e1e18; transition:0.3s; padding-top: 0; }
    body.dark { background:#121212 !important; color:#f9f5ef !important; }
    body.dark .navbar { background:#1e1e1e !important; }
    body.dark .navbar a { color:#f9f5ef !important; }
    body.dark .dashboard-container { background:#1a1a1a !important; }
    body.dark table { background:#1e1e1e !important; }
    body.dark th { background:#3a4a25 !important; color:#fff !important; }
    body.dark td { border-bottom-color:#333 !important; color:#f9f5ef !important; }
    body.dark tr:hover { background:#2a2a2a !important; }
    body.dark .stat-box { background:#1e1e1e !important; }
    body.dark .stat-box .stat-label { color:#cfcfcf !important; }
    body.dark .section-title-pro { border-top-color:#333 !important; }
    body.dark .badge-attente { background:#4a3b0a !important; color:#ffd76b !important; }
    body.dark .badge-livree  { background:#163d22 !important; color:#7fd99a !important; }
    body.dark .badge-annulee { background:#4a1c1c !important; color:#ff9b9b !important; }
    .navbar { display:flex; justify-content:space-between; align-items:center; background:#5c6b3a; color:white; padding:15px 30px; }
    .navbar a { color:white; text-decoration:none; font-weight:bold; }
    .navbar .logo { font-family:'Cormorant Garamond', serif; font-size:1.4rem; font-weight:600; }
    .navbar .user-menu { display:flex; align-items:center; gap:18px; }
    .dashboard-container { max-width:1200px; margin:30px auto; background:white; padding:30px; border-radius:12px; transition:0.3s; }
    .dashboard-header h2 { font-family:'Cormorant Garamond', serif; font-size:1.8rem; color:#5c6b3a; }
    .stats-row { display:flex; gap:20px; margin:25px 0; }
    .stat-box { flex:1; background:#edf0e4; border-radius:10px; padding:20px; text-align:center; transition:0.3s; }
    .stat-box .stat-value { font-size:1.8rem; font-weight:bold; color:#5c6b3a; }
    .stat-box .stat-label { font-size:0.85rem; color:#4a4a3a; margin-top:5px; }
    .stock-table { width:100%; border-collapse:collapse; margin-top:20px; }
    .stock-table th, .stock-table td { padding:12px; border-bottom:1px solid #e8dfd0; text-align:left; }
    .stock-table th { background:#f2ebe0; }
    .btn-add { display:inline-block; padding:10px 20px; background:#5c6b3a; color:white; text-decoration:none; border-radius:5px; font-weight:bold; }
    .section-title-pro { font-size:1.3rem; color:#5c6b3a; margin:35px 0 10px 0; font-weight:bold; border-top:2px solid #e8dfd0; padding-top:25px; }
    .badge { display:inline-block; padding:4px 10px; border-radius:12px; font-size:12px; font-weight:600; text-transform:uppercase; }
    .badge-attente { background:#fff3cd; color:#856404; }
    .badge-livree  { background:#d1e7dd; color:#0f5132; }
    .badge-annulee { background:#f8d7da; color:#842029; }
  </style>
</head>
<body class="<?= $theme_actif ?>">
<nav class="navbar">
  <a href="acceuil.php" class="logo">Green<span>Market</span></a>
  <div class="user-menu"><?php afficher_selecteurs(); ?><span>👤 <strong><?= htmlspecialchars($_SESSION['nomu']) ?></strong> (<?= tr('producer_tag') ?>)</span><a href="profil.php" style="color:white;"><?= tr('profile_btn') ?></a><a href="deconnexion.php" style="color:#ffbcbc;"><?= tr('logout') ?></a></div>
</nav>
<div class="dashboard-container">
  <div class="dashboard-header"><h2><?= tr('producer_welcome') ?></h2><p><?= tr('producer_sub') ?></p></div>
  <?php if(isset($_GET['msgs'])): ?><div style="color:green; background:#edf0e4; padding:12px; margin:15px 0; border-radius:6px; font-weight:bold;"><?= htmlspecialchars($_GET['msgs']) ?></div><?php endif; ?>
  <?php if(isset($_GET['msgerr'])): ?><div style="color:red; background:#fdeced; padding:12px; margin:15px 0; border-radius:6px; font-weight:bold;"><?= htmlspecialchars($_GET['msgerr']) ?></div><?php endif; ?>
  <div style="margin:20px 0;"><strong><?= tr('badges_label') ?> :</strong> <?php if(count($badges)>0): foreach($badges as $b): ?><span style="background:#f4c542; padding:4px 12px; border-radius:20px; margin-right:8px; font-weight:bold; color:#1e1e18;">🏅 <?= htmlspecialchars($b) ?></span><?php endforeach; else: ?><span style="color:gray;"><?= tr('no_badges') ?></span><?php endif; ?></div>
  <div class="stats-row">
    <div class="stat-box"><div class="stat-value"><?= $nb_ventes ?></div><div class="stat-label"><?= tr('sales_lines') ?></div></div>
    <div class="stat-box"><div class="stat-value"><?= number_format($ca_producteur, 2) ?> DH</div><div class="stat-label"><?= tr('revenue_lbl') ?></div></div>
    <div class="stat-box"><div class="stat-value"><?= count($tab_prod) ?></div><div class="stat-label"><?= tr('online_products_lbl') ?></div></div>
  </div>
  <div style="margin-top:20px; text-align:right;"><a href="ajouterprod.php" class="btn-add">+ <?= tr('add_product') ?></a></div>
  <h3 class="section-title-pro" style="border-top:none; margin-top:25px;">📦 <?= tr('my_products') ?></h3>
  <table class="stock-table"><thead><tr><th><?= tr('ref_lbl') ?></th><th><?= tr('image_lbl') ?></th><th><?= tr('product_lbl') ?></th><th><?= tr('category_lbl') ?></th><th><?= tr('stock_lbl') ?></th><th><?= tr('price_lbl') ?></th><th><?= tr('statut') ?></th><th><?= tr('actions_lbl') ?></th></tr></thead>
  <tbody><?php if(empty($tab_prod)): ?><tr><td colspan="8" style="text-align:center; color:#9a7455;"><?= tr('no_products_yet') ?></td></tr><?php else: foreach($tab_prod as $p): ?><tr><td><strong><?= htmlspecialchars($p['reference']) ?></strong></td><td><img src="<?= htmlspecialchars($p['image']) ?>" style="width:50px; height:50px; object-fit:cover; border-radius:6px;"></td><td style="font-weight:500;"><?= htmlspecialchars($p['libelle']) ?></td><td><?= htmlspecialchars($p['nomcat']) ?></td><td><?= htmlspecialchars($p['quantite']) ?></td><td><?= htmlspecialchars($p['prixu']) ?> DH</td><td><span style="color: <?= $p['statut'] == 'valide' ? 'green' : 'orange' ?>; font-weight:bold;"><?= htmlspecialchars($p['statut']) ?></span></td><td><a href="modifierprod.php?refp=<?= urlencode($p['reference']) ?>" style="color:#748249; margin-right:15px; font-weight:bold;"><?= tr('modify_lbl') ?></a><a href="supprimerprod.php?refp=<?= urlencode($p['reference']) ?>" class="btn-delete" style="color:#c95a5a; font-weight:bold;"><?= tr('delete_lbl') ?></a></td></tr><?php endforeach; endif; ?></tbody></table>
  <h3 class="section-title-pro">💰 <?= tr('my_sales') ?></h3>
  <table class="stock-table"><thead><tr><th><?= tr('date') ?></th><th><?= tr('product_lbl') ?></th><th><?= tr('qty_sold') ?></th><th><?= tr('unit_price') ?></th><th><?= tr('subtotal') ?></th><th><?= tr('method_lbl') ?></th><th><?= tr('statut') ?></th></tr></thead>
  <tbody><?php if(empty($ventes)): ?><tr><td colspan="7" style="text-align:center; color:#9a7455;"><?= tr('no_sales_yet') ?></td></tr><?php else: foreach($ventes as $v): ?><tr><td><?= date('d/m/Y H:i', strtotime($v['date_commande'])) ?></td><td style="font-weight:500;"><?= htmlspecialchars($v['libelle']) ?></td><td><?= htmlspecialchars($v['quantite']) ?></td><td><?= number_format($v['prix_unitaire'], 2) ?> DH</td><td style="font-weight:bold;"><?= number_format($v['quantite'] * $v['prix_unitaire'], 2) ?> DH</td><td style="text-transform:uppercase; font-size:12px; color:#748249;"><?= htmlspecialchars($v['methode_paiement']) ?></td><td><span class="badge badge-<?= htmlspecialchars($v['statut_cmd']) ?>"><?= htmlspecialchars($v['statut_cmd']) ?></span></td></tr><?php endforeach; endif; ?></tbody></table>
</div>
<script src="script_dash.js"></script>
</body>
</html>