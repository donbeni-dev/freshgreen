<?php
session_start();
require 'db.php';

// Block access if not logged in
if (!isset($_SESSION['user_id'])) {
  header('Location: index.php');
  exit;
}

// Get all products from database
$search   = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';

// Build query with optional search and filter
$sql = "SELECT * FROM products WHERE 1=1";
if ($search) {
  $safe = $conn->real_escape_string($search);
  $sql .= " AND (name LIKE '%$safe%' OR description LIKE '%$safe%')";
}
if ($category && $category !== 'All') {
  $safe = $conn->real_escape_string($category);
  $sql .= " AND category = '$safe'";
}
$sql .= " ORDER BY created_at DESC";
$products = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);

// Get all categories for filter tabs
$categories = $conn->query("SELECT DISTINCT category FROM products")->fetch_all(MYSQLI_ASSOC);
$total      = $conn->query("SELECT COUNT(*) as c FROM products")->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <title>FreshGreen – Shop</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="style.css"/>
</head>
<body>

<!-- NAV -->
<nav>
  <div class="nav-logo"><span>🌿</span> FreshGreen</div>
  <div class="nav-tabs">
    <a class="nav-tab active" href="shop.php">🛒 Shop</a>
  </div>
  <div class="spacer"></div>
  <div style="display:flex;align-items:center;gap:12px">
    <span class="nav-badge">👤 Customer</span>
    <div class="user-avatar"><?= strtoupper(substr($_SESSION['name'], 0, 1)) ?></div>
    <a href="logout.php" class="btn btn-outline btn-sm">Logout</a>
  </div>
</nav>

<!-- HERO -->
<div class="hero">
  <h2>Fresh From The Farm 🌾</h2>
  <p>Discover fresh produce sourced daily from 500+ local farmers.</p>
  <div class="hero-stats">
    <div class="hero-stat"><div class="num"><?= $total ?></div><div class="lbl">Products Available</div></div>
    <div class="hero-stat"><div class="num">500+</div><div class="lbl">Local Farmers</div></div>
    <div class="hero-stat"><div class="num">10K+</div><div class="lbl">Families Served</div></div>
  </div>
</div>

<!-- SHOP SECTION -->
<div class="section">

  <!-- Search Bar -->
  <div class="section-header">
    <span class="section-title">Our Products</span>
    <form method="GET" action="shop.php">
      <div class="search-bar">
        <span>🔍</span>
        <input type="text" name="search" placeholder="Search produce…" value="<?= htmlspecialchars($search) ?>"/>
        <?php if ($category): ?>
          <input type="hidden" name="category" value="<?= htmlspecialchars($category) ?>"/>
        <?php endif; ?>
      </div>
    </form>
  </div>

  <!-- Category Filter Tabs -->
  <div class="filter-tabs">
    <a class="filter-tab <?= !$category ? 'active' : '' ?>" href="shop.php">All</a>
    <?php foreach ($categories as $cat): ?>
      <a class="filter-tab <?= $category === $cat['category'] ? 'active' : '' ?>"
         href="shop.php?category=<?= urlencode($cat['category']) ?>">
        <?= htmlspecialchars($cat['category']) ?>
      </a>
    <?php endforeach; ?>
  </div>

  <!-- Product Grid -->
  <div class="product-grid">
    <?php if (empty($products)): ?>
      <div class="empty-state">
        <div class="empty-icon">🌾</div>
        No products found. Try a different search.
      </div>
    <?php else: ?>
      <?php foreach ($products as $p): ?>
        <div class="product-card">
          <div class="product-badge"><?= htmlspecialchars($p['category']) ?></div>
          <div class="product-img">
            <?php if ($p['image']): ?>
              <img src="uploads/<?= htmlspecialchars($p['image']) ?>" style="width:100%;height:180px;object-fit:cover"/>
            <?php else: ?>
              <?= $p['emoji'] ?>
            <?php endif; ?>
          </div>
          <div class="product-info">
            <div class="product-name"><?= htmlspecialchars($p['name']) ?></div>
            <div class="product-desc"><?= htmlspecialchars($p['description']) ?></div>
            <div class="product-footer">
              <div class="product-price">KSh <?= number_format($p['price'], 2) ?></div>
              <button class="add-cart-btn" onclick="addToCart(<?= $p['id'] ?>, '<?= addslashes($p['name']) ?>', <?= $p['price'] ?>, '<?= $p['emoji'] ?>')">+</button>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<!-- CART BUTTON & PANEL (same as before) -->
<button class="cart-float-btn" id="cart-float" onclick="toggleCart()">
  🛒 <span class="cart-count" id="cart-count">0</span>
</button>
<div class="cart-panel" id="cart-panel">
  <h3>🛒 Your Cart</h3>
  <div class="cart-items" id="cart-items"></div>
  <div class="cart-total">
    <div class="row"><span>Subtotal</span><span id="cart-subtotal">KSh 0</span></div>
    <div class="row"><span>Delivery</span><span>KSh 100</span></div>
    <div class="row" style="font-weight:700"><span>Total</span><span class="total" id="cart-total-val">KSh 100</span></div>
    <button class="btn btn-primary" style="width:100%;justify-content:center;margin-top:12px" onclick="checkout()">Checkout →</button>
  </div>
</div>
<div id="toast" class="success-toast hidden"></div>

<script src="script.js"></script>
</body>
</html>