<?php
require_once "db.php";
if(!isset($_SESSION['user'])){
    header("Location: connexion.php");
    exit();
}
if($_SESSION['user']['role'] != "admin"){
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
  <title>GreenMarket – Admin Dashboard | Platform Management</title>
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
      --danger: #c95a5a;
      --info: #5c8a8a;
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
    .admin-badge {
      font-size: 0.7rem;
      background: var(--olive-bg);
      padding: 4px 12px;
      border-radius: 60px;
      color: var(--olive);
    }

    /* Dashboard Layout */
    .dashboard-container {
      max-width: 1600px;
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
      grid-template-columns: repeat(5, 1fr);
      gap: 20px;
      margin-bottom: 48px;
    }
    .stat-card {
      background: white;
      border-radius: 24px;
      padding: 20px;
      box-shadow: var(--shadow-sm);
      border: 1px solid rgba(212, 197, 173, 0.25);
      transition: transform 0.2s;
    }
    .stat-card:hover {
      transform: translateY(-3px);
      box-shadow: var(--shadow-md);
    }
    .stat-title {
      font-size: 0.65rem;
      letter-spacing: 0.1em;
      text-transform: uppercase;
      color: var(--text-lt);
      margin-bottom: 10px;
    }
    .stat-value {
      font-family: var(--serif);
      font-size: 1.8rem;
      font-weight: 600;
      color: var(--olive);
      margin-bottom: 4px;
    }
    .stat-trend {
      font-size: 0.65rem;
      color: var(--success);
    }

    /* Dashboard 2-column */
    .dashboard-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 32px;
      margin-bottom: 48px;
    }

    /* User Management / Pending Producers */
    .pending-producers {
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
    .user-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 16px 0;
      border-bottom: 1px solid var(--cream2);
    }
    .user-info h4 {
      font-weight: 600;
      margin-bottom: 4px;
    }
    .user-email {
      font-size: 0.7rem;
      color: var(--text-lt);
    }
    .approve-btn {
      background: var(--success);
      color: white;
      border: none;
      padding: 6px 18px;
      border-radius: 60px;
      font-size: 0.7rem;
      cursor: pointer;
      margin-right: 8px;
    }
    .reject-btn {
      background: var(--danger);
      color: white;
      border: none;
      padding: 6px 18px;
      border-radius: 60px;
      font-size: 0.7rem;
      cursor: pointer;
    }

    /* Recent Orders (platform wide) */
    .recent-orders {
      background: white;
      border-radius: 28px;
      padding: 28px;
      box-shadow: var(--shadow-sm);
      border: 1px solid rgba(212, 197, 173, 0.25);
    }
    .order-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 14px 0;
      border-bottom: 1px solid var(--cream2);
    }
    .order-status {
      font-size: 0.7rem;
      padding: 4px 12px;
      border-radius: 60px;
      background: var(--olive-bg);
      color: var(--olive);
    }

    /* Product Management Table */
    .product-section {
      background: white;
      border-radius: 28px;
      padding: 28px;
      box-shadow: var(--shadow-sm);
      border: 1px solid rgba(212, 197, 173, 0.25);
      margin-bottom: 32px;
      overflow-x: auto;
    }
    .admin-table {
      width: 100%;
      border-collapse: collapse;
    }
    .admin-table th, .admin-table td {
      text-align: left;
      padding: 14px 8px;
      border-bottom: 1px solid var(--cream2);
    }
    .admin-table th {
      font-size: 0.7rem;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      color: var(--text-lt);
    }
    .validate-btn {
      background: var(--success);
      color: white;
      border: none;
      padding: 4px 12px;
      border-radius: 40px;
      cursor: pointer;
      font-size: 0.7rem;
    }
    .delete-btn {
      background: var(--danger);
      color: white;
      border: none;
      padding: 4px 12px;
      border-radius: 40px;
      cursor: pointer;
      font-size: 0.7rem;
    }

    /* Category Management */
    .category-section {
      background: white;
      border-radius: 28px;
      padding: 28px;
      box-shadow: var(--shadow-sm);
      border: 1px solid rgba(212, 197, 173, 0.25);
      margin-top: 32px;
    }
    .category-form {
      display: flex;
      gap: 16px;
      margin-bottom: 24px;
      flex-wrap: wrap;
    }
    .category-form input {
      flex: 1;
      padding: 12px 16px;
      border: 1px solid var(--sand);
      border-radius: 60px;
      font-family: var(--sans);
    }
    .cat-list {
      display: flex;
      flex-wrap: wrap;
      gap: 12px;
    }
    .cat-tag {
      background: var(--olive-bg);
      padding: 8px 20px;
      border-radius: 60px;
      display: flex;
      align-items: center;
      gap: 12px;
    }
    .cat-tag button {
      background: none;
      border: none;
      color: var(--danger);
      cursor: pointer;
    }

    /* Reports & Signalements */
    .reports-section {
      background: white;
      border-radius: 28px;
      padding: 28px;
      box-shadow: var(--shadow-sm);
      border: 1px solid rgba(212, 197, 173, 0.25);
      margin-top: 32px;
    }

    @media (max-width: 1200px) {
      .stats-grid { grid-template-columns: repeat(3, 1fr); }
    }
    @media (max-width: 1024px) {
      .navbar { padding: 0 32px; }
      .dashboard-container { padding: 100px 24px 40px; }
      .stats-grid { grid-template-columns: repeat(2, 1fr); }
      .dashboard-grid { grid-template-columns: 1fr; }
    }
    @media (max-width: 768px) {
      .nav-links { display: none; }
      .stats-grid { grid-template-columns: 1fr; }
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
    <li><a href="dashboradadmis.html" class="active">Admin</a></li>
  </ul>
  <div class="user-menu">
    <span class="admin-badge"><i class="fas fa-shield-alt"></i> Super Admin</span>
    <div class="avatar" id="userAvatar" style="cursor:pointer;" title="Se déconnecter" onclick="logout()">?</div>
  </div>
</nav>

<div class="dashboard-container">
  <div class="dashboard-header">
    <div class="welcome">
      <h1>Admin Dashboard, <span style="color: var(--olive);">Alexandra</span></h1>
      <p>Oversee platform activity, manage users, products, and categories</p>
    </div>
    <div class="date-badge"><i class="far fa-calendar-alt"></i> Last 30 days · Global View</div>
  </div>

  <!-- Global Stats -->
  <div class="stats-grid">
    <div class="stat-card"><div class="stat-title">Total Users</div><div class="stat-value">1,284</div><div class="stat-trend"><i class="fas fa-user-plus"></i> +48 this week</div></div>
    <div class="stat-card"><div class="stat-title">Active Producers</div><div class="stat-value">247</div><div class="stat-trend">+12 pending validation</div></div>
    <div class="stat-card"><div class="stat-title">Total Orders</div><div class="stat-value">3,912</div><div class="stat-trend"><i class="fas fa-arrow-up"></i> +18%</div></div>
    <div class="stat-card"><div class="stat-title">Revenue (MTD)</div><div class="stat-value">$48,230</div><div class="stat-trend">+$5,200 vs last month</div></div>
    <div class="stat-card"><div class="stat-title">Active Products</div><div class="stat-value">1,247</div><div class="stat-trend">+87 this month</div></div>
  </div>

  <div class="dashboard-grid">
    <!-- Pending Producer Validations -->
    <div class="pending-producers">
      <div class="section-title">Producer Validation <span style="font-size: 0.7rem;">12 pending requests</span></div>
      <div class="user-item"><div class="user-info"><h4>Ferme des Trois Chênes</h4><div class="user-email">contact@troischenes.bio · Registered: Mar 18, 2026</div></div><div><button class="approve-btn">Approve</button><button class="reject-btn">Reject</button></div></div>
      <div class="user-item"><div class="user-info"><h4>Miel de Provence</h4><div class="user-email">apiculteur@mielprovence.fr · Registered: Mar 15, 2026</div></div><div><button class="approve-btn">Approve</button><button class="reject-btn">Reject</button></div></div>
      <div class="user-item"><div class="user-info"><h4>Les Vergers du Soleil</h4><div class="user-email">vergers@soleil.bio · Registered: Mar 12, 2026</div></div><div><button class="approve-btn">Approve</button><button class="reject-btn">Reject</button></div></div>
      <div class="user-item"><div class="user-info"><h4>Boulangerie Artisanale du Coin</h4><div class="user-email">pain@artisanboulangerie.com · Registered: Mar 10, 2026</div></div><div><button class="approve-btn">Approve</button><button class="reject-btn">Reject</button></div></div>
      <div class="section-title" style="margin-top: 16px; font-size: 1rem;">All Users <a href="catalogue.html" style="font-size: 0.7rem;">Manage →</a></div>
      <div class="user-item"><div class="user-info"><h4>Emma Thompson (Client)</h4><div class="user-email">emma.t@greenmarket.com · Orders: 12</div></div><button class="reject-btn" style="background: #7f8c8d;">Suspend</button></div>
    </div>

    <!-- Recent Platform Orders -->
    <div class="recent-orders">
      <div class="section-title">Recent Orders <a href="catalogue.html">View all →</a></div>
      <div class="order-item"><div><strong>#GM-2841</strong> · Emma T. · $42.50</div><span class="order-status">Delivered</span></div>
      <div class="order-item"><div><strong>#GM-2790</strong> · Mark L. · $23.80</div><span class="order-status">Shipped</span></div>
      <div class="order-item"><div><strong>#GM-2712</strong> · Sophie A. · $58.00</div><span class="order-status">Processing</span></div>
      <div class="order-item"><div><strong>#GM-2688</strong> · Jean D. · $31.20</div><span class="order-status">Delivered</span></div>
      <div class="order-item"><div><strong>#GM-2650</strong> · Claire B. · $94.30</div><span class="order-status">Shipped</span></div>
    </div>
  </div>

  <!-- Product Management (Pending validation) -->
  <div class="product-section">
    <div class="section-title">Products Pending Validation <span style="font-size: 0.7rem;">Approve or reject before publication</span></div>
    <table class="admin-table">
      <thead><tr><th>Product</th><th>Producer</th><th>Price</th><th>Category</th><th>Actions</th></tr></thead>
      <tbody>
        <tr><td>Organic Goat Cheese</td><td>Ferme des Trois Chênes</td><td>$8.90</td><td>Dairy</td><td><button class="validate-btn">✓ Approve</button> <button class="delete-btn">✗ Reject</button></td></tr>
        <tr><td>Raw Lavender Honey</td><td>Miel de Provence</td><td>$14.50</td><td>Honey</td><td><button class="validate-btn">✓ Approve</button> <button class="delete-btn">✗ Reject</button></td></tr>
        <tr><td>Apple Cider Vinegar</td><td>Les Vergers du Soleil</td><td>$6.20</td><td>Beverages</td><td><button class="validate-btn">✓ Approve</button> <button class="delete-btn">✗ Reject</button></td></tr>
        <tr><td>Sourdough Rye Bread</td><td>Boulangerie Artisanale</td><td>$7.50</td><td>Bakery</td><td><button class="validate-btn">✓ Approve</button> <button class="delete-btn">✗ Reject</button></td></tr>
      </tbody>
    </table>
  </div>

  <!-- Category Management -->
  <div class="category-section">
    <div class="section-title">Manage Categories</div>
    <div class="category-form">
      <input type="text" id="newCategoryName" placeholder="New category name (e.g., 'Herbs')">
      <button id="addCategoryBtn" class="approve-btn" style="background: var(--olive);">Add Category</button>
    </div>
    <div class="cat-list" id="categoryList">
      <div class="cat-tag">Fruits <button onclick="removeCategory(this)">✕</button></div>
      <div class="cat-tag">Vegetables <button onclick="removeCategory(this)">✕</button></div>
      <div class="cat-tag">Dairy <button onclick="removeCategory(this)">✕</button></div>
      <div class="cat-tag">Honey <button onclick="removeCategory(this)">✕</button></div>
      <div class="cat-tag">Bakery <button onclick="removeCategory(this)">✕</button></div>
      <div class="cat-tag">Handmade <button onclick="removeCategory(this)">✕</button></div>
    </div>
  </div>

  <!-- Reports & Flagged Content -->
  <div class="reports-section">
    <div class="section-title">Flagged Content & Disputes <span style="font-size: 0.7rem;">2 active reports</span></div>
    <div class="user-item"><div class="user-info"><h4>⚠️ Review reported on "Wild Forest Honey"</h4><div class="user-email">Reason: Inappropriate language · Submitted by producer</div></div><button class="approve-btn" style="background: var(--info);">Dismiss</button><button class="delete-btn">Remove Review</button></div>
    <div class="user-item"><div class="user-info"><h4>⚠️ Dispute: Order #GM-2712</h4><div class="user-email">Customer claims delayed delivery · Under investigation</div></div><button class="approve-btn" style="background: var(--info);">Resolve</button><button class="delete-btn">Escalate</button></div>
  </div>
</div>

<footer style="background: var(--text); padding: 40px 72px 24px; margin-top: 20px;">
  <div style="display: flex; justify-content: space-between; flex-wrap: wrap; gap: 20px; align-items: center;">
    <div style="display: flex; align-items: center; gap: 10px;"><div style="width: 30px; height: 30px; background: var(--olive); border-radius: 50% 50% 50% 0; transform: rotate(-45deg);"></div><span style="color: white; font-family: var(--serif); font-size: 1.1rem;">Green<span style="color: var(--olive-lt);">Market</span></span></div>
    <div style="color: rgba(255,255,255,0.4); font-size: 0.7rem;">Administration Panel · Supervise & empower local food systems</div>
  </div>
</footer>

<script>
  const navbar = document.getElementById('navbar');
  window.addEventListener('scroll', () => navbar.classList.toggle('scrolled', window.scrollY > 20));

  // Approve / Reject producer actions
  document.querySelectorAll('.approve-btn').forEach(btn => {
    if(btn.id !== 'addCategoryBtn') {
      btn.addEventListener('click', (e) => {
        const userItem = btn.closest('.user-item');
        if(userItem) {
          const producerName = userItem.querySelector('h4')?.innerText || 'Producer';
          if(btn.innerText === 'Approve') {
            alert(`✅ Producer "${producerName}" has been approved. They can now list products.`);
            userItem.remove();
          }
        }
      });
    }
  });

  document.querySelectorAll('.reject-btn').forEach(btn => {
    btn.addEventListener('click', (e) => {
      const userItem = btn.closest('.user-item');
      if(userItem) {
        const producerName = userItem.querySelector('h4')?.innerText || 'User';
        if(confirm(`Reject ${producerName}? This will deny their application.`)) {
          userItem.remove();
          alert(`❌ ${producerName} has been rejected.`);
        }
      }
    });
  });

  // Product validation (approve/reject)
  document.querySelectorAll('.validate-btn').forEach(btn => {
    btn.addEventListener('click', (e) => {
      const row = btn.closest('tr');
      const productName = row.cells[0]?.innerText || 'Product';
      alert(`✅ "${productName}" has been approved and is now visible on the marketplace.`);
      row.remove();
    });
  });

  document.querySelectorAll('.delete-btn').forEach(btn => {
    if(btn.innerText.includes('Reject') || (btn.innerText === '✗ Reject')) {
      btn.addEventListener('click', (e) => {
        const row = btn.closest('tr');
        const productName = row.cells[0]?.innerText || 'Product';
        if(confirm(`Reject "${productName}"? It will not be published.`)) {
          row.remove();
          alert(`❌ "${productName}" rejected.`);
        }
      });
    }
  });

  // Add Category
  const addCatBtn = document.getElementById('addCategoryBtn');
  const catInput = document.getElementById('newCategoryName');
  const catList = document.getElementById('categoryList');

  function removeCategory(btn) {
    const tag = btn.closest('.cat-tag');
    if(tag && confirm('Remove this category?')) {
      tag.remove();
    }
  }

  if(addCatBtn) {
    addCatBtn.addEventListener('click', () => {
      const newCat = catInput.value.trim();
      if(newCat === '') {
        alert('Please enter a category name');
        return;
      }
      const catTag = document.createElement('div');
      catTag.className = 'cat-tag';
      catTag.innerHTML = `${newCat} <button onclick="removeCategory(this)">✕</button>`;
      catList.appendChild(catTag);
      catInput.value = '';
      alert(`Category "${newCat}" added successfully.`);
    });
  }

  // Remove category from inline buttons (global function)
  window.removeCategory = removeCategory;

  // Additional: simulate removing reported content
  const dismissBtns = document.querySelectorAll('.reports-section .approve-btn');
  dismissBtns.forEach(btn => {
    if(btn.innerText === 'Dismiss') {
      btn.addEventListener('click', (e) => {
        const item = btn.closest('.user-item');
        if(item) item.remove();
        alert('Report dismissed.');
      });
    }
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