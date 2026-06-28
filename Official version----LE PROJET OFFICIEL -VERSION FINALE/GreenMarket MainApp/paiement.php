<?php
include("preferences.php");
if(!isset($_SESSION['idu']) || $_SESSION['roleu'] !== 'client') {
    header("Location: authentification.php");
    exit;
}
// Vérifier que le panier n'est pas vide
$panier = isset($_COOKIE['panier']) ? json_decode($_COOKIE['panier'], true) : [];
if (!is_array($panier)) $panier = [];
if(empty($panier)) {
    header("Location: catalogue.php");
    exit;
}

$montant_total = isset($_SESSION['total_panier']) ? floatval($_SESSION['total_panier']) : 0;
if($montant_total <= 0) {
    // Recalculer si nécessaire
    include("prodconnex.php");
    $refs = array_keys($panier);
    $ph = implode(',', array_fill(0, count($refs), '?'));
    $rp = $c->prepare("SELECT reference, prixu FROM produit WHERE reference IN ($ph)");
    $rp->execute($refs);
    $prix = [];
    while($row = $rp->fetch(PDO::FETCH_ASSOC)) {
        $prix[$row['reference']] = $row['prixu'];
    }
    $montant_total = 0;
    foreach($panier as $ref => $qte) {
        if(isset($prix[$ref])) {
            $montant_total += $qte * $prix[$ref];
        }
    }
    $_SESSION['total_panier'] = $montant_total;
}

$err = [];

if($_SERVER['REQUEST_METHOD'] == "POST"){
    extract($_POST, EXTR_SKIP);
    include("prodconnex.php");

    // Validation méthode de paiement
    if(!isset($methode_paiement) || empty($methode_paiement)) {
        $err['global'] = "Veuillez sélectionner un mode de paiement.";
    } else {
        // Validation spécifique (simplifiée pour l'exemple)
        if($methode_paiement == "carte") {
            if(!isset($card_name) || empty(trim($card_name))) $err['card_name'] = "Nom sur la carte requis.";
            if(!isset($card_number) || strlen(str_replace(' ', '', $card_number)) < 16) $err['card_number'] = "Numéro de carte invalide.";
        }
        // Autres méthodes...
    }

    if(empty($err)){
        try {
            $c->beginTransaction();

            // 1. Insérer la commande
            $rc = $c->prepare("INSERT INTO commande (id_client, montant_total, methode_paiement, statut) VALUES (?, ?, ?, 'en_attente')");
            $rc->execute([$_SESSION['idu'], $montant_total, $methode_paiement]);
            $idcom = $c->lastInsertId();

            // 2. Insérer les lignes et décrémenter le stock
            // Récupérer les prix
            $refs = array_keys($panier);
            $ph = implode(',', array_fill(0, count($refs), '?'));
            $rp = $c->prepare("SELECT reference, prixu, quantite FROM produit WHERE reference IN ($ph)");
            $rp->execute($refs);
            $produits = [];
            while($row = $rp->fetch(PDO::FETCH_ASSOC)) {
                $produits[$row['reference']] = $row;
            }

            $rcp = $c->prepare("INSERT INTO commande_produit (idcom, reference_produit, quantite, prix_unitaire) VALUES (?, ?, ?, ?)");
            $ruq = $c->prepare("UPDATE produit SET quantite = quantite - ? WHERE reference = ? AND quantite >= ?");

            foreach($panier as $ref => $qte) {
                if(!isset($produits[$ref])) {
                    throw new Exception("Produit $ref introuvable.");
                }
                $prix_unitaire = $produits[$ref]['prixu'];
                $stock = $produits[$ref]['quantite'];
                if($stock < $qte) {
                    throw new Exception("Stock insuffisant pour $ref.");
                }
                $rcp->execute([$idcom, $ref, $qte, $prix_unitaire]);
                $ruq->execute([$qte, $ref, $qte]);
            }

            // 3. Attribution des points de fidélité (1 point par DH dépensé)
            $points = floor($montant_total);
            if($points > 0) {
                $ins_pts = $c->prepare("INSERT INTO point_fidelite (id_client, points, source) VALUES (?, ?, 'commande')");
                $ins_pts->execute([$_SESSION['idu'], $points]);
            }

            // 4. Vider le panier (cookie)
            setcookie('panier', '', time() - 3600, "/");
            unset($_COOKIE['panier']);
            unset($_SESSION['total_panier']);

            $c->commit();

            // Redirection vers le dashboard client avec confirmation
            header("Location: dashboard.php?cmd_ok=" . $idcom);
            exit;

        } catch(Exception $e) {
            $c->rollBack();
            $err['global'] = "Erreur lors de la validation : " . $e->getMessage();
        }
    }
}

include("header.php");
?>
<style>
.pay-container { max-width: 820px; margin: 100px auto 40px; background: var(--white); padding: 40px; border-radius: 16px; box-shadow: 0 4px 25px rgba(0,0,0,0.04); border: 1px solid var(--cream2); }
.payment-methods { display: grid; grid-template-columns: repeat(3,1fr); gap: 15px; margin-bottom: 25px; }
.method-card { border: 2px solid var(--cream2); padding: 20px 15px; border-radius: 12px; text-align: center; cursor: pointer; transition: 0.3s; }
.method-card.active { border-color: var(--olive); background: var(--olive-bg); }
.payment-details { display: none; background: var(--ivory); border: 1px solid var(--cream2); padding: 25px; border-radius: 12px; margin-bottom: 25px; }
.payment-details.active { display: block; }
.btn-pay { width: 100%; padding: 15px; background: var(--olive); color: white; border: none; border-radius: 8px; font-size: 16px; font-weight: bold; cursor: pointer; transition: 0.2s; }
.btn-pay:hover { background: #4a5d2e; }
@media(max-width:600px){ .payment-methods { grid-template-columns: 1fr; } }
</style>
<div class="pay-container">
  <h2><i class="fa-solid fa-shield-halved"></i> Sécurisation de votre commande</h2>
  <div class="order-summary" style="background:var(--olive-bg); padding:18px; border-radius:10px; display:flex; justify-content:space-between; margin-bottom:30px;">
    <span>Montant total à régler :</span> <span style="font-size:1.6rem; font-weight:bold; color:var(--olive);"><?= number_format($montant_total, 2) ?> DH</span>
  </div>
  <?php if(isset($err['global'])): ?><div style="color:#c95a5a; background:#fdeced; padding:15px; border-radius:8px; margin-bottom:20px;"><?= htmlspecialchars($err['global']) ?></div><?php endif; ?>
  <form method="POST">
    <div class="payment-methods">
      <div class="method-card" data-target="panel-carte"><input type="radio" name="methode_paiement" value="carte" style="accent-color:var(--olive);"><i class="fa-solid fa-credit-card fa-2x"></i><div>Carte Bancaire</div></div>
      <div class="method-card" data-target="panel-paypal"><input type="radio" name="methode_paiement" value="paypal" style="accent-color:var(--olive);"><i class="fa-brands fa-paypal fa-2x"></i><div>PayPal</div></div>
      <div class="method-card" data-target="panel-crypto"><input type="radio" name="methode_paiement" value="crypto" style="accent-color:var(--olive);"><i class="fa-solid fa-coins fa-2x"></i><div>Stablecoins</div></div>
    </div>
    <div id="panel-carte" class="payment-details">
      <h4>Carte Bancaire</h4>
      <input type="text" name="card_name" placeholder="Nom sur la carte" style="width:100%; padding:10px; margin-bottom:10px; border:1px solid #d4c5ad; border-radius:6px;">
      <input type="text" name="card_number" placeholder="Numéro de carte (16 chiffres)" maxlength="19" style="width:100%; padding:10px; margin-bottom:10px; border:1px solid #d4c5ad; border-radius:6px;">
      <div style="display:flex; gap:10px;"><input type="text" name="card_expiry" placeholder="MM/AA" maxlength="5" style="flex:1; padding:10px; border:1px solid #d4c5ad; border-radius:6px;"><input type="password" name="card_cvv" placeholder="CVV" maxlength="3" style="flex:1; padding:10px; border:1px solid #d4c5ad; border-radius:6px;"></div>
    </div>
    <div id="panel-paypal" class="payment-details">
      <h4>PayPal</h4>
      <input type="email" name="paypal_email" placeholder="Email PayPal" style="width:100%; padding:10px; border:1px solid #d4c5ad; border-radius:6px;">
    </div>
    <div id="panel-crypto" class="payment-details">
      <h4>USDT / USDC (TRC20)</h4>
      <select name="crypto_type" style="width:100%; padding:10px; margin-bottom:10px; border:1px solid #d4c5ad; border-radius:6px;"><option value="usdt">USDT</option><option value="usdc">USDC</option></select>
      <input type="text" name="wallet_client" placeholder="Votre adresse de portefeuille" style="width:100%; padding:10px; border:1px solid #d4c5ad; border-radius:6px;">
    </div>
    <button type="submit" name="valider_paiement" class="btn-pay"><i class="fa-solid fa-lock"></i> Confirmer et régler ma commande (<?= number_format($montant_total,2) ?> DH)</button>
  </form>
</div>
<script src="script_paiement.js"></script>
<?php include("footer.php"); ?>