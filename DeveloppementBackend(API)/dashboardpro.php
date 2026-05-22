<?php
require_once "db.php";
if(!isset($_SESSION['user'])){
    header("Location: connexion.php");
    exit();
}
if($_SESSION['user']['role'] != "producteur"){
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
  <title>GreenMarket – Producer Dashboard | Manage Your Farm Shop</title>
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
    .shop-badge {
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

    /* Dashboard 2-column */
    .dashboard-grid {
      display: grid;
      grid-template-columns: 1fr 1.2fr;
      gap: 32px;
      margin-bottom: 48px;
    }

    /* Recent Orders (for producer) */
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
    .status-pending {
      background: #fff3e0;
      color: var(--warning);
    }
    .status-shipped {
      background: #e3f2fd;
      color: #2c6e9e;
    }
    .order-total {
      font-weight: 600;
    }
    .status-btn {
      background: var(--olive);
      color: white;
      border: none;
      padding: 4px 12px;
      border-radius: 60px;
      font-size: 0.65rem;
      cursor: pointer;
    }

    /* Top Products */
    .top-products {
      background: white;
      border-radius: 28px;
      padding: 28px;
      box-shadow: var(--shadow-sm);
      border: 1px solid rgba(212, 197, 173, 0.25);
    }
    .product-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 14px 0;
      border-bottom: 1px solid var(--cream2);
    }
    .product-name {
      font-weight: 600;
    }
    .product-sales {
      color: var(--olive);
      font-weight: 500;
    }

    /* Stock Management Table */
    .stock-section {
      background: white;
      border-radius: 28px;
      padding: 28px;
      box-shadow: var(--shadow-sm);
      border: 1px solid rgba(212, 197, 173, 0.25);
      margin-bottom: 32px;
      overflow-x: auto;
    }
    .stock-table {
      width: 100%;
      border-collapse: collapse;
    }
    .stock-table th, .stock-table td {
      text-align: left;
      padding: 14px 8px;
      border-bottom: 1px solid var(--cream2);
    }
    .stock-table th {
      font-size: 0.7rem;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      color: var(--text-lt);
    }
    .low-stock {
      color: var(--danger);
      font-weight: 600;
    }
    .edit-stock {
      background: transparent;
      border: 1px solid var(--sand);
      padding: 4px 12px;
      border-radius: 40px;
      cursor: pointer;
      font-size: 0.7rem;
    }

    /* Add Product Form */
    .add-product {
      background: white;
      border-radius: 28px;
      padding: 28px;
      box-shadow: var(--shadow-sm);
      border: 1px solid rgba(212, 197, 173, 0.25);
    }
    .form-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 20px;
      margin: 20px 0;
    }
    .input-group {
      display: flex;
      flex-direction: column;
      gap: 6px;
    }
    .input-group label {
      font-size: 0.7rem;
      text-transform: uppercase;
      color: var(--text-lt);
      letter-spacing: 0.08em;
    }
    .input-group input, .input-group select, .input-group textarea {
      padding: 10px 14px;
      border: 1px solid var(--sand);
      border-radius: 16px;
      font-family: var(--sans);
      background: var(--ivory);
    }
    .submit-btn {
      background: var(--olive);
      color: white;
      border: none;
      padding: 12px 28px;
      border-radius: 60px;
      font-weight: 600;
      cursor: pointer;
      margin-top: 12px;
    }

    /* Rating & Reviews Summary */
    .rating-section {
      background: white;
      border-radius: 28px;
      padding: 28px;
      box-shadow: var(--shadow-sm);
      border: 1px solid rgba(212, 197, 173, 0.25);
      margin-top: 32px;
    }
    .rating-summary {
      display: flex;
      align-items: center;
      gap: 24px;
      flex-wrap: wrap;
    }
    .big-rating {
      font-family: var(--serif);
      font-size: 2.5rem;
      font-weight: 600;
      color: var(--warning);
    }
    .stars-review {
      color: var(--warning);
      letter-spacing: 2px;
    }

    @media (max-width: 1024px) {
      .navbar { padding: 0 32px; }
      .dashboard-container { padding: 100px 24px 40px; }
      .stats-grid { grid-template-columns: repeat(2, 1fr); }
      .dashboard-grid { grid-template-columns: 1fr; }
      .form-grid { grid-template-columns: 1fr; }
    }
    @media (max-width: 768px) {
      .nav-links { display: none; }
      .stats-grid { grid-template-columns: 1fr; }
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
    <li><a href="dashboardpro.html" class="active">Producer Hub</a></li>
  </ul>
  <div class="user-menu">
    <div class="avatar" id="userAvatar" style="cursor:pointer;" title="Se déconnecter" onclick="logout()">?</div>
  </div>
</nav>

<div class="dashboard-container">
  <div class="dashboard-header">
    <div class="welcome">
      <h1>Producer Dashboard, <span style="color: var(--olive);">Jean Dupont</span></h1>
      <p>Manage your farm shop, track sales, and grow your local impact</p>
    </div>
    <div class="shop-badge"><i class="fas fa-store"></i> Les Jardins d'Antan · Active since 2025</div>
  </div>

  <!-- Stats Cards -->
  <div class="stats-grid">
    <div class="stat-card"><div class="stat-title">Total Revenue</div><div class="stat-value">$3,842</div><div class="stat-trend"><i class="fas fa-arrow-up"></i> +18% vs last month</div></div>
    <div class="stat-card"><div class="stat-title">Orders Received</div><div class="stat-value">127</div><div class="stat-trend">This month: 34 orders</div></div>
    <div class="stat-card"><div class="stat-title">Products Listed</div><div class="stat-value">14</div><div class="stat-trend">+2 new this week</div></div>
    <div class="stat-card"><div class="stat-title">Avg. Rating</div><div class="stat-value">4.9 ★</div><div class="stat-trend">Based on 86 reviews</div></div>
  </div>

  <div class="dashboard-grid">
    <!-- Recent Orders (needs action) -->
    <div class="recent-orders">
      <div class="section-title">Pending Orders <a href="catalogue.html">View all →</a></div>
      <div class="order-item"><div class="order-info"><h4>Organic Rainbow Carrots (x3)</h4><div class="order-date">Order #GM-2841 • Customer: Emma T.</div></div><div><span class="order-status status-pending">Pending</span></div><button class="status-btn">Process</button></div>
      <div class="order-item"><div class="order-info"><h4>Heirloom Tomatoes (2kg)</h4><div class="order-date">Order #GM-2790 • Customer: Mark L.</div></div><div><span class="order-status status-pending">Pending</span></div><button class="status-btn">Process</button></div>
      <div class="order-item"><div class="order-info"><h4>Wild Forest Honey (5 jars)</h4><div class="order-date">Order #GM-2712 • Customer: Sophie A.</div></div><div><span class="order-status status-shipped">Shipped</span></div><button class="status-btn" style="background: #7f8c8d;">Track</button></div>
    </div>

    <!-- Top Selling Products -->
    <div class="top-products">
      <div class="section-title">Best Selling Products</div>
      <div class="product-item"><div class="product-name">Heirloom Tomatoes</div><div class="product-sales">47 units sold · $291.40</div></div>
      <div class="product-item"><div class="product-name">Rainbow Carrots</div><div class="product-sales">38 units sold · $171.00</div></div>
      <div class="product-item"><div class="product-name">Wild Forest Honey</div><div class="product-sales">29 units sold · $348.00</div></div>
      <div class="product-item"><div class="product-name">Organic Sourdough</div><div class="product-sales">22 units sold · $195.80</div></div>
    </div>
  </div>

  <!-- Stock Management Table -->
  <div class="stock-section">
    <div class="section-title">Inventory Management <span style="font-size: 0.8rem; font-weight: normal;">Update stock levels instantly</span></div>
    <table class="stock-table">
      <thead>
        <tr><th>Product</th><th>Category</th><th>Current Stock</th><th>Price</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <tr><td>Heirloom Tomatoes</td><td>Vegetables</td><td class="low-stock">8 kg</td><td>$6.20/kg</td><td><button class="edit-stock" data-product="Tomatoes">Edit Stock</button></td></tr>
        <tr><td>Rainbow Carrots</td><td>Roots</td><td>24 bunches</td><td>$4.50/bunch</td><td><button class="edit-stock" data-product="Carrots">Edit Stock</button></td></tr>
        <tr><td>Wild Forest Honey</td><td>Honey</td><td class="low-stock">5 jars</td><td>$12.00/jar</td><td><button class="edit-stock" data-product="Honey">Edit Stock</button></td></tr>
        <tr><td>Organic Sourdough</td><td>Bakery</td><td>12 loaves</td><td>$8.90/loaf</td><td><button class="edit-stock" data-product="Sourdough">Edit Stock</button></td></tr>
        <tr><td>Courgettes bio</td><td>Vegetables</td><td>21 kg</td><td>$2.50/kg</td><td><button class="edit-stock" data-product="Courgettes">Edit Stock</button></td></tr>
      </tbody>
    </table>
  </div>

  <!-- Add New Product Form -->
  <div class="add-product">
    <div class="section-title">Add New Product <span style="font-size: 0.8rem;">List your seasonal harvest</span></div>
    <div class="form-grid">
      <div class="input-group"><label>Product Name</label><input type="text" id="prodName" placeholder="e.g., Artisanal Goat Cheese"></div>
      <div class="input-group"><label>Category</label><select id="prodCategory"><option>Vegetables</option><option>Fruits</option><option>Dairy</option><option>Honey</option><option>Bakery</option></select></div>
      <div class="input-group"><label>Price (USD)</label><input type="number" id="prodPrice" placeholder="0.00"></div>
      <div class="input-group"><label>Stock Quantity</label><input type="number" id="prodStock" placeholder="units available"></div>
      <div class="input-group" style="grid-column: span 2;"><label>Description</label><textarea rows="2" id="prodDesc" placeholder="Fresh, organic, harvested weekly..."></textarea></div>
    </div>
    <button class="submit-btn" id="addProductBtn"><i class="fas fa-plus-circle"></i> Add to Catalog</button>
  </div>

  <!-- Rating & Reviews Summary -->
  <div class="rating-section">
    <div class="section-title">Customer Satisfaction & Reviews</div>
    <div class="rating-summary">
      <div><div class="big-rating">4.9 ★</div><div class="stars-review">★★★★★</div><div style="font-size: 0.7rem;">86 verified ratings</div></div>
      <div style="flex: 1;">
        <div><span style="font-size: 0.7rem;">5 ★</span> <div style="display: inline-block; width: 70%; background: var(--cream2); border-radius: 10px; margin-left: 8px;"><div style="width: 88%; background: var(--warning); height: 6px; border-radius: 10px;"></div></div> <span style="font-size: 0.7rem;">76 reviews</span></div>
        <div><span style="font-size: 0.7rem;">4 ★</span> <div style="display: inline-block; width: 70%; background: var(--cream2); border-radius: 10px; margin-left: 8px;"><div style="width: 8%; background: var(--warning); height: 6px; border-radius: 10px;"></div></div> <span style="font-size: 0.7rem;">7 reviews</span></div>
      </div>
      <div><button class="edit-stock" id="viewReviewsBtn" style="background: var(--olive); color: white; border: none;">View All Reviews →</button></div>
    </div>
    <div style="margin-top: 20px; background: var(--olive-bg); padding: 16px; border-radius: 20px;">
      <p style="font-style: italic;">"Absolutely fresh and delicious! The tomatoes are the best I've ever had." — Emma T.</p>
      <p style="font-style: italic; margin-top: 8px;">"Fast shipping and beautiful packaging. Will order again." — James M.</p>
    </div>
  </div>
</div>

<footer style="background: var(--text); padding: 40px 72px 24px; margin-top: 20px;">
  <div style="display: flex; justify-content: space-between; flex-wrap: wrap; gap: 20px; align-items: center;">
    <div style="display: flex; align-items: center; gap: 10px;"><div style="width: 30px; height: 30px; background: var(--olive); border-radius: 50% 50% 50% 0; transform: rotate(-45deg);"></div><span style="color: white; font-family: var(--serif); font-size: 1.1rem;">Green<span style="color: var(--olive-lt);">Market</span></span></div>
    <div style="color: rgba(255,255,255,0.4); font-size: 0.7rem;">Producer Dashboard · Empowering local food systems</div>
  </div>
</footer>

<script>
  const navbar = document.getElementById('navbar');
  window.addEventListener('scroll', () => navbar.classList.toggle('scrolled', window.scrollY > 20));

  // Edit stock buttons (simulate update)
  document.querySelectorAll('.edit-stock').forEach(btn => {
    if(btn.id !== 'viewReviewsBtn') {
      btn.addEventListener('click', (e) => {
        const product = btn.getAttribute('data-product') || 'product';
        const newStock = prompt(`Enter new stock quantity for ${product}:`);
        if (newStock !== null && !isNaN(newStock)) {
          const row = btn.closest('tr');
          const stockCell = row.cells[2];
          stockCell.innerHTML = newStock + (stockCell.innerHTML.includes('kg') ? ' kg' : (stockCell.innerHTML.includes('bunches') ? ' bunches' : (stockCell.innerHTML.includes('jars') ? ' jars' : (stockCell.innerHTML.includes('loaves') ? ' loaves' : ' units'))));
          if (newStock < 10) stockCell.classList.add('low-stock');
          else stockCell.classList.remove('low-stock');
          alert(`Stock updated to ${newStock}`);
        }
      });
    }
  });

  // Add Product simulation
  const addBtn = document.getElementById('addProductBtn');
  if(addBtn) {
    addBtn.addEventListener('click', () => {
      const name = document.getElementById('prodName').value;
      const price = document.getElementById('prodPrice').value;
      const stock = document.getElementById('prodStock').value;
      if(!name || !price || !stock) {
        alert('Please fill all product fields');
        return;
      }
      alert(`✅ Product "${name}" added successfully! (Price: $${price}, Stock: ${stock})\nIt will appear in your catalog after admin validation.`);
      document.getElementById('prodName').value = '';
      document.getElementById('prodPrice').value = '';
      document.getElementById('prodStock').value = '';
      document.getElementById('prodDesc').value = '';
    });
  }

  // Process order buttons (update status)
  document.querySelectorAll('.status-btn').forEach(btn => {
    if(btn.innerText === 'Process') {
      btn.addEventListener('click', (e) => {
        const orderDiv = btn.closest('.order-item');
        const statusSpan = orderDiv.querySelector('.order-status');
        statusSpan.innerText = 'Processing';
        statusSpan.className = 'order-status status-shipped';
        btn.innerText = 'Shipped';
        btn.style.background = '#5e7340';
        alert('Order status updated to "Processing". You can mark as shipped later.');
      });
    } else if(btn.innerText === 'Shipped') {
      btn.addEventListener('click', () => {
        alert('Tracking information sent to customer.');
      });
    }
  });

  // View reviews modal simulation
  const viewReviews = document.getElementById('viewReviewsBtn');
  if(viewReviews) {
    viewReviews.addEventListener('click', () => {
      alert("All customer reviews:\n\n⭐ Heirloom Tomatoes: 'Perfect ripeness!' - 5 stars\n⭐ Wild Honey: 'Authentic and delicious' - 5 stars\n⭐ Rainbow Carrots: 'So sweet and crunchy' - 5 stars\n\nTotal 86 reviews · 4.9 average rating");
    });
  }


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