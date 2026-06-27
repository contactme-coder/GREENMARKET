<?php
include("preferences.php");
if(!isset($_SESSION['idu']) || $_SESSION['roleu'] !== 'client'){ header("Location: authentification.php"); exit; }
include("prodconnex.php");
$pts_req = $c->prepare("SELECT SUM(points) as total_points FROM point_fidelite WHERE id_client = ?");
$pts_req->execute([$_SESSION['idu']]);
$points = $pts_req->fetchColumn(); if($points === null) $points = 0;
$req = $c->prepare("SELECT cmd.*, (SELECT COUNT(*) FROM commande_produit cp WHERE cp.idcom = cmd.idcom) as nb_articles FROM commande cmd WHERE cmd.id_client = ? ORDER BY cmd.date_commande DESC");
$req->execute([$_SESSION['idu']]);
$commandes = $req->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="<?= $lang_active ?>" dir="<?= $lang_active === 'ar' ? 'rtl' : 'ltr' ?>">
<head>
  <meta charset="UTF-8"><title>GreenMarket – <?= tr('acc') ?></title>
  <link rel="icon" type="image/svg+xml" href="favicon.svg">
  <style>
    *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
    body { font-family:sans-serif; background:#f9f5ef; color:#1e1e18; transition:0.3s; padding-top:90px; }
    body.dark { background:#121212 !important; color:#f9f5ef !important; }
    body.dark .navbar { background:#1e1e1e !important; border-bottom:1px solid #333; }
    body.dark .navbar a { color:#f9f5ef !important; }
    body.dark table { background:#1e1e1e !important; }
    body.dark th { background:#3a4a25 !important; color:#fff !important; }
    body.dark td { border-bottom-color:#333 !important; color:#f9f5ef !important; }
    body.dark tr:hover { background:#2a2a2a !important; }
    body.dark .cmd-success { background:#1e3320; color:#a3c07a; border-color:#5c6b3a; }
    body.dark .badge-attente { background:#4a3b0a !important; color:#ffd76b !important; }
    body.dark .badge-livree  { background:#163d22 !important; color:#7fd99a !important; }
    body.dark .badge-annulee { background:#4a1c1c !important; color:#ff9b9b !important; }
    body.dark .empty-msg { color:#cfcfcf !important; }
    .navbar { position:fixed; top:0; left:0; right:0; height:72px; display:flex; justify-content:space-between; align-items:center; padding:0 40px; background:#fff; box-shadow:0 2px 5px rgba(0,0,0,0.05); z-index:1000; }
    .nav-links { display:flex; list-style:none; gap:30px; }
    .nav-links a { text-decoration:none; color:#5c6b3a; font-weight:500; font-size:14px; }
    .container { max-width:1100px; margin:0 auto; padding:30px 20px; }
    .page-title { font-family:'Cormorant Garamond',Georgia,serif; font-size:2rem; color:#5c6b3a; margin-bottom:25px; }
    .points-badge { background:#f4c542; color:#1e1e18; padding:10px 20px; border-radius:30px; font-weight:bold; display:inline-block; margin-bottom:20px; }
    .cmd-success { background:#edf0e4; color:#5c6b3a; padding:18px 25px; border-radius:10px; border-left:5px solid #5c6b3a; margin-bottom:25px; font-weight:bold; display:flex; align-items:center; gap:12px; }
    table { width:100%; border-collapse:collapse; margin-top:10px; background:#fff; border-radius:10px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,0.04); }
    th, td { padding:15px 20px; text-align:left; border-bottom:1px solid #eee; }
    th { background:#5c6b3a; color:white; font-size:13px; text-transform:uppercase; letter-spacing:0.04em; font-weight:600; }
    tr:hover { background:#f9f5ef; }
    td:first-child { font-weight:bold; color:#5c6b3a; }
    .badge { display:inline-block; padding:4px 10px; border-radius:12px; font-size:12px; font-weight:600; text-transform:uppercase; }
    .badge-attente { background:#fff3cd; color:#856404; }
    .badge-livree  { background:#d1e7dd; color:#0f5132; }
    .badge-annulee { background:#f8d7da; color:#842029; }
    .empty-msg { text-align:center; padding:50px; color:#8a8a74; }
    .empty-msg .icon { font-size:3rem; display:block; margin-bottom:10px; }
  </style>
</head>
<body class="<?= $theme_actif ?>">
<nav class="navbar">
  <a href="acceuil.php" style="font-weight:bold; color:#5c6b3a; font-size:1.3rem; text-decoration:none;">GreenMarket</a>
  <ul class="nav-links"><li><a href="acceuil.php"><?= tr('home') ?></a></li><li><a href="catalogue.php"><?= tr('cat') ?></a></li><li><a href="boutique.php"><?= tr('shop') ?></a></li></ul>
  <div style="display:flex; align-items:center; gap:20px;"><?php afficher_selecteurs(); ?><a href="panier.php" style="text-decoration:none; color:#5c6b3a; font-size:1.1rem;">🛒</a><a href="profil.php" style="text-decoration:none; color:#5c6b3a; font-weight:bold;">👤</a><a href="deconnexion.php" style="color:#c95a5a; font-weight:bold; text-decoration:none; font-size:13px; text-transform:uppercase;"><?= tr('logout') ?></a></div>
</nav>
<div class="container">
  <h1 class="page-title">👋 <?= htmlspecialchars($_SESSION['nomu']) ?></h1>
  <div class="points-badge">⭐ <?= $points ?> <?= tr('points_label') ?></div>
  <?php if(isset($_GET['cmd_ok'])): ?><div class="cmd-success">✅ Commande #<?= intval($_GET['cmd_ok']) ?> confirmée !</div><?php endif; ?>
  <h2 style="font-size:1.1rem; font-weight:600; margin-bottom:15px; color:inherit;"><?= tr('cmd_hist') ?></h2>
  <?php if(empty($commandes)): ?><div class="empty-msg"><span class="icon">🧺</span><?= tr('no_cmd') ?><div style="margin-top:15px;"><a href="catalogue.php" style="color:#5c6b3a; font-weight:bold; text-decoration:none;">→ Voir le catalogue</a></div></div>
  <?php else: ?><table><thead><tr><th><?= tr('num_cmd') ?></th><th><?= tr('date') ?></th><th><?= tr('articles') ?></th><th><?= tr('montant') ?></th><th><?= tr('pay') ?></th><th><?= tr('statut') ?></th></tr></thead>
  <tbody><?php foreach($commandes as $cmd): ?><tr><td>#<?= $cmd['idcom'] ?></td><td><?= date('d/m/Y H:i', strtotime($cmd['date_commande'])) ?></td><td><?= $cmd['nb_articles'] ?> article<?= $cmd['nb_articles'] > 1 ? 's' : '' ?></td><td style="font-weight:bold; color:#5c6b3a;"><?= number_format($cmd['montant_total'], 2) ?> DH</td><td style="text-transform:uppercase; font-size:12px;"><?= htmlspecialchars($cmd['methode_paiement']) ?></td><td><span class="badge badge-<?= $cmd['statut'] ?>"><?= htmlspecialchars($cmd['statut']) ?></span></td></tr><?php endforeach; ?></tbody></table><?php endif; ?>
</div>
</body>
</html>