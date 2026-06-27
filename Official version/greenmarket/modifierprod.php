<?php
session_start();
if(!isset($_SESSION) || empty($_SESSION) || $_SESSION['roleu'] !== 'producteur'){ header("Location: authentification.php"); exit; }
$err = []; include("prodconnex.php");
if(isset($_GET['refp'])){
    try{ $rs= $c->prepare("SELECT * FROM produit WHERE reference = ? AND id_producteur = ?"); $rs->execute([$_GET['refp'], $_SESSION['idu']]); $tprod = $rs->fetch(PDO::FETCH_ASSOC); if(!$tprod) { header("Location: dashboardpro.php"); exit; } } catch(PDOException $e) {die("Erreur de selection prod:".$e->getMessage());}
}
if($_SERVER['REQUEST_METHOD']=="POST"){
    extract($_POST);
    if(!isset($lib) || empty(trim($lib))) $err['lib'] = 'Veuillez entrer le libellé';
    if(!isset($prx) || empty($prx)) $err['prx'] = 'Veuillez entrer un prix';
    elseif($prx <= 0) $err['prx'] = "Le prix ne doit pas être négatif ou nul";
    if(!isset($qte) || empty($qte)) $err['qte'] = 'Veuillez entrer une quantité';
    elseif($qte < 0) $err['qte'] = "La quantité ne doit pas être négative";
    if(empty($err)){
        $lib = trim($lib);
        try {
            $ri = $c->prepare("UPDATE produit SET libelle = ?, description = ?, prixu = ?, quantite = ?, idcateg = ?, statut = 'en_attente' WHERE reference = ? AND id_producteur = ?");
            $r = $ri->execute([$lib, $desc, $prx, $qte, $cat, $ref_hide, $_SESSION['idu']]);
            if($r == False ) { header("Location: dashboardpro.php?msgerr=Echec de la modification"); exit; } else { header("Location: dashboardpro.php?msgs=Produit modifié avec succès, en attente de re-validation"); exit; }
        } catch(PDOException $e) {die ("Erreur modification prod : ".$e->getMessage());}
    }
}
?>
<html><head><title>Modifier produit</title><link rel="icon" type="image/svg+xml" href="favicon.svg"></head>
<body style="font-family: sans-serif; background: #f9f5ef; padding: 40px;">
<form method="POST"><fieldset style="max-width:500px; padding:20px; background:white; border-radius:10px; border:none; box-shadow:0 4px 15px rgba(0,0,0,0.05);"><legend style="font-size:20px; color:#5c6b3a; font-weight:bold;">Modifier le produit</legend>
<input type="hidden" name="ref_hide" value="<?= htmlspecialchars($tprod['reference']) ?>"><p>Reference : <strong><?= htmlspecialchars($tprod['reference']) ?></strong> (Non modifiable)</p>
<?php if(isset($err['lib'])) echo"<div style='color:red'>".$err['lib']."</div>";?>Libelle : <input type="text" name="lib" value="<?= htmlspecialchars($tprod['libelle']) ?>" style="width:100%; padding:8px; margin-bottom:15px;"><br>
Description : <textarea name="desc" style="width:100%; padding:8px; margin-bottom:15px;"><?= htmlspecialchars($tprod['description']) ?></textarea><br>
<?php if(isset($err['prx'])) echo"<div style='color:red'>".$err['prx']."</div>";?>Prix unitaire :<input type="number" step="0.01" name="prx" value="<?= htmlspecialchars($tprod['prixu']) ?>" style="width:100%; padding:8px; margin-bottom:15px;"><br>
<?php if(isset($err['qte'])) echo"<div style='color:red'>".$err['qte']."</div>";?>Quantite : <input type="number" name="qte" value="<?= htmlspecialchars($tprod['quantite']) ?>" style="width:100%; padding:8px; margin-bottom:15px;"><br>
Categorie : <select name="cat" style="width:100%; padding:8px; margin-bottom:20px;"><?php $rs = $c->query("SELECT idcat, libelle FROM categorie"); while($cat = $rs->fetch(PDO::FETCH_ASSOC)){ $s = ($cat['idcat'] == $tprod['idcateg']) ? "selected" : ""; echo "<option value='".$cat['idcat']."' $s>".$cat['libelle']."</option>"; } ?></select><br>
<img src="<?= htmlspecialchars($tprod['image']) ?>" width="80" style="border-radius:5px; margin-bottom:15px;"><br>
<input type="submit" value="Enregistrer les modifications" style="padding:10px 20px; background:#5e7340; color:white; border:none; border-radius:5px; cursor:pointer;"><a href="dashboardpro.php" style="margin-left:15px; color:#c95a5a; text-decoration:none;">Annuler</a></fieldset></form>
</body></html>