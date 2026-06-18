<?php
session_start();
$err = [];
$msgs = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    extract($_POST);
    include("prodconnex.php");

    // 1. TRAITEMENT DE LA CONNEXION
    if(isset($conx)){
        if(!isset($loginEmail) || empty(trim($loginEmail))) $err['loginEmail'] = "Veuillez entrer votre email.";
        if(!isset($loginPassword) || empty($loginPassword)) $err['loginPassword'] = "Veuillez entrer un mot de passe.";
        
        if(empty($err)){
            try{
                $reqs = $c->prepare("SELECT * FROM compte WHERE email = ?");
                $reqs->execute([trim($loginEmail)]);
                $tuser = $reqs->fetch(PDO::FETCH_ASSOC);
                
                if(empty($tuser)){
                    $err['global_conx'] = "Email ou mot de passe incorrects.";
                } else {
                    if(password_verify($loginPassword, $tuser['motpasse'])) {
                        if($tuser['statut'] == 'suspendu') {
                            $err['global_conx'] = "Votre compte est suspendu.";
                        } elseif ($tuser['role'] == 'producteur' && $tuser['statut'] == 'en_attente') {
                            $err['global_conx'] = "Votre compte producteur est en attente de validation.";
                        } else {
                            $_SESSION['idu'] = $tuser['id'];
                            $_SESSION['nomu'] = $tuser['nom'];
                            $_SESSION['roleu'] = $tuser['role'];
                            
                            if($tuser['role'] == 'admin') header("Location: dashboradadmis.php");
                            elseif($tuser['role'] == 'producteur') header("Location: dashboardpro.php");
                            else header("Location: dashboard.php");
                            exit;
                        }
                    } else {
                        $err['global_conx'] = "Email ou mot de passe incorrects.";
                    }
                }
            } catch(PDOException $e) { die("Erreur : ".$e->getMessage()); }
        }
    }

    // 2. TRAITEMENT DE L'INSCRIPTION
    if(isset($insc)){
        if(!isset($regName) || empty(trim($regName))) $err['regName'] = "Veuillez entrer votre nom.";
        if(!isset($regEmail) || empty(trim($regEmail))) $err['regEmail'] = "Veuillez entrer un email.";
        if(!isset($regPassword) || empty($regPassword)) $err['regPassword'] = "Veuillez entrer un mot de passe.";
        
        if(empty($err)){
            try{
                $chk = $c->prepare("SELECT id FROM compte WHERE email = ?");
                $chk->execute([trim($regEmail)]);
                if($chk->rowCount() > 0){
                    $err['regEmail'] = "Cet email est déjà utilisé.";
                } else {
                    $hash = password_hash($regPassword, PASSWORD_ARGON2ID);
                    $statut = ($regRole == 'producteur') ? 'en_attente' : 'actif';
                    
                    $ri = $c->prepare("INSERT INTO compte (nom, email, motpasse, role, statut) VALUES (?, ?, ?, ?, ?)");
                    $r = $ri->execute([trim($regName), trim($regEmail), $hash, $regRole, $statut]);
                    
                    if($r == false) $err['global_insc'] = "Échec de l'inscription.";
                    else $msgs = "Compte créé avec succès ! Vous pouvez vous connecter.";
                }
            } catch(PDOException $e) { die("Erreur : ".$e->getMessage()); }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>GreenMarket – Connexion & Inscription</title>
  <style>
    /* Mets ici le style CSS complet de ton login.html d'origine */
    body { font-family: 'Jost', sans-serif; background: #f9f5ef; color: #1e1e18; padding: 50px; }
    .login-card { max-width: 450px; margin: 0 auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
    .form-group { margin-bottom: 15px; }
    .form-group input, .form-group select { width: 100%; padding: 12px; border: 1px solid #d4c5ad; border-radius: 8px; background: #f2ebe0; }
    .btn-submit { width: 100%; padding: 12px; background: #5c6b3a; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; }
    .error-msg { color: #c95a5a; font-size: 13px; margin-top: 5px; }
    .success-msg { color: #5c6b3a; font-size: 14px; text-align: center; margin-bottom: 15px; font-weight: bold; }
  </style>
</head>
<body>

<main class="login-container">
  <div class="login-card">
    <div class="login-header" style="text-align: center; margin-bottom: 25px;">
      <h2>GreenMarket</h2>
      <p>Artisan & Organic</p>
    </div>

    <?php if(!empty($msgs)) echo "<div class='success-msg'>".$msgs."</div>"; ?>
    <?php if(isset($err['global_conx'])) echo "<div class='error-msg' style='text-align:center; margin-bottom:15px;'>".$err['global_conx']."</div>"; ?>
    <?php if(isset($err['global_insc'])) echo "<div class='error-msg' style='text-align:center; margin-bottom:15px;'>".$err['global_insc']."</div>"; ?>

    <div id="loginPane" class="form-pane">
      <h3 style="margin-bottom:15px;">Connexion</h3>
      <form method="POST" action="authentification.php">
        <div class="form-group">
          <input type="email" name="loginEmail" placeholder="Email" value="<?php if(isset($loginEmail)) echo htmlspecialchars($loginEmail); ?>">
          <?php if(isset($err['loginEmail'])) echo "<div class='error-msg'>".$err['loginEmail']."</div>"; ?>
        </div>
        <div class="form-group">
          <input type="password" name="loginPassword" placeholder="Mot de passe">
          <?php if(isset($err['loginPassword'])) echo "<div class='error-msg'>".$err['loginPassword']."</div>"; ?>
        </div>
        <button type="submit" name="conx" class="btn-submit">Se connecter</button>
      </form>
    </div>

    <hr style="margin: 30px 0; border: 0; border-top: 1px solid #d4c5ad;">

    <div id="registerPane" class="form-pane">
      <h3 style="margin-bottom:15px;">Créer un compte</h3>
      <form method="POST" action="authentification.php">
        <div class="form-group">
          <input type="text" name="regName" placeholder="Nom complet" value="<?php if(isset($regName)) echo htmlspecialchars($regName); ?>">
          <?php if(isset($err['regName'])) echo "<div class='error-msg'>".$err['regName']."</div>"; ?>
        </div>
        <div class="form-group">
          <input type="email" name="regEmail" placeholder="Email" value="<?php if(isset($regEmail)) echo htmlspecialchars($regEmail); ?>">
          <?php if(isset($err['regEmail'])) echo "<div class='error-msg'>".$err['regEmail']."</div>"; ?>
        </div>
        <div class="form-group">
          <input type="password" name="regPassword" placeholder="Mot de passe">
          <?php if(isset($err['regPassword'])) echo "<div class='error-msg'>".$err['regPassword']."</div>"; ?>
        </div>
        <div class="form-group">
          <select name="regRole">
            <option value="client" <?php if(isset($regRole) && $regRole == 'client') echo 'selected'; ?>>Client</option>
            <option value="producteur" <?php if(isset($regRole) && $regRole == 'producteur') echo 'selected'; ?>>Producteur</option>
          </select>
        </div>
        <button type="submit" name="insc" class="btn-submit" style="background:#4a4a3a;">M'inscrire</button>
      </form>
    </div>
  </div>
</main>

<script src="script_auth.js"></script>
</body>
</html>