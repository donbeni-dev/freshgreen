<?php
session_start();
require 'db.php';

// Block non-admins
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header('Location: index.php');
  exit;
}

// Get stats from database
$total_products = $conn->query("SELECT COUNT(*) as c FROM products")->fetch_assoc()['c'];
$total_categories = $conn->query("SELECT COUNT(DISTINCT category) as c FROM products")->fetch_assoc()['c'];
$total_value = $conn->query("SELECT SUM(price) as s FROM products")->fetch_assoc()['s'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <title>FreshGreen – Admin</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="style.css"/>
</head>
<body>

<nav>
  <div class="nav-logo"><span>🌿</span> FreshGreen</div>
  <div class="nav-tabs">
    <a class="nav-tab" href="shop.php">🛒 Shop</a>
    <a class="nav-tab active" href="admin.php">🛠 Admin Panel</a>
  </div>
  <div class="spacer"></div>
  <div style="display:flex;align-items:center;gap:12px">
    <span class="nav-badge admin">🛠 Admin</span>
    <div class="user-avatar"><?= strtoupper(substr($_SESSION['name'], 0, 1)) ?></div>
    <a href="logout.php" class="btn btn-outline btn-sm">Logout</a>
  </div>
</nav>

<div class="admin-layout">
  <!-- Sidebar -->
  <div class="admin-sidebar">
    <div class="sidebar-label">Management</div>
    <a class="sidebar-item active" href="admin.php"><span>📊</span> Dashboard</a>
    <a class="sidebar-item" href="add-product.php"><span>➕</span> Add Product</a>
    <a class="sidebar-item" href="manage-products.php"><span>📦</span> Manage Products</a>
    <div class="sidebar-label">Store</div>
    <a class="sidebar-item" href="shop.php"><span>🛒</span> View Shop</a>
  </div>

  <!-- Dashboard Content -->
  <div class="admin-content">
    <div class="admin-header">
      <h2>Dashboard</h2>
      <p>Welcome back, <?= htmlspecialchars($_SESSION['name']) ?>!</p>
    </div>
    <div class="stats-row">
      <div class="stat-card"><div class="icon">📦</div><div class="num"><?= $total_products ?></div><div class="lbl">Total Products</div></div>
      <div class="stat-card"><div class="icon">🏷️</div><div class="num"><?= $total_categories ?></div><div class="lbl">Categories</div></div>
      <div class="stat-card"><div class="icon">🌾</div><div class="num">500+</div><div class="lbl">Partner Farmers</div></div>
      <div class="stat-card"><div class="icon">💰</div><div class="num">KSh <?= number_format($total_value) ?></div><div class="lbl">Inventory Value</div></div>
    </div>
  </div>
</div>

</body>
</html>