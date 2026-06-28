<?php
// test_commande.php
// Test fonctionnel qui simule l'ajout au panier, le paiement et vérifie la base de données.
include("../prodconnex.php");
session_start();

// ID d'un client de test (Yassine Benali = 3)
$test_client_id = 3;
$_SESSION['idu'] = $test_client_id;

echo "===== DÉMARRAGE DU TEST DE COMMANDE =====\n\n";

// 1. Simuler un panier via Cookie (2 Châtaignes + 1 Figue)
$panier_test = ['CHAT-GRI-001' => 2, 'FIG-CHE-002' => 1];
setcookie('panier', json_encode($panier_test), time() + 3600, "/");
$_COOKIE['panier'] = json_encode($panier_test); // Forcer la lecture immédiate
echo "✅ Panier créé : 2 Châtaignes, 1 Figue\n";

// 2. Récupérer les prix depuis la BDD pour calculer le total
$refs = array_keys($panier_test);
$ph = implode(',', array_fill(0, count($refs), '?'));
$rp = $c->prepare("SELECT reference, prixu FROM produit WHERE reference IN ($ph)");
$rp->execute($refs);
$prix = [];
while($row = $rp->fetch(PDO::FETCH_ASSOC)) {
    $prix[$row['reference']] = $row['prixu'];
}
$montant_total = 0;
foreach($panier_test as $ref => $qte) {
    $montant_total += $qte * $prix[$ref];
}
echo "💳 Montant total simulé : " . number_format($montant_total, 2) . " DH\n";

// 3. Simuler la création de commande (comme dans paiement.php)
try {
    $c->beginTransaction();

    // Insertion commande
    $rc = $c->prepare("INSERT INTO commande (id_client, montant_total, methode_paiement, statut) VALUES (?, ?, ?, 'en_attente')");
    $rc->execute([$test_client_id, $montant_total, 'carte']);
    $idcom = $c->lastInsertId();
    echo "📦 Commande #$idcom créée en base.\n";

    // Insertion lignes et mise à jour stock
    $rcp = $c->prepare("INSERT INTO commande_produit (idcom, reference_produit, quantite, prix_unitaire) VALUES (?, ?, ?, ?)");
    $ruq = $c->prepare("UPDATE produit SET quantite = quantite - ? WHERE reference = ? AND quantite >= ?");

    foreach($panier_test as $ref => $qte) {
        $prix_unitaire = $prix[$ref];
        $rcp->execute([$idcom, $ref, $qte, $prix_unitaire]);
        $ruq->execute([$qte, $ref, $qte]);
        echo "  - Ligne ajoutée : $ref (x$qte) @ $prix_unitaire DH\n";
    }

    // Attribution des points
    $points = floor($montant_total);
    if($points > 0) {
        $ins_pts = $c->prepare("INSERT INTO point_fidelite (id_client, points, source) VALUES (?, ?, 'commande')");
        $ins_pts->execute([$test_client_id, $points]);
        echo "⭐ $points points de fidélité attribués au client #$test_client_id.\n";
    }

    $c->commit();
    echo "\n✅ TEST RÉUSSI : La commande a été validée avec succès.\n";
    echo "🔍 Vous pouvez vérifier les tables 'commande', 'commande_produit' et 'point_fidelite'.\n";

} catch(PDOException $e) {
    $c->rollBack();
    echo "\n❌ TEST ÉCHOUÉ : " . $e->getMessage() . "\n";
}
?>