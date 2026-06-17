<?php
session_start();
if(!isset($_SESSION['idu']) || $_SESSION['roleu'] !== 'producteur'){
    header("Location: authentification.php");
    exit;
}
$err = [];
include("prodconnex.php");

if($_SERVER['REQUEST_METHOD'] == "POST"){
    extract($_POST);

    // ===== VALIDATIONS =====
    if(!isset($ref) || empty(trim($ref)))          $err['ref'] = "Veuillez entrer une référence.";
    elseif(strlen(trim($ref)) > 15)                $err['ref'] = "La référence ne doit pas dépasser 15 caractères.";

    if(!isset($lib) || empty(trim($lib)))          $err['lib'] = "Veuillez entrer un libellé.";

    if(!isset($prx) || empty($prx))               $err['prx'] = "Veuillez entrer un prix.";
    elseif($prx <= 0)                              $err['prx'] = "Le prix ne doit pas être négatif ou nul.";

    if(!isset($qte) || $qte === "")               $err['qte'] = "Veuillez entrer une quantité.";
    elseif($qte < 0)                              $err['qte'] = "La quantité ne doit pas être négative.";

    if(!isset($cat) || empty($cat))               $err['cat'] = "Veuillez choisir une catégorie.";

    // Vérification unicité de la référence
    if(empty($err['ref'])){
        try {
            $chk = $c->prepare("SELECT reference FROM produit WHERE reference = ?");
            $chk->execute([trim($ref)]);
            if($chk->rowCount() > 0) $err['ref'] = "Cette référence est déjà utilisée par un autre produit.";
        } catch(PDOException $e) { die("Erreur vérification référence : ".$e->getMessage()); }
    }

    // ===== GESTION DE L'IMAGE =====
    $chemin_image = "photos/default.jpg";
    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0){
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $exts_ok = ['jpg', 'jpeg', 'png', 'webp'];
        if(!in_array($ext, $exts_ok)){
            $err['image'] = "Format non autorisé. Utilisez : jpg, jpeg, png ou webp.";
        } elseif($_FILES['image']['size'] > 2000000){
            $err['image'] = "L'image est trop lourde (maximum 2 Mo).";
        } else {
            $nom_fichier = "prod_" . time() . "_" . rand(100, 999) . "." . $ext;
            $chemin_image = "photos/" . $nom_fichier;
        }
    }

    // ===== INSERTION EN BASE =====
    if(empty($err)){
        $ref = trim($ref);
        $lib = trim($lib);
        $desc = isset($desc) ? trim($desc) : "";
        try {
            // Déplacer l'image vers le dossier photos/
            if(isset($_FILES['image']) && $_FILES['image']['error'] == 0){
                if(!is_dir("photos")) mkdir("photos", 0755, true);
                move_uploaded_file($_FILES['image']['tmp_name'], $chemin_image);
            }

            $ri = $c->prepare("INSERT INTO produit (reference, libelle, description, prixu, quantite, idcateg, id_producteur, image, statut) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'en_attente')");
            $r = $ri->execute([$ref, $lib, $desc, $prx, $qte, $cat, $_SESSION['idu'], $chemin_image]);

            if($r == false){
                header("Location: dashboardpro.php?msgerr=" . urlencode("Échec de l'ajout du produit."));
                exit;
            } else {
                header("Location: dashboardpro.php?msgs=" . urlencode("Produit ajouté avec succès ! Il est en attente de validation par l'administrateur."));
                exit;
            }
        } catch(PDOException $e) { die("Erreur ajout produit : ".$e->getMessage()); }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>GreenMarket – Ajouter un Produit</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,400&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
    :root {
      --ivory:    #f9f5ef;
      --cream:    #f2ebe0;
      --sand:     #d4c5ad;
      --olive:    #5c6b3a;
      --brown:    #6b4c2a;
      --text:     #1e1e18;
      --text-mid: #4a4a3a;
      --text-lt:  #8a8a74;
      --white:    #ffffff;
      --red:      #c95a5a;
      --serif: 'Cormorant Garamond', Georgia, serif;
      --sans:  'Jost', sans-serif;
    }
    body { font-family: var(--sans); background: var(--ivory); color: var(--text); padding: 40px 20px; -webkit-font-smoothing: antialiased; }

    .navbar { display: flex; justify-content: space-between; align-items: center; background: #5c6b3a; padding: 15px 30px; margin: -40px -20px 40px -20px; }
    .navbar .logo { font-family: var(--serif); font-size: 1.4rem; font-weight: 600; color: white; }
    .navbar a { color: #ccc; text-decoration: none; font-size: 0.9rem; }
    .navbar a:hover { color: white; }

    form { max-width: 560px; margin: 0 auto; }

    fieldset {
        padding: 35px 30px;
        background: var(--white);
        border-radius: 14px;
        border: none;
        box-shadow: 0 4px 20px rgba(60,50,20,0.07);
    }
    legend {
        font-family: var(--serif);
        font-size: 1.8rem;
        font-weight: 600;
        color: var(--olive);
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 1px solid var(--sand);
        width: 100%;
    }
    label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: var(--text-mid);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 6px;
    }
    input[type="text"],
    input[type="number"],
    textarea,
    select {
        width: 100%;
        padding: 11px 14px;
        border: 1px solid var(--sand);
        border-radius: 8px;
        background: var(--cream);
        font-family: var(--sans);
        font-size: 14px;
        color: var(--text);
        margin-bottom: 18px;
        transition: border-color 0.2s;
    }
    input[type="text"]:focus,
    input[type="number"]:focus,
    textarea:focus,
    select:focus { outline: none; border-color: var(--olive); background: var(--white); }
    textarea { min-height: 90px; resize: vertical; }

    input[type="file"] { width: 100%; margin-bottom: 18px; font-size: 13px; color: var(--text-mid); }

    .row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }

    .err { color: var(--red); font-size: 12px; margin-top: -14px; margin-bottom: 12px; font-weight: 500; }

    .form-actions { display: flex; align-items: center; gap: 20px; margin-top: 10px; }
    .btn-submit {
        padding: 12px 30px;
        background: var(--olive);
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-family: var(--sans);
        font-weight: 600;
        font-size: 14px;
        transition: background 0.2s;
    }
    .btn-submit:hover { background: #4a5d2e; }
    .btn-cancel { color: var(--red); text-decoration: none; font-size: 14px; font-weight: 500; }
    .hint { font-size: 11px; color: var(--text-lt); margin-top: -14px; margin-bottom: 14px; }
  </style>
</head>
<body>

<nav class="navbar">
  <div class="logo">Green<span style="color:#a3c07a;">Market</span></div>
  <div>
    <span style="color:white; margin-right:20px;">👤 <?= htmlspecialchars($_SESSION['nomu']) ?> (Producteur)</span>
    <a href="dashboardpro.php">← Retour au Dashboard</a>
  </div>
</nav>

<form method="POST" action="ajouterprod.php" enctype="multipart/form-data">
  <fieldset>
    <legend>Nouveau Produit</legend>

    <label>Référence produit *</label>
    <?php if(isset($err['ref'])) echo "<div class='err'>".$err['ref']."</div>"; ?>
    <input type="text" name="ref" maxlength="15" placeholder="Ex : BIO-MEL-001" value="<?php if(isset($ref)) echo htmlspecialchars($ref); ?>">
    <p class="hint">Code unique de 15 caractères max. Servira d'identifiant permanent (non modifiable après création).</p>

    <label>Libellé du produit *</label>
    <?php if(isset($err['lib'])) echo "<div class='err'>".$err['lib']."</div>"; ?>
    <input type="text" name="lib" placeholder="Ex : Miel de thym artisanal" value="<?php if(isset($lib)) echo htmlspecialchars($lib); ?>">

    <label>Description</label>
    <textarea name="desc" placeholder="Décrivez votre produit : origine, méthode de fabrication, certifications bio..."><?php if(isset($desc)) echo htmlspecialchars($desc); ?></textarea>

    <div class="row-2">
      <div>
        <label>Prix unitaire (DH) *</label>
        <?php if(isset($err['prx'])) echo "<div class='err'>".$err['prx']."</div>"; ?>
        <input type="number" step="0.01" min="0.01" name="prx" placeholder="Ex : 75.00" value="<?php if(isset($prx)) echo htmlspecialchars($prx); ?>">
      </div>
      <div>
        <label>Quantité en stock *</label>
        <?php if(isset($err['qte'])) echo "<div class='err'>".$err['qte']."</div>"; ?>
        <input type="number" min="0" name="qte" placeholder="Ex : 50" value="<?php if(isset($qte)) echo htmlspecialchars($qte); ?>">
      </div>
    </div>

    <label>Catégorie *</label>
    <?php if(isset($err['cat'])) echo "<div class='err'>".$err['cat']."</div>"; ?>
    <select name="cat">
      <option value="">-- Choisir une catégorie --</option>
      <?php
        $rs = $c->query("SELECT idcat, libelle FROM categorie ORDER BY libelle");
        while($row = $rs->fetch(PDO::FETCH_ASSOC)){
            $sel = (isset($cat) && $cat == $row['idcat']) ? "selected" : "";
            echo "<option value='".$row['idcat']."' ".$sel.">".htmlspecialchars($row['libelle'])."</option>";
        }
      ?>
    </select>

    <label>Photo du produit</label>
    <?php if(isset($err['image'])) echo "<div class='err'>".$err['image']."</div>"; ?>
    <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp">
    <p class="hint">Formats acceptés : jpg, jpeg, png, webp. Taille max : 2 Mo. Si aucune image, une image par défaut sera utilisée.</p>

    <div class="form-actions">
      <input type="submit" value="Publier le produit" class="btn-submit">
      <a href="dashboardpro.php" class="btn-cancel">✕ Annuler</a>
    </div>

    <p style="font-size:12px; color:var(--text-lt); margin-top:20px; border-top:1px solid var(--sand); padding-top:15px;">
      ℹ️ Votre produit sera soumis à l'administrateur pour validation avant d'apparaître dans le catalogue public.
    </p>
  </fieldset>
</form>

</body>
</html>
