<?php
include("preferences.php");
if(!isset($_SESSION['idu']) || $_SESSION['roleu'] !== 'admin'){
    header("Location: authentification.php");
    exit;
}
include("prodconnex.php");

// --- GESTION DES ACTIONS ADMIN ---
if(isset($_GET['action'])) {
    extract($_GET);
    try {
        if($action == 'valider_compte' && isset($id)) {
            $c->query("UPDATE compte SET statut = 'actif' WHERE id = " . intval($id));
            $msg = "Le compte producteur a été validé.";
        } elseif($action == 'rejeter_compte' && isset($id)) {
            $c->query("DELETE FROM compte WHERE id = " . intval($id));
            $msg = "Le compte a été refusé et supprimé.";
        } elseif($action == 'valider_prod' && isset($ref)) {
            $c->prepare("UPDATE produit SET statut = 'valide' WHERE reference = ?")->execute([$ref]);
            $msg = "Le produit a été validé et est en ligne.";
        } elseif($action == 'rejeter_prod' && isset($ref)) {
            $c->prepare("UPDATE produit SET statut = 'refuse' WHERE reference = ?")->execute([$ref]);
            $msg = "Le produit a été refusé.";
        }
        header("Location: dashboradadmis.php?msgs=" . urlencode($msg));
        exit;
    } catch(PDOException $e) { die("Erreur d'action : " . $e->getMessage()); }
}

// --- STATISTIQUES GLOBALES (Total et Jour) ---
try {
    // Totaux globaux
    $stat_total = $c->query("SELECT COUNT(*) as nb_total, COALESCE(SUM(montant_total),0) as ca_total FROM commande")->fetch(PDO::FETCH_ASSOC);
    $nb_commandes = $stat_total['nb_total'] ?? 0;
    $ca_total = $stat_total['ca_total'] ?? 0;

    // Totaux du jour
    $stat_jour = $c->query("SELECT COUNT(*) as nb, COALESCE(SUM(montant_total),0) as ca FROM commande WHERE DATE(date_commande) = CURDATE()")->fetch(PDO::FETCH_ASSOC);
    $nb_commandes_jour = $stat_jour['nb'] ?? 0;
    $ca_jour = $stat_jour['ca'] ?? 0;

    // --- STATISTIQUES POUR LES GRAPHIQUES (État des commandes) ---
    // Récupération des compteurs par statut
    $stat_attente = $c->query("SELECT COUNT(*) as nb FROM commande WHERE statut = 'en_attente'")->fetch(PDO::FETCH_ASSOC);
    $nb_attente = $stat_attente['nb'] ?? 0;

    $stat_livree = $c->query("SELECT COUNT(*) as nb FROM commande WHERE statut = 'livree'")->fetch(PDO::FETCH_ASSOC);
    $nb_livree = $stat_livree['nb'] ?? 0;

    $stat_annulee = $c->query("SELECT COUNT(*) as nb FROM commande WHERE statut = 'annulee'")->fetch(PDO::FETCH_ASSOC);
    $nb_annulee = $stat_annulee['nb'] ?? 0;

    // Récupération du CA par statut (pour le graphique de répartition du CA)
    $ca_stat = $c->query("SELECT statut, SUM(montant_total) as ca FROM commande GROUP BY statut")->fetchAll(PDO::FETCH_ASSOC);
    $ca_attente = 0; $ca_livree = 0; $ca_annulee = 0;
    foreach($ca_stat as $row) {
        if($row['statut'] == 'en_attente') $ca_attente = $row['ca'];
        elseif($row['statut'] == 'livree') $ca_livree = $row['ca'];
        elseif($row['statut'] == 'annulee') $ca_annulee = $row['ca'];
    }

    // --- LISTES EN ATTENTE ---
    $req_c = $c->query("SELECT * FROM compte WHERE statut = 'en_attente'");
    $comptes = $req_c->fetchAll(PDO::FETCH_ASSOC);

    $req_p = $c->query("SELECT p.*, c.nom as producteur FROM produit p JOIN compte c ON p.id_producteur = c.id WHERE p.statut = 'en_attente'");
    $prods = $req_p->fetchAll(PDO::FETCH_ASSOC);

    // --- RECHERCHE DE COMMANDES ---
    $recherche = isset($_GET['recherche']) ? trim($_GET['recherche']) : "";
    if($recherche !== ""){
        $req_cmd = $c->prepare("SELECT cmd.*, cpt.nom as client_nom, cpt.email as client_email FROM commande cmd JOIN compte cpt ON cmd.id_client = cpt.id WHERE cpt.nom LIKE ? OR cpt.email LIKE ? OR cmd.idcom = ? ORDER BY cmd.date_commande DESC");
        $motcle = "%" . $recherche . "%";
        $req_cmd->execute([$motcle, $motcle, is_numeric($recherche) ? $recherche : 0]);
    } else {
        $req_cmd = $c->query("SELECT cmd.*, cpt.nom as client_nom, cpt.email as client_email FROM commande cmd JOIN compte cpt ON cmd.id_client = cpt.id ORDER BY cmd.date_commande DESC LIMIT 30");
    }
    $commandes_admin = $req_cmd->fetchAll(PDO::FETCH_ASSOC);

    // --- AVIS EN ATTENTE ---
    $req_avis = $c->query("SELECT a.*, c.nom as client_nom, p.libelle as prod_libelle FROM avis a JOIN compte c ON a.id_client = c.id JOIN produit p ON a.reference_produit = p.reference WHERE a.statut = 'en_attente'");
    $avis_attente = $req_avis->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) { die("Erreur BD : " . $e->getMessage()); }

// --- TRAITEMENT MODÉRATION AVIS ---
if(isset($_GET['action_avis']) && isset($_GET['id_avis'])) {
    $id = intval($_GET['id_avis']);
    $action = $_GET['action_avis'];
    $new_statut = ($action == 'valider') ? 'valide' : 'refuse';
    $up = $c->prepare("UPDATE avis SET statut = ?, id_moderateur = ? WHERE id = ?");
    $up->execute([$new_statut, $_SESSION['idu'], $id]);
    header("Location: dashboradadmis.php?msgs=" . urlencode("Avis " . ($action == 'valider' ? 'validé' : 'refusé')));
    exit;
}

include("header.php");
?>
<style>
.card { background: var(--white); padding: 20px; border-radius: 12px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); border:1px solid var(--cream2); }
.top-bar { padding: 100px 40px 40px; max-width: 1200px; margin:0 auto; }
.btn-approve { background: #2b6e2f; color: white; padding: 6px 12px; text-decoration: none; border-radius: 5px; }
.btn-reject { background: #c95a5a; color: white; padding: 6px 12px; text-decoration: none; border-radius: 5px; }
.stat-grid { display: flex; gap: 20px; flex-wrap: wrap; margin-bottom: 20px; }
.stat-box { flex: 1; min-width: 150px; background: var(--white); border-radius: 12px; padding: 20px; text-align: center; border:1px solid var(--cream2); }
.stat-value { font-size: 1.8rem; font-weight: bold; color: var(--olive); }
.search-box { display: flex; gap: 10px; margin-bottom: 15px; flex-wrap: wrap; }
.search-box input { flex: 1; padding: 10px; border: 1px solid var(--cream2); border-radius: 6px; }
table { width: 100%; text-align: left; border-collapse: collapse; }
th, td { padding: 10px 0; border-bottom: 1px solid var(--cream2); }
.chart-admin-row { display:grid; grid-template-columns: 1fr 1fr; gap:20px; margin-bottom:20px; }
@media(max-width:768px){ .chart-admin-row { grid-template-columns:1fr; } }
</style>
<div class="top-bar">
  <h1 style="font-family:'Cormorant Garamond', serif; color:var(--olive);"><i class="fa-solid fa-gauge-high"></i> <?= tr('admin_space') ?></h1>
  <?php if(isset($_GET['msgs'])) echo "<div style='color:white; background:#2b6e2f; padding:15px; border-radius:8px; margin-bottom:20px;'>".htmlspecialchars($_GET['msgs'])."</div>"; ?>
  
  <div class="stat-grid">
    <div class="stat-box"><div class="stat-value"><?= $nb_commandes ?></div><div class="stat-label"><?= tr('total_orders_lbl') ?></div></div>
    <div class="stat-box"><div class="stat-value"><?= number_format($ca_total, 2) ?> DH</div><div class="stat-label"><?= tr('revenue_lbl') ?></div></div>
    <div class="stat-box"><div class="stat-value"><?= $nb_commandes_jour ?></div><div class="stat-label"><?= tr('orders_today_lbl') ?></div></div>
    <div class="stat-box"><div class="stat-value"><?= number_format($ca_jour, 2) ?> DH</div><div class="stat-label"><?= tr('revenue_today_lbl') ?></div></div>
  </div>

  <div class="chart-admin-row">
    <div class="card">
        <h3 style="color:var(--olive);">État des commandes</h3>
        <canvas id="adminChart" width="300" height="200"></canvas>
    </div>
    <div class="card">
        <h3 style="color:var(--olive);">Répartition du CA (DH)</h3>
        <canvas id="adminRevenueChart" width="300" height="200"></canvas>
    </div>
  </div>

  <script>
    document.addEventListener("DOMContentLoaded", function() {
        const ctx1 = document.getElementById('adminChart').getContext('2d');
        new Chart(ctx1, {
            type: 'pie',
            data: {
                labels: ['En attente', 'Livrée', 'Annulée'],
                datasets: [{
                    data: [<?= $nb_attente ?>, <?= $nb_livree ?>, <?= $nb_annulee ?>],
                    backgroundColor: ['#f39c12', '#27ae60', '#c95a5a']
                }]
            },
            options: { plugins: { legend: { position: 'bottom' } } }
        });

        const ctx2 = document.getElementById('adminRevenueChart').getContext('2d');
        new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: ['CA En attente', 'CA Livrée', 'CA Annulée'],
                datasets: [{
                    data: [<?= $ca_attente ?>, <?= $ca_livree ?>, <?= $ca_annulee ?>],
                    backgroundColor: ['#f39c12', '#27ae60', '#c95a5a']
                }]
            },
            options: { plugins: { legend: { position: 'bottom' } } }
        });
    });
  </script>

  <!-- Surveillance des commandes -->
  <div class="card"><h3><?= tr('order_surveillance') ?></h3>
    <form method="GET" class="search-box"><input type="text" name="recherche" placeholder="<?= htmlspecialchars(tr('search_ph')) ?>" value="<?= htmlspecialchars($recherche) ?>"><button type="submit" style="background:var(--olive); color:white; padding:10px 20px; border:none; border-radius:6px; cursor:pointer;"><?= tr('search_btn') ?></button><?php if($recherche !== ""): ?><a href="dashboradadmis.php" style="color:#c95a5a; text-decoration:none; align-self:center;">✕ <?= tr('reset_btn') ?></a><?php endif; ?></form>
    <table><tr><th><?= tr('num_cmd') ?></th><th><?= tr('client_lbl') ?></th><th><?= tr('montant') ?></th><th><?= tr('method_lbl') ?></th><th><?= tr('statut') ?></th><th><?= tr('date') ?></th></tr>
    <?php if(empty($commandes_admin)): ?><tr><td colspan="6" style="padding:15px;"><?= tr('no_order_found') ?></td></tr><?php else: foreach($commandes_admin as $cmd): ?><tr><td>#<?= htmlspecialchars($cmd['idcom']) ?></td><td><strong><?= htmlspecialchars($cmd['client_nom']) ?></strong><br><span style="font-size:12px; color:#888;"><?= htmlspecialchars($cmd['client_email']) ?></span></td><td style="font-weight:bold; color:var(--olive);"><?= number_format($cmd['montant_total'], 2) ?> DH</td><td><?= htmlspecialchars($cmd['methode_paiement']) ?></td><td><?= htmlspecialchars($cmd['statut']) ?></td><td><?= date('d/m/Y H:i', strtotime($cmd['date_commande'])) ?></td></tr><?php endforeach; endif; ?></table>
  </div>

  <!-- Modération des avis -->
  <div class="card"><h3><?= tr('moderate_reviews') ?></h3>
    <table><tr><th>Client</th><th>Produit</th><th>Note</th><th>Commentaire</th><th>Actions</th></tr>
    <?php if(empty($avis_attente)): ?><tr><td colspan="5"><?= tr('no_pending_reviews') ?></td></tr><?php else: foreach($avis_attente as $av): ?><tr><td><?= htmlspecialchars($av['client_nom']) ?></td><td><?= htmlspecialchars($av['prod_libelle']) ?></td><td><?= $av['note'] ?> ★</td><td><?= htmlspecialchars(substr($av['commentaire'],0,50)) ?>...</td><td><a href="dashboradadmis.php?action_avis=valider&id_avis=<?= $av['id'] ?>" class="btn-approve">✅ Valider</a><a href="dashboradadmis.php?action_avis=refuser&id_avis=<?= $av['id'] ?>" class="btn-reject">❌ Refuser</a></td></tr><?php endforeach; endif; ?></table>
  </div>

  <!-- Comptes en attente -->
  <div class="card"><h3><?= tr('pending_producers') ?></h3>
    <table><tr><th><?= tr('name_lbl') ?></th><th><?= tr('email_lbl') ?></th><th><?= tr('actions_lbl') ?></th></tr>
    <?php if(empty($comptes)): ?><tr><td colspan="3"><?= tr('no_pending_accounts') ?></td></tr><?php else: foreach($comptes as $u): ?><tr><td><strong><?= htmlspecialchars($u['nom']) ?></strong></td><td><?= htmlspecialchars($u['email']) ?></td><td><a href="dashboradadmis.php?action=valider_compte&id=<?= $u['id'] ?>" class="btn-approve"><?= tr('approve_lbl') ?></a><a href="dashboradadmis.php?action=rejeter_compte&id=<?= $u['id'] ?>" class="btn-reject" onclick="return confirm('Refuser ce compte ?')"><?= tr('reject_lbl') ?></a></td></tr><?php endforeach; endif; ?></table>
  </div>

  <!-- Produits en attente -->
  <div class="card"><h3><?= tr('pending_products') ?></h3>
    <table><tr><th><?= tr('product_lbl') ?></th><th><?= tr('producer_tag') ?></th><th><?= tr('price_short') ?></th><th><?= tr('actions_lbl') ?></th></tr>
    <?php if(empty($prods)): ?><tr><td colspan="4"><?= tr('no_pending_products') ?></td></tr><?php else: foreach($prods as $p): ?><tr><td><strong><?= htmlspecialchars($p['libelle']) ?></strong></td><td><?= htmlspecialchars($p['producteur']) ?></td><td><?= htmlspecialchars($p['prixu']) ?> DH</td><td><a href="dashboradadmis.php?action=valider_prod&ref=<?= urlencode($p['reference']) ?>" class="btn-approve"><?= tr('publish_lbl') ?></a><a href="dashboradadmis.php?action=rejeter_prod&ref=<?= urlencode($p['reference']) ?>" class="btn-reject" onclick="return confirm('Refuser ce produit ?')"><?= tr('refuse_lbl') ?></a></td></tr><?php endforeach; endif; ?></table>
  </div>
</div>
<?php include("footer.php"); ?>