<?php
include("preferences.php");
if(!isset($_SESSION['idu'])) { header("Location: authentification.php"); exit; }
$err = []; $msgs = ""; include("prodconnex.php");
$req = $c->prepare("SELECT * FROM compte WHERE id = ?"); $req->execute([$_SESSION['idu']]); $user = $req->fetch(PDO::FETCH_ASSOC);

if($_SERVER['REQUEST_METHOD'] == "POST") {
    extract($_POST); $update_fields = []; $params = [];
    if(isset($email) && trim($email) !== $user['email']) {
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)) $err['email'] = "Email invalide.";
        else { $chk = $c->prepare("SELECT id FROM compte WHERE email = ? AND id != ?"); $chk->execute([$email, $_SESSION['idu']]); if($chk->rowCount() > 0) $err['email'] = "Cet email est déjà utilisé."; else { $update_fields[] = "email = ?"; $params[] = $email; } }
    }
    if(isset($new_password) && !empty($new_password)) { if(strlen($new_password) < 6) $err['password'] = "6 caractères minimum."; else { $update_fields[] = "motpasse = ?"; $params[] = password_hash($new_password, PASSWORD_ARGON2ID); } }
    if(isset($question_secrete) && !empty($question_secrete) && isset($reponse_secrete) && !empty($reponse_secrete)) { $update_fields[] = "question_secrete = ?"; $params[] = $question_secrete; $update_fields[] = "reponse_secrete = ?"; $params[] = $reponse_secrete; }
    if(empty($err) && !empty($update_fields)) { $params[] = $_SESSION['idu']; $sql = "UPDATE compte SET " . implode(", ", $update_fields) . " WHERE id = ?"; $up = $c->prepare($sql); if($up->execute($params)) { $msgs = "Profil mis à jour avec succès."; $req->execute([$_SESSION['idu']]); $user = $req->fetch(PDO::FETCH_ASSOC); } else { $err['global'] = "Erreur lors de la mise à jour."; } }
}
include("header.php");
?>
<style>.profile-container { max-width:600px; margin: 100px auto 40px; padding:20px; } .card { background:var(--white); border-radius:12px; padding:30px; box-shadow:0 2px 10px rgba(0,0,0,0.05); border:1px solid var(--cream2); } h2 { color:var(--olive); } input, select { width:100%; padding:10px; border:1px solid var(--cream2); border-radius:6px; margin-bottom:15px; } .btn { background:var(--olive); color:white; padding:10px 20px; border:none; border-radius:6px; cursor:pointer; font-weight:bold; } .err { color:#c95a5a; } .success { color:var(--olive); background:#edf0e4; padding:10px; border-radius:6px; margin-bottom:20px; }</style>
<div class="profile-container"><div class="card"><h2>👤 <?= tr('profile_btn') ?></h2><?php if($msgs): ?><div class="success"><?= htmlspecialchars($msgs) ?></div><?php endif; ?><form method="POST">
<label>Nom</label><input type="text" value="<?= htmlspecialchars($user['nom']) ?>" disabled>
<label>Email *</label><input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>"><?php if(isset($err['email'])) echo "<div class='err'>".$err['email']."</div>"; ?>
<label>Nouveau mot de passe (laisser vide pour ne pas changer)</label><input type="password" name="new_password" placeholder="Min. 6 caractères"><?php if(isset($err['password'])) echo "<div class='err'>".$err['password']."</div>"; ?>
<label>Question secrète</label><input type="text" name="question_secrete" placeholder="Ex: Quel est mon plat préféré ?" value="<?= htmlspecialchars($user['question_secrete'] ?? '') ?>">
<label>Réponse</label><input type="text" name="reponse_secrete" placeholder="Votre réponse" value="<?= htmlspecialchars($user['reponse_secrete'] ?? '') ?>">
<button type="submit" class="btn">Enregistrer</button>
<a href="<?= $_SESSION['roleu'] === 'admin' ? 'dashboradadmis.php' : ($_SESSION['roleu'] === 'producteur' ? 'dashboardpro.php' : 'dashboard.php') ?>" style="display:block; margin-top:15px; color:#c95a5a; text-decoration:none; font-weight:bold;">← Retour</a>
</form></div></div>
<?php include("footer.php"); ?>