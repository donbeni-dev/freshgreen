<?php
session_start();
require 'db.php';

$error = '';

// If already logged in, redirect
if (isset($_SESSION['user_id'])) {
  if ($_SESSION['role'] === 'admin') {
    header('Location: admin.php');
  } else {
    header('Location: shop.php');
  }
  exit;
}

// Handle login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email    = trim($_POST['email']);
  $password = $_POST['password'];

  // ── ADMIN CHECK (hardcoded, no database needed) ──
  if ($email === 'admin@freshgreen.com' && $password === 'admin123') {
    $_SESSION['user_id'] = 0;
    $_SESSION['name']    = 'Admin';
    $_SESSION['email']   = 'admin@freshgreen.com';
    $_SESSION['role']    = 'admin';
    header('Location: admin.php');
    exit;
  }

  // ── USER CHECK (must exist in database) ──
  $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $user = $stmt->get_result()->fetch_assoc();

  if ($user && password_verify($password, $user['password'])) {
    // Correct — log them in
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['name']    = $user['name'];
    $_SESSION['email']   = $user['email'];
    $_SESSION['role']    = 'user';
    header('Location: shop.php');
    exit;
  } else {
    // Wrong email or password
    $error = 'Wrong email or password. If you are new, please Sign Up first.';
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>FreshGreen – Login</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="style.css"/>
  <style>
    .bottom-links {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 10px;
      margin-top: 20px;
      font-size: 13px;
      color: var(--text-muted);
    }
    .bottom-links a {
      color: var(--dark-green);
      font-weight: 600;
      text-decoration: none;
    }
    .bottom-links a:hover {
      text-decoration: underline;
    }
    .divider {
      display: flex;
      align-items: center;
      gap: 12px;
      margin: 20px 0;
      color: var(--text-muted);
      font-size: 12px;
    }
    .divider::before,
    .divider::after {
      content: '';
      flex: 1;
      height: 1px;
      background: var(--border);
    }
  </style>
</head>
<body>

<div id="login-page">

  <!-- ── LEFT SIDE BRANDING ── -->
  <div class="login-left">
    <div class="login-logo">🌿</div>
    <h1>Fresh<br/>Green</h1>
    <p>Connecting Farms to Families — Fresh produce delivered to your door.</p>
    <div class="login-tags">
      <span class="login-tag">🚜 Farm Direct</span>
      <span class="login-tag">⚡ Fast Delivery</span>
      <span class="login-tag">🌱 Sustainable</span>
      <span class="login-tag">✅ Trusted</span>
    </div>
  </div>

  <!-- ── RIGHT SIDE LOGIN FORM ── -->
  <div class="login-right">
    <h2>Welcome Back</h2>
    <p class="sub">Sign in to your account</p>

    <!-- Error Message -->
    <?php if ($error): ?>
      <div class="login-error">
        ❌ <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <!-- Login Form -->
    <form method="POST" action="index.php">

      <div class="form-group">
        <label>Email Address</label>
        <input
          type="email"
          name="email"
          placeholder="you@example.com"
          value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
          required
        />
      </div>

      <div class="form-group">
        <label>Password</label>
        <input
          type="password"
          name="password"
          placeholder="••••••••"
          required
        />
      </div>

      <button
        type="submit"
        class="btn btn-primary"
        style="width:100%; justify-content:center;">
        Sign In →
      </button>

    </form>

    <!-- Divider -->
    <div class="divider">or</div>

    <!-- Sign Up Button -->
    <a href="signup.php"
       class="btn btn-outline"
       style="width:100%; justify-content:center; text-decoration:none;">
      ✨ Create New Account
    </a>

    <!-- Admin hint -->
    <div class="bottom-links">
      <span>Are you the admin? Use your admin credentials above.</span>
    </div>

  </div>
</div>

</body>
</html>