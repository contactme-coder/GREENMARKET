<?php
include("preferences.php");
include("prodconnex.php");

// Lecture du cookie du panier
$panier = isset($_COOKIE['panier']) ? json_decode($_COOKIE['panier'], true) : [];
if (!is_array($panier)) $panier = [];

// Suppression d'un article
if(isset($_GET['action']) && $_GET['action'] == 'remove' && isset($_GET['ref'])) {
    unset($panier[$_GET['ref']]);
    setcookie('panier', json_encode($panier), time() + (86400 * 30), "/");
    $_COOKIE['panier'] = json_encode($panier);
    header("Location: panier.php");
    exit;
}

$cart_items = [];
$total = 0;
if(!empty($panier)) {
    $refs = implode("','", array_keys($panier));
    try {
        $req = $c->query("SELECT * FROM produit WHERE reference IN ('$refs')");
        while($row = $req->fetch(PDO::FETCH_ASSOC)) {
            $row['qte_panier'] = $panier[$row['reference']];
            $row['sous_total'] = $row['prixu'] * $row['qte_panier'];
            $total += $row['sous_total'];
            $cart_items[] = $row;
        }
        $_SESSION['total_panier'] = $total; // on stocke le total pour le paiement
    } catch(PDOException $e) { }
} else {
    $_SESSION['total_panier'] = 0;
}

$cart_count = array_sum($panier);
include("header.php");
?>
<style>
.container { padding: 100px 40px 40px; max-width: 1200px; margin:0 auto; display: grid; grid-template-columns: 2fr 1fr; gap: 30px; }
.cart-item { background: var(--white); border: 1px solid var(--cream2); padding: 15px; margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center; border-radius: 8px; }
.summary-box { background: var(--white); border: 1px solid var(--cream2); padding: 20px; border-radius: 8px; height: fit-content; }
@media(max-width:768px){ .container { grid-template-columns:1fr; } }
</style>
<div class="container">
  <div>
    <h2><i class="fa-solid fa-cart-shopping"></i> <?= tr('cart') ?></h2><br>
    <?php if(empty($cart_items)): ?>
        <p style="color:var(--text-lt);"><?= tr('empty_cart') ?></p>
    <?php else: ?>
        <?php foreach($cart_items as $item): ?>
          <div class="cart-item">
            <div>
              <strong><?= htmlspecialchars($item['libelle']) ?></strong>
              <div style="font-size:12px; color:var(--text-lt);">QTY: <?= $item['qte_panier'] ?></div>
            </div>
            <div><strong style="color:var(--olive);"><?= number_format($item['sous_total'], 2) ?> DH</strong></div>
            <a href="panier.php?action=remove&ref=<?= urlencode($item['reference']) ?>" style="color:#c95a5a; text-decoration:none; font-size:13px; font-weight:bold;"><i class="fa-solid fa-trash-can"></i></a>
          </div>
        <?php endforeach; ?>
    <?php endif; ?>
  </div>
  <div class="summary-box">
    <h3><?= tr('summary') ?></h3><br>
    <div style="display:flex; justify-content:space-between; margin-bottom:15px;">
      <span><?= tr('total') ?></span>
      <span style="font-weight:bold; color:var(--olive); font-size:1.3rem;"><?= number_format($total, 2) ?> DH</span>
    </div>
    <?php if(!empty($cart_items)): ?>
      <a href="paiement.php" style="display:block; text-align:center; background:var(--olive); color:white; padding:12px; border-radius:8px; text-decoration:none; font-weight:bold; text-transform:uppercase; font-size:13px;"><i class="fa-solid fa-lock"></i> Passer au paiement</a>
    <?php endif; ?>
  </div>
</div>
<?php include("footer.php"); ?>