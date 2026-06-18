<?php
session_start();

// Vérification de sécurité : le client doit être connecté pour payer
if(!isset($_SESSION) || empty($_SESSION) || $_SESSION['roleu'] !== 'client'){
    // Si tu n'as pas encore restreint l'accès client, tu peux commenter ces 3 lignes pour tester librement
    header("Location: authentification.php");
    exit;
}

$err = [];
$success_msg = "";
// On simule un montant récupéré depuis le panier (Ex: 450.00 DH)
$montant_total = isset($_SESSION['total_panier']) ? $_SESSION['total_panier'] : 450.00; 

if($_SERVER["REQUEST_METHOD"] == "POST"){
    extract($_POST);
    
    // 1. VERIFICATION DU CHOIX DU MODE DE PAIEMENT
    if(!isset($methode_paiement) || empty($methode_paiement)){
        $err['global'] = "Veuillez sélectionner un mode de paiement.";
    } else {
        // 2. VERIFICATIONS SPECIFIQUES AU MODE CHOISI
        if($methode_paiement == "carte"){
            if(empty(trim($card_name))) $err['card_name'] = "Le nom du titulaire est obligatoire.";
            if(empty(trim($card_number)) || strlen(str_replace(' ', '', $card_number)) < 16) $err['card_number'] = "Numéro de carte invalide (16 chiffres requis).";
            if(empty(trim($card_expiry))) $err['card_expiry'] = "Date d'expiration requise (MM/AA).";
            if(empty(trim($card_cvv)) || strlen($card_cvv) < 3) $err['card_cvv'] = "Code CVV incorrect.";
            if($banque_maroc == "choisir") $err['banque_maroc'] = "Veuillez sélectionner votre banque marocaine.";
        }
        
        if($methode_paiement == "paypal"){
            if(empty(trim($paypal_email)) || !filter_var($paypal_email, FILTER_VALIDATE_EMAIL)) {
                $err['paypal_email'] = "Veuillez entrer une adresse email PayPal valide.";
            }
        }
        
        if($methode_paiement == "crypto"){
            if($crypto_type == "choisir") $err['crypto_type'] = "Veuillez choisir entre USDT et USDC.";
            if(empty(trim($wallet_client))) $err['wallet_client'] = "Votre adresse de portefeuille (Wallet) est requise pour vérification.";
        }
    }

    // 3. SI ZERO ERREUR : VALIDATION DU PAIEMENT & INSERTION BDD
    if(empty($err)){
        include("prodconnex.php");
        try {
            // Optionnel : Insertion dans la table Facture si tu l'utilises
            // $req = $c->prepare("INSERT INTO Facture (montant_total, moment) VALUES (?, NOW())");
            // $req->execute([$montant_total]);
            
            $success_msg = "🎉 Paiement de " . $montant_total . " DH effectué avec succès via " . strtoupper($methode_paiement) . " ! Votre commande est en cours de préparation.";
            
            // On vide le panier après succès
            if(isset($_SESSION['panier'])) unset($_SESSION['panier']);
        } catch(PDOException $e) {
            die("Erreur lors de la validation du paiement : " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>GreenMarket – Finaliser le Paiement</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Jost:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    /* Intégration de la charte graphique GreenMarket */
    body { font-family: 'Jost', sans-serif; background: #f9f5ef; color: #1e1e18; margin: 0; padding: 20px; }
    .pay-container { max-width: 800px; margin: 40px auto; background: white; padding: 40px; border-radius: 16px; box-shadow: 0 4px 25px rgba(0,0,0,0.04); }
    h2 { font-family: 'Cormorant Garamond', serif; color: #5c6b3a; font-size: 2rem; margin-bottom: 10px; }
    .order-summary { background: #edf0e4; padding: 20px; border-radius: 8px; margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center; }
    .amount { font-size: 1.5rem; font-weight: bold; color: #5c6b3a; }
    
    /* Style des options de paiement */
    .payment-methods { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 30px; }
    .method-card { border: 2px solid #e8dfd0; padding: 20px; border-radius: 12px; text-align: center; cursor: pointer; transition: all 0.3s; position: relative; }
    .method-card i { font-size: 2rem; margin-bottom: 10px; color: #9a7455; }
    .method-card input[type="radio"] { position: absolute; top: 10px; right: 10px; accent-color: #5c6b3a; }
    .method-card.active { border-color: #5c6b3a; background: #edf0e4; }
    
    /* Blocs de formulaires dynamiques */
    .payment-details { background: #fdfcf9; border: 1px solid #e8dfd0; padding: 25px; border-radius: 12px; margin-bottom: 25px; display: none; }
    .payment-details.active { display: block; }
    
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; margin-bottom: 5px; font-weight: 500; font-size: 14px; }
    .form-group input, .form-group select { width: 100%; padding: 12px; border: 1px solid #d4c5ad; border-radius: 8px; box-sizing: border-box; background: white; }
    .inline-group { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
    
    .btn-pay { width: 100%; padding: 15px; background: #5c6b3a; color: white; border: none; border-radius: 8px; font-size: 16px; font-weight: bold; cursor: pointer; transition: background 0.2s; }
    .btn-pay:hover { background: #4a5d2e; }
    
    .error-text { color: #c95a5a; font-size: 13px; margin-top: 5px; }
    .alert-success { background: #edf0e4; color: #5c6b3a; padding: 20px; border-radius: 8px; border-left: 5px solid #5c6b3a; margin-bottom: 20px; font-weight: bold; text-align: center; }
    .alert-danger { background: #fdeced; color: #c95a5a; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: bold; }
    .crypto-address-box { background: #f2ebe0; padding: 15px; border-radius: 8px; font-family: monospace; font-size: 13px; word-break: break-all; margin-top: 10px; border: 1px dashed #9a7455; text-align: center; }
  </style>
</head>
<body>

<div class="pay-container">
  <h2>Sécurisation de votre commande</h2>
  <p>Choisissez votre méthode de paiement préférée pour finaliser vos achats bio sur GreenMarket.</p>

  <?php if(!empty($success_msg)): ?>
      <div class="alert-success"><?= $success_msg ?></div>
      <div style="text-align: center; margin-top: 20px;"><a href="acceuil.html" style="color: #5c6b3a; font-weight: bold;">Retour à l'accueil</a></div>
  <?php else: ?>

      <?php if(isset($err['global'])): ?>
          <div class="alert-danger"><?= $err['global'] ?></div>
      <?php endif; ?>

      <div class="order-summary">
        <span>Montant à régler :</span>
        <span class="amount"><?= number_format($montant_total, 2) ?> DH</span>
      </div>

      <form method="POST" action="paiement.php" id="paymentForm">
        
        <div class="payment-methods">
          <div class="method-card" data-target="panel-carte">
            <input type="radio" name="methode_paiement" value="carte" id="payCarte" <?php if(isset($methode_paiement) && $methode_paiement == 'carte') echo 'checked'; ?>>
            <i class="fa-solid fa-credit-card"></i>
            <div style="font-weight: bold; font-size: 15px;">Cartes Marocaines / Visa</div>
            <div style="font-size: 11px; color:#777; margin-top:5px;">CMI, Attijari, BCP, BMCE...</div>
          </div>

          <div class="method-card" data-target="panel-paypal">
            <input type="radio" name="methode_paiement" value="paypal" id="payPaypal" <?php if(isset($methode_paiement) && $methode_paiement == 'paypal') echo 'checked'; ?>>
            <i class="fa-brands fa-paypal" style="color: #003087;"></i>
            <div style="font-weight: bold; font-size: 15px;">PayPal</div>
            <div style="font-size: 11px; color:#777; margin-top:5px;">Compte International</div>
          </div>

          <div class="method-card" data-target="panel-crypto">
            <input type="radio" name="methode_paiement" value="crypto" id="payCrypto" <?php if(isset($methode_paiement) && $methode_paiement == 'crypto') echo 'checked'; ?>>
            <i class="fa-solid fa-coins" style="color: #f3ba2f;"></i>
            <div style="font-weight: bold; font-size: 15px;">Stablecoins</div>
            <div style="font-size: 11px; color:#777; margin-top:5px;">USDT / USDC (TRC20)</div>
          </div>
        </div>

        <div id="panel-carte" class="payment-details">
          <h4 style="margin-top:0; color:#5c6b3a;"><i class="fa-solid fa-shield-halved"></i> Passerelle d'autorisation CMI Maroc</h4>
          
          <div class="form-group">
            <label>Votre Banque Marocaine</label>
            <select name="banque_maroc">
              <option value="choisir">-- Sélectionnez votre banque --</option>
              <option value="attijari" <?php if(isset($banque_maroc) && $banque_maroc == 'attijari') echo 'selected'; ?>>Attijariwafa Bank</option>
              <option value="bcp" <?php if(isset($banque_maroc) && $banque_maroc == 'bcp') echo 'selected'; ?>>Banque Centrale Populaire (BCP)</option>
              <option value="bmce" <?php if(isset($banque_maroc) && $banque_maroc == 'bmce') echo 'selected'; ?>>Bank of Africa (BMCE)</option>
              <option value="cih" <?php if(isset($banque_maroc) && $banque_maroc == 'cih') echo 'selected'; ?>>CIH Bank</option>
              <option value="credit_maroc" <?php if(isset($banque_maroc) && $banque_maroc == 'credit_maroc') echo 'selected'; ?>>Crédit du Maroc</option>
            </select>
            <?php if(isset($err['banque_maroc'])) echo "<div class='error-text'>".$err['banque_maroc']."</div>"; ?>
          </div>

          <div class="form-group">
            <label>Nom sur la carte</label>
            <input type="text" name="card_name" placeholder="M. Mohamed Alami" value="<?php if(isset($card_name)) echo htmlspecialchars($card_name); ?>">
            <?php if(isset($err['card_name'])) echo "<div class='error-text'>".$err['card_name']."</div>"; ?>
          </div>

          <div class="form-group">
            <label>Numéro de carte bancaire</label>
            <input type="text" name="card_number" maxlength="19" placeholder="4263 1234 5678 9012" value="<?php if(isset($card_number)) echo htmlspecialchars($card_number); ?>">
            <?php if(isset($err['card_number'])) echo "<div class='error-text'>".$err['card_number']."</div>"; ?>
          </div>

          <div class="inline-group">
            <div class="form-group">
              <label>Expiration (MM/AA)</label>
              <input type="text" name="card_expiry" maxlength="5" placeholder="12/28" value="<?php if(isset($card_expiry)) echo htmlspecialchars($card_expiry); ?>">
              <?php if(isset($err['card_expiry'])) echo "<div class='error-text'>".$err['card_expiry']."</div>"; ?>
            </div>
            <div class="form-group">
              <label>Code Cryptogramme (CVV)</label>
              <input type="password" name="card_cvv" maxlength="3" placeholder="312" value="<?php if(isset($card_cvv)) echo htmlspecialchars($card_cvv); ?>">
              <?php if(isset($err['card_cvv'])) echo "<div class='error-text'>".$err['card_cvv']."</div>"; ?>
            </div>
          </div>
        </div>

        <div id="panel-paypal" class="payment-details">
          <h4 style="margin-top:0; color:#003087;"><i class="fa-brands fa-paypal"></i> Redirection Express Securisée PayPal</h4>
          <p style="font-size:14px; color:#555;">Une fenêtre contextuelle sécurisée PayPal s'ouvrira pour authentifier votre paiement.</p>
          <div class="form-group">
            <label>Identifiant Email PayPal</label>
            <input type="email" name="paypal_email" placeholder="votre-compte@email.ma" value="<?php if(isset($paypal_email)) echo htmlspecialchars($paypal_email); ?>">
            <?php if(isset($err['paypal_email'])) echo "<div class='error-text'>".$err['paypal_email']."</div>"; ?>
          </div>
        </div>

        <div id="panel-crypto" class="payment-details">
          <h4 style="margin-top:0; color:#b18a1a;"><i class="fa-solid fa-network-wired"></i> Paiement Décentralisé en Stablecoin (Réseau TRON TRC-20)</h4>
          <div class="form-group">
            <label>Sélectionner le jeton stable</label>
            <select name="crypto_type">
              <option value="choisir">-- Choisir la devise --</option>
              <option value="usdt" <?php if(isset($crypto_type) && $crypto_type == 'usdt') echo 'selected'; ?>>USDT (Tether USD)</option>
              <option value="usdc" <?php if(isset($crypto_type) && $crypto_type == 'usdc') echo 'selected'; ?>>USDC (USD Coin)</option>
            </select>
            <?php if(isset($err['crypto_type'])) echo "<div class='error-text'>".$err['crypto_type']."</div>"; ?>
          </div>

          <div class="form-group">
            <label>Adresse de versement GreenMarket (Envoyez le montant exact ci-dessus)</label>
            <div class="crypto-address-box">
              <i class="fa-solid fa-wallet"></i> <span id="gmWalletAddr">TY67sX92PzQwNmLkE19vBxa83Mop71ZqRs</span>
            </div>
            <p style="font-size:11px; color:#c95a5a; margin-top:5px;"><i class="fa-solid fa-triangle-exclamation"></i> Attention : N'envoyez que le jeton sélectionné via le réseau TRC20 sous peine de perte définitive.</p>
          </div>

          <div class="form-group">
            <label>Votre adresse publique de portefeuille (Pour validation de la transaction)</label>
            <input type="text" name="wallet_client" placeholder="Ex: TX9zPq...71mK" value="<?php if(isset($wallet_client)) echo htmlspecialchars($wallet_client); ?>">
            <?php if(isset($err['wallet_client'])) echo "<div class='error-text'>".$err['wallet_client']."</div>"; ?>
          </div>
        </div>

        <button type="submit" name="valider_paiement" class="btn-pay">Confirmer et régler ma commande</button>
      </form>
  <?php endif; ?>
</div>

<script src="script_paiement.js"></script>
</body>
</html>