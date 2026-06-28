<?php
include("preferences.php");
$err = []; $step = 1; $email = ""; $secret_question = "";

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    include("prodconnex.php");
    if(isset($_POST['email'])) {
        $email = trim($_POST['email']);
        $req = $c->prepare("SELECT id, question_secrete, reponse_secrete FROM compte WHERE email = ?");
        $req->execute([$email]); $user = $req->fetch(PDO::FETCH_ASSOC);
        if($user && !empty($user['question_secrete'])) {
            $step = 2; $secret_question = $user['question_secrete'];
            $_SESSION['reset_user_id'] = $user['id'];
            $_SESSION['reset_secret_answer'] = $user['reponse_secrete'];
        } else { $err['email'] = "Email introuvable ou aucune question secrète définie."; }
    }
    elseif(isset($_POST['secret_answer'])) {
        $answer = trim($_POST['secret_answer']);
        if(hash_equals($_SESSION['reset_secret_answer'], $answer)) { $step = 3; }
        else { $err['answer'] = "Réponse incorrecte."; }
    }
    elseif(isset($_POST['new_password'])) {
        $new_pass = $_POST['new_password'];
        if(strlen($new_pass) < 6) { $err['password'] = "Le mot de passe doit contenir au moins 6 caractères."; }
        else {
            $hash = password_hash($new_pass, PASSWORD_ARGON2ID);
            $up = $c->prepare("UPDATE compte SET motpasse = ? WHERE id = ?");
            $up->execute([$hash, $_SESSION['reset_user_id']]);
            unset($_SESSION['reset_user_id'], $_SESSION['reset_secret_answer']);
            header("Location: authentification.php?msgs=" . urlencode("Mot de passe réinitialisé avec succès !")); exit;
        }
    }
}
include("header.php");
?>
<style>.reset-card { max-width: 400px; margin: 120px auto 40px; background: var(--white); padding: 40px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); border:1px solid var(--cream2); } h2 { color: var(--olive); } input { width:100%; padding:10px; margin-bottom:15px; border:1px solid var(--cream2); border-radius:6px; } .btn { background:var(--olive); color:white; padding:10px 20px; border:none; border-radius:6px; cursor:pointer; width:100%; font-weight:bold; } .err { color:#c95a5a; font-size:13px; margin-bottom:10px; }</style>
<div class="reset-card">
<h2>🔐 Réinitialisation</h2>
<?php if($step == 1): ?><form method="POST"><p>Saisissez votre email pour retrouver votre question secrète.</p><?php if(isset($err['email'])) echo "<div class='err'>".$err['email']."</div>"; ?><input type="email" name="email" placeholder="votre@email.com" required value="<?= htmlspecialchars($email) ?>"><button type="submit" class="btn">Suivant</button></form>
<?php elseif($step == 2): ?><form method="POST"><p><strong>Question secrète :</strong><br><?= htmlspecialchars($secret_question) ?></p><?php if(isset($err['answer'])) echo "<div class='err'>".$err['answer']."</div>"; ?><input type="text" name="secret_answer" placeholder="Votre réponse" required><button type="submit" class="btn">Vérifier</button></form>
<?php elseif($step == 3): ?><form method="POST"><p>Entrez votre nouveau mot de passe.</p><?php if(isset($err['password'])) echo "<div class='err'>".$err['password']."</div>"; ?><input type="password" name="new_password" placeholder="Nouveau mot de passe (min. 6)" required><button type="submit" class="btn">Réinitialiser</button></form><?php endif; ?>
</div>
<?php include("footer.php"); ?>