<?php
include("preferences.php");
if(!isset($_SESSION['idu']) || $_SESSION['roleu'] !== 'client') {
    header("Location: authentification.php");
    exit;
}
include("prodconnex.php");

// Récupération des points
$pts_req = $c->prepare("SELECT SUM(points) as total_points FROM point_fidelite WHERE id_client = ?");
$pts_req->execute([$_SESSION['idu']]);
$points = $pts_req->fetchColumn();
if($points === null) $points = 0;

// Récupération des commandes
$req = $c->prepare("SELECT cmd.*, (SELECT COUNT(*) FROM commande_produit cp WHERE cp.idcom = cmd.idcom) as nb_articles FROM commande cmd WHERE cmd.id_client = ? ORDER BY cmd.date_commande DESC");
$req->execute([$_SESSION['idu']]);
$commandes = $req->fetchAll(PDO::FETCH_ASSOC);

include("header.php");
?>
<style>
.container { padding: 100px 40px 40px; max-width: 1100px; margin:0 auto; }
.points-badge { background: #d4af37; color: #1e1e18; padding: 10px 20px; border-radius: 30px; font-weight: bold; display: inline-block; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(212,175,55,0.3); }
.stat-card { background: var(--white); border-radius: 12px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); border:1px solid var(--cream2); }
table { width:100%; border-collapse:collapse; margin-top:10px; background:var(--white); border-radius:12px; overflow:hidden; }
th { background:var(--olive); color:white; padding:15px; }
td { padding:15px; border-bottom:1px solid var(--cream2); }
.badge-statut { padding:4px 10px; border-radius:12px; font-size:12px; font-weight:bold; text-transform:uppercase; }
.badge-en_attente { background:#fff3cd; color:#856404; } .badge-livree { background:#d1e7dd; color:#0f5132; } .badge-annulee { background:#f8d7da; color:#842029; }
.btn-facture { background:var(--gold); color:white; padding:4px 10px; border-radius:4px; text-decoration:none; font-size:12px; font-weight:bold; }
</style>
<div class="container">
  <h1 style="font-family:'Cormorant Garamond', serif; font-size:2.5rem; color:var(--olive);">👋 <?= htmlspecialchars($_SESSION['nomu']) ?></h1>
  <div class="points-badge"><i class="fa-solid fa-star"></i> <?= $points ?> <?= tr('points_label') ?></div>
  <?php if(isset($_GET['cmd_ok'])): ?>
      <div style="background:#edf0e4; color:#5c6b3a; padding:15px; border-radius:10px; margin-bottom:20px; border-left:5px solid #5c6b3a;">
          ✅ Commande #<?= intval($_GET['cmd_ok']) ?> confirmée ! <br>
          <a href="facture.php?id=<?= intval($_GET['cmd_ok']) ?>" style="color:var(--olive); font-weight:bold; text-decoration:underline;">Télécharger la facture (PDF)</a>
      </div>
  <?php endif; ?>
  <div class="stat-card">
    <h3><?= tr('cmd_hist') ?></h3>
    <?php if(empty($commandes)): ?>
        <p style="padding:30px; text-align:center; color:var(--text-lt);"><?= tr('no_cmd') ?></p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th><?= tr('num_cmd') ?></th>
                    <th><?= tr('date') ?></th>
                    <th><?= tr('articles') ?></th>
                    <th><?= tr('montant') ?></th>
                    <th><?= tr('pay') ?></th>
                    <th><?= tr('statut') ?></th>
                    <th>Facture</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($commandes as $cmd): ?>
                <tr>
                    <td>#<?= $cmd['idcom'] ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($cmd['date_commande'])) ?></td>
                    <td><?= $cmd['nb_articles'] ?></td>
                    <td style="font-weight:bold; color:var(--olive);"><?= number_format($cmd['montant_total'], 2) ?> DH</td>
                    <td style="text-transform:uppercase; font-size:12px;"><?= htmlspecialchars($cmd['methode_paiement']) ?></td>
                    <td><span class="badge-statut badge-<?= $cmd['statut'] ?>"><?= htmlspecialchars($cmd['statut']) ?></span></td>
                    <td><a href="facture.php?id=<?= $cmd['idcom'] ?>" class="btn-facture"><i class="fa-solid fa-file-pdf"></i> PDF</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
  </div>
</div>
<?php include("footer.php"); ?>