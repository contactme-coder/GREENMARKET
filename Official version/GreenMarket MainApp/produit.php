<?php
include("preferences.php");
include("prodconnex.php");

if(!isset($_GET['ref']) || empty($_GET['ref'])){ header("Location: catalogue.php"); exit; }
$ref = $_GET['ref'];

try {
    $req = $c->prepare("SELECT p.*, c.libelle as nomcat, u.nom as producteur FROM produit p 
                        JOIN categorie c ON p.idcateg = c.idcat 
                        JOIN compte u ON p.id_producteur = u.id 
                        WHERE p.reference = ? AND p.statut = 'valide'");
    $req->execute([$ref]);
    $produit = $req->fetch(PDO::FETCH_ASSOC);
    if(!$produit){ header("Location: catalogue.php"); exit; }

    $media_req = $c->prepare("SELECT * FROM produit_media WHERE reference_produit = ? ORDER BY ordre");
    $media_req->execute([$ref]);
    $medias = $media_req->fetchAll(PDO::FETCH_ASSOC);
    
    $av_req = $c->prepare("SELECT a.*, c.nom as client_nom FROM avis a 
                           JOIN compte c ON a.id_client = c.id 
                           WHERE a.reference_produit = ? AND a.statut = 'valide' 
                           ORDER BY a.date_avis DESC");
    $av_req->execute([$ref]);
    $avis = $av_req->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e){ die("Erreur : ".$e->getMessage()); }

$panier = isset($_COOKIE['panier']) ? json_decode($_COOKIE['panier'], true) : [];
if (!is_array($panier)) $panier = [];
$cart_count = array_sum($panier);

include("header.php");
?>
<style>
.container { padding: 100px 40px 40px; max-width: 1200px; margin:0 auto; display:grid; grid-template-columns: 1fr 1fr; gap:40px; }
.gallery { display:flex; flex-direction:column; gap:15px; }
.main-img { width:100%; border-radius:12px; border:1px solid #e8dfd0; background:#f9f5ef; text-align:center; }
.main-img img, .main-img video { max-width:100%; max-height:500px; object-fit:contain; border-radius:12px; }
.thumbnails { display:flex; gap:10px; flex-wrap:wrap; }
.thumbnails img, .thumbnails video { width:80px; height:80px; object-fit:cover; border-radius:8px; cursor:pointer; border:2px solid transparent; transition:0.3s; }
.thumbnails img:hover, .thumbnails video:hover { border-color:#5c6b3a; }
.thumbnails video { background:black; }
.info h1 { font-family:'Cormorant Garamond',serif; font-size:2.5rem; color:#5c6b3a; }
.price { font-size:2rem; font-weight:bold; color:#c95a5a; margin:10px 0; }
.btn-add { background:#5c6b3a; color:white; padding:12px 30px; border:none; border-radius:8px; font-weight:bold; cursor:pointer; transition:0.3s;}
.btn-add:hover { background:#4a5d2e; }
.btn-recommend { background:transparent; color:#5c6b3a; border:2px solid #5c6b3a; padding:12px 30px; border-radius:8px; font-weight:bold; cursor:pointer; transition:0.3s; margin-left:10px; }
.btn-recommend:hover { background:#5c6b3a; color:white; }
.review-item { border-bottom:1px solid #eee; padding:10px 0; }
.review-item strong { color:#5c6b3a; }
.review-stars { color:#f4c542; }

/* MODALE DE RECOMMANDATION */
.modal-overlay { position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:1000; display:none; align-items:center; justify-content:center; }
.modal-overlay.open { display:flex; }
.modal-box { background:#fff; padding:30px; border-radius:16px; max-width:450px; width:90%; position:relative; }
.modal-box .close { position:absolute; top:15px; right:20px; font-size:28px; cursor:pointer; color:#888; }
.modal-box h3 { color:#5c6b3a; margin-bottom:15px; }
.modal-box input, .modal-box textarea { width:100%; padding:10px; border:1px solid #d4c5ad; border-radius:6px; margin-bottom:12px; font-family:inherit; }
.modal-box button[type="submit"] { width:100%; background:#5c6b3a; color:white; padding:12px; border:none; border-radius:6px; font-weight:bold; cursor:pointer; }
.modal-box .alert { padding:10px; border-radius:6px; margin-bottom:10px; display:none; }
.modal-box .alert-success { background:#d1e7dd; color:#0f5132; }
.modal-box .alert-error { background:#f8d7da; color:#842029; }

@media(max-width:768px){ .container { grid-template-columns:1fr; } }
</style>

<div class="container">
  <!-- Galerie (inchangée) -->
  <div class="gallery">
    <?php if(count($medias) > 0): ?>
        <div class="main-img" id="mainMedia">
            <?php $first = $medias[0]; ?>
            <?php if($first['type'] == 'video'): ?>
                <video src="<?= htmlspecialchars($first['url']) ?>" controls style="width:100%; border-radius:12px;" onerror="this.parentElement.innerHTML='<p style=\'padding:20px;color:red;\'>Vidéo indisponible</p>'"></video>
            <?php else: ?>
                <img src="<?= htmlspecialchars($first['url']) ?>" style="width:100%; border-radius:12px;" onerror="this.src='https://via.placeholder.com/600x400?text=Image+non+trouvée'">
            <?php endif; ?>
        </div>
        <div class="thumbnails">
            <?php foreach($medias as $m): ?>
                <?php if($m['type'] == 'video'): ?>
                    <video src="<?= htmlspecialchars($m['url']) ?>" onclick="document.getElementById('mainMedia').innerHTML = '<video src=\'' + this.src + '\' controls style=\'width:100%; border-radius:12px;\' onerror=\'this.parentElement.innerHTML=\\'<p style=\\\'padding:20px;color:red;\\\'>Vidéo indisponible</p>\\\'\'></video>'" onerror="this.style.display='none'"></video>
                <?php else: ?>
                    <img src="<?= htmlspecialchars($m['url']) ?>" onclick="document.getElementById('mainMedia').innerHTML = '<img src=\'' + this.src + '\' style=\'width:100%; border-radius:12px;\' onerror=\'this.src=\\\'https://via.placeholder.com/600x400?text=Image+non+trouvée\\\'\'>'" onerror="this.src='https://via.placeholder.com/80x80?text=?'">
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="main-img">
            <img src="<?= htmlspecialchars($produit['image']) ?>" style="width:100%; border-radius:12px;" onerror="this.src='https://via.placeholder.com/600x400?text=Image+non+trouvée'">
        </div>
    <?php endif; ?>
  </div>
  
  <div class="info">
    <h1><?= htmlspecialchars($produit['libelle']) ?></h1>
    <div class="price"><?= htmlspecialchars($produit['prixu']) ?> DH</div>
    <p style="color:#4a4a3a; line-height:1.6;"><?= htmlspecialchars($produit['description']) ?></p>
    <p style="margin:15px 0; font-size:0.9rem; color:#748249;"><i class="fa-solid fa-leaf"></i> Produit par <strong><?= htmlspecialchars($produit['producteur']) ?></strong> - Catégorie : <?= htmlspecialchars($produit['nomcat']) ?></p>
    <div style="margin-top:20px; display:flex; flex-wrap:wrap; gap:10px;">
        <?php if($produit['quantite'] > 0): ?>
            <a href="catalogue.php?action=add&ref=<?= urlencode($produit['reference']) ?>" class="btn-add"><i class="fa-solid fa-cart-plus"></i> Ajouter au panier</a>
        <?php else: ?>
            <span style="color:#c95a5a; font-weight:bold;">Rupture de stock</span>
        <?php endif; ?>
        <button onclick="openRecommendModal('<?= urlencode($produit['reference']) ?>')" class="btn-recommend"><i class="fa-regular fa-envelope"></i> Recommander</button>
    </div>
    
    <div style="margin-top:40px; border-top:1px solid #e8dfd0; padding-top:20px;">
        <h3><i class="fa-regular fa-star"></i> Avis clients (<?= count($avis) ?>)</h3>
        <?php if(count($avis) > 0): ?>
            <?php foreach($avis as $av): ?>
                <div class="review-item">
                    <div style="display:flex; justify-content:space-between;">
                        <strong><?= htmlspecialchars($av['client_nom']) ?></strong>
                        <span class="review-stars">
                            <?= str_repeat('★', $av['note']) . str_repeat('☆', 5 - $av['note']) ?>
                        </span>
                    </div>
                    <p style="font-size:0.95rem; color:#4a4a3a; margin-top:5px;"><?= htmlspecialchars($av['commentaire']) ?></p>
                    <small style="color:#8a8a74;"><?= date('d/m/Y', strtotime($av['date_avis'])) ?></small>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="color:#8a8a74;">Soyez le premier à donner votre avis !</p>
        <?php endif; ?>
    </div>
  </div>
</div>

<!-- MODALE DE RECOMMANDATION -->
<div class="modal-overlay" id="recommendModal">
    <div class="modal-box">
        <span class="close" onclick="closeRecommendModal()">&times;</span>
        <h3><i class="fa-regular fa-share-from-square"></i> Recommander ce produit</h3>
        <div id="modalAlert" class="alert"></div>
        <form id="recommendForm">
            <input type="hidden" name="ref" id="recommendRef">
            <label style="font-weight:600; font-size:14px;">Email du destinataire *</label>
            <input type="email" name="dest_email" id="destEmail" placeholder="ami@exemple.com" required>
            <label style="font-weight:600; font-size:14px;">Votre message *</label>
            <textarea name="message" id="recommendMessage" rows="3" placeholder="Dites-lui pourquoi vous aimez ce produit..." required></textarea>
            <button type="submit">Envoyer la recommandation</button>
        </form>
    </div>
</div>

<script>
function openRecommendModal(ref) {
    document.getElementById('recommendRef').value = ref;
    document.getElementById('recommendModal').classList.add('open');
}

function closeRecommendModal() {
    document.getElementById('recommendModal').classList.remove('open');
    document.getElementById('modalAlert').style.display = 'none';
    document.getElementById('recommendForm').reset();
}

document.getElementById('recommendForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const alertBox = document.getElementById('modalAlert');
    
    fetch('recommander.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        alertBox.style.display = 'block';
        if (data.success) {
            alertBox.className = 'alert alert-success';
            alertBox.textContent = data.message;
            setTimeout(closeRecommendModal, 3000);
        } else {
            alertBox.className = 'alert alert-error';
            alertBox.textContent = data.message;
        }
    })
    .catch(error => {
        alertBox.style.display = 'block';
        alertBox.className = 'alert alert-error';
        alertBox.textContent = "Une erreur technique est survenue.";
    });
});

// Fermer la modale en cliquant à l'extérieur
document.getElementById('recommendModal').addEventListener('click', function(e) {
    if (e.target === this) closeRecommendModal();
});
</script>

<?php include("footer.php"); ?>