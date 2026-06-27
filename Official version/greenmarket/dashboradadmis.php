<?php
include("preferences.php");
if(!isset($_SESSION['idu']) || $_SESSION['roleu'] !== 'admin'){ header("Location: authentification.php"); exit; }
include("prodconnex.php");

if(isset($_GET['action'])) {
    extract($_GET);
    try {
        if($action == 'valider_compte' && isset($id)) { $c->query("UPDATE compte SET statut = 'actif' WHERE id = " . intval($id)); $msg = "Le compte producteur a été validé."; }
        elseif($action == 'rejeter_compte' && isset($id)) { $c->query("DELETE FROM compte WHERE id = " . intval($id)); $msg = "Le compte a été refusé et supprimé."; }
        elseif($action == 'valider_prod' && isset($ref)) { $c->prepare("UPDATE produit SET statut = 'valide' WHERE reference = ?")->execute([$ref]); $msg = "Le produit a été validé et est en ligne."; }
        elseif($action == 'rejeter_prod' && isset($ref)) { $c->prepare("UPDATE produit SET statut = 'refuse' WHERE reference = ?")->execute([$ref]); $msg = "Le produit a été refusé."; }
        header("Location: dashboradadmis.php?msgs=" . urlencode($msg)); exit;
    } catch(PDOException $e) { die("Erreur d'action : " . $e->getMessage()); }
}
try {
    $req_c = $c->query("SELECT * FROM compte WHERE statut = 'en_attente'"); $comptes = $req_c->fetchAll(PDO::FETCH_ASSOC);
    $req_p = $c->query("SELECT p.*, c.nom as producteur FROM produit p JOIN compte c ON p.id_producteur = c.id WHERE p.statut = 'en_attente'"); $prods = $req_p->fetchAll(PDO::FETCH_ASSOC);
    $stat = $c->query("SELECT COUNT(*) as nb_total, COALESCE(SUM(montant_total),0) as ca_total FROM commande")->fetch(PDO::FETCH_ASSOC);
    $nb_commandes = $stat['nb_total']; $ca_total = $stat['ca_total'];
    $stat_jour = $c->query("SELECT COUNT(*) as nb, COALESCE(SUM(montant_total),0) as ca FROM commande WHERE DATE(date_commande) = CURDATE()")->fetch(PDO::FETCH_ASSOC);
    $nb_commandes_jour = $stat_jour['nb']; $ca_jour = $stat_jour['ca'];
    $recherche = isset($_GET['recherche']) ? trim($_GET['recherche']) : "";
    if($recherche !== ""){
        $req_cmd = $c->prepare("SELECT cmd.*, cpt.nom as client_nom, cpt.email as client_email FROM commande cmd JOIN compte cpt ON cmd.id_client = cpt.id WHERE cpt.nom LIKE ? OR cpt.email LIKE ? OR cmd.idcom = ? ORDER BY cmd.date_commande DESC");
        $motcle = "%" . $recherche . "%"; $req_cmd->execute([$motcle, $motcle, is_numeric($recherche) ? $recherche : 0]);
    } else { $req_cmd = $c->query("SELECT cmd.*, cpt.nom as client_nom, cpt.email as client_email FROM commande cmd JOIN compte cpt ON cmd.id_client = cpt.id ORDER BY cmd.date_commande DESC LIMIT 30"); }
    $commandes_admin = $req_cmd->fetchAll(PDO::FETCH_ASSOC);
    $req_avis = $c->query("SELECT a.*, c.nom as client_nom, p.libelle as prod_libelle FROM avis a JOIN compte c ON a.id_client = c.id JOIN produit p ON a.reference_produit = p.reference WHERE a.statut = 'en_attente'");
    $avis_attente = $req_avis->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) { die("Erreur BD : " . $e->getMessage()); }

if(isset($_GET['action_avis']) && isset($_GET['id_avis'])) {
    $id = intval($_GET['id_avis']); $action = $_GET['action_avis']; $new_statut = ($action == 'valider') ? 'valide' : 'refuse';
    $up = $c->prepare("UPDATE avis SET statut = ?, id_moderateur = ? WHERE id = ?");
    $up->execute([$new_statut, $_SESSION['idu'], $id]);
    header("Location: dashboradadmis.php?msgs=" . urlencode("Avis " . ($action == 'valider' ? 'validé' : 'refusé'))); exit;
}
?>
<!DOCTYPE html>
<html lang="<?= $lang_active ?>" dir="<?= $lang_active === 'ar' ? 'rtl' : 'ltr' ?>">
<head>
  <meta charset="UTF-8"><title>GreenMarket – <?= tr('admin_space') ?></title>
  <link rel="icon" type="image/svg+xml" href="favicon.svg">
  <style>
    *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
    body { font-family:sans-serif; background:#f9f5ef; color:#1e1e18; transition:0.3s; padding:20px; }
    body.dark { background:#121212 !important; color:#f9f5ef !important; }
    body.dark .card { background:#1e1e1e !important; box-shadow:none !important; }
    body.dark table { background:#1e1e1e !important; }
    body.dark th { background:#3a4a25 !important; color:#fff !important; }
    body.dark td { border-bottom-color:#333 !important; color:#f9f5ef !important; }
    body.dark tr:hover { background:#2a2a2a !important; }
    body.dark .stat-box { background:#1e1e1e !important; }
    body.dark .stat-box .stat-label { color:#cfcfcf !important; }
    body.dark .search-box input { background:#1e1e1e !important; color:#f9f5ef !important; border-color:#444 !important; }
    .top-bar { display:flex; justify-content:space-between; align-items:center; margin-bottom:30px; flex-wrap:wrap; gap:15px; }
    .top-bar h2 { font-family:'Cormorant Garamond', Georgia, serif; }
    .user-menu { display:flex; align-items:center; gap:18px; }
    .card { background:white; padding:20px; border-radius:12px; margin-bottom:20px; box-shadow:0 2px 10px rgba(0,0,0,0.05); transition:0.3s; }
    .btn-approve { background:#2b6e2f; color:white; padding:6px 12px; text-decoration:none; border-radius:5px; }
    .btn-reject { background:#c95a5a; color:white; padding:6px 12px; text-decoration:none; border-radius:5px; }
    .stats-row { display:flex; gap:20px; margin-bottom:20px; flex-wrap:wrap; }
    .stat-box { flex:1; min-width:180px; background:white; border-radius:12px; padding:22px; text-align:center; box-shadow:0 2px 10px rgba(0,0,0,0.05); transition:0.3s; }
    .stat-box .stat-value { font-size:1.9rem; font-weight:bold; color:#5c6b3a; }
    .stat-box .stat-label { font-size:0.85rem; color:#4a4a3a; margin-top:6px; }
    .search-box { display:flex; gap:10px; margin-bottom:15px; flex-wrap:wrap; }
    .search-box input { flex:1; min-width:200px; padding:10px 14px; border:1px solid #d4c5ad; border-radius:8px; }
    .search-box button { padding:10px 20px; background:#5c6b3a; color:white; border:none; border-radius:8px; cursor:pointer; font-weight:bold; }
    .search-box a.reset { padding:10px 16px; color:#c95a5a; text-decoration:none; font-size:0.85rem; align-self:center; }
  </style>
</head>
<body class="<?= $theme_actif ?>">
<div class="top-bar"><h2><?= tr('admin_space') ?></h2><div class="user-menu"><?php afficher_selecteurs(); ?><span style="font-weight:bold;"><?= tr('admin_tag') ?>: <?= htmlspecialchars($_SESSION['nomu']) ?></span><a href="profil.php" style="color:black; text-decoration:none;"><?= tr('profile_btn') ?></a><a href="deconnexion.php" style="color:#c95a5a; text-decoration:none; font-weight:bold;"><?= tr('logout') ?></a></div></div>
<?php if(isset($_GET['msgs'])) echo "<div style='color:white; background:#2b6e2f; padding:15px; border-radius:8px; margin-bottom:20px;'>".htmlspecialchars($_GET['msgs'])."</div>"; ?>
<div class="stats-row"><div class="stat-box"><div class="stat-value"><?= $nb_commandes ?></div><div class="stat-label"><?= tr('total_orders_lbl') ?></div></div><div class="stat-box"><div class="stat-value"><?= number_format($ca_total, 2) ?> DH</div><div class="stat-label"><?= tr('revenue_lbl') ?></div></div><div class="stat-box"><div class="stat-value"><?= $nb_commandes_jour ?></div><div class="stat-label"><?= tr('orders_today_lbl') ?></div></div><div class="stat-box"><div class="stat-value"><?= number_format($ca_jour, 2) ?> DH</div><div class="stat-label"><?= tr('revenue_today_lbl') ?></div></div></div>
<div class="card"><h3 style="margin-top:0;"><?= tr('order_surveillance') ?></h3><form method="GET" class="search-box"><input type="text" name="recherche" placeholder="<?= htmlspecialchars(tr('search_ph')) ?>" value="<?= htmlspecialchars($recherche) ?>"><button type="submit"><?= tr('search_btn') ?></button><?php if($recherche !== ""): ?><a href="dashboradadmis.php" class="reset">✕ <?= tr('reset_btn') ?></a><?php endif; ?></form><table style="width:100%; text-align:left; border-collapse:collapse;"><tr><th><?= tr('num_cmd') ?></th><th><?= tr('client_lbl') ?></th><th><?= tr('montant') ?></th><th><?= tr('method_lbl') ?></th><th><?= tr('statut') ?></th><th><?= tr('date') ?></th></tr><?php if(empty($commandes_admin)): ?><tr><td colspan="6"><?= tr('no_order_found') ?></td></tr><?php else: foreach($commandes_admin as $cmd): ?><tr><td>#<?= htmlspecialchars($cmd['idcom']) ?></td><td><strong><?= htmlspecialchars($cmd['client_nom']) ?></strong><br><span style="font-size:12px; color:#888;"><?= htmlspecialchars($cmd['client_email']) ?></span></td><td style="font-weight:bold; color:#5c6b3a;"><?= number_format($cmd['montant_total'], 2) ?> DH</td><td><?= htmlspecialchars($cmd['methode_paiement']) ?></td><td><?= htmlspecialchars($cmd['statut']) ?></td><td><?= date('d/m/Y H:i', strtotime($cmd['date_commande'])) ?></td></tr><?php endforeach; endif; ?></table></div>
<div class="card"><h3><?= tr('moderate_reviews') ?></h3><table style="width:100%;"><tr><th>Client</th><th>Produit</th><th>Note</th><th>Commentaire</th><th>Actions</th></tr><?php if(empty($avis_attente)): ?><tr><td colspan="5"><?= tr('no_pending_reviews') ?></td></tr><?php else: foreach($avis_attente as $av): ?><tr><td><?= htmlspecialchars($av['client_nom']) ?></td><td><?= htmlspecialchars($av['prod_libelle']) ?></td><td><?= $av['note'] ?> ★</td><td><?= htmlspecialchars(substr($av['commentaire'],0,50)) ?>...</td><td><a href="dashboradadmis.php?action_avis=valider&id_avis=<?= $av['id'] ?>" class="btn-approve">✅ Valider</a><a href="dashboradadmis.php?action_avis=refuser&id_avis=<?= $av['id'] ?>" class="btn-reject">❌ Refuser</a></td></tr><?php endforeach; endif; ?></table></div>
<div class="card"><h3><?= tr('pending_producers') ?></h3><table style="width:100%;"><tr><th><?= tr('name_lbl') ?></th><th><?= tr('email_lbl') ?></th><th><?= tr('actions_lbl') ?></th></tr><?php if(empty($comptes)): ?><tr><td colspan="3"><?= tr('no_pending_accounts') ?></td></tr><?php else: foreach($comptes as $u): ?><tr><td><strong><?= htmlspecialchars($u['nom']) ?></strong></td><td><?= htmlspecialchars($u['email']) ?></td><td><a href="dashboradadmis.php?action=valider_compte&id=<?= $u['id'] ?>" class="btn-approve"><?= tr('approve_lbl') ?></a><a href="dashboradadmis.php?action=rejeter_compte&id=<?= $u['id'] ?>" class="btn-reject" onclick="return confirm('Refuser ce compte ?')"><?= tr('reject_lbl') ?></a></td></tr><?php endforeach; endif; ?></table></div>
<div class="card"><h3><?= tr('pending_products') ?></h3><table style="width:100%;"><tr><th><?= tr('product_lbl') ?></th><th><?= tr('producer_tag') ?></th><th><?= tr('price_short') ?></th><th><?= tr('actions_lbl') ?></th></tr><?php if(empty($prods)): ?><tr><td colspan="4"><?= tr('no_pending_products') ?></td></tr><?php else: foreach($prods as $p): ?><tr><td><strong><?= htmlspecialchars($p['libelle']) ?></strong></td><td><?= htmlspecialchars($p['producteur']) ?></td><td><?= htmlspecialchars($p['prixu']) ?> DH</td><td><a href="dashboradadmis.php?action=valider_prod&ref=<?= urlencode($p['reference']) ?>" class="btn-approve"><?= tr('publish_lbl') ?></a><a href="dashboradadmis.php?action=rejeter_prod&ref=<?= urlencode($p['reference']) ?>" class="btn-reject" onclick="return confirm('Refuser ce produit ?')"><?= tr('refuse_lbl') ?></a></td></tr><?php endforeach; endif; ?></table></div>
</body>
</html>