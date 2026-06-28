<?php
include("preferences.php");
if(!isset($_SESSION['idu'])) { header("Location: authentification.php"); exit; }
$err = [];
$msgs = "";
include("prodconnex.php");

$req = $c->prepare("SELECT * FROM compte WHERE id = ?");
$req->execute([$_SESSION['idu']]);
$user = $req->fetch(PDO::FETCH_ASSOC);

if($_SERVER['REQUEST_METHOD'] == "POST") {
    extract($_POST);
    $update_fields = [];
    $params = [];
    
    if(isset($email) && trim($email) !== $user['email']) {
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)) $err['email'] = "Email invalide.";
        else {
            $chk = $c->prepare("SELECT id FROM compte WHERE email = ? AND id != ?");
            $chk->execute([$email, $_SESSION['idu']]);
            if($chk->rowCount() > 0) $err['email'] = "Cet email est déjà utilisé.";
            else { $update_fields[] = "email = ?"; $params[] = $email; }
        }
    }
    if(isset($new_password) && !empty($new_password)) {
        if(strlen($new_password) < 6) $err['password'] = "Le mot de passe doit contenir au moins 6 caractères.";
        else { $update_fields[] = "motpasse = ?"; $params[] = password_hash($new_password, PASSWORD_ARGON2ID); }
    }
    if(isset($question_secrete) && !empty($question_secrete) && isset($reponse_secrete) && !empty($reponse_secrete)) {
        $update_fields[] = "question_secrete = ?"; $params[] = $question_secrete;
        $update_fields[] = "reponse_secrete = ?"; $params[] = $reponse_secrete;
    }
    if(empty($err) && !empty($update_fields)) {
        $params[] = $_SESSION['idu'];
        $sql = "UPDATE compte SET " . implode(", ", $update_fields) . " WHERE id = ?";
        $up = $c->prepare($sql);
        if($up->execute($params)) { $msgs = "Profil mis à jour avec succès."; $req->execute([$_SESSION['idu']]); $user = $req->fetch(PDO::FETCH_ASSOC); }
        else { $err['global'] = "Erreur lors de la mise à jour."; }
    }
}
?>
<!DOCTYPE html>
<html lang="<?= $lang_active ?>">
<head><meta charset="UTF-8"><title>Mon Profil</title>
<link rel="icon" type="image/svg+xml" href="favicon.svg">
<style>
body { font-family: sans-serif; background: #f9f5ef; padding-top: 90px; transition:0.3s; }
body.dark { background:#121212; color:#f9f5ef; }
body.dark .card { background:#1e1e1e; }
.container { max-width:600px; margin:0 auto; padding:20px; }
.card { background:white; border-radius:12px; padding:30px; box-shadow:0 2px 10px rgba(0,0,0,0.05); }
h2 { color:#5c6b3a; margin-top:0; }
.form-group { margin-bottom:20px; }
label { display:block; font-weight:bold; margin-bottom:5px; }
input[type="text"], input[type="email"], input[type="password"] { width:100%; padding:10px; border:1px solid #e8dfd0; border-radius:6px; background: #fcfcfc; }
.btn { background:#5c6b3a; color:white; padding:10px 20px; border:none; border-radius:6px; cursor:pointer; font-weight:bold; }
.btn:hover { background:#4a5d2e; }
.err { color:#c95a5a; font-size:13px; margin-bottom:10px; }
.success { color:#5c6b3a; background:#edf0e4; padding:10px; border-radius:6px; margin-bottom:20px; }
</style>
</head>
<body class="<?= $theme_actif ?>">
<div class="container">
    <div class="card">
        <h2>👤 Mon Profil</h2>
        <?php if($msgs): ?><div class="success"><?= htmlspecialchars($msgs) ?></div><?php endif; ?>
        <form method="POST">
            <div class="form-group"><label>Nom</label><input type="text" value="<?= htmlspecialchars($user['nom']) ?>" disabled style="background:#eee;"></div>
            <div class="form-group"><label>Email *</label><input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>"><?php if(isset($err['email'])) echo "<div class='err'>".$err['email']."</div>"; ?></div>
            <div class="form-group"><label>Nouveau mot de passe (laisser vide pour ne pas changer)</label><input type="password" name="new_password" placeholder="Min. 6 caractères"><?php if(isset($err['password'])) echo "<div class='err'>".$err['password']."</div>"; ?></div>
            <div class="form-group"><label>Question secrète (pour récupération)</label><input type="text" name="question_secrete" placeholder="Ex: Quel est mon plat préféré ?" value="<?= htmlspecialchars($user['question_secrete'] ?? '') ?>"></div>
            <div class="form-group"><label>Réponse à la question secrète</label><input type="text" name="reponse_secrete" placeholder="Votre réponse" value="<?= htmlspecialchars($user['reponse_secrete'] ?? '') ?>"></div>
            <button type="submit" class="btn">Enregistrer les modifications</button>
            <a href="<?= $_SESSION['roleu'] === 'admin' ? 'dashboradadmis.php' : ($_SESSION['roleu'] === 'producteur' ? 'dashboardpro.php' : 'dashboard.php') ?>" style="display:inline-block; margin-top:15px; color:#c95a5a; text-decoration:none; font-weight:bold;">← Retour</a>
        </form>
    </div>
</div>
</body>
</html>