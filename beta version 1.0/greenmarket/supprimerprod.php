<?php
session_start();
if(!isset($_SESSION['idu']) || $_SESSION['roleu'] !== 'producteur'){
    header("Location: authentification.php");
    exit;
}
include("prodconnex.php");

// Vérification que le paramètre refp est bien fourni
if(!isset($_GET['refp']) || empty(trim($_GET['refp']))){
    header("Location: dashboardpro.php");
    exit;
}

try {
    // ÉTAPE 1 : Vérifier que ce produit appartient bien au producteur connecté
    $rs = $c->prepare("SELECT reference, libelle, image FROM produit WHERE reference = ? AND id_producteur = ?");
    $rs->execute([trim($_GET['refp']), $_SESSION['idu']]);
    $tprod = $rs->fetch(PDO::FETCH_ASSOC);

    if(!$tprod){
        // Le produit n'existe pas ou n'appartient pas à ce producteur
        header("Location: dashboardpro.php?msgerr=" . urlencode("Produit introuvable ou accès refusé."));
        exit;
    }

    // ÉTAPE 2 : Supprimer l'image du serveur si elle existe et n'est pas l'image par défaut
    if(!empty($tprod['image']) && $tprod['image'] != "photos/default.jpg" && file_exists($tprod['image'])){
        unlink($tprod['image']);
    }

    // ÉTAPE 3 : Supprimer le produit de la base de données
    $rd = $c->prepare("DELETE FROM produit WHERE reference = ? AND id_producteur = ?");
    $r = $rd->execute([$tprod['reference'], $_SESSION['idu']]);

    if($r == false){
        header("Location: dashboardpro.php?msgerr=" . urlencode("Échec de la suppression du produit."));
        exit;
    } else {
        header("Location: dashboardpro.php?msgs=" . urlencode("Le produit \"" . $tprod['libelle'] . "\" a été supprimé définitivement."));
        exit;
    }

} catch(PDOException $e) { die("Erreur lors de la suppression : ".$e->getMessage()); }
?>
