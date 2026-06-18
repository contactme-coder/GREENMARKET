<?php
session_start();
if(!isset($_SESSION) || empty($_SESSION) || $_SESSION['roleu'] !== 'admin'){
    header("Location: authentification.php");
    exit;
}
include("prodconnex.php");

// Traitement des actions Admin (Validation/Rejet)
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
            $req = $c->prepare("UPDATE produit SET statut = 'valide' WHERE reference = ?");
            $req->execute([$ref]);
            $msg = "Le produit a été validé et est en ligne.";
        } elseif($action == 'rejeter_prod' && isset($ref)) {
            $req = $c->prepare("UPDATE produit SET statut = 'refuse' WHERE reference = ?");
            $req->execute([$ref]);
            $msg = "Le produit a été refusé.";
        }
        header("Location: dashboradadmis.php?msgs=" . urlencode($msg));
        exit;
    } catch(PDOException $e) { die("Erreur d'action : " . $e->getMessage()); }
}

// Chargement des données en attente
try {
    $req_c = $c->query("SELECT * FROM compte WHERE statut = 'en_attente'");
    $comptes = $req_c->fetchAll(PDO::FETCH_ASSOC);

    $req_p = $c->query("SELECT p.*, c.nom as producteur FROM produit p JOIN compte c ON p.id_producteur = c.id WHERE p.statut = 'en_attente'");
    $prods = $req_p->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) { die("Erreur BD : " . $e->getMessage()); }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Admin - GreenMarket</title>
  <style>
    /* COPIE-COLLE ICI TOUT LE CSS DE TON FICHIER dashboradadmis.html D'ORIGINE */
    body { font-family: sans-serif; background: #f9f5ef; padding:20px; }
    .card { background: white; padding: 20px; border-radius: 12px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
    .btn-approve { background: #2b6e2f; color: white; padding: 6px 12px; text-decoration: none; border-radius: 5px; }
    .btn-reject { background: #c95a5a; color: white; padding: 6px 12px; text-decoration: none; border-radius: 5px; }
  </style>
</head>
<body>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
    <h2>Espace Administration</h2>
    <div>
        <span style="margin-right:20px; font-weight:bold;">Admin: <?= htmlspecialchars($_SESSION['nomu']) ?></span>
        <a href="deconnexion.php" style="color:red; text-decoration:none;">Déconnexion</a>
    </div>
</div>

<?php if(isset($_GET['msgs'])) echo "<div style='color:white; background:#2b6e2f; padding:15px; border-radius:8px; margin-bottom:20px;'>".htmlspecialchars($_GET['msgs'])."</div>"; ?>

<div class="card">
    <h3 style="margin-top:0;">Producteurs en attente de validation</h3>
    <table style="width:100%; text-align:left; border-collapse:collapse;">
        <tr style="border-bottom:2px solid #eee;"><th>Nom</th><th>Email</th><th>Actions</th></tr>
        <?php if(empty($comptes)): ?>
            <tr><td colspan="3" style="padding:15px 0;">Aucun compte en attente.</td></tr>
        <?php else: ?>
            <?php foreach($comptes as $u): ?>
            <tr style="border-bottom:1px solid #eee;">
                <td style="padding:15px 0;"><strong><?= htmlspecialchars($u['nom']) ?></strong></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td>
                    <a href="dashboradadmis.php?action=valider_compte&id=<?= $u['id'] ?>" class="btn-approve">Approuver</a>
                    <a href="dashboradadmis.php?action=rejeter_compte&id=<?= $u['id'] ?>" class="btn-reject" onclick="return confirm('Refuser ce compte ?')">Rejeter</a>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </table>
</div>

<div class="card">
    <h3 style="margin-top:0;">Produits en attente de publication</h3>
    <table style="width:100%; text-align:left; border-collapse:collapse;">
        <tr style="border-bottom:2px solid #eee;"><th>Produit</th><th>Producteur</th><th>Prix</th><th>Actions</th></tr>
        <?php if(empty($prods)): ?>
            <tr><td colspan="4" style="padding:15px 0;">Aucun produit en attente.</td></tr>
        <?php else: ?>
            <?php foreach($prods as $p): ?>
            <tr style="border-bottom:1px solid #eee;">
                <td style="padding:15px 0;"><strong><?= htmlspecialchars($p['libelle']) ?></strong></td>
                <td><?= htmlspecialchars($p['producteur']) ?></td>
                <td><?= htmlspecialchars($p['prixu']) ?> DH</td>
                <td>
                    <a href="dashboradadmis.php?action=valider_prod&ref=<?= urlencode($p['reference']) ?>" class="btn-approve">Publier</a>
                    <a href="dashboradadmis.php?action=rejeter_prod&ref=<?= urlencode($p['reference']) ?>" class="btn-reject" onclick="return confirm('Refuser ce produit ?')">Refuser</a>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </table>
</div>

<script src="script_admin.js"></script>
</body>
</html>