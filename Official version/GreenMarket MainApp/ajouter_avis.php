<?php
include("preferences.php");
if(!isset($_SESSION['idu']) || $_SESSION['roleu'] != 'client') {
    header("Location: authentification.php");
    exit;
}
include("prodconnex.php");

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reference'], $_POST['note'], $_POST['commentaire'])) {
    $ref = $_POST['reference'];
    $note = intval($_POST['note']);
    $commentaire = trim($_POST['commentaire']);
    if(strlen($commentaire) > 1500) {
        // Redirection avec erreur
        header("Location: catalogue.php?err=avis_trop_long");
        exit;
    }
    if($note >= 1 && $note <= 5 && !empty($commentaire) && !empty($ref)) {
        $ins = $c->prepare("INSERT INTO avis (id_client, reference_produit, note, commentaire, statut) VALUES (?, ?, ?, ?, 'en_attente')");
        $ins->execute([$_SESSION['idu'], $ref, $note, $commentaire]);
    }
}
header("Location: catalogue.php");
exit;