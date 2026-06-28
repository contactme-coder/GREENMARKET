<?php
include("preferences.php");
if(!isset($_SESSION['idu']) || $_SESSION['roleu'] !== 'producteur'){ header("Location: authentification.php"); exit; }
include("prodconnex.php");

try {
    // --- MES PRODUITS ---
    $req = $c->prepare("SELECT p.*, c.libelle as nomcat FROM produit p JOIN categorie c ON p.idcateg = c.idcat WHERE p.id_producteur = ? ORDER BY p.dateachat DESC");
    $req->execute([$_SESSION['idu']]); $tab_prod = $req->fetchAll(PDO::FETCH_ASSOC);
    $total_stock = 0;
    foreach($tab_prod as $p) { $total_stock += $p['quantite']; }

    // --- MES VENTES ---
    $reqv = $c->prepare("SELECT cp.*, cm.date_commande, cm.methode_paiement, cm.statut as statut_cmd, p.libelle, p.quantite as stock_actuel 
                         FROM commande_produit cp 
                         JOIN commande cm ON cp.idcom = cm.idcom 
                         JOIN produit p ON cp.reference_produit = p.reference 
                         WHERE p.id_producteur = ? 
                         ORDER BY cm.date_commande DESC");
    $reqv->execute([$_SESSION['idu']]); $ventes = $reqv->fetchAll(PDO::FETCH_ASSOC);
    
    $nb_ventes = count($ventes); 
    $ca_producteur = 0; $qte_vendue = 0;
    $ca_livree_prod = 0; $ca_attente_prod = 0;
    
    foreach($ventes as $v) {
        $subtotal = $v['quantite'] * $v['prix_unitaire'];
        $ca_producteur += $subtotal;
        $qte_vendue += $v['quantite'];
        if($v['statut_cmd'] == 'livree') $ca_livree_prod += $subtotal;
        elseif($v['statut_cmd'] == 'en_attente') $ca_attente_prod += $subtotal;
    }

} catch(PDOException $e) { die("Erreur : ".$e->getMessage()); }

// --- GAMIFICATION (BADGES) ---
$badge_req = $c->prepare("SELECT badge_nom FROM badge_producteur WHERE id_producteur = ?");
$badge_req->execute([$_SESSION['idu']]); $badges = $badge_req->fetchAll(PDO::FETCH_COLUMN);
if($nb_ventes >= 10 && !in_array('Bronze', $badges)) { $c->prepare("INSERT INTO badge_producteur (id_producteur, badge_nom) VALUES (?, 'Bronze')")->execute([$_SESSION['idu']]); $badges[] = 'Bronze'; }
if($nb_ventes >= 50 && !in_array('Argent', $badges)) { $c->prepare("INSERT INTO badge_producteur (id_producteur, badge_nom) VALUES (?, 'Argent')")->execute([$_SESSION['idu']]); $badges[] = 'Argent'; }
if($nb_ventes >= 100 && !in_array('Or', $badges)) { $c->prepare("INSERT INTO badge_producteur (id_producteur, badge_nom) VALUES (?, 'Or')")->execute([$_SESSION['idu']]); $badges[] = 'Or'; }

include("header.php");
?>
<style>
.dashboard-container { max-width: 1200px; margin: 100px auto 40px; background: var(--white); padding: 30px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); border:1px solid var(--cream2); }
.stat-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px; margin: 25px 0; }
.stat-box { background: var(--ivory); border-radius: 12px; padding: 20px; text-align: center; border:1px solid var(--cream2); }
.stat-value { font-size: 2rem; font-weight: bold; color: var(--olive); }
.stat-label { font-size: 0.9rem; color: var(--text-mid); }
.chart-container { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin: 30px 0; }
.chart-box { background: var(--ivory); border-radius: 12px; padding: 20px; border:1px solid var(--cream2); text-align:center; }
table { width: 100%; border-collapse: collapse; margin-top: 15px; background: var(--white); border-radius: 12px; overflow: hidden; }
th { background: var(--olive); color: white; padding: 12px; text-align: left; }
td { padding: 12px; border-bottom: 1px solid var(--cream2); }
.btn-add { padding: 10px 20px; background: var(--olive); color: white; text-decoration: none; border-radius: 6px; font-weight: bold; transition:0.3s; }
.btn-add:hover { background: var(--terracotta); }
@media(max-width:768px){ .chart-container { grid-template-columns:1fr; } }
</style>
<div class="dashboard-container">
  <h1 style="font-family:'Cormorant Garamond', serif; font-size:2rem; color:var(--olive);"><i class="fa-solid fa-store"></i> <?= tr('producer_welcome') ?></h1>
  
  <div class="stat-grid">
    <div class="stat-box"><div class="stat-value"><?= $nb_ventes ?></div><div class="stat-label"><?= tr('sales_lines') ?></div></div>
    <div class="stat-box"><div class="stat-value"><?= number_format($ca_producteur, 2) ?> DH</div><div class="stat-label"><?= tr('revenue_lbl') ?></div></div>
    <div class="stat-box"><div class="stat-value"><?= count($tab_prod) ?></div><div class="stat-label"><?= tr('online_products_lbl') ?></div></div>
  </div>

  <div class="chart-container">
    <div class="chart-box">
        <h3 style="color:var(--olive); margin-bottom:15px;">Répartition Stock vs Ventes</h3>
        <canvas id="prodStockChart" width="300" height="300"></canvas>
    </div>
    <div class="chart-box">
        <h3 style="color:var(--olive); margin-bottom:15px;">Performance des Ventes (CA)</h3>
        <canvas id="prodSalesChart" width="300" height="300"></canvas>
    </div>
  </div>

  <script>
    document.addEventListener("DOMContentLoaded", function() {
        // Graphique 1 : Stock total vs Quantité vendue
        const ctx1 = document.getElementById('prodStockChart').getContext('2d');
        new Chart(ctx1, {
            type: 'doughnut',
            data: {
                labels: ['Quantité vendue', 'Stock restant'],
                datasets: [{
                    data: [<?= $qte_vendue ?>, <?= max(0, $total_stock - $qte_vendue) ?>],
                    backgroundColor: ['#c95a5a', '#5c6b3a'],
                    hoverBackgroundColor: ['#a14444', '#4a5d2e'],
                    borderWidth: 2
                }]
            },
            options: { plugins: { legend: { position: 'bottom' } }, responsive: true }
        });

        // Graphique 2 : CA des commandes livrées vs en attente
        const ctx2 = document.getElementById('prodSalesChart').getContext('2d');
        new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: ['CA Livrée (encaissée)', 'CA en attente'],
                datasets: [{
                    data: [<?= $ca_livree_prod ?>, <?= $ca_attente_prod ?>],
                    backgroundColor: ['#27ae60', '#f39c12'],
                    hoverBackgroundColor: ['#1e8449', '#d68910']
                }]
            },
            options: { plugins: { legend: { position: 'bottom' } }, responsive: true }
        });
    });
  </script>

  <div style="text-align:right; margin:20px 0;"><a href="ajouterprod.php" class="btn-add">+ <?= tr('add_product') ?></a></div>
  <h3 style="color:var(--olive); border-top:2px solid var(--cream2); padding-top:20px;">📦 <?= tr('my_products') ?></h3>
  <table><thead><tr><th><?= tr('ref_lbl') ?></th><th>Image</th><th><?= tr('product_lbl') ?></th><th><?= tr('category_lbl') ?></th><th><?= tr('stock_lbl') ?></th><th><?= tr('price_lbl') ?></th><th><?= tr('statut') ?></th><th><?= tr('actions_lbl') ?></th></tr></thead>
  <tbody><?php if(empty($tab_prod)): ?><tr><td colspan="8" style="text-align:center; color:gray;"><?= tr('no_products_yet') ?></td></tr><?php else: foreach($tab_prod as $p): ?><tr><td><strong><?= htmlspecialchars($p['reference']) ?></strong></td><td><img src="<?= htmlspecialchars($p['image']) ?>" style="width:50px; height:50px; object-fit:cover; border-radius:6px;"></td><td><?= htmlspecialchars($p['libelle']) ?></td><td><?= htmlspecialchars($p['nomcat']) ?></td><td><?= htmlspecialchars($p['quantite']) ?></td><td><?= htmlspecialchars($p['prixu']) ?> DH</td><td><span style="color:<?= $p['statut']=='valide'?'green':'orange' ?>; font-weight:bold;"><?= htmlspecialchars($p['statut']) ?></span></td><td><a href="modifierprod.php?refp=<?= urlencode($p['reference']) ?>" style="color:#748249;">Modifier</a><a href="supprimerprod.php?refp=<?= urlencode($p['reference']) ?>" style="color:#c95a5a; font-weight:bold; margin-left:10px;">Supprimer</a></td></tr><?php endforeach; endif; ?></tbody></table>
</div>
<?php include("footer.php"); ?>