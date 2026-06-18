<?php
session_start();
if(!isset($_SESSION) || empty($_SESSION) || $_SESSION['roleu'] !== 'client'){
    header("Location: authentification.php");
    exit;
}
include("prodconnex.php");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>GreenMarket – Mon Espace Client</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght=0,300;0,400;0,600;1,300;1,400&family=Jost:wght=300;400;500;600&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
    :root {
      --ivory:     #f9f5ef;
      --cream:     #f2ebe0;
      --cream2:    #e8dfd0;
      --sand:      #d4c5ad;
      --olive:     #5c6b3a;
      --olive-mid: #748249;
      --olive-lt:  #a3b37a;
      --olive-bg:  #edf0e4;
      --brown:     #6b4c2a;
      --brown-lt:  #9a7455;
      --text:      #1e1e18;
      --text-mid:  #4a4a3a;
      --text-lt:   #8a8a74;
      --white:     #ffffff;
      --serif: 'Cormorant Garamond', Georgia, serif;
      --sans:  'Jost', sans-serif;
      --shadow-sm: 0 2px 12px rgba(60,50,20,0.07);
    }
    body {
      font-family: var(--sans);
      background: var(--ivory);
      color: var(--text);
      -webkit-font-smoothing: antialiased;
    }
    .navbar {
      position: fixed; top: 0; left: 0; right: 0; z-index: 200;
      display: flex; align-items: center; justify-content: space-between;
      padding: 0 60px; height: 72px;
      background: rgba(249,245,239,0.90);
      backdrop-filter: blur(16px);
      border-bottom: 1px solid rgba(212,197,173,0.35);
    }
    .logo { display: flex; align-items: center; gap: 9px; text-decoration: none; }
    .logo-leaf {
      width: 34px; height: 34px; background: var(--olive);
      border-radius: 50% 50% 50% 0; transform: rotate(-45deg);
      display: flex; align-items: center; justify-content: center;
    }
    .logo-leaf::after {
      content: ''; width: 14px; height: 14px; background: var(--ivory);
      border-radius: 50%; transform: rotate(45deg) translate(-1px, -1px);
    }
    .logo-text { font-family: var(--serif); font-size: 1.4rem; font-weight: 600; color: var(--olive); }
    .logo-text span { color: var(--brown); }
    .nav-actions { display: flex; align-items: center; gap: 20px; }
    .cart-btn { text-decoration: none; font-size: 1.2rem; }
    .dashboard-container { max-width: 1200px; margin: 0 auto; padding: 120px 20px 60px 20px; }
    .dashboard-header { margin-bottom: 40px; }
    .dashboard-header h1 { font-family: var(--serif); font-size: 2.5rem; font-weight: 300; }
    .profile-section, .orders-section { background: var(--white); padding: 30px; border-radius: 15px; margin-bottom: 30px; box-shadow: var(--shadow-sm); border: 1px solid rgba(212,197,173,0.2); }
    .profile-section h3, .orders-section h3 { font-family: var(--serif); font-size: 1.5rem; color: var(--brown); margin-bottom: 20px; }
    .profile-section p, .orders-section p { font-size: 0.95rem; color: var(--text-mid); line-height: 2; }
  </style>
</head>
<body>

<nav class="navbar" id="navbar">
  <a href="acceuil.php" class="logo">
    <div class="logo-leaf"></div>
    <div class="logo-text">Green<span>Market</span></div>
  </a>
  <div class="nav-actions">
    <a href="panier.php" class="cart-btn">🧺</a>
    <a href="deconnexion.php" style="color:#c95a5a; font-weight:bold; text-decoration:none; margin-left:20px;">Déconnexion</a>
  </div>
</nav>

<div class="dashboard-container">
  <div class="dashboard-header">
    <h1>Bienvenue, <span style="color:var(--olive);"><?= htmlspecialchars($_SESSION['nomu']) ?></span></h1>
    <p>Votre espace personnel bio.</p>
  </div>

  <div class="profile-section">
    <h3>Mes informations</h3>
    <p><strong>Nom :</strong> <?= htmlspecialchars($_SESSION['nomu']) ?></p>
    <p><strong>Statut du compte :</strong> Vérifié et Actif</p>
  </div>
  
  <div class="orders-section">
    <h3>Mon historique de commandes</h3>
    <p style="color:#777;">Vos dernières commandes s'afficheront ici une fois finalisées depuis le module de paiement.</p>
  </div>
</div>

</body>
</html>