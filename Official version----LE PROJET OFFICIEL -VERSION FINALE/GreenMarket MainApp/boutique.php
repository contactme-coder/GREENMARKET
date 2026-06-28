<?php
include("preferences.php"); include("prodconnex.php");
try { $req=$c->query("SELECT id, nom, email FROM compte WHERE role='producteur' AND statut='actif'"); $boutiques=$req->fetchAll(PDO::FETCH_ASSOC); } catch(PDOException $e){ $boutiques=[]; }
include("header.php");
?>
<style>.container { padding: 100px 40px 40px; max-width: 1200px; margin:0 auto; } .shops-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 25px; margin-top:30px; } .shop-card { background: var(--white); border: 1px solid var(--cream2); padding: 20px; border-radius: 12px; text-align: center; transition:0.3s; } .shop-card:hover { transform: translateY(-5px); box-shadow:0 5px 15px rgba(0,0,0,0.08); }</style>
<div class="container"><h1 style="font-family:'Cormorant Garamond', serif; color:var(--olive);"><?= tr('prod_by') ?></h1><p style="color:gray;"><?= tr('prod_sub') ?></p>
<div class="shops-grid"><?php if(empty($boutiques)): ?><p><?= tr('empty_shop') ?></p><?php else: foreach($boutiques as $b): ?><div class="shop-card"><div style="font-size:3rem; margin-bottom:10px;">🏪</div><h3><?= htmlspecialchars($b['nom']) ?></h3><p style="color:gray; font-size:13px; margin-top:5px;"><?= htmlspecialchars($b['email']) ?></p></div><?php endforeach; endif; ?></div></div>
<?php include("footer.php"); ?>