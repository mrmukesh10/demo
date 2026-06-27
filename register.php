<?php
require_once 'db.php';
start_secure_session();

// Redirect if already logged in
if (!empty($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error   = '';
$success = '';
$old     = []; // repopulate form on error

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ── CSRF check ──────────────────────────────────────────
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Security token mismatch. Please refresh and try again.';
    } else {
        // ── Collect & sanitize ───────────────────────────────
        $old = [
            'full_name'  => trim($_POST['full_name']  ?? ''),
            'email'      => trim($_POST['email']      ?? ''),
            'phone'      => trim($_POST['phone']      ?? ''),
            'department' => trim($_POST['department'] ?? ''),
            'job_title'  => trim($_POST['job_title']  ?? ''),
        ];
        $password  = $_POST['password']         ?? '';
        $password2 = $_POST['password_confirm'] ?? '';

        // ── Validate ─────────────────────────────────────────
        if (empty($old['full_name']) || strlen($old['full_name']) < 2) {
            $error = 'Full name must be at least 2 characters.';
        } elseif (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } elseif (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters.';
        } elseif ($password !== $password2) {
            $error = 'Passwords do not match.';
        } else {
            // ── Check duplicate email ────────────────────────
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
            $stmt->bind_param("s", $old['email']);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $error = 'This email address is already registered.';
            } else {
                // ── Insert ───────────────────────────────────
                $hashed = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
                $stmt2  = $conn->prepare(
                    "INSERT INTO users (full_name, email, phone, department, job_title, password)
                     VALUES (?, ?, ?, ?, ?, ?)"
                );
                $stmt2->bind_param(
                    "ssssss",
                    $old['full_name'],
                    $old['email'],
                    $old['phone'],
                    $old['department'],
                    $old['job_title'],
                    $hashed
                );

                if ($stmt2->execute()) {
                    $success = 'Account created successfully. <a href="login.php">Sign in now →</a>';
                    $old = [];
                } else {
                    error_log("Register insert error: " . $conn->error);
                    $error = 'Something went wrong. Please try again.';
                }
                $stmt2->close();
            }
            $stmt->close();
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
  <title>Create Account — NexCore Solutions</title>
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
      <h2>Build the future of <em>enterprise technology.</em></h2>
      <p>Join a team of 400+ engineers, strategists, and designers delivering mission-critical solutions across 30 countries. Your career starts here.</p>
    </div>

    <div class="brand-stats">
      <div class="stat-card">
        <div class="num">98%</div>
        <div class="label">Client Retention</div>
      </div>
      <div class="stat-card">
        <div class="num">14+</div>
        <div class="label">Years Active</div>
      </div>
      <div class="stat-card">
        <div class="num">400+</div>
        <div class="label">Professionals</div>
      </div>
      <div class="stat-card">
        <div class="num">30</div>
        <div class="label">Countries Served</div>
      </div>
    </div>
  </aside>

  <!-- ── Form Panel ── -->
  <main class="form-panel">
    <div class="form-box">
      <h1>Create your account</h1>
      <p class="subtitle">Already registered? <a href="login.php">Sign in</a></p>

      <?php if ($error): ?>
        <div class="alert alert-error">
          <span class="alert-icon">⚠</span>
          <span><?= htmlspecialchars($error) ?></span>
        </div>
      <?php endif; ?>

      <?php if ($success): ?>
        <div class="alert alert-success">
          <span class="alert-icon">✓</span>
          <span><?= $success /* contains safe link */ ?></span>
        </div>
      <?php endif; ?>

      <form method="POST" action="register.php" novalidate>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">

        <!-- Row 1 -->
        <div class="field-group">
          <div class="field">
            <label for="full_name">Full Name *</label>
            <input type="text" id="full_name" name="full_name"
                   value="<?= htmlspecialchars($old['full_name'] ?? '') ?>"
                   placeholder="Jane Smith" required>
          </div>
          <div class="field">
            <label for="email">Work Email *</label>
            <input type="email" id="email" name="email"
                   value="<?= htmlspecialchars($old['email'] ?? '') ?>"
                   placeholder="jane@company.com" required>
          </div>
        </div>

        <!-- Row 2 -->
        <div class="field-group">
          <div class="field">
            <label for="phone">Phone</label>
            <input type="tel" id="phone" name="phone"
                   value="<?= htmlspecialchars($old['phone'] ?? '') ?>"
                   placeholder="+91 98765 43210">
          </div>
          <div class="field">
            <label for="department">Department</label>
            <select id="department" name="department">
              <option value="" disabled <?= empty($old['department']) ? 'selected' : '' ?>>Select…</option>
              <?php
              $departments = ['Engineering','Product','Design','Sales','Marketing','Finance','HR','Operations'];
              foreach ($departments as $d):
                $sel = ($old['department'] ?? '') === $d ? 'selected' : '';
              ?>
                <option value="<?= $d ?>" <?= $sel ?>><?= $d ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <!-- Job Title -->
        <div class="field">
          <label for="job_title">Job Title</label>
          <input type="text" id="job_title" name="job_title"
                 value="<?= htmlspecialchars($old['job_title'] ?? '') ?>"
                 placeholder="Senior Software Engineer">
        </div>

        <!-- Row 3 -->
        <div class="field-group">
          <div class="field">
            <label for="password">Password *</label>
            <input type="password" id="password" name="password"
                   placeholder="Min. 8 characters" required>
          </div>
          <div class="field">
            <label for="password_confirm">Confirm Password *</label>
            <input type="password" id="password_confirm" name="password_confirm"
                   placeholder="Repeat password" required>
          </div>
        </div>

        <button type="submit" class="btn btn-primary">Create Account →</button>
      </form>
    </div>
  </main>

</div>

</body>
</html>
