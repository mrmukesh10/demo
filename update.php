<?php
require_once 'db.php';
require_login();

// ── Fetch current data ────────────────────────────────────────────────────
$stmt = $conn->prepare(
    "SELECT full_name, email, phone, department, job_title, bio
     FROM users WHERE id = ? LIMIT 1"
);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit();
}

$error   = '';
$success = '';

// ── Handle POST ──────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Security token mismatch. Refresh and try again.';
    } else {
        // Collect
        $full_name  = trim($_POST['full_name']  ?? '');
        $email      = trim($_POST['email']      ?? '');
        $phone      = trim($_POST['phone']      ?? '');
        $department = trim($_POST['department'] ?? '');
        $job_title  = trim($_POST['job_title']  ?? '');
        $bio        = trim($_POST['bio']        ?? '');
        $new_pass   = $_POST['new_password']         ?? '';
        $new_pass2  = $_POST['new_password_confirm'] ?? '';

        // Validate
        if (strlen($full_name) < 2) {
            $error = 'Full name must be at least 2 characters.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } elseif (!empty($new_pass) && strlen($new_pass) < 8) {
            $error = 'New password must be at least 8 characters.';
        } elseif (!empty($new_pass) && $new_pass !== $new_pass2) {
            $error = 'New passwords do not match.';
        } else {
            // Check email uniqueness (exclude self)
            $stmt = $conn->prepare(
                "SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1"
            );
            $stmt->bind_param("si", $email, $_SESSION['user_id']);
            $stmt->execute();
            $stmt->store_result();
            $dup = $stmt->num_rows > 0;
            $stmt->close();

            if ($dup) {
                $error = 'That email address is already in use by another account.';
            } else {
                // Build update query dynamically
                if (!empty($new_pass)) {
                    $hashed = password_hash($new_pass, PASSWORD_BCRYPT, ['cost' => 12]);
                    $stmt2  = $conn->prepare(
                        "UPDATE users
                         SET full_name=?, email=?, phone=?, department=?, job_title=?, bio=?, password=?
                         WHERE id=?"
                    );
                    $stmt2->bind_param(
                        "sssssssi",
                        $full_name, $email, $phone, $department, $job_title, $bio, $hashed,
                        $_SESSION['user_id']
                    );
                } else {
                    $stmt2 = $conn->prepare(
                        "UPDATE users
                         SET full_name=?, email=?, phone=?, department=?, job_title=?, bio=?
                         WHERE id=?"
                    );
                    $stmt2->bind_param(
                        "ssssssi",
                        $full_name, $email, $phone, $department, $job_title, $bio,
                        $_SESSION['user_id']
                    );
                }

                if ($stmt2->execute()) {
                    // Refresh session name
                    $_SESSION['user_name'] = $full_name;
                    // Refresh local $user for form repopulation
                    $user = compact('full_name','email','phone','department','job_title','bio');
                    header("Location: dashboard.php?updated=1");
                    exit();
                } else {
                    error_log("Update error: " . $conn->error);
                    $error = 'Update failed. Please try again.';
                }
                $stmt2->close();
            }
        }
    }
}

$csrf = generate_csrf_token();
$departments = ['Engineering','Product','Design','Sales','Marketing','Finance','HR','Operations'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Profile — NexCore</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="dashboard-shell">

  <!-- ── Top Bar ── -->
  <header class="topbar">
    <a href="#" class="logo">
      <div class="logo-mark" style="width:32px;height:32px;font-size:14px;">N</div>
      <div class="logo-text" style="font-size:17px;">Nex<span>Core</span></div>
    </a>
    <div class="topbar-right">
      <a href="dashboard.php" class="btn btn-secondary" style="padding:7px 16px;font-size:13px;">
        ← Profile
      </a>
      <a href="logout.php" class="btn btn-secondary" style="padding:7px 16px;font-size:13px;">
        Sign Out
      </a>
    </div>
  </header>

  <!-- ── Main Content ── -->
  <main class="main-content">

    <div class="update-card">

      <h1 class="page-title">Edit Profile</h1>
      <p class="page-sub">Changes take effect immediately after saving.</p>

      <?php if ($error): ?>
        <div class="alert alert-error">
          <span class="alert-icon">⚠</span>
          <span><?= htmlspecialchars($error) ?></span>
        </div>
      <?php endif; ?>

      <form method="POST" action="update.php" novalidate>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">

        <!-- Section: Personal Info -->
        <p style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.8px;color:var(--grey-500);margin-bottom:16px;">
          Personal Information
        </p>

        <div class="field-group">
          <div class="field">
            <label for="full_name">Full Name *</label>
            <input type="text" id="full_name" name="full_name"
                   value="<?= htmlspecialchars($user['full_name']) ?>"
                   required>
          </div>
          <div class="field">
            <label for="email">Email Address *</label>
            <input type="email" id="email" name="email"
                   value="<?= htmlspecialchars($user['email']) ?>"
                   required>
          </div>
        </div>

        <div class="field-group">
          <div class="field">
            <label for="phone">Phone</label>
            <input type="tel" id="phone" name="phone"
                   value="<?= htmlspecialchars($user['phone'] ?? '') ?>"
                   placeholder="+91 98765 43210">
          </div>
          <div class="field">
            <label for="department">Department</label>
            <select id="department" name="department">
              <option value="">Select…</option>
              <?php foreach ($departments as $d):
                $sel = ($user['department'] ?? '') === $d ? 'selected' : ''; ?>
                <option value="<?= $d ?>" <?= $sel ?>><?= $d ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="field">
          <label for="job_title">Job Title</label>
          <input type="text" id="job_title" name="job_title"
                 value="<?= htmlspecialchars($user['job_title'] ?? '') ?>"
                 placeholder="Senior Software Engineer">
        </div>

        <div class="field">
          <label for="bio">About / Bio</label>
          <textarea id="bio" name="bio"
                    placeholder="A short description about yourself..."><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
        </div>

        <div class="divider"></div>

        <!-- Section: Change Password -->
        <p style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.8px;color:var(--grey-500);margin-bottom:16px;">
          Change Password <span style="font-weight:400;color:var(--grey-300);">(leave blank to keep current)</span>
        </p>

        <div class="field-group">
          <div class="field">
            <label for="new_password">New Password</label>
            <input type="password" id="new_password" name="new_password"
                   placeholder="Min. 8 characters">
          </div>
          <div class="field">
            <label for="new_password_confirm">Confirm New Password</label>
            <input type="password" id="new_password_confirm" name="new_password_confirm"
                   placeholder="Repeat new password">
          </div>
        </div>

        <div class="divider"></div>

        <!-- Actions -->
        <div style="display:flex;gap:12px;justify-content:flex-end;">
          <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
          <button type="submit" class="btn btn-primary" style="width:auto;padding:12px 28px;">
            Save Changes →
          </button>
        </div>

      </form>
    </div>
  </main>

</div>

</body>
</html>
