<?php
session_start();
require 'db.php';

$error   = '';
$success = '';

// If already logged in, go to shop
if (isset($_SESSION['user_id'])) {
  header('Location: shop.php');
  exit;
}

// Handle sign up form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name     = trim($_POST['name']);
  $email    = trim($_POST['email']);
  $password = $_POST['password'];
  $confirm  = $_POST['confirm_password'];

  // Check all fields filled
  if (!$name || !$email || !$password || !$confirm) {
    $error = 'Please fill in all fields.';

  // Check passwords match
  } elseif ($password !== $confirm) {
    $error = 'Passwords do not match!';

  // Check password is at least 6 characters
  } elseif (strlen($password) < 6) {
    $error = 'Password must be at least 6 characters.';

  } else {
    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $exists = $stmt->get_result()->fetch_assoc();

    if ($exists) {
      $error = 'This email is already registered. Please login instead.';
    } else {
      // Hash the password (NEVER store plain passwords!)
      $hashed = password_hash($password, PASSWORD_DEFAULT);

      // Save new user to database
      $stmt = $conn->prepare(
        "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'user')"
      );
      $stmt->bind_param("sss", $name, $email, $hashed);

      if ($stmt->execute()) {
        // Auto login after signup
        $_SESSION['user_id'] = $conn->insert_id;
        $_SESSION['name']    = $name;
        $_SESSION['email']   = $email;
        $_SESSION['role']    = 'user';

        // Send to shop
        header('Location: shop.php');
        exit;
      } else {
        $error = 'Something went wrong. Please try again.';
      }
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>FreshGreen – Sign Up</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="style.css"/>
  <style>
    .signup-extra {
      font-size: 13px;
      color: var(--text-muted);
      text-align: center;
      margin-top: 20px;
    }
    .signup-extra a {
      color: var(--dark-green);
      font-weight: 600;
      text-decoration: none;
    }
    .signup-extra a:hover { text-decoration: underline; }
    .password-strength {
      height: 4px;
      border-radius: 4px;
      margin-top: 6px;
      transition: all .3s;
      background: var(--border);
    }
    .strength-weak   { background: #e53935; width: 33%; }
    .strength-medium { background: #FB8C00; width: 66%; }
    .strength-strong { background: var(--light-green); width: 100%; }
    .strength-label  { font-size: 11px; color: var(--text-muted); margin-top: 4px; }
  </style>
</head>
<body>

<div id="login-page">

  <!-- Left side branding -->
  <div class="login-left">
    <div class="login-logo">🌿</div>
    <h1>Join<br/>FreshGreen</h1>
    <p>Create your free account and start ordering fresh produce directly from local farmers.</p>
    <div class="login-tags">
      <span class="login-tag">🚜 Farm Direct</span>
      <span class="login-tag">⚡ Fast Delivery</span>
      <span class="login-tag">🌱 Sustainable</span>
      <span class="login-tag">✅ Trusted</span>
    </div>
  </div>

  <!-- Right side sign up form -->
  <div class="login-right">
    <h2>Create Account</h2>
    <p class="sub">Join thousands of families getting fresh produce</p>

    <!-- Error message -->
    <?php if ($error): ?>
      <div class="login-error">❌ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- Sign Up Form -->
    <form method="POST" action="signup.php">

      <div class="form-group">
        <label>Full Name</label>
        <input
          type="text"
          name="name"
          placeholder="e.g. Jane Wanjiku"
          value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
          required
        />
      </div>

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
          placeholder="At least 6 characters"
          id="password-input"
          oninput="checkStrength(this.value)"
          required
        />
        <div class="password-strength" id="strength-bar"></div>
        <div class="strength-label" id="strength-label"></div>
      </div>

      <div class="form-group">
        <label>Confirm Password</label>
        <input
          type="password"
          name="confirm_password"
          placeholder="Type password again"
          required
        />
      </div>

      <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;margin-top:4px">
        Create Account →
      </button>
    </form>

    <!-- Link to login -->
    <p class="signup-extra">
      Already have an account? <a href="index.php">Sign In here</a>
    </p>
  </div>
</div>

<script>
// Password strength checker
function checkStrength(val) {
  const bar   = document.getElementById('strength-bar');
  const label = document.getElementById('strength-label');
  if (!val) { bar.className = 'password-strength'; label.textContent = ''; return; }
  if (val.length < 6) {
    bar.className = 'password-strength strength-weak';
    label.textContent = '⚠️ Too short';
  } else if (val.length < 10 || !/[0-9]/.test(val)) {
    bar.className = 'password-strength strength-medium';
    label.textContent = '🟡 Medium strength';
  } else {
    bar.className = 'password-strength strength-strong';
    label.textContent = '✅ Strong password';
  }
}
</script>

</body>
</html>