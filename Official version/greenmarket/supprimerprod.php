<?php
session_start();
if(!isset($_SESSION['idu']) || $_SESSION['roleu'] !== 'producteur'){ header("Location: authentification.php"); exit; }
include("prodconnex.php");
if(!isset($_GET['refp']) || empty(trim($_GET['refp']))){ header("Location: dashboardpro.php"); exit; }
try {
    $rs = $c->prepare("SELECT reference, libelle, image FROM produit WHERE reference = ? AND id_producteur = ?");
    $rs->execute([trim($_GET['refp']), $_SESSION['idu']]); $tprod = $rs->fetch(PDO::FETCH_ASSOC);
    if(!$tprod){ header("Location: dashboardpro.php?msgerr=" . urlencode("Produit introuvable ou accès refusé.")); exit; }
    if(!empty($tprod['image']) && $tprod['image'] != "photos/default.jpg" && file_exists($tprod['image'])) unlink($tprod['image']);
    $rd = $c->prepare("DELETE FROM produit WHERE reference = ? AND id_producteur = ?");
    $r = $rd->execute([$tprod['reference'], $_SESSION['idu']]);
    if($r == false){ header("Location: dashboardpro.php?msgerr=" . urlencode("Échec de la suppression du produit.")); exit; } else { header("Location: dashboardpro.php?msgs=" . urlencode("Le produit \"" . $tprod['libelle'] . "\" a été supprimé définitivement.")); exit; }
} catch(PDOException $e) { die("Erreur lors de la suppression : ".$e->getMessage()); }
?>