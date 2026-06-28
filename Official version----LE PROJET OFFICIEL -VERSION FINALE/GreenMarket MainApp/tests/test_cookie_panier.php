<?php
// Simuler le panier
$panier = ['CHAT-GRI-001' => 2, 'FIG-CHE-002' => 1];
setcookie('panier', json_encode($panier), time()+3600, '/');
// Relire
$cookie = isset($_COOKIE['panier']) ? json_decode($_COOKIE['panier'], true) : [];
if($cookie === $panier) echo "Test panier cookie : Réussi\n";
else echo "Test panier cookie : Échec\n";