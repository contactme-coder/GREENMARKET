<?php
session_start();
if(!isset($_SESSION['idu']) || $_SESSION['roleu'] !== 'producteur'){
    header("Location: authentification.php");
    exit;
}
include("preferences.php");
include("prodconnex.php");

$err = [];

if($_SERVER['REQUEST_METHOD'] == "POST"){
    extract($_POST);
    // Validation des champs basiques
    if(!isset($ref) || empty(trim($ref))) $err['ref'] = "Référence requise.";
    elseif(strlen(trim($ref)) > 15) $err['ref'] = "Max 15 caractères.";
    if(!isset($lib) || empty(trim($lib))) $err['lib'] = "Libellé requis.";
    if(!isset($prx) || empty($prx)) $err['prx'] = "Prix requis.";
    elseif($prx <= 0) $err['prx'] = "Prix positif.";
    if(!isset($qte) || $qte === "") $err['qte'] = "Quantité requise.";
    elseif($qte < 0) $err['qte'] = "Quantité non négative.";
    if(!isset($cat) || empty($cat)) $err['cat'] = "Catégorie requise.";
    // Vérification unicité référence
    if(empty($err['ref'])){
        try {
            $chk = $c->prepare("SELECT reference FROM produit WHERE reference = ?");
            $chk->execute([trim($ref)]);
            if($chk->rowCount() > 0) $err['ref'] = "Référence déjà utilisée.";
        } catch(PDOException $e){ die("Erreur : ".$e->getMessage()); }
    }
    
    // Image principale (obligatoire)
    $chemin_image = "photos/default.jpg";
    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0){
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $exts_ok = ['jpg','jpeg','png','webp'];
        if(!in_array($ext, $exts_ok)) $err['image'] = "Format d'image invalide.";
        elseif($_FILES['image']['size'] > 2000000) $err['image'] = "Image trop lourde (2Mo max).";
        else {
            $nom_fichier = "prod_".time()."_".rand(100,999).".".$ext;
            $chemin_image = "photos/".$nom_fichier;
        }
    } else {
        $err['image'] = "Veuillez sélectionner une image principale.";
    }

    if(empty($err)){
        try {
            // Début transaction
            $c->beginTransaction();

            // Déplacer l'image principale
            if(!is_dir("photos")) mkdir("photos", 0755, true);
            move_uploaded_file($_FILES['image']['tmp_name'], $chemin_image);

            // Insérer le produit
            $ri = $c->prepare("INSERT INTO produit (reference, libelle, description, prixu, quantite, idcateg, id_producteur, image, statut) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'en_attente')");
            $r = $ri->execute([trim($ref), trim($lib), (isset($desc)?trim($desc):""), $prx, $qte, $cat, $_SESSION['idu'], $chemin_image]);

            if(!$r) throw new Exception("Erreur insertion produit");

            // Gestion des médias supplémentaires (images et vidéos)
            $medias = [];
            // Si des fichiers sont uploadés via un champ 'media_files[]'
            if(isset($_FILES['media_files'])) {
                $files = $_FILES['media_files'];
                $count = count($files['name']);
                for($i=0; $i<$count; $i++) {
                    if($files['error'][$i] == 0) {
                        $ext = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
                        $type = in_array($ext, ['jpg','jpeg','png','webp']) ? 'image' : 'video';
                        if($type == 'image' && $files['size'][$i] > 2000000) continue;
                        if($type == 'video' && $files['size'][$i] > 10000000) continue; // 10Mo pour vidéos
                        $nom = "media_".time()."_".rand(100,999).".".$ext;
                        $destination = "photos/".$nom;
                        move_uploaded_file($files['tmp_name'][$i], $destination);
                        $medias[] = ['type'=>$type, 'url'=>$destination];
                    }
                }
            }

            // Insérer les médias dans produit_media
            $insMedia = $c->prepare("INSERT INTO produit_media (reference_produit, type, url, ordre) VALUES (?, ?, ?, ?)");
            $ordre = 1;
            foreach($medias as $m) {
                $insMedia->execute([$ref, $m['type'], $m['url'], $ordre++]);
            }

            $c->commit();
            header("Location: dashboardpro.php?msgs=".urlencode("Produit ajouté avec succès ! En attente de validation."));
            exit;

        } catch(Exception $e) {
            $c->rollBack();
            $err['global'] = "Erreur : ".$e->getMessage();
        }
    }
}
include("header.php");
?>
<style>
.form-container{max-width:600px; margin:100px auto 40px; padding:20px;}
fieldset{background:var(--white); border:none; border-radius:12px; padding:30px; box-shadow:0 2px 10px rgba(0,0,0,0.05); border:1px solid var(--cream2);}
legend{font-size:1.5rem; font-weight:bold; color:var(--olive);}
label{display:block; margin-top:10px; font-weight:600;}
input, select, textarea{width:100%; padding:8px; border:1px solid var(--cream2); border-radius:6px; margin-bottom:10px;}
.btn-submit{background:var(--olive); color:white; padding:10px 20px; border:none; border-radius:6px; cursor:pointer;}
.err{color:#c95a5a; font-size:13px;}
.hint{font-size:12px; color:#8a8a74;}
</style>
<div class="form-container"><fieldset><legend>Nouveau Produit</legend>
<form method="POST" enctype="multipart/form-data">
    <label>Référence</label>
    <input type="text" name="ref" maxlength="15" value="<?= htmlspecialchars($ref??'') ?>">
    <?php if(isset($err['ref'])) echo "<div class='err'>".$err['ref']."</div>"; ?>
    
    <label>Libellé</label>
    <input type="text" name="lib" value="<?= htmlspecialchars($lib??'') ?>">
    <?php if(isset($err['lib'])) echo "<div class='err'>".$err['lib']."</div>"; ?>
    
    <label>Description</label>
    <textarea name="desc"><?= htmlspecialchars($desc??'') ?></textarea>
    
    <label>Prix (DH)</label>
    <input type="number" step="0.01" name="prx" value="<?= htmlspecialchars($prx??'') ?>">
    <?php if(isset($err['prx'])) echo "<div class='err'>".$err['prx']."</div>"; ?>
    
    <label>Quantité</label>
    <input type="number" name="qte" value="<?= htmlspecialchars($qte??'') ?>">
    <?php if(isset($err['qte'])) echo "<div class='err'>".$err['qte']."</div>"; ?>
    
    <label>Catégorie</label>
    <select name="cat">
        <?php $rs = $c->query("SELECT idcat, libelle FROM categorie ORDER BY libelle"); while($row=$rs->fetch(PDO::FETCH_ASSOC)){ $sel=(isset($cat)&&$cat==$row['idcat'])?"selected":""; echo "<option value='".$row['idcat']."' $sel>".htmlspecialchars($row['libelle'])."</option>"; } ?>
    </select>
    <?php if(isset($err['cat'])) echo "<div class='err'>".$err['cat']."</div>"; ?>
    
    <label>Image principale</label>
    <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp" required>
    <?php if(isset($err['image'])) echo "<div class='err'>".$err['image']."</div>"; ?>
    
    <label>Médias supplémentaires (images ou vidéos)</label>
    <input type="file" name="media_files[]" multiple accept=".jpg,.jpeg,.png,.webp,.mp4,.mov,.avi">
    <p class="hint">Vous pouvez sélectionner plusieurs fichiers (images et vidéos). Taille max : image 2Mo, vidéo 10Mo.</p>
    
    <button type="submit" class="btn-submit">Publier</button>
</form>
</fieldset></div>
<?php include("footer.php"); ?>