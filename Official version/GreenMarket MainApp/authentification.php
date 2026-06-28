<?php
include("preferences.php");
$err = [];
$msgs = "";

// Gestion du CAPTCHA : on le génère seulement si ce n'est pas un POST ou si la session est vide
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['captcha_code'])) {
    $captcha_code = rand(100000, 999999);
    $_SESSION['captcha_code'] = $captcha_code;
} else {
    // Si on est en POST, on récupère le code déjà en session pour ne pas l'écraser
    $captcha_code = $_SESSION['captcha_code'] ?? rand(100000, 999999);
    // On s'assure que la session contient bien un code
    if (!isset($_SESSION['captcha_code'])) {
        $_SESSION['captcha_code'] = $captcha_code;
    }
}

if($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['lang_select']) && !isset($_POST['theme_select'])){
    extract($_POST, EXTR_SKIP);
    include("prodconnex.php");

    // --- TRAITEMENT CONNEXION ---
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
            } catch(PDOException $e) { $err['global_conx'] = "Erreur technique lors de la connexion."; }
        }
    }

    // --- TRAITEMENT INSCRIPTION ---
    if(isset($insc)){
        if(!isset($regName) || empty(trim($regName))) $err['regName'] = "Veuillez entrer votre nom.";
        if(!isset($regEmail) || empty(trim($regEmail))) $err['regEmail'] = "Veuillez entrer un email.";
        if(!isset($regPassword) || empty($regPassword)) $err['regPassword'] = "Veuillez entrer un mot de passe.";
        if(!isset($question_secrete) || empty(trim($question_secrete))) $err['question'] = "Veuillez choisir une question secrète.";
        if(!isset($reponse_secrete) || empty(trim($reponse_secrete))) $err['reponse'] = "Veuillez entrer la réponse à votre question secrète.";
        
        // Vérification CAPTCHA
        if(trim($_POST['captcha']) != $_SESSION['captcha_code']) {
            $err['captcha'] = "Code CAPTCHA incorrect.";
            // On régénère un nouveau CAPTCHA pour la prochaine tentative
            $_SESSION['captcha_code'] = rand(100000, 999999);
        }

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
                    if($r == false) $err['global_insc'] = "Échec de l'inscription. Veuillez réessayer.";
                    else $msgs = ($regRole == 'producteur') ? "Compte producteur créé ! En attente de validation par l'administrateur." : "Compte créé avec succès ! Vous pouvez maintenant vous connecter.";
                }
            } catch(PDOException $e) { $err['global_insc'] = "Erreur technique lors de l'inscription : ".$e->getMessage(); }
        }
    }
}

// On récupère le code CAPTCHA courant pour l'affichage (après un éventuel POST avec erreur)
$captcha_code = $_SESSION['captcha_code'] ?? rand(100000, 999999);

include("header.php");
?>
<style>
.auth-container { max-width: 900px; margin: 120px auto 40px; display:flex; gap:40px; background:var(--white); border-radius:16px; padding:40px; border:1px solid var(--cream2); }
.auth-box { flex:1; }
@media(max-width:768px){ .auth-container { flex-direction:column; } }
.captcha-row { display:flex; align-items:center; gap:15px; margin-bottom:15px; }
.captcha-row input { width:150px; padding:8px; border:1px solid var(--cream2); border-radius:6px; }
.captcha-row strong { font-size:1.2rem; letter-spacing:2px; color:var(--olive); background:var(--ivory); padding:5px 10px; border-radius:5px; }
.error-msg { color:#c95a5a; font-size:12px; margin-top:5px; font-weight:500; }
.success-msg { color:#5c6b3a; background:#edf0e4; padding:12px; border-radius:8px; margin-bottom:20px; font-size:14px; font-weight:bold; text-align:center; }
.global-error { color:#c95a5a; background:#fdf2f2; padding:12px; border-radius:8px; margin-bottom:20px; font-size:14px; font-weight:bold; text-align:center; border:1px solid #fbcbcb; }
</style>

<div class="auth-container">
  <div class="auth-box">
    <h2><?= tr('connexion') ?></h2>
    <?php if(isset($err['global_conx'])): ?><div class="global-error"><?= htmlspecialchars($err['global_conx']) ?></div><?php endif; ?>
    <form method="POST" action="">
      <div class="form-group">
        <input type="email" name="loginEmail" placeholder="Email" value="<?= htmlspecialchars(isset($_COOKIE['loginEmail']) ? $_COOKIE['loginEmail'] : (isset($loginEmail) ? $loginEmail : '')) ?>" style="width:100%; padding:10px; border:1px solid var(--cream2); border-radius:6px; margin-bottom:10px;">
        <?php if(isset($err['loginEmail'])): ?><div class="error-msg"><?= htmlspecialchars($err['loginEmail']) ?></div><?php endif; ?>
      </div>
      <div class="form-group">
        <input type="password" name="loginPassword" placeholder="Mot de passe" value="<?= htmlspecialchars(isset($_COOKIE['loginPassword']) ? $_COOKIE['loginPassword'] : '') ?>" style="width:100%; padding:10px; border:1px solid var(--cream2); border-radius:6px; margin-bottom:10px;">
        <?php if(isset($err['loginPassword'])): ?><div class="error-msg"><?= htmlspecialchars($err['loginPassword']) ?></div><?php endif; ?>
      </div>
      <div class="remember-me" style="display:flex; align-items:center; gap:8px; margin-bottom:15px;">
        <input type="checkbox" name="autofillcookies" id="autofillcookies" value="1" <?= isset($_COOKIE['loginEmail']) ? 'checked' : '' ?> style="accent-color:var(--olive);">
        <label for="autofillcookies" style="font-size:14px; cursor:pointer;">Se souvenir la prochaine fois</label>
      </div>
      <button type="submit" name="conx" style="width:100%; padding:12px; background:var(--olive); color:white; border:none; border-radius:8px; font-weight:bold; cursor:pointer; font-size:14px; transition:0.2s;"><?= tr('submit_conx') ?></button>
      <a href="reset_password.php" style="display:block; text-align:center; margin-top:10px; font-size:13px; color:var(--olive);">Mot de passe oublié ?</a>
    </form>
  </div>

  <div class="auth-box" style="border-left:1px solid var(--cream2); padding-left:40px;">
    <h2><?= tr('inscription') ?></h2>
    <?php if(!empty($msgs)): ?><div class="success-msg"><?= htmlspecialchars($msgs) ?></div><?php endif; ?>
    <?php if(isset($err['global_insc'])): ?><div class="global-error"><?= htmlspecialchars($err['global_insc']) ?></div><?php endif; ?>
    <form method="POST" action="">
      <div class="form-group">
        <input type="text" name="regName" placeholder="<?= htmlspecialchars(tr('fullname')) ?>" value="<?= htmlspecialchars(isset($regName) ? $regName : '') ?>" style="width:100%; padding:10px; border:1px solid var(--cream2); border-radius:6px; margin-bottom:10px;">
        <?php if(isset($err['regName'])): ?><div class="error-msg"><?= htmlspecialchars($err['regName']) ?></div><?php endif; ?>
      </div>
      <div class="form-group">
        <input type="email" name="regEmail" placeholder="Email" value="<?= htmlspecialchars(isset($regEmail) ? $regEmail : '') ?>" style="width:100%; padding:10px; border:1px solid var(--cream2); border-radius:6px; margin-bottom:10px;">
        <?php if(isset($err['regEmail'])): ?><div class="error-msg"><?= htmlspecialchars($err['regEmail']) ?></div><?php endif; ?>
      </div>
      <div class="form-group">
        <input type="password" name="regPassword" placeholder="Mot de passe" style="width:100%; padding:10px; border:1px solid var(--cream2); border-radius:6px; margin-bottom:10px;">
        <?php if(isset($err['regPassword'])): ?><div class="error-msg"><?= htmlspecialchars($err['regPassword']) ?></div><?php endif; ?>
      </div>
      <div class="form-group">
        <select name="regRole" style="width:100%; padding:10px; border:1px solid var(--cream2); border-radius:6px; margin-bottom:10px;">
          <option value="client" <?= (isset($regRole) && $regRole == 'client') ? 'selected' : '' ?>><?= tr('role_client') ?></option>
          <option value="producteur" <?= (isset($regRole) && $regRole == 'producteur') ? 'selected' : '' ?>><?= tr('role_prod') ?></option>
        </select>
      </div>
      <div class="form-group">
        <input type="text" name="question_secrete" placeholder="Votre question secrète" value="<?= htmlspecialchars(isset($question_secrete)?$question_secrete:'') ?>" style="width:100%; padding:10px; border:1px solid var(--cream2); border-radius:6px; margin-bottom:10px;">
        <?php if(isset($err['question'])): ?><div class="error-msg"><?= htmlspecialchars($err['question']) ?></div><?php endif; ?>
      </div>
      <div class="form-group">
        <input type="text" name="reponse_secrete" placeholder="Réponse à la question" value="<?= htmlspecialchars(isset($reponse_secrete)?$reponse_secrete:'') ?>" style="width:100%; padding:10px; border:1px solid var(--cream2); border-radius:6px; margin-bottom:10px;">
        <?php if(isset($err['reponse'])): ?><div class="error-msg"><?= htmlspecialchars($err['reponse']) ?></div><?php endif; ?>
      </div>
      <div class="captcha-row">
        <span>Code de sécurité :</span> <strong><?= $captcha_code ?></strong>
        <input type="text" name="captcha" placeholder="Saisir le code" required>
      </div>
      <?php if(isset($err['captcha'])): ?><div class="error-msg"><?= htmlspecialchars($err['captcha']) ?></div><?php endif; ?>
      <button type="submit" name="insc" style="width:100%; padding:12px; background:var(--terracotta); color:white; border:none; border-radius:8px; font-weight:bold; cursor:pointer; font-size:14px; transition:0.2s; margin-top:10px;"><?= tr('submit_insc') ?></button>
    </form>
  </div>
</div>

<?php include("footer.php"); ?>