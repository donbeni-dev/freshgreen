<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header('Location: index.php'); exit;
}

// Handle delete
if (isset($_GET['delete'])) {
  $id = intval($_GET['delete']);
  // Delete image file too
  $row = $conn->query("SELECT image FROM products WHERE id=$id")->fetch_assoc();
  if ($row['image'] && file_exists("uploads/" . $row['image'])) {
    unlink("uploads/" . $row['image']);
  }
  $conn->query("DELETE FROM products WHERE id=$id");
  header('Location: manage-products.php?deleted=1'); exit;
}

// Get all products
$products = $conn->query("SELECT * FROM products ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <title>FreshGreen – Manage Products</title>
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
    <a href="logout.php" class="btn btn-outline btn-sm">Logout</a>
  </div>
</nav>

<div class="admin-layout">
  <div class="admin-sidebar">
    <div class="sidebar-label">Management</div>
    <a class="sidebar-item" href="admin.php"><span>📊</span> Dashboard</a>
    <a class="sidebar-item" href="add-product.php"><span>➕</span> Add Product</a>
    <a class="sidebar-item active" href="manage-products.php"><span>📦</span> Manage Products</a>
    <div class="sidebar-label">Store</div>
    <a class="sidebar-item" href="shop.php"><span>🛒</span> View Shop</a>
  </div>

  <div class="admin-content">
    <div class="admin-header" style="display:flex;justify-content:space-between;align-items:flex-start">
      <div>
        <h2>Manage Products</h2>
        <p>Edit or remove products from the store.</p>
      </div>
      <a href="add-product.php" class="btn btn-primary btn-sm">➕ Add New</a>
    </div>

    <?php if (isset($_GET['deleted'])): ?>
      <div style="background:#e8f5e9;border:1px solid #a5d6a7;border-radius:10px;padding:14px 18px;color:#2e7d32;margin-bottom:24px;">
        ✅ Product deleted successfully.
      </div>
    <?php endif; ?>

    <div class="admin-product-list">
      <?php if (empty($products)): ?>
        <div class="empty-state"><div class="empty-icon">📦</div>No products yet.</div>
      <?php else: ?>
        <?php foreach ($products as $p): ?>
          <div class="admin-product-item">
            <div class="admin-product-thumb">
              <?php if ($p['image']): ?>
                <img src="uploads/<?= htmlspecialchars($p['image']) ?>" style="width:64px;height:64px;object-fit:cover;border-radius:10px"/>
              <?php else: ?>
                <?= $p['emoji'] ?>
              <?php endif; ?>
            </div>
            <div class="admin-product-info">
              <div class="name"><?= htmlspecialchars($p['name']) ?></div>
              <div class="meta"><?= htmlspecialchars($p['category']) ?> • KSh <?= number_format($p['price'], 2) ?></div>
              <div class="meta" style="margin-top:4px;font-size:12px"><?= htmlspecialchars(substr($p['description'], 0, 80)) ?>…</div>
            </div>
            <div class="admin-product-actions">
              <a href="edit-product.php?id=<?= $p['id'] ?>" class="btn btn-outline btn-sm">✏️ Edit</a>
              <a href="manage-products.php?delete=<?= $p['id'] ?>"
                 class="btn btn-danger btn-sm"
                 onclick="return confirm('Delete this product?')">🗑 Delete</a>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</div>

</body>
</html>