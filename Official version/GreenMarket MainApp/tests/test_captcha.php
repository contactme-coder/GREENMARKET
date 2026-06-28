<?php
session_start();
$captcha_code = rand(100000, 999999);
$_SESSION['captcha_code'] = $captcha_code;

// Simuler une soumission correcte
$_POST['captcha'] = $captcha_code;
$test1 = (trim($_POST['captcha']) == $_SESSION['captcha_code']);

// Simuler une soumission incorrecte
$_POST['captcha'] = 123456;
$test2 = (trim($_POST['captcha']) == $_SESSION['captcha_code']);

echo "Test CAPTCHA - Code généré : $captcha_code\n";
echo "Test 1 (correct) : " . ($test1 ? "RÉUSSI" : "ÉCHEC") . "\n";
echo "Test 2 (incorrect) : " . ($test2 ? "ÉCHEC" : "RÉUSSI (détecté)") . "\n";
?>