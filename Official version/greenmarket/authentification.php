<?php
include("preferences.php");
$err = [];
$msgs = "";

if($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['lang_select']) && !isset($_POST['theme_select'])){
    extract($_POST, EXTR_SKIP);
    include("prodconnex.php");

    // --- CONNEXION ---
    if(isset($conx)){
        if(!isset($loginEmail) || empty(trim($loginEmail))) $err['loginEmail'] = "Veuillez entrer votre email.";
        if(!isset($loginPassword) || empty($loginPassword)) $err['loginPassword'] = "Veuillez entrer un mot de passe.";
        if(empty($err)){
            try {
                $reqs = $c->prepare("SELECT * FROM compte WHERE email = ?");
                $reqs->execute([trim($loginEmail)]);
                $tuser = $reqs->fetch(PDO::FETCH_ASSOC);
                if(empty($tuser)){
                    $err['global_conx'] = "Email ou mot de passe incorrects.";
                } else {
                    if(password_verify($loginPassword, $tuser['motpasse'])) {
                        if($tuser['statut'] == 'suspendu') $err['global_conx'] = "Votre compte est suspendu.";
                        elseif ($tuser['role'] == 'producteur' && $tuser['statut'] == 'en_attente') $err['global_conx'] = "Votre compte producteur est en attente de validation.";
                        else {
                            $_SESSION['idu'] = $tuser['id'];
                            $_SESSION['nomu'] = $tuser['nom'];
                            $_SESSION['roleu'] = $tuser['role'];
                            if(isset($autofillcookies)) {
                                setcookie('loginEmail', trim($loginEmail), time() + (8*24*60*60));
                                setcookie('loginPassword', $loginPassword, time() + (8*24*60*60));
                            }
                            if($tuser['role'] == 'admin') header("Location: dashboradadmis.php");
                            elseif($tuser['role'] == 'producteur') header("Location: dashboardpro.php");
                            else header("Location: dashboard.php");
                            exit;
                        }
                    } else {
                        $err['global_conx'] = "Email ou mot de passe incorrects.";
                    }
                }
            } catch(PDOException $e) { $err['global_conx'] = "Erreur technique."; }
        }
    }

    // --- INSCRIPTION ---
    if(isset($insc)){
        if(!isset($regName) || empty(trim($regName))) $err['regName'] = "Veuillez entrer votre nom.";
        if(!isset($regEmail) || empty(trim($regEmail))) $err['regEmail'] = "Veuillez entrer un email.";
        if(!isset($regPassword) || empty($regPassword)) $err['regPassword'] = "Veuillez entrer un mot de passe.";
        if(!isset($question_secrete) || empty(trim($question_secrete))) $err['question'] = "Veuillez choisir une question secrète.";
        if(!isset($reponse_secrete) || empty(trim($reponse_secrete))) $err['reponse'] = "Veuillez entrer la réponse à votre question secrète.";
        
        $captcha_answer = isset($_POST['captcha']) ? intval($_POST['captcha']) : 0;
        if($captcha_answer != $_SESSION['captcha_math']) $err['captcha'] = "Résultat du CAPTCHA incorrect.";

        if(empty($err)){
            try {
                $chk = $c->prepare("SELECT id FROM compte WHERE email = ?");
                $chk->execute([trim($regEmail)]);
                if($chk->rowCount() > 0){
                    $err['regEmail'] = "Cet email est déjà utilisé.";
                } else {
                    $hash = password_hash($regPassword, PASSWORD_ARGON2ID);
                    $statut = ($regRole == 'producteur') ? 'en_attente' : 'actif';
                    $ri = $c->prepare("INSERT INTO compte (nom, email, motpasse, role, statut, question_secrete, reponse_secrete) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $r = $ri->execute([trim($regName), trim($regEmail), $hash, $regRole, $statut, trim($question_secrete), trim($reponse_secrete)]);
                    if($r == false) $err['global_insc'] = "Échec de l'inscription.";
                    else $msgs = ($regRole == 'producteur') ? "Compte producteur créé ! En attente de validation." : "Compte créé avec succès ! Vous pouvez vous connecter.";
                }
            } catch(PDOException $e) { $err['global_insc'] = "Erreur technique : ".$e->getMessage(); }
        }
    }
}

$a = rand(1, 10);
$b = rand(1, 10);
$_SESSION['captcha_math'] = $a + $b;
?>
<!DOCTYPE html>
<html lang="<?= $lang_active ?>" dir="<?= $lang_active === 'ar' ? 'rtl' : 'ltr' ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>GreenMarket – <?= tr('connexion') ?></title>
  <link rel="icon" type="image/svg+xml" href="favicon.svg">
  <style>
    *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
    body { font-family:sans-serif; background:#f9f5ef; color:#1e1e18; display:flex; flex-direction:column; justify-content:center; align-items:center; min-height:100vh; padding:20px; transition:0.3s; }
    body.dark { background:#121212 !important; color:#f9f5ef !important; }
    body.dark .auth-container { background:#1e1e1e !important; box-shadow:none !important; }
    body.dark .auth-box { border-color:#333 !important; }
    body.dark h2 { color:#a3c07a !important; border-bottom-color:#333 !important; }
    body.dark input, body.dark select { background:#2a2a2a !important; color:#f9f5ef !important; border-color:#444 !important; }
    body.dark .success-msg { background:#1e3320 !important; color:#a3c07a !important; }
    body.dark .global-error { background:#3a1f1f !important; color:#ff9b9b !important; border-color:#5c2b2b !important; }
    .top-selectors { margin-bottom:20px; }
    .auth-container { background:#fff; padding:40px; border-radius:12px; box-shadow:0 4px 20px rgba(0,0,0,0.06); width:100%; max-width:900px; display:flex; gap:40px; transition:0.3s; }
    .auth-box { flex:1; display:flex; flex-direction:column; }
    h2 { color:#5c6b3a; margin-bottom:20px; font-size:1.6rem; border-bottom:2px solid #e8dfd0; padding-bottom:10px; }
    .form-group { margin-bottom:15px; }
    input, select { width:100%; padding:12px; border:1px solid #e8dfd0; border-radius:8px; background:#f9f5ef; color:#1e1e18; font-size:14px; outline:none; }
    input:focus, select:focus { border-color:#5c6b3a; }
    .remember-me { display:flex; align-items:center; gap:8px; margin-bottom:15px; }
    .remember-me input[type="checkbox"] { width:auto; padding:0; margin:0; accent-color:#5c6b3a; cursor:pointer; }
    .remember-me label { font-size:14px; color:inherit; cursor:pointer; }
    .btn-submit { background:#5c6b3a; color:#fff; border:none; padding:12px; border-radius:8px; font-weight:bold; cursor:pointer; font-size:14px; transition:0.2s; margin-top:10px; width:100%; }
    .btn-submit:hover { background:#4a572e; }
    .error-msg { color:#c95a5a; font-size:12px; margin-top:5px; font-weight:500; }
    .success-msg { color:#5c6b3a; background:#e8f0e0; padding:12px; border-radius:8px; margin-bottom:20px; font-size:14px; font-weight:bold; text-align:center; }
    .global-error { color:#c95a5a; background:#fdf2f2; padding:12px; border-radius:8px; margin-bottom:20px; font-size:14px; font-weight:bold; text-align:center; border:1px solid #fbcbcb; }
    .captcha-row { display:flex; align-items:center; gap:10px; margin-bottom:15px; }
    .captcha-row input { width:80px; }
    @media (max-width:768px) { .auth-container { flex-direction:column; gap:30px; } }
  </style>
</head>
<body class="<?= $theme_actif ?>">
<div class="top-selectors"><?php afficher_selecteurs(); ?></div>
<div class="auth-container">
  <div class="auth-box">
    <h2><?= tr('connexion') ?></h2>
    <?php if(isset($err['global_conx'])): ?><div class="global-error"><?= htmlspecialchars($err['global_conx']) ?></div><?php endif; ?>
    <form method="POST" action="">
      <div class="form-group"><input type="email" name="loginEmail" placeholder="Email" value="<?= htmlspecialchars(isset($_COOKIE['loginEmail']) ? $_COOKIE['loginEmail'] : (isset($loginEmail) ? $loginEmail : '')) ?>"></div>
      <div class="form-group"><input type="password" name="loginPassword" placeholder="Mot de passe" value="<?= htmlspecialchars(isset($_COOKIE['loginPassword']) ? $_COOKIE['loginPassword'] : '') ?>"></div>
      <div class="remember-me"><input type="checkbox" name="autofillcookies" id="autofillcookies" value="1" <?= isset($_COOKIE['loginEmail']) ? 'checked' : '' ?>><label for="autofillcookies">Se souvenir</label></div>
      <button type="submit" name="conx" class="btn-submit"><?= tr('submit_conx') ?></button>
      <a href="reset_password.php" style="display:block; text-align:center; margin-top:10px; font-size:13px; color:#5c6b3a;">Mot de passe oublié ?</a>
    </form>
  </div>
  <div class="auth-box" style="border-left:1px solid #e8dfd0; padding-left:40px;">
    <h2><?= tr('inscription') ?></h2>
    <?php if(!empty($msgs)): ?><div class="success-msg"><?= htmlspecialchars($msgs) ?></div><?php endif; ?>
    <form method="POST" action="">
      <div class="form-group"><input type="text" name="regName" placeholder="<?= htmlspecialchars(tr('fullname')) ?>" value="<?= htmlspecialchars(isset($regName) ? $regName : '') ?>"></div>
      <div class="form-group"><input type="email" name="regEmail" placeholder="Email" value="<?= htmlspecialchars(isset($regEmail) ? $regEmail : '') ?>"></div>
      <div class="form-group"><input type="password" name="regPassword" placeholder="Mot de passe"></div>
      <div class="form-group"><select name="regRole"><option value="client" <?= (isset($regRole) && $regRole == 'client') ? 'selected' : '' ?>><?= tr('role_client') ?></option><option value="producteur" <?= (isset($regRole) && $regRole == 'producteur') ? 'selected' : '' ?>><?= tr('role_prod') ?></option></select></div>
      <div class="form-group"><input type="text" name="question_secrete" placeholder="Votre question secrète" value="<?= htmlspecialchars(isset($question_secrete)?$question_secrete:'') ?>"></div>
      <div class="form-group"><input type="text" name="reponse_secrete" placeholder="Réponse à la question" value="<?= htmlspecialchars(isset($reponse_secrete)?$reponse_secrete:'') ?>"></div>
      <div class="captcha-row"><span>Combien font <?= $a ?> + <?= $b ?> ?</span><input type="number" name="captcha" placeholder="?" required></div>
      <button type="submit" name="insc" class="btn-submit" style="background:#4a4a3a;"><?= tr('submit_insc') ?></button>
    </form>
  </div>
</div>
<script src="script_auth.js"></script>
</body>
</html>