<?php
session_start();
include("prodconnex.php");

if(!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = [];
}

if(isset($_GET['action']) && $_GET['action'] == 'remove' && isset($_GET['ref'])) {
    $ref = $_GET['ref'];
    unset($_SESSION['panier'][$ref]);
    header("Location: panier.php");
    exit;
}

$cart_items = [];
$total = 0;

if(!empty($_SESSION['panier'])) {
    $refs = implode("','", array_keys($_SESSION['panier']));
    try {
        $req = $c->query("SELECT * FROM produit WHERE reference IN ('$refs')");
        while($row = $req->fetch(PDO::FETCH_ASSOC)) {
            $row['qte_panier'] = $_SESSION['panier'][$row['reference']];
            $row['sous_total'] = $row['prixu'] * $row['qte_panier'];
            $total += $row['sous_total'];
            $cart_items[] = $row;
        }
    } catch(PDOException $e) { die("Erreur : " . $e->getMessage()); }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>GreenMarket – Mon Panier</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght=0,300;0,400;0,600;1,300;1,400&family=Jost:wght=300;400;500;600&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
    :root {
      --ivory:     #f9f5ef;
      --cream:     #f2ebe0;
      --cream2:    #e8dfd0;
      --sand:      #d4c5ad;
      --olive:     #5c6b3a;
      --olive-mid: #748249;
      --olive-lt:  #a3b37a;
      --olive-bg:  #edf0e4;
      --brown:     #6b4c2a;
      --brown-lt:  #9a7455;
      --text:      #1e1e18;
      --text-mid:  #4a4a3a;
      --text-lt:   #8a8a74;
      --white:     #ffffff;
      --serif: 'Cormorant Garamond', Georgia, serif;
      --sans:  'Jost', sans-serif;
      --shadow-sm: 0 2px 12px rgba(60,50,20,0.07);
    }
    body { font-family: var(--sans); background: var(--ivory); color: var(--text); -webkit-font-smoothing: antialiased; }
    .navbar { position: fixed; top: 0; left: 0; right: 0; z-index: 200; display: flex; align-items: center; justify-content: space-between; padding: 0 60px; height: 72px; background: rgba(249,245,239,0.90); backdrop-filter: blur(16px); border-bottom: 1px solid rgba(212,197,173,0.35); }
    .logo { display: flex; align-items: center; gap: 9px; text-decoration: none; }
    .logo-leaf { width: 34px; height: 34px; background: var(--olive); border-radius: 50% 50% 50% 0; transform: rotate(-45deg); display: flex; align-items: center; justify-content: center; }
    .logo-leaf::after { content: ''; width: 14px; height: 14px; background: var(--ivory); border-radius: 50%; transform: rotate(45deg) translate(-1px, -1px); }
    .logo-text { font-family: var(--serif); font-size: 1.4rem; font-weight: 600; color: var(--olive); }
    .logo-text span { color: var(--brown); }
    .cart-container { max-width: 1200px; margin: 0 auto; padding: 120px 20px 60px 20px; display: flex; gap: 40px; }
    .cart-main { flex: 2; }
    .cart-main h1 { font-family: var(--serif); font-size: 2.5rem; font-weight: 300; margin-bottom: 30px; }
    .cart-item { display: flex; align-items: center; background: var(--white); padding: 20px; border-radius: 12px; margin-bottom: 15px; box-shadow: var(--shadow-sm); border: 1px solid rgba(212,197,173,0.2); }
    .item-details { flex: 2; margin-left: 20px; }
    .item-title { font-family: var(--serif); font-size: 1.25rem; font-weight: 600; color: var(--text); }
    .order-summary { flex: 1; background: var(--white); padding: 25px; border-radius: 15px; height: fit-content; box-shadow: var(--shadow-sm); border: 1px solid rgba(212,197,173,0.2); }
    .order-summary h3 { font-family: var(--serif); font-size: 1.5rem; color: var(--brown); }
  </style>
</head>
<body>

<nav class="navbar" id="navbar">
  <a href="acceuil.php" class="logo">
    <div class="logo-leaf"></div>
    <div class="logo-text">Green<span>Market</span></div>
  </a>
  <div class="nav-actions">
    <a href="catalogue.php" style="text-decoration:none; color:var(--olive); font-weight:500;">← Retour au Catalogue</a>
  </div>
</nav>

<div class="cart-container">
  <div class="cart-main">
    <h1>Votre <em>Panier</em></h1>
    
    <div>
      <?php if(empty($cart_items)): ?>
          <p style="color: var(--text-lt);">Votre panier est actuellement vide.</p>
      <?php else: ?>
          <?php foreach($cart_items as $item): ?>
          <div class="cart-item">
            <div style="background-image:url('<?= htmlspecialchars($item['image']) ?>'); width:80px; height:80px; background-size:cover; border-radius:8px;"></div>
            <div class="item-details">
                <div class="item-title"><?= htmlspecialchars($item['libelle']) ?></div>
                <div style="font-size:13px; color:var(--text-lt);">Quantité : <?= $item['qte_panier'] ?></div>
            </div>
            <div style="font-weight:bold;">
                <?= number_format($item['sous_total'], 2) ?> DH
            </div>
            <a href="panier.php?action=remove&ref=<?= urlencode($item['reference']) ?>" style="margin-left:20px; color:#c95a5a; text-decoration:none; font-size:14px; font-weight:500;">Retirer</a>
          </div>
          <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <div class="order-summary">
    <h3>Résumé de la commande</h3>
    <div style="display:flex; justify-content:space-between; margin-top:15px; font-size:15px; color:var(--text-mid);">
        <span>Sous-total</span>
        <span><?= number_format($total, 2) ?> DH</span>
    </div>
    <hr style="margin:20px 0; border:0; border-top:1px solid rgba(212,197,173,0.35);">
    <div style="display:flex; justify-content:space-between; font-weight:bold; font-size:1.2rem;">
        <span>Total</span>
        <span style="color:#5c6b3a;"><?= number_format($total, 2) ?> DH</span>
    </div>
    <?php if(!empty($cart_items)): ?>
        <?php $_SESSION['total_panier'] = $total; ?>
        <a href="paiement.php" style="display:block; text-align:center; width:100%; padding:15px; background:#5c6b3a; color:white; border-radius:25px; text-decoration:none; margin-top:25px; font-weight:500; font-size:15px;">Passer au paiement</a>
    <?php endif; ?>
  </div>
</div>

</body>
</html>