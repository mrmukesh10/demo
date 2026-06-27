<?php
require_once 'db.php';
start_secure_session();

// Already logged in
if (!empty($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';
$email_val = '';

// Friendly message from redirects
$messages = [
    'login_required' => 'Please sign in to access that page.',
    'logged_out'     => 'You have been signed out.',
    'updated'        => 'Profile updated. Sign in to continue.',
];
$info_msg = $messages[$_GET['msg'] ?? ''] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Security token mismatch. Refresh and try again.';
    } else {
        $email    = trim($_POST['email']    ?? '');
        $password = $_POST['password'] ?? '';
        $email_val = $email;

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || empty($password)) {
            $error = 'Please enter a valid email and password.';
        } else {
            $stmt = $conn->prepare(
                "SELECT id, full_name, password FROM users WHERE email = ? LIMIT 1"
            );
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $user   = $result->fetch_assoc();
            $stmt->close();

            if ($user && password_verify($password, $user['password'])) {
                // ── Login success ─────────────────────────────
                session_regenerate_id(true); // prevent session fixation
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['user_name'] = $user['full_name'];
                header("Location: dashboard.php");
                exit();
            } else {
                // Generic message — don't reveal which field is wrong
                $error = 'Incorrect email or password.';
            }
        }
    }
}

$csrf = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign In — NexCore Solutions</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="page-shell">

  <!-- ── Brand Panel ── -->
  <aside class="brand-panel">
    <a href="#" class="logo">
      <div class="logo-mark">N</div>
      <div class="logo-text">Nex<span>Core</span></div>
    </a>

    <div class="brand-headline">
      <h2>Welcome back to <em>NexCore.</em></h2>
      <p>Access your dashboard, update your profile, and stay connected with what's happening across our global network.</p>
    </div>

    <div class="brand-stats">
      <div class="stat-card">
        <div class="num">24/7</div>
        <div class="label">Platform Uptime</div>
      </div>
      <div class="stat-card">
        <div class="num">ISO</div>
        <div class="label">27001 Certified</div>
      </div>
      <div class="stat-card">
        <div class="num">AES</div>
        <div class="label">256-bit Encrypted</div>
      </div>
      <div class="stat-card">
        <div class="num">SOC2</div>
        <div class="label">Compliant</div>
      </div>
    </div>
  </aside>

  <!-- ── Form Panel ── -->
  <main class="form-panel">
    <div class="form-box">
      <h1>Sign in</h1>
      <p class="subtitle">New here? <a href="register.php">Create an account</a></p>

      <?php if ($info_msg): ?>
        <div class="alert alert-success">
          <span class="alert-icon">ℹ</span>
          <span><?= htmlspecialchars($info_msg) ?></span>
        </div>
      <?php endif; ?>

      <?php if ($error): ?>
        <div class="alert alert-error">
          <span class="alert-icon">⚠</span>
          <span><?= htmlspecialchars($error) ?></span>
        </div>
      <?php endif; ?>

      <form method="POST" action="login.php" novalidate>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">

        <div class="field">
          <label for="email">Work Email</label>
          <input type="email" id="email" name="email"
                 value="<?= htmlspecialchars($email_val) ?>"
                 placeholder="jane@company.com"
                 autofocus required>
        </div>

        <div class="field">
          <label for="password">Password</label>
          <input type="password" id="password" name="password"
                 placeholder="Your password" required>
        </div>

        <button type="submit" class="btn btn-primary" style="margin-top:8px;">
          Sign In →
        </button>
      </form>
    </div>
  </main>

</div>

</body>
</html>
