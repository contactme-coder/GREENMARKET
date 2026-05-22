<?php
require_once "db.php";
if(!isset($_SESSION['user'])){
    header("Location: connexion.php");
    exit();
}
if($_SESSION['user']['role'] != "client"){
    header("Location: connexion.php");
    exit();
}
$user = $_SESSION['user'];
$initiales = strtoupper(substr($user['prenom'],0,1).substr($user['nom'],0,1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <title>GreenMarket – Customer Dashboard | My Account</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=Jost:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
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
      --olive: #4a5d2e;
      --olive-mid: #5e7340;
      --olive-lt: #8fa06c;
      --olive-bg: #eef2e4;
      --brown: #6b4c2a;
      --text: #1e1e18;
      --text-mid: #4a4a3a;
      --text-lt: #8a8a74;
      --white: #ffffff;
      --success: #2b6e2f;
      --warning: #d4a373;
      --serif: 'Cormorant Garamond', Georgia, serif;
      --sans: 'Jost', sans-serif;
      --shadow-sm: 0 2px 12px rgba(60, 50, 20, 0.06);
      --shadow-md: 0 8px 32px rgba(60, 50, 20, 0.10);
      --shadow-lg: 0 24px 60px rgba(60, 50, 20, 0.12);
    }

    body {
      font-family: var(--sans);
      background: var(--ivory);
      color: var(--text);
      line-height: 1.4;
    }

    /* Navbar */
    .navbar {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      z-index: 200;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 72px;
      height: 80px;
      background: rgba(249, 245, 239, 0.96);
      backdrop-filter: blur(18px);
      border-bottom: 1px solid rgba(212, 197, 173, 0.4);
      transition: all 0.3s;
    }
    .navbar.scrolled {
      box-shadow: var(--shadow-sm);
    }
    .logo {
      display: flex;
      align-items: center;
      gap: 8px;
      text-decoration: none;
    }
    .logo-leaf {
      width: 36px;
      height: 36px;
      background: var(--olive);
      border-radius: 50% 50% 50% 0;
      transform: rotate(-45deg);
    }
    .logo-text {
      font-family: var(--serif);
      font-size: 1.5rem;
      font-weight: 600;
      color: var(--olive);
      letter-spacing: 0.02em;
    }
    .logo-text span {
      color: var(--brown);
    }
    .nav-links {
      display: flex;
      gap: 42px;
      list-style: none;
    }
    .nav-links a {
      font-size: 0.8rem;
      font-weight: 500;
      color: var(--text-mid);
      text-decoration: none;
      letter-spacing: 0.12em;
      text-transform: uppercase;
      transition: color 0.2s;
    }
    .nav-links a:hover, .nav-links a.active {
      color: var(--olive);
    }
    .user-menu {
      display: flex;
      align-items: center;
      gap: 20px;
    }
    .cart-icon {
      position: relative;
      font-size: 1.2rem;
      color: var(--text-mid);
      cursor: pointer;
    }
    .avatar {
      width: 42px;
      height: 42px;
      border-radius: 50%;
      background: var(--olive);
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-weight: 600;
      cursor: pointer;
    }

    /* Dashboard Layout */
    .dashboard-container {
      max-width: 1400px;
      margin: 0 auto;
      padding: 120px 40px 60px;
    }
    .dashboard-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 40px;
      flex-wrap: wrap;
      gap: 20px;
    }
    .welcome h1 {
      font-family: var(--serif);
      font-size: 2rem;
      font-weight: 500;
      margin-bottom: 6px;
    }
    .welcome p {
      color: var(--text-lt);
      font-size: 0.9rem;
    }
    .date-badge {
      background: white;
      padding: 8px 20px;
      border-radius: 60px;
      font-size: 0.8rem;
      box-shadow: var(--shadow-sm);
      color: var(--olive);
    }

    /* Stats Cards */
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 24px;
      margin-bottom: 48px;
    }
    .stat-card {
      background: white;
      border-radius: 24px;
      padding: 24px;
      box-shadow: var(--shadow-sm);
      border: 1px solid rgba(212, 197, 173, 0.25);
      transition: transform 0.2s;
    }
    .stat-card:hover {
      transform: translateY(-3px);
      box-shadow: var(--shadow-md);
    }
    .stat-title {
      font-size: 0.7rem;
      letter-spacing: 0.1em;
      text-transform: uppercase;
      color: var(--text-lt);
      margin-bottom: 12px;
    }
    .stat-value {
      font-family: var(--serif);
      font-size: 2.2rem;
      font-weight: 600;
      color: var(--olive);
      margin-bottom: 6px;
    }
    .stat-trend {
      font-size: 0.7rem;
      color: var(--success);
    }

    /* Dashboard Grid */
    .dashboard-grid {
      display: grid;
      grid-template-columns: 1fr 1.2fr;
      gap: 32px;
      margin-bottom: 48px;
    }
    /* Recent Orders */
    .recent-orders {
      background: white;
      border-radius: 28px;
      padding: 28px;
      box-shadow: var(--shadow-sm);
      border: 1px solid rgba(212, 197, 173, 0.25);
    }
    .section-title {
      font-family: var(--serif);
      font-size: 1.3rem;
      font-weight: 600;
      margin-bottom: 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .section-title a {
      font-size: 0.75rem;
      color: var(--olive);
      text-decoration: none;
    }
    .order-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 16px 0;
      border-bottom: 1px solid var(--cream2);
    }
    .order-item:last-child {
      border-bottom: none;
    }
    .order-info h4 {
      font-weight: 600;
      margin-bottom: 4px;
    }
    .order-date {
      font-size: 0.7rem;
      color: var(--text-lt);
    }
    .order-status {
      font-size: 0.7rem;
      padding: 4px 12px;
      border-radius: 60px;
      background: var(--olive-bg);
      color: var(--olive);
    }
    .status-delivered {
      background: #e8f5e9;
      color: var(--success);
    }
    .status-shipped {
      background: #fff3e0;
      color: var(--warning);
    }
    .order-total {
      font-weight: 600;
    }

    /* Favorites / Wishlist */
    .favorites {
      background: white;
      border-radius: 28px;
      padding: 28px;
      box-shadow: var(--shadow-sm);
      border: 1px solid rgba(212, 197, 173, 0.25);
    }
    .fav-item {
      display: flex;
      gap: 16px;
      align-items: center;
      padding: 14px 0;
      border-bottom: 1px solid var(--cream2);
    }
    .fav-item:last-child {
      border-bottom: none;
    }
    .fav-img {
      width: 60px;
      height: 60px;
      border-radius: 16px;
      object-fit: cover;
    }
    .fav-info {
      flex: 1;
    }
    .fav-info h4 {
      font-weight: 600;
      margin-bottom: 4px;
    }
    .fav-price {
      color: var(--olive);
      font-weight: 500;
    }
    .fav-actions button {
      background: none;
      border: none;
      color: var(--text-lt);
      cursor: pointer;
      margin-left: 12px;
    }
    .add-cart-fav {
      background: var(--olive);
      color: white;
      border: none;
      padding: 6px 16px;
      border-radius: 60px;
      font-size: 0.7rem;
      cursor: pointer;
    }

    /* Profile & Settings */
    .profile-section {
      background: white;
      border-radius: 28px;
      padding: 28px;
      box-shadow: var(--shadow-sm);
      border: 1px solid rgba(212, 197, 173, 0.25);
      margin-bottom: 32px;
    }
    .profile-header {
      display: flex;
      gap: 24px;
      align-items: center;
      margin-bottom: 28px;
      flex-wrap: wrap;
    }
    .profile-avatar-large {
      width: 80px;
      height: 80px;
      border-radius: 50%;
      background: var(--olive);
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-size: 2rem;
      font-weight: 500;
    }
    .profile-info h3 {
      font-family: var(--serif);
      font-size: 1.5rem;
      margin-bottom: 6px;
    }
    .profile-info p {
      color: var(--text-lt);
    }
    .info-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
      margin-bottom: 24px;
    }
    .info-field label {
      font-size: 0.7rem;
      text-transform: uppercase;
      color: var(--text-lt);
      letter-spacing: 0.08em;
      display: block;
      margin-bottom: 4px;
    }
    .info-field p {
      font-weight: 500;
    }
    .edit-btn {
      background: transparent;
      border: 1px solid var(--sand);
      padding: 8px 24px;
      border-radius: 60px;
      font-family: var(--sans);
      cursor: pointer;
      transition: all 0.2s;
    }
    .edit-btn:hover {
      background: var(--olive);
      color: white;
      border-color: var(--olive);
    }

    /* Order Tracking Timeline */
    .tracking-section {
      background: white;
      border-radius: 28px;
      padding: 28px;
      box-shadow: var(--shadow-sm);
      border: 1px solid rgba(212, 197, 173, 0.25);
    }
    .timeline {
      display: flex;
      justify-content: space-between;
      margin-top: 24px;
      position: relative;
    }
    .timeline::before {
      content: '';
      position: absolute;
      top: 24px;
      left: 10%;
      right: 10%;
      height: 2px;
      background: var(--cream2);
      z-index: 0;
    }
    .timeline-step {
      text-align: center;
      flex: 1;
      position: relative;
      z-index: 1;
    }
    .step-dot {
      width: 48px;
      height: 48px;
      background: white;
      border: 2px solid var(--cream2);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 12px;
      background: var(--ivory);
    }
    .step-dot.active {
      border-color: var(--olive);
      background: var(--olive);
      color: white;
    }
    .step-dot.completed {
      border-color: var(--success);
      background: var(--success);
      color: white;
    }
    .step-label {
      font-size: 0.7rem;
      font-weight: 500;
    }
    .step-label.active {
      color: var(--olive);
      font-weight: 600;
    }

    /* Responsive */
    @media (max-width: 1024px) {
      .navbar { padding: 0 32px; }
      .dashboard-container { padding: 100px 24px 40px; }
      .stats-grid { grid-template-columns: repeat(2, 1fr); }
      .dashboard-grid { grid-template-columns: 1fr; }
      .info-grid { grid-template-columns: 1fr; }
    }
    @media (max-width: 768px) {
      .nav-links { display: none; }
      .stats-grid { grid-template-columns: 1fr; }
      .timeline { flex-direction: column; gap: 20px; }
      .timeline::before { display: none; }
      .dashboard-header { flex-direction: column; align-items: flex-start; }
    }
  </style>
</head>
<body>

<nav class="navbar" id="navbar">
  <a href="acceuil.html" class="logo">
    <div class="logo-leaf"></div>
    <div class="logo-text">Green<span>Market</span></div>
  </a>
  <ul class="nav-links">
    <li><a href="acceuil.html">Home</a></li>
    <li><a href="catalogue.html">Shop</a></li>
    <li><a href="dashboard.html" class="active">Dashboard</a></li>
  </ul>
  <div class="user-menu">
    <a href="panier.html" class="cart-icon"><i class="fas fa-shopping-bag"></i><span id="cartCounter" style="position:absolute;top:-8px;right:-8px;background:var(--olive);color:white;font-size:0.6rem;width:16px;height:16px;border-radius:50%;display:flex;align-items:center;justify-content:center;">0</span></a>
    <div class="avatar" id="userAvatar" style="cursor:pointer;" title="Se déconnecter" onclick="logout()">?</div>
  </div>
</nav>

<div class="dashboard-container">
  <div class="dashboard-header">
    <div class="welcome">
      <h1>Bienvenue, <span style="color:var(--olive);" id="userName"></span></h1>
      <p>Your organic journey at a glance</p>
    </div>
    <div class="date-badge"><i class="far fa-calendar-alt"></i> Last 30 days</div>
  </div>

  <!-- Stats Cards -->
  <div class="stats-grid">
    <div class="stat-card"><div class="stat-title">Total Orders</div><div class="stat-value">12</div><div class="stat-trend"><i class="fas fa-arrow-up"></i> +2 this month</div></div>
    <div class="stat-card"><div class="stat-title">Total Spent</div><div class="stat-value">$284.50</div><div class="stat-trend"><i class="fas fa-leaf"></i> Eco credits: 128</div></div>
    <div class="stat-card"><div class="stat-title">Pending Delivery</div><div class="stat-value">1</div><div class="stat-trend">Estimated: Tomorrow</div></div>
    <div class="stat-card"><div class="stat-title">Favorite Products</div><div class="stat-value">6</div><div class="stat-trend">+3 new this month</div></div>
  </div>

  <div class="dashboard-grid">
    <!-- Recent Orders Column -->
    <div class="recent-orders">
      <div class="section-title">Recent Orders <a href="catalogue.html">View all →</a></div>
      <div class="order-item"><div class="order-info"><h4>Organic Rainbow Carrots</h4><div class="order-date">Order #GM-2841 • Mar 15, 2026</div></div><div><span class="order-status status-delivered">Delivered</span></div><div class="order-total">$4.50</div></div>
      <div class="order-item"><div class="order-info"><h4>Heirloom Tomatoes (1kg)</h4><div class="order-date">Order #GM-2790 • Mar 10, 2026</div></div><div><span class="order-status status-delivered">Delivered</span></div><div class="order-total">$6.20</div></div>
      <div class="order-item"><div class="order-info"><h4>Wild Forest Honey</h4><div class="order-date">Order #GM-2712 • Mar 2, 2026</div></div><div><span class="order-status status-shipped">Shipped</span></div><div class="order-total">$12.00</div></div>
      <div class="order-item"><div class="order-info"><h4>Sourdough Bread + Artisan bundle</h4><div class="order-date">Order #GM-2650 • Feb 25, 2026</div></div><div><span class="order-status status-delivered">Delivered</span></div><div class="order-total">$18.90</div></div>
    </div>

    <!-- Favorites / Wishlist -->
    <div class="favorites">
      <div class="section-title">My Favorites <a href="panier.html">Manage →</a></div>
      <div class="fav-item"><img class="fav-img" src="Tomates anciennes.png" alt="Tomatoes"><div class="fav-info"><h4>Heirloom Tomatoes</h4><div class="fav-price">$6.20 / kg</div></div><div class="fav-actions"><button class="add-cart-fav">Add to Cart</button><button><i class="far fa-heart"></i></button></div></div>
      <div class="fav-item"><img class="fav-img" src="carrotarcenciel.png" alt="Carrots"><div class="fav-info"><h4>Rainbow Carrots</h4><div class="fav-price">$4.50 / bunch</div></div><div class="fav-actions"><button class="add-cart-fav">Add to Cart</button><button><i class="fas fa-heart" style="color: #e25555;"></i></button></div></div>
      <div class="fav-item"><img class="fav-img" src="https://images.pexels.com/photos/1775043/pexels-photo-1775043.jpeg?auto=compress&cs=tinysrgb&w=100&h=100&fit=crop" alt="Sourdough"><div class="fav-info"><h4>Organic Sourdough</h4><div class="fav-price">$8.90 / loaf</div></div><div class="fav-actions"><button class="add-cart-fav">Add to Cart</button><button><i class="far fa-heart"></i></button></div></div>
    </div>
  </div>

  <!-- Profile Information -->
  <div class="profile-section">
    <div class="profile-header">
      <div class="profile-avatar-large">ES</div>
      <div class="profile-info"><h3>Emma Thompson</h3><p>Member since January 2026 · Verified Organic Buyer</p></div>
    </div>
    <div class="info-grid">
      <div class="info-field"><label>Email Address</label><p>emma.thompson@greenmarket.com</p></div>
      <div class="info-field"><label>Phone Number</label><p>+1 (555) 987-6543</p></div>
      <div class="info-field"><label>Delivery Address</label><p>123 Green Street, Eco City, CA 94102</p></div>
      <div class="info-field"><label>Preferred Payment</label><p>Visa •••• 4242</p></div>
    </div>
    <button class="edit-btn">Edit Profile</button>
  </div>

  <!-- Order Tracking (Current active order) -->
  <div class="tracking-section">
    <div class="section-title">Track Your Order <span style="font-size: 0.8rem; font-weight: normal;">#GM-2712 · Wild Forest Honey</span></div>
    <div class="timeline">
      <div class="timeline-step"><div class="step-dot completed"><i class="fas fa-check" style="font-size: 0.8rem;"></i></div><div class="step-label">Order Placed</div><div class="step-label" style="font-size: 0.6rem; color: var(--text-lt);">Mar 2, 2026</div></div>
      <div class="timeline-step"><div class="step-dot completed"><i class="fas fa-check"></i></div><div class="step-label">Confirmed</div><div class="step-label" style="font-size: 0.6rem; color: var(--text-lt);">Mar 3, 2026</div></div>
      <div class="timeline-step"><div class="step-dot active"><i class="fas fa-truck"></i></div><div class="step-label active">Shipped</div><div class="step-label" style="font-size: 0.6rem; color: var(--text-lt);">Mar 4, 2026</div></div>
      <div class="timeline-step"><div class="step-dot"><i class="fas fa-box"></i></div><div class="step-label">Out for Delivery</div></div>
      <div class="timeline-step"><div class="step-dot"><i class="fas fa-check-circle"></i></div><div class="step-label">Delivered</div></div>
    </div>
    <div style="margin-top: 24px; padding: 16px; background: var(--olive-bg); border-radius: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
      <span><i class="fas fa-shipping-fast"></i> Estimated delivery: Tomorrow, March 6</span>
      <span style="color: var(--olive); font-weight: 500;">Tracking: USPS 1Z999E9E029291122</span>
    </div>
  </div>
</div>

<footer style="background: var(--text); padding: 40px 72px 24px; margin-top: 20px;">
  <div style="display: flex; justify-content: space-between; flex-wrap: wrap; gap: 20px; align-items: center;">
    <div style="display: flex; align-items: center; gap: 10px;"><div style="width: 30px; height: 30px; background: var(--olive); border-radius: 50% 50% 50% 0; transform: rotate(-45deg);"></div><span style="color: white; font-family: var(--serif); font-size: 1.1rem;">Green<span style="color: var(--olive-lt);">Market</span></span></div>
    <div style="color: rgba(255,255,255,0.4); font-size: 0.7rem;">© 2026 GreenMarket – All rights reserved. Empowering local food systems.</div>
  </div>
</footer>

<script>
  const navbar = document.getElementById('navbar');
  window.addEventListener('scroll', () => navbar.classList.toggle('scrolled', window.scrollY > 20));
  
  // Simulate add to cart from favorites
  document.querySelectorAll('.add-cart-fav').forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      const originalText = btn.innerText;
      btn.innerText = '✓ Added';
      btn.style.background = '#2b6e2f';
      setTimeout(() => {
        btn.innerText = originalText;
        btn.style.background = '';
      }, 1200);
      // Update cart badge simulation
      const badge = document.querySelector('.cart-badge, .cart-icon span');
      if (badge) {
        let count = parseInt(badge.innerText) || 0;
        badge.innerText = count + 1;
      } else {
        const cartSpan = document.createElement('span');
        cartSpan.style.position = 'absolute';
        cartSpan.style.top = '-8px';
        cartSpan.style.right = '-8px';
        cartSpan.style.background = 'var(--olive)';
        cartSpan.style.color = 'white';
        cartSpan.style.fontSize = '0.6rem';
        cartSpan.style.width = '16px';
        cartSpan.style.height = '16px';
        cartSpan.style.borderRadius = '50%';
        cartSpan.style.display = 'flex';
        cartSpan.style.alignItems = 'center';
        cartSpan.style.justifyContent = 'center';
        cartSpan.innerText = '1';
        document.querySelector('.cart-icon').appendChild(cartSpan);
      }
    });
  });

  // Edit profile alert demo
  document.querySelector('.edit-btn')?.addEventListener('click', () => {
    alert('Profile editing feature would open here (connected to backend).');
  });


  const _phpUser = {
    nom: "<?php echo $user['nom'];?>",
    prenom: "<?php echo $user['prenom'];?>",
    email: "<?php echo $user['email'];?>",
    role: "<?php echo $user['role'];?>"
  };
  const av = document.getElementById('userAvatar');
  const nm = document.getElementById('userName');
  if(av) av.textContent = "<?php echo $initiales;?>";
  if(nm) nm.textContent = "<?php echo $user['prenom'].' '.$user['nom'];?>";
  function logout(){
    window.location.href = 'deconnexion.php';
  }

</script>
</body>
</html>