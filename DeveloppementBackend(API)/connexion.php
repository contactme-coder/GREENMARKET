<?php
require_once "db.php";
$err = [];
$msg = "";
$login_email = "";
$active_tab  = "login";

if($_SERVER['REQUEST_METHOD'] == "POST"){
    extract($_POST);

    if(isset($form_type) && $form_type == "login"){
        $active_tab  = "login";
        $login_email = isset($email) ? trim($email) : "";
        $password    = isset($password) ? trim($password) : "";

        if(empty($login_email))                                        $err['erremail'] = "L'email est obligatoire";
        elseif(!filter_var($login_email, FILTER_VALIDATE_EMAIL))       $err['erremail'] = "L'email n'est pas valide";
        if(empty($password))                                           $err['errpass']  = "Le mot de passe est obligatoire";

        if(empty($err)){
            $user = null; $role = null;

            $e = mysqli_real_escape_string($conn, $login_email);
            foreach(["admin","producteur","client"] as $table){
                $col = "id_".$table;
                $res = mysqli_query($conn, "SELECT * FROM $table WHERE email='$e'");
                if(mysqli_num_rows($res) > 0){
                    $row = mysqli_fetch_assoc($res);
                    if(password_verify($password, $row['mot_de_passe'])){
                        if(isset($row['statut']) && $row['statut'] == 'suspendu'){
                            $err['errpass'] = "Votre compte est suspendu";
                        } elseif(isset($row['statut']) && $row['statut'] == 'en_attente'){
                            $err['errpass'] = "Votre compte est en attente de validation";
                        } else {
                            $user = $row; $role = $table;
                        }
                        break;
                    } else {
                        $err['errpass'] = "Email ou mot de passe incorrect";
                        break;
                    }
                }
            }

            if($user && empty($err)){
                $id_key = "id_".$role;
                $_SESSION['user'] = [
                    'id'     => $user[$id_key],
                    'nom'    => $user['nom'],
                    'prenom' => $user['prenom'],
                    'email'  => $user['email'],
                    'role'   => $role
                ];
                if($role == "admin")       header("Location: dashboradadmis.php");
                elseif($role == "producteur") header("Location: dashboardpro.php");
                else                       header("Location: dashboard.php");
                exit();
            } elseif(empty($err)){
                $err['errpass'] = "Email ou mot de passe incorrect";
            }
        }
    }

    if(isset($form_type) && $form_type == "register"){
        $active_tab = "register";
        $nom        = isset($nom)       ? htmlspecialchars(trim($nom))     : "";
        $prenom     = isset($prenom)    ? htmlspecialchars(trim($prenom))  : "";
        $reg_email  = isset($reg_email) ? trim($reg_email)                 : "";
        $reg_pass   = isset($reg_pass)  ? $reg_pass                        : "";
        $reg_cpass  = isset($reg_cpass) ? $reg_cpass                       : "";
        $role       = isset($role)      ? $role                            : "client";

        if(empty($nom))                                               $err['errnom']    = "Le nom est obligatoire";
        if(empty($prenom))                                            $err['errprenom'] = "Le prénom est obligatoire";
        if(empty($reg_email))                                         $err['erregemail']= "L'email est obligatoire";
        elseif(!filter_var($reg_email, FILTER_VALIDATE_EMAIL))        $err['erregemail']= "L'email n'est pas valide";
        if(empty($reg_pass))                                          $err['errregpass']= "Le mot de passe est obligatoire";
        elseif(strlen($reg_pass) < 8)                                 $err['errregpass']= "8 caractères minimum";
        if($reg_cpass != $reg_pass)                                   $err['errcpass']  = "Les mots de passe ne correspondent pas";

        if(empty($err)){
            $e    = mysqli_real_escape_string($conn, $reg_email);
            $hash = password_hash($reg_pass, PASSWORD_DEFAULT);
            $date = date("Y-m-d H:i:s");

            if($role == "producteur"){
                $chk = mysqli_query($conn, "SELECT id_producteur FROM producteur WHERE email='$e'");
                if(mysqli_num_rows($chk) > 0){
                    $err['erregemail'] = "Cet email est déjà utilisé";
                } else {
                    $n = mysqli_real_escape_string($conn, $nom);
                    $p = mysqli_real_escape_string($conn, $prenom);
                    mysqli_query($conn, "INSERT INTO producteur (nom,prenom,email,mot_de_passe,role,statut,date_inscription)
                                        VALUES ('$n','$p','$e','$hash','producteur','en_attente','$date')");
                    $msg = "Inscription réussie ! Votre compte producteur est en attente de validation.";
                    $active_tab = "login";
                }
            } else {
                $chk = mysqli_query($conn, "SELECT id_client FROM client WHERE email='$e'");
                if(mysqli_num_rows($chk) > 0){
                    $err['erregemail'] = "Cet email est déjà utilisé";
                } else {
                    $ra = mysqli_query($conn, "SELECT id_admin FROM admin LIMIT 1");
                    $id_admin = ($ra && mysqli_num_rows($ra) > 0) ? mysqli_fetch_assoc($ra)['id_admin'] : 1;
                    $rp = mysqli_query($conn, "SELECT id_producteur FROM producteur WHERE statut='actif' LIMIT 1");
                    $id_prod  = ($rp && mysqli_num_rows($rp) > 0) ? mysqli_fetch_assoc($rp)['id_producteur'] : 1;
                    $n = mysqli_real_escape_string($conn, $nom);
                    $p = mysqli_real_escape_string($conn, $prenom);
                    mysqli_query($conn, "INSERT INTO client (nom,prenom,email,mot_de_passe,role,statut,date_inscription,id_producteur,id_admin)
                                        VALUES ('$n','$p','$e','$hash','client','actif','$date','$id_prod','$id_admin')");
                    $msg = "Inscription réussie ! Vous pouvez vous connecter.";
                    $active_tab = "login";
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <title>GreenMarket – Connexion & Inscription</title>
  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=Jost:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <!-- Font Awesome 6 -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    /* -----------------------------------------------
       RESET & ROOT (same theme)
    ------------------------------------------------ */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    :root {
      --ivory: #f9f5ef;
      --cream: #f2ebe0;
      --cream2: #e8dfd0;
      --sand: #d4c5ad;
      --olive: #5c6b3a;
      --olive-mid: #748249;
      --olive-lt: #a3b37a;
      --olive-bg: #edf0e4;
      --brown: #6b4c2a;
      --brown-lt: #9a7455;
      --text: #1e1e18;
      --text-mid: #4a4a3a;
      --text-lt: #8a8a74;
      --white: #ffffff;
      --serif: 'Cormorant Garamond', Georgia, serif;
      --sans: 'Jost', sans-serif;
      --r-sm: 12px;
      --r-md: 20px;
      --r-lg: 32px;
      --r-xl: 48px;
      --shadow-sm: 0 2px 12px rgba(60, 50, 20, 0.07);
      --shadow-md: 0 8px 32px rgba(60, 50, 20, 0.10);
      --shadow-lg: 0 24px 64px rgba(60, 50, 20, 0.13);
    }

    body {
      font-family: var(--sans);
      background: linear-gradient(135deg, var(--ivory) 0%, var(--cream) 100%);
      color: var(--text);
      overflow-x: hidden;
      -webkit-font-smoothing: antialiased;
      min-height: 100vh;
    }

    /* ---------- NAVBAR ---------- */
    .navbar {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      z-index: 200;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 60px;
      height: 72px;
      background: rgba(249, 245, 239, 0.92);
      backdrop-filter: blur(16px);
      border-bottom: 1px solid rgba(212, 197, 173, 0.35);
      transition: box-shadow 0.3s;
    }
    .navbar.scrolled {
      box-shadow: var(--shadow-sm);
    }
    .logo {
      display: flex;
      align-items: center;
      gap: 9px;
      text-decoration: none;
    }
    .logo-leaf {
      width: 34px;
      height: 34px;
      background: var(--olive);
      border-radius: 50% 50% 50% 0;
      transform: rotate(-45deg);
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }
    .logo-leaf::after {
      content: '';
      width: 14px;
      height: 14px;
      background: var(--ivory);
      border-radius: 50%;
      transform: rotate(45deg) translate(-1px, -1px);
    }
    .logo-text {
      font-family: var(--serif);
      font-size: 1.4rem;
      font-weight: 600;
      color: var(--olive);
      letter-spacing: 0.02em;
    }
    .logo-text span {
      color: var(--brown);
    }
    .nav-links {
      display: flex;
      align-items: center;
      gap: 36px;
      list-style: none;
    }
    .nav-links a {
      font-size: 0.82rem;
      font-weight: 500;
      color: var(--text-mid);
      text-decoration: none;
      letter-spacing: 0.1em;
      text-transform: uppercase;
      transition: color 0.2s;
      position: relative;
    }
    .nav-links a::after {
      content: '';
      position: absolute;
      bottom: -4px;
      left: 0;
      right: 0;
      height: 1px;
      background: var(--olive);
      transform: scaleX(0);
      transform-origin: left;
      transition: transform 0.3s;
    }
    .nav-links a:hover {
      color: var(--olive);
    }
    .nav-links a:hover::after {
      transform: scaleX(1);
    }
    .nav-actions {
      display: flex;
      align-items: center;
      gap: 16px;
    }
    .cart-btn {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      border: 1px solid var(--sand);
      background: transparent;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      transition: all 0.2s;
      color: var(--text-mid);
    }
    .cart-btn:hover {
      background: var(--cream);
      border-color: var(--olive-lt);
    }
    .cart-badge {
      position: absolute;
      top: -4px;
      right: -4px;
      width: 18px;
      height: 18px;
      background: var(--olive);
      color: white;
      font-size: 0.6rem;
      font-weight: 600;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    /* ----- MAIN LOGIN SECTION ----- */
    .login-container {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 120px 24px 80px;
    }

    .login-card {
      max-width: 480px;
      width: 100%;
      background: var(--white);
      border-radius: 32px;
      box-shadow: var(--shadow-lg);
      overflow: hidden;
      animation: fadeSlideUp 0.4s ease-out;
    }

    @keyframes fadeSlideUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .login-header {
      background: linear-gradient(135deg, var(--olive) 0%, var(--olive-mid) 100%);
      padding: 32px;
      text-align: center;
      color: white;
    }
    .login-header .logo-leaf {
      margin: 0 auto 16px;
      background: white;
    }
    .login-header .logo-leaf::after {
      background: var(--olive);
    }
    .login-header h2 {
      font-family: var(--serif);
      font-size: 1.8rem;
      font-weight: 500;
      margin-bottom: 8px;
    }
    .login-header p {
      font-size: 0.85rem;
      opacity: 0.85;
    }

    .tabs {
      display: flex;
      border-bottom: 1px solid var(--cream2);
      background: var(--white);
    }
    .tab-btn {
      flex: 1;
      padding: 16px;
      background: none;
      border: none;
      font-family: var(--sans);
      font-size: 0.9rem;
      font-weight: 600;
      color: var(--text-mid);
      cursor: pointer;
      transition: all 0.2s;
      position: relative;
    }
    .tab-btn.active {
      color: var(--olive);
    }
    .tab-btn.active::after {
      content: '';
      position: absolute;
      bottom: -1px;
      left: 0;
      right: 0;
      height: 2px;
      background: var(--olive);
    }
    .tab-btn:hover:not(.active) {
      color: var(--olive-mid);
    }

    .form-container {
      padding: 32px;
    }
    .form-pane {
      display: none;
    }
    .form-pane.active {
      display: block;
      animation: fadeIn 0.3s ease;
    }
    @keyframes fadeIn {
      from { opacity: 0; transform: translateX(10px); }
      to { opacity: 1; transform: translateX(0); }
    }

    .form-group {
      margin-bottom: 24px;
    }
    .form-group label {
      display: block;
      font-size: 0.8rem;
      font-weight: 500;
      color: var(--text-mid);
      margin-bottom: 8px;
    }
    .form-group input {
      width: 100%;
      padding: 14px 16px;
      border: 1.5px solid var(--cream2);
      border-radius: 16px;
      font-family: var(--sans);
      font-size: 0.9rem;
      transition: all 0.2s;
      background: var(--white);
    }
    .form-group input:focus {
      outline: none;
      border-color: var(--olive);
      box-shadow: 0 0 0 3px rgba(92, 107, 58, 0.1);
    }
    .form-group input.error {
      border-color: #e74c3c;
    }

    .input-icon {
      position: relative;
    }
    .input-icon i {
      position: absolute;
      left: 16px;
      top: 50%;
      transform: translateY(-50%);
      color: var(--text-lt);
      font-size: 1rem;
    }
    .input-icon input {
      padding-left: 44px;
    }

    .role-selector {
      display: flex;
      gap: 16px;
      margin-top: 8px;
    }
    .role-option {
      flex: 1;
      padding: 12px;
      border: 1.5px solid var(--cream2);
      border-radius: 16px;
      text-align: center;
      cursor: pointer;
      transition: all 0.2s;
      background: var(--white);
    }
    .role-option.selected {
      border-color: var(--olive);
      background: var(--olive-bg);
      color: var(--olive);
    }
    .role-option i {
      font-size: 1.2rem;
      margin-bottom: 6px;
      display: block;
    }
    .role-option span {
      font-size: 0.8rem;
      font-weight: 500;
    }

    .checkbox-group {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 24px;
    }
    .checkbox-group input {
      width: 18px;
      height: 18px;
      accent-color: var(--olive);
    }
    .checkbox-group label {
      margin-bottom: 0;
      font-size: 0.8rem;
      color: var(--text-mid);
    }

    .btn-submit {
      width: 100%;
      padding: 14px;
      background: var(--olive);
      color: white;
      border: none;
      border-radius: 40px;
      font-family: var(--sans);
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s;
      margin-bottom: 20px;
    }
    .btn-submit:hover {
      background: var(--olive-mid);
      transform: translateY(-2px);
      box-shadow: var(--shadow-sm);
    }

    .demo-accounts {
      background: var(--olive-bg);
      border-radius: 20px;
      padding: 20px;
      margin-top: 24px;
    }
    .demo-accounts h4 {
      font-size: 0.8rem;
      color: var(--olive);
      margin-bottom: 12px;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .demo-list {
      display: flex;
      flex-direction: column;
      gap: 10px;
    }
    .demo-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-size: 0.75rem;
      padding: 8px 12px;
      background: var(--white);
      border-radius: 12px;
      cursor: pointer;
      transition: all 0.2s;
    }
    .demo-item:hover {
      background: var(--cream);
      transform: translateX(4px);
    }
    .demo-email {
      font-family: monospace;
      color: var(--olive);
      font-weight: 500;
    }
    .demo-badge {
      background: var(--olive-lt);
      padding: 2px 8px;
      border-radius: 20px;
      font-size: 0.65rem;
      color: white;
    }

    .forgot-password {
      text-align: center;
      margin-top: 16px;
    }
    .forgot-password a {
      color: var(--text-lt);
      font-size: 0.75rem;
      text-decoration: none;
    }
    .forgot-password a:hover {
      color: var(--olive);
    }

    .separator {
      text-align: center;
      margin: 20px 0;
      position: relative;
    }
    .separator::before {
      content: '';
      position: absolute;
      left: 0;
      right: 0;
      top: 50%;
      height: 1px;
      background: var(--cream2);
    }
    .separator span {
      background: var(--white);
      padding: 0 16px;
      position: relative;
      font-size: 0.75rem;
      color: var(--text-lt);
    }

    /* Toast Notification */
    .toast-msg {
      position: fixed;
      bottom: 30px;
      left: 50%;
      transform: translateX(-50%);
      background: #1f3b1c;
      color: white;
      padding: 12px 28px;
      border-radius: 60px;
      font-size: 0.85rem;
      z-index: 1200;
      display: flex;
      align-items: center;
      gap: 12px;
      opacity: 0;
      transition: opacity 0.25s;
      pointer-events: none;
      box-shadow: var(--shadow-md);
    }
    .toast-msg.show {
      opacity: 1;
    }

    footer {
      background: var(--text);
      padding: 40px 60px 32px;
      color: rgba(255, 255, 255, 0.5);
      text-align: center;
      font-size: 0.8rem;
    }

    @media (max-width: 768px) {
      .navbar {
        padding: 0 24px;
      }
      .nav-links {
        display: none;
      }
      .login-container {
        padding: 100px 16px 60px;
      }
      .form-container {
        padding: 24px;
      }
      .demo-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 6px;
      }
    }
  </style>
</head>
<body>

<nav class="navbar" id="navbar">
  <a href="acceuil.html" class="logo" id="homeLink">
    <div class="logo-leaf"></div>
    <div class="logo-text">Green<span>Market</span></div>
  </a>
  <ul class="nav-links">
    <li><a href="acceuil.html" id="navHome">Accueil</a></li>
    <li><a href="boutique.html" id="navShops">Boutiques</a></li>
    <li><a href="catalogue.html" id="navCatalog">Catalogue</a></li>
    <li><a href="catalogue.html" id="navReviews">Avis</a></li>
  </ul>
  <div class="nav-actions">
    <button class="cart-btn" aria-label="Cart">
      <i class="fas fa-shopping-basket"></i>
      <span class="cart-badge" id="cartCounter">0</span>
    </button>
  </div>
</nav>

<main class="login-container">
  <div class="login-card">
    <div class="login-header">
      <div class="logo-leaf"></div>
      <h2>GreenMarket</h2>
      <p>Artisan & Organic</p>
    </div>

    <div class="tabs">
      <button class="tab-btn <?php echo $active_tab=='login' ? 'active' : ''; ?>" data-tab="login">Connexion</button>
      <button class="tab-btn <?php echo $active_tab=='register' ? 'active' : ''; ?>" data-tab="register">Inscription</button>
    </div>

    <div class="form-container">
      <?php if($msg !== "") echo "<div style='color:#2b6e2f;background:#edf0e4;padding:12px 16px;border-radius:12px;font-size:0.85rem;margin-bottom:16px;'>".$msg."</div>";?>
      <!-- Login Form -->
      <div id="loginPane" class="form-pane <?php echo $active_tab=='login' ? 'active' : ''; ?>">
        <form id="loginForm" method="POST" action="connexion.php">
          <div class="form-group input-icon">
            <i class="fas fa-envelope"></i>
            <input type="email" id="loginEmail" name="email" placeholder="Email" value="<?php echo $login_email;?>" required>
            <input type="hidden" name="form_type" value="login">
            <?php if(isset($err['erremail'])) echo "<div style='color:#c0392b;font-size:0.75rem;margin-top:4px;'>".$err['erremail']."</div>";?>
          </div>
          <div class="form-group input-icon">
            <i class="fas fa-lock"></i>
            <input type="password" id="loginPassword" name="password" placeholder="Mot de passe" required>
            <?php if(isset($err['errpass'])) echo "<div style='color:#c0392b;font-size:0.75rem;margin-top:4px;'>".$err['errpass']."</div>";?>
          </div>
          <div class="checkbox-group">
            <input type="checkbox" id="rememberMe">
            <label for="rememberMe">Se souvenir de moi</label>
          </div>
          <button type="submit" class="btn-submit">Se connecter</button>
        </form>

        <div class="separator">
          <span>Comptes de démonstration</span>
        </div>

        <div class="demo-accounts">
          <h4><i class="fas fa-flask"></i> Tester l'application</h4>
          <div class="demo-list">
            <div class="demo-item" data-email="client@test.com" data-password="password" data-role="client">
              <span class="demo-email">client@test.com</span>
              <span class="demo-badge">Client</span>
            </div>
            <div class="demo-item" data-email="producer@test.com" data-password="password" data-role="producer">
              <span class="demo-email">producer@test.com</span>
              <span class="demo-badge">Producteur</span>
            </div>
            <div class="demo-item" data-email="admin@test.com" data-password="password" data-role="admin">
              <span class="demo-email">admin@test.com</span>
              <span class="demo-badge">Administrateur</span>
            </div>
          </div>
        </div>

        <div class="forgot-password">
          <a href="#">Mot de passe oublié ?</a>
        </div>
      </div>

      <!-- Register Form -->
      <div id="registerPane" class="form-pane <?php echo $active_tab=='register' ? 'active' : ''; ?>">
        <form id="registerForm" method="POST" action="connexion.php">
          <div class="form-group input-icon">
            <i class="fas fa-user"></i>
            <input type="text" id="regName" name="nom" placeholder="Nom" required>
            <?php if(isset($err['errnom'])) echo "<div style='color:#c0392b;font-size:0.75rem;margin-top:4px;'>".$err['errnom']."</div>";?>
            <input type="text" name="prenom" placeholder="Prénom" style="width:100%;padding:14px 16px;border:1.5px solid var(--cream2);border-radius:16px;font-family:var(--sans);font-size:0.9rem;margin-top:12px;" required>
            <?php if(isset($err['errprenom'])) echo "<div style='color:#c0392b;font-size:0.75rem;margin-top:4px;'>".$err['errprenom']."</div>";?>
          </div>
          <div class="form-group input-icon">
            <i class="fas fa-envelope"></i>
            <input type="email" id="regEmail" name="reg_email" placeholder="Email" required>
            <?php if(isset($err['erregemail'])) echo "<div style='color:#c0392b;font-size:0.75rem;margin-top:4px;'>".$err['erregemail']."</div>";?>
          </div>
          <div class="form-group input-icon">
            <i class="fas fa-lock"></i>
            <input type="password" id="regPassword" name="reg_pass" placeholder="Mot de passe (min 8 caractères)" required>
            <?php if(isset($err['errregpass'])) echo "<div style='color:#c0392b;font-size:0.75rem;margin-top:4px;'>".$err['errregpass']."</div>";?>
          </div>
          <div class="form-group input-icon">
            <i class="fas fa-check-circle"></i>
            <input type="password" id="regConfirmPassword" name="reg_cpass" placeholder="Confirmer le mot de passe" required>
            <?php if(isset($err['errcpass'])) echo "<div style='color:#c0392b;font-size:0.75rem;margin-top:4px;'>".$err['errcpass']."</div>";?>
          </div>
          
          <div class="form-group">
            <label>Je souhaite m'inscrire en tant que</label>
            <div class="role-selector" id="roleSelector">
              <div class="role-option selected" data-role="client">
                <i class="fas fa-user"></i>
                <span>Client</span>
              </div>
              <div class="role-option" data-role="producer">
                <i class="fas fa-store"></i>
                <span>Producteur</span>
              </div>
            </div>
            <input type="hidden" id="regRole" name="role" value="client">
            <input type="hidden" name="form_type" value="register">
          </div>

          <div class="checkbox-group">
            <input type="checkbox" id="acceptTerms" required>
            <label for="acceptTerms">J'accepte les <a href="#" style="color: var(--olive);">conditions d'utilisation</a></label>
          </div>

          <button type="submit" class="btn-submit">Créer mon compte</button>
        </form>

        <div class="separator">
          <span>Déjà inscrit ?</span>
        </div>
        <button class="btn-submit" id="switchToLogin" style="background: transparent; color: var(--olive); border: 2px solid var(--olive); margin-top: 0;">Se connecter</button>
      </div>
    </div>
  </div>
</main>

<footer>
  <p><i class="fas fa-leaf"></i> GreenMarket – Soutenons l'agriculture locale et les circuits courts.</p>
  <p style="margin-top: 10px;">&copy; 2026 GreenMarket – Tous droits réservés</p>
</footer>

<div id="toastMsg" class="toast-msg">
  <i class="fas fa-check-circle"></i> <span id="toastText">Connexion réussie !</span>
</div>

<script>
  // Helper functions
  function showToast(message, isError = false) {
    const toast = document.getElementById('toastMsg');
    const toastSpan = document.getElementById('toastText');
    toastSpan.innerHTML = isError ? `<i class="fas fa-exclamation-triangle"></i> ${message}` : `<i class="fas fa-check-circle"></i> ${message}`;
    toast.style.background = isError ? '#c0392b' : '#1f3b1c';
    toast.classList.add('show');
    setTimeout(() => {
      toast.classList.remove('show');
      toast.style.background = '#1f3b1c';
    }, 3000);
  }

  function updateCartUI() {
    let cart = JSON.parse(localStorage.getItem('greenmarket_cart')) || [];
    const totalQty = cart.reduce((sum, item) => sum + item.quantity, 0);
    const badge = document.getElementById('cartCounter');
    if (badge) badge.innerText = totalQty;
  }

  // Tab switching
  const tabs = document.querySelectorAll('.tab-btn');
  const loginPane = document.getElementById('loginPane');
  const registerPane = document.getElementById('registerPane');

  tabs.forEach(tab => {
    tab.addEventListener('click', () => {
      const tabId = tab.dataset.tab;
      tabs.forEach(t => t.classList.remove('active'));
      tab.classList.add('active');
      
      if (tabId === 'login') {
        loginPane.classList.add('active');
        registerPane.classList.remove('active');
      } else {
        loginPane.classList.remove('active');
        registerPane.classList.add('active');
      }
    });
  });

  // Role selector for registration
  const roleOptions = document.querySelectorAll('.role-option');
  const regRoleInput = document.getElementById('regRole');
  
  roleOptions.forEach(option => {
    option.addEventListener('click', () => {
      roleOptions.forEach(opt => opt.classList.remove('selected'));
      option.classList.add('selected');
      regRoleInput.value = option.dataset.role;
    });
  });

  // Demo account login
  const demoItems = document.querySelectorAll('.demo-item');
  demoItems.forEach(item => {
    item.addEventListener('click', () => {
      const email = item.dataset.email;
      const password = item.dataset.password;
      const role = item.dataset.role;
      
      document.getElementById('loginEmail').value = email;
      document.getElementById('loginPassword').value = password;
      
      document.getElementById('loginEmail').value = email;
      document.getElementById('loginPassword').value = password;
      document.getElementById('loginForm').submit();
    });
  });

  // Login form submission — handled by PHP

  // Register form submission
  document.getElementById('registerForm').addEventListener('submit', (e) => {
    e.preventDefault();
    
    const name = document.getElementById('regName').value;
    const email = document.getElementById('regEmail').value;
    const password = document.getElementById('regPassword').value;
    const confirmPassword = document.getElementById('regConfirmPassword').value;
    const acceptTerms = document.getElementById('acceptTerms').checked;
    const role = document.getElementById('regRole').value;
    
    if (!name || !email || !password) {
      showToast('Veuillez remplir tous les champs', true);
      return;
    }
    
    if (password !== confirmPassword) {
      showToast('Les mots de passe ne correspondent pas', true);
      return;
    }
    
    if (!acceptTerms) {
      showToast('Veuillez accepter les conditions d\'utilisation', true);
      return;
    }
    
    // Store user
    localStorage.setItem('greenmarket_user', JSON.stringify({
      name: name,
      email: email,
      role: role,
      loggedIn: true
    }));
    
    showToast(`Compte créé avec succès ! Bienvenue ${name}`);
    
    setTimeout(() => {
      if(role==='producer') window.location.href='dashboardpro.html';
      else window.location.href='dashboard.html';
    }, 1500);
  });

  // Switch to login from register
  document.getElementById('switchToLogin').addEventListener('click', () => {
    document.querySelector('.tab-btn[data-tab="login"]').click();
  });

  // Navbar scroll effect
  const navbar = document.getElementById('navbar');
  window.addEventListener('scroll', () => {
    if (window.scrollY > 20) navbar.classList.add('scrolled');
    else navbar.classList.remove('scrolled');
  });

  // Navigation links (simulated)
  const navLinks = {
    home: ['#', 'Accueil'],
    shops: ['#', 'Boutiques'],
    catalog: ['#', 'Catalogue'],
    reviews: ['#', 'Avis']
  };
  
  document.getElementById('navHome')?.addEventListener('click', () => { window.location.href='acceuil.html'; });
  document.getElementById('navShops')?.addEventListener('click', () => { window.location.href='boutique.html'; });
  document.getElementById('navCatalog')?.addEventListener('click', () => { window.location.href='catalogue.html'; });
  document.getElementById('navReviews')?.addEventListener('click', () => { window.location.href='catalogue.html'; });
  document.getElementById('homeLink')?.addEventListener('click', () => { window.location.href='acceuil.html'; });



  updateCartUI();
</script>
</body>
</html>