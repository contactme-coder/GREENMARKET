<?php
include("../prodconnex.php");
include("../preferences.php");

// Test 1 : Attribution des points fidélité
$client_id = 3; // Yassine
$montant = 150; // DH
$points = floor($montant);
echo "Test points : Montant $montant DH -> $points points attendus.\n";

// Test 2 : Badges producteur
$producteur_id = 2; // Alaiz
$ventes = 15; // simulé
$badges = [];
if($ventes >= 10) $badges[] = "Bronze";
if($ventes >= 50) $badges[] = "Argent";
if($ventes >= 100) $badges[] = "Or";
echo "Test badges : Ventes $ventes -> Badges obtenus : " . implode(", ", $badges) . "\n";

// Test 3 : Vérification du CAPTCHA
$captcha = 123456;
$_SESSION['captcha_code'] = 123456;
if($_POST['captcha'] == $_SESSION['captcha_code']) echo "Test CAPTCHA : Réussi\n";
else echo "Test CAPTCHA : Échec\n";