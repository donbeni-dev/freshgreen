<?php
session_start();
require 'db.php';

// Block non-admins
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header('Location: index.php');
  exit;
}

$success = '';
$error   = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name     = trim($_POST['name']);
  $price    = floatval($_POST['price']);
  $category = trim($_POST['category']);
  $desc     = trim($_POST['description']);
  $emoji    = trim($_POST['emoji']) ?: '🥬';
  $image    = null;

  // Validate fields
  if (!$name || !$price || !$category || !$desc) {
    $error = 'Please fill in all required fields.';
  } else {
    // Handle image upload
    if (!empty($_FILES['image']['name'])) {
      $allowed   = ['jpg', 'jpeg', 'png', 'webp'];
      $ext       = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
      if (!in_array($ext, $allowed)) {
        $error = 'Only JPG, PNG, WEBP images are allowed.';
      } else {
        $filename = uniqid('prod_') . '.' . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], "uploads/$filename");
        $image = $filename;
      }
    }

    if (!$error) {
      // Save to database
      $stmt = $conn->prepare("INSERT INTO products (name, price, category, description, emoji, image) VALUES (?, ?, ?, ?, ?, ?)");
      $stmt->bind_param("sdssss", $name, $price, $category, $desc, $emoji, $image);
      $stmt->execute();
      $success = "\"$name\" was added to the store successfully!";
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <title>FreshGreen – Add Product</title>
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
    <a class="sidebar-item active" href="add-product.php"><span>➕</span> Add Product</a>
    <a class="sidebar-item" href="manage-products.php"><span>📦</span> Manage Products</a>
    <div class="sidebar-label">Store</div>
    <a class="sidebar-item" href="shop.php"><span>🛒</span> View Shop</a>
  </div>

  <div class="admin-content">
    <div class="admin-header">
      <h2>Add New Product</h2>
      <p>Fill in the details to list a new product in the store.</p>
    </div>

    <!-- Success / Error Messages -->
    <?php if ($success): ?>
      <div style="background:#e8f5e9;border:1px solid #a5d6a7;border-radius:10px;padding:14px 18px;color:#2e7d32;margin-bottom:24px;font-weight:600;">
        ✅ <?= htmlspecialchars($success) ?>
      </div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div style="background:#fdf2f2;border:1px solid #f0b8b8;border-radius:10px;padding:14px 18px;color:#c0392b;margin-bottom:24px;">
        ❌ <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <div class="form-card">
      <h3>Product Details</h3>
      <!-- enctype needed for file upload -->
      <form method="POST" action="add-product.php" enctype="multipart/form-data">
        <div class="form-group">
          <label>Product Name *</label>
          <input type="text" name="name" placeholder="e.g. Sweet Mangoes" required/>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Price (KSh) *</label>
            <input type="number" name="price" placeholder="150" min="0" step="0.01" required/>
          </div>
          <div class="form-group">
            <label>Category *</label>
            <select name="category" required>
              <option value="">Select category</option>
              <option>Fruits</option>
              <option>Vegetables</option>
              <option>Organic</option>
              <option>Herbs</option>
              <option>Bulk Supply</option>
              <option>Other</option>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label>Description *</label>
          <textarea name="description" placeholder="Describe the produce…" required></textarea>
        </div>
        <div class="form-group">
          <label>Emoji Icon</label>
          <input type="text" name="emoji" placeholder="🥭 (optional)"/>
        </div>
        <div class="form-group">
          <label>Product Image</label>
          <div class="img-upload-area">
            <div class="upload-icon">📷</div>
            <div style="font-size:14px;color:var(--text-muted)">Choose an image file</div>
            <input type="file" name="image" accept="image/*" style="margin-top:12px"/>
          </div>
        </div>
        <div style="display:flex;gap:12px;margin-top:8px">
          <button type="submit" class="btn btn-primary">➕ Add Product</button>
          <a href="manage-products.php" class="btn btn-outline">View All Products</a>
        </div>
      </form>
    </div>
  </div>
</div>

</body>
</html>