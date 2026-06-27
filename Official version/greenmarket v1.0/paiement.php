<?php
include("preferences.php");
if(!isset($_SESSION['idu']) || $_SESSION['roleu'] !== 'client'){ header("Location: authentification.php"); exit; }
if(empty($_SESSION['panier'])){ header("Location: catalogue.php"); exit; }
$err = [];
$montant_total = isset($_SESSION['total_panier']) ? floatval($_SESSION['total_panier']) : 0;

if($_SERVER['REQUEST_METHOD'] == "POST"){
    extract($_POST, EXTR_SKIP);
    include("prodconnex.php");
    if(!isset($methode_paiement) || empty($methode_paiement)) $err['global'] = "Veuillez sélectionner un mode de paiement.";
    else {
        if($methode_paiement == "carte"){
            if(!isset($banque_maroc) || $banque_maroc == "choisir") $err['banque_maroc'] = "Veuillez sélectionner votre banque.";
            if(!isset($card_name) || empty(trim($card_name))) $err['card_name'] = "Le nom du titulaire est requis.";
            if(!isset($card_number) || strlen(str_replace(' ', '', $card_number)) < 16) $err['card_number'] = "Numéro de carte invalide (16 chiffres requis).";
            if(!isset($card_expiry) || empty(trim($card_expiry))) $err['card_expiry'] = "Date d'expiration requise (MM/AA).";
            if(!isset($card_cvv) || strlen($card_cvv) < 3) $err['card_cvv'] = "Code CVV incorrect (3 chiffres).";
        }
        if($methode_paiement == "paypal"){
            if(!isset($paypal_email) || !filter_var($paypal_email, FILTER_VALIDATE_EMAIL)) $err['paypal_email'] = "Adresse email PayPal invalide.";
        }
        if($methode_paiement == "crypto"){
            if(!isset($crypto_type) || $crypto_type == "choisir") $err['crypto_type'] = "Veuillez choisir entre USDT et USDC.";
            if(!isset($wallet_client) || empty(trim($wallet_client))) $err['wallet_client'] = "Votre adresse de portefeuille est requise.";
        }
    }
    if(empty($err)){
        try {
            $c->beginTransaction();
            $rc = $c->prepare("INSERT INTO commande (id_client, montant_total, methode_paiement, statut) VALUES (?, ?, ?, 'en_attente')");
            $rc->execute([$_SESSION['idu'], $montant_total, $methode_paiement]);
            $idcom = $c->lastInsertId();
            $refs_list = array_keys($_SESSION['panier']);
            $ph = implode(',', array_fill(0, count($refs_list), '?'));
            $rp = $c->prepare("SELECT reference, prixu FROM produit WHERE reference IN ($ph)");
            $rp->execute($refs_list);
            $prix_produits = [];
            while($row = $rp->fetch(PDO::FETCH_ASSOC)) $prix_produits[$row['reference']] = $row['prixu'];
            $rcp = $c->prepare("INSERT INTO commande_produit (idcom, reference_produit, quantite, prix_unitaire) VALUES (?, ?, ?, ?)");
            $ruq = $c->prepare("UPDATE produit SET quantite = quantite - ? WHERE reference = ? AND quantite >= ?");
            foreach($_SESSION['panier'] as $ref => $qte){
                $prix = isset($prix_produits[$ref]) ? $prix_produits[$ref] : 0;
                $rcp->execute([$idcom, $ref, $qte, $prix]);
                $ruq->execute([$qte, $ref, $qte]);
            }
            $c->commit();
            $points = floor($montant_total);
            if($points > 0) {
                $ins_pts = $c->prepare("INSERT INTO point_fidelite (id_client, points, source) VALUES (?, ?, 'commande')");
                $ins_pts->execute([$_SESSION['idu'], $points]);
            }
            unset($_SESSION['panier']); unset($_SESSION['total_panier']);
            header("Location: dashboard.php?cmd_ok=" . $idcom);
            exit;
        } catch(PDOException $e){ $c->rollBack(); $err['global'] = "Erreur lors de la validation. Veuillez réessayer."; }
    }
}
?>
<!DOCTYPE html>
<html lang="<?= $lang_active ?>" dir="<?= $lang_active === 'ar' ? 'rtl' : 'ltr' ?>">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>GreenMarket – Paiement</title>
  <link rel="icon" type="image/svg+xml" href="favicon.svg">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
    :root { --ivory:#f9f5ef; --cream:#f2ebe0; --sand:#d4c5ad; --olive:#5c6b3a; --olive-bg:#edf0e4; --brown:#6b4c2a; --text:#1e1e18; --text-lt:#8a8a74; --white:#ffffff; --red:#c95a5a; }
    body { font-family:'Jost',sans-serif; background:var(--ivory); color:var(--text); min-height:100vh; transition:0.3s; padding-top:80px; }
    body.dark { background:#121212; color:#f9f5ef; }
    body.dark .navbar { background:#1e1e1e; border-bottom:1px solid #333; }
    body.dark .navbar a { color:#f9f5ef; }
    body.dark .pay-container { background:#1e1e1e; border-color:#333; }
    body.dark .order-summary { background:#2a2a2a; }
    body.dark .method-card { border-color:#444; }
    body.dark .method-card.active { border-color:var(--olive); background:#1e3320; }
    body.dark .payment-details { background:#222; border-color:#444; }
    body.dark input, body.dark select { background:#2a2a2a; border-color:#555; color:#f9f5ef; }
    body.dark .alert-danger { background:#3a1a1a; color:#ff9999; border-color:#c95a5a; }
    .navbar { position:fixed; top:0; left:0; right:0; height:72px; display:flex; justify-content:space-between; align-items:center; padding:0 40px; background:rgba(249,245,239,0.95); border-bottom:1px solid #e8dfd0; z-index:100; }
    .navbar a { text-decoration:none; color:var(--text); font-weight:bold; font-size:13px; }
    .pay-container { max-width:820px; margin:30px auto; background:var(--white); padding:40px; border-radius:16px; box-shadow:0 4px 25px rgba(0,0,0,0.04); border:1px solid rgba(212,197,173,0.2); }
    .pay-container h2 { font-family:'Cormorant Garamond',serif; color:var(--olive); font-size:2rem; margin-bottom:5px; }
    .pay-container p { color:var(--text-lt); margin-bottom:25px; font-size:14px; }
    .order-summary { background:var(--olive-bg); padding:18px 25px; border-radius:10px; display:flex; justify-content:space-between; align-items:center; margin-bottom:30px; }
    .order-summary .amount { font-size:1.6rem; font-weight:bold; color:var(--olive); }
    .payment-methods { display:grid; grid-template-columns:repeat(3,1fr); gap:15px; margin-bottom:25px; }
    .method-card { border:2px solid var(--sand); padding:20px 15px; border-radius:12px; text-align:center; cursor:pointer; transition:all 0.3s; position:relative; }
    .method-card i { font-size:2rem; margin-bottom:8px; color:var(--brown); display:block; }
    .method-card input[type="radio"] { position:absolute; top:10px; right:10px; accent-color:var(--olive); }
    .method-card.active { border-color:var(--olive); background:var(--olive-bg); }
    .method-card .method-name { font-weight:bold; font-size:14px; }
    .method-card .method-sub { font-size:11px; color:var(--text-lt); margin-top:4px; }
    .payment-details { display:none; background:#fdfcf9; border:1px solid #e8dfd0; padding:25px; border-radius:12px; margin-bottom:25px; }
    .payment-details.active { display:block; }
    .payment-details h4 { margin-bottom:18px; color:var(--olive); }
    .form-group { margin-bottom:15px; }
    .form-group label { display:block; font-size:13px; font-weight:600; color:var(--text); margin-bottom:6px; text-transform:uppercase; letter-spacing:0.04em; }
    .form-group input, .form-group select { width:100%; padding:12px; border:1px solid var(--sand); border-radius:8px; background:var(--cream); font-size:14px; outline:none; transition:border-color 0.2s; }
    .form-group input:focus, .form-group select:focus { border-color:var(--olive); }
    .inline-group { display:grid; grid-template-columns:1fr 1fr; gap:15px; }
    .error-text { color:var(--red); font-size:12px; margin-top:5px; font-weight:500; }
    .crypto-box { background:var(--cream); padding:14px; border-radius:8px; font-family:monospace; font-size:12px; word-break:break-all; border:1px dashed var(--brown); text-align:center; margin-top:8px; color:var(--brown); }
    .btn-pay { width:100%; padding:15px; background:var(--olive); color:white; border:none; border-radius:8px; font-size:15px; font-weight:bold; cursor:pointer; transition:background 0.2s; text-transform:uppercase; letter-spacing:0.05em; }
    .btn-pay:hover { background:#4a5d2e; }
    .alert-danger { background:#fdeced; color:var(--red); padding:15px; border-radius:8px; margin-bottom:20px; font-weight:bold; border:1px solid #fbcbcb; text-align:center; }
    @media(max-width:600px) { .payment-methods { grid-template-columns:1fr; } .inline-group { grid-template-columns:1fr; } }
  </style>
</head>
<body class="<?= $theme_actif ?>">
<nav class="navbar">
  <div style="font-weight:bold; color:var(--olive); font-size:1.3rem;">GreenMarket</div>
  <div style="display:flex; align-items:center; gap:20px;"><?php afficher_selecteurs(); ?><a href="panier.php">← <?= tr('cart') ?></a><a href="deconnexion.php" style="color:var(--red);"><?= tr('logout') ?></a></div>
</nav>
<div class="pay-container">
  <h2>Sécurisation de votre commande</h2>
  <p>Choisissez votre méthode de paiement pour finaliser vos achats bio sur GreenMarket.</p>
  <?php if(isset($err['global'])): ?><div class="alert-danger"><?= htmlspecialchars($err['global']) ?></div><?php endif; ?>
  <div class="order-summary"><span style="font-size:15px; color:var(--text);">Montant total à régler :</span><span class="amount"><?= number_format($montant_total, 2) ?> DH</span></div>
  <form method="POST" action="paiement.php" id="paymentForm">
    <div class="payment-methods">
      <div class="method-card" data-target="panel-carte"><input type="radio" name="methode_paiement" value="carte" <?= (isset($methode_paiement)&&$methode_paiement=='carte')?'checked':'' ?>><i class="fa-solid fa-credit-card"></i><div class="method-name">Carte Bancaire</div><div class="method-sub">CMI · Attijari · BCP · BMCE</div></div>
      <div class="method-card" data-target="panel-paypal"><input type="radio" name="methode_paiement" value="paypal" <?= (isset($methode_paiement)&&$methode_paiement=='paypal')?'checked':'' ?>><i class="fa-brands fa-paypal" style="color:#003087;"></i><div class="method-name">PayPal</div><div class="method-sub">Compte International</div></div>
      <div class="method-card" data-target="panel-crypto"><input type="radio" name="methode_paiement" value="crypto" <?= (isset($methode_paiement)&&$methode_paiement=='crypto')?'checked':'' ?>><i class="fa-solid fa-coins" style="color:#f3ba2f;"></i><div class="method-name">Stablecoins</div><div class="method-sub">USDT / USDC (TRC20)</div></div>
    </div>
    <div id="panel-carte" class="payment-details">
      <h4><i class="fa-solid fa-shield-halved"></i> Passerelle CMI Maroc</h4>
      <div class="form-group"><label>Votre banque marocaine</label><select name="banque_maroc"><option value="choisir">-- Sélectionnez votre banque --</option><option value="attijari" <?= (isset($banque_maroc)&&$banque_maroc=='attijari')?'selected':'' ?>>Attijariwafa Bank</option><option value="bcp" <?= (isset($banque_maroc)&&$banque_maroc=='bcp')?'selected':'' ?>>Banque Centrale Populaire</option><option value="bmce" <?= (isset($banque_maroc)&&$banque_maroc=='bmce')?'selected':'' ?>>Bank of Africa (BMCE)</option><option value="cih" <?= (isset($banque_maroc)&&$banque_maroc=='cih')?'selected':'' ?>>CIH Bank</option><option value="cdm" <?= (isset($banque_maroc)&&$banque_maroc=='cdm')?'selected':'' ?>>Crédit du Maroc</option></select><?php if(isset($err['banque_maroc'])) echo "<div class='error-text'>".$err['banque_maroc']."</div>"; ?></div>
      <div class="form-group"><label>Nom sur la carte</label><input type="text" name="card_name" placeholder="M. Mohamed Alami" value="<?= htmlspecialchars(isset($card_name)?$card_name:'') ?>"><?php if(isset($err['card_name'])) echo "<div class='error-text'>".$err['card_name']."</div>"; ?></div>
      <div class="form-group"><label>Numéro de carte (16 chiffres)</label><input type="text" name="card_number" maxlength="19" placeholder="4263 1234 5678 9012" value="<?= htmlspecialchars(isset($card_number)?$card_number:'') ?>"><?php if(isset($err['card_number'])) echo "<div class='error-text'>".$err['card_number']."</div>"; ?></div>
      <div class="inline-group"><div class="form-group"><label>Expiration (MM/AA)</label><input type="text" name="card_expiry" maxlength="5" placeholder="12/28" value="<?= htmlspecialchars(isset($card_expiry)?$card_expiry:'') ?>"><?php if(isset($err['card_expiry'])) echo "<div class='error-text'>".$err['card_expiry']."</div>"; ?></div><div class="form-group"><label>Code CVV</label><input type="password" name="card_cvv" maxlength="3" placeholder="312"><?php if(isset($err['card_cvv'])) echo "<div class='error-text'>".$err['card_cvv']."</div>"; ?></div></div>
    </div>
    <div id="panel-paypal" class="payment-details">
      <h4><i class="fa-brands fa-paypal" style="color:#003087;"></i> Redirection PayPal Sécurisée</h4>
      <p style="font-size:13px; color:var(--text-lt); margin-bottom:15px;">Une fenêtre PayPal s'ouvrira pour authentifier votre paiement.</p>
      <div class="form-group"><label>Identifiant Email PayPal</label><input type="email" name="paypal_email" placeholder="votre-compte@email.com" value="<?= htmlspecialchars(isset($paypal_email)?$paypal_email:'') ?>"><?php if(isset($err['paypal_email'])) echo "<div class='error-text'>".$err['paypal_email']."</div>"; ?></div>
    </div>
    <div id="panel-crypto" class="payment-details">
      <h4><i class="fa-solid fa-coins" style="color:#f3ba2f;"></i> Paiement Stablecoin (Réseau TRC-20)</h4>
      <div class="form-group"><label>Sélectionner la devise</label><select name="crypto_type"><option value="choisir">-- Choisir --</option><option value="usdt" <?= (isset($crypto_type)&&$crypto_type=='usdt')?'selected':'' ?>>USDT (Tether)</option><option value="usdc" <?= (isset($crypto_type)&&$crypto_type=='usdc')?'selected':'' ?>>USDC (USD Coin)</option></select><?php if(isset($err['crypto_type'])) echo "<div class='error-text'>".$err['crypto_type']."</div>"; ?></div>
      <div class="form-group"><label>Adresse de versement GreenMarket</label><div class="crypto-box">🔐 TY67sX92PzQwNmLkE19vBxa83Mop71ZqRs</div><p style="font-size:11px; color:var(--red); margin-top:6px;"><i class="fa-solid fa-triangle-exclamation"></i> N'envoyez que via le réseau TRC20.</p></div>
      <div class="form-group"><label>Votre adresse de portefeuille (pour validation)</label><input type="text" name="wallet_client" placeholder="Ex: TX9zPq...71mK" value="<?= htmlspecialchars(isset($wallet_client)?$wallet_client:'') ?>"><?php if(isset($err['wallet_client'])) echo "<div class='error-text'>".$err['wallet_client']."</div>"; ?></div>
    </div>
    <button type="submit" name="valider_paiement" class="btn-pay"><i class="fa-solid fa-lock"></i> Confirmer et régler ma commande (<?= number_format($montant_total,2) ?> DH)</button>
  </form>
</div>
<script src="script_paiement.js"></script>
</body>
</html>