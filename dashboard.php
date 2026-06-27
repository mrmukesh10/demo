<?php
require_once 'db.php';
require_login();

// ── Fetch full user record ─────────────────────────────────────────────────
$stmt = $conn->prepare(
    "SELECT id, full_name, email, phone, department, job_title, bio, created_at
     FROM users WHERE id = ? LIMIT 1"
);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    // User deleted from DB but session exists — clean up
    session_destroy();
    header("Location: login.php?msg=login_required");
    exit();
}

// Initials for avatar
$initials = implode('', array_map(fn($w) => strtoupper($w[0]),
    array_slice(explode(' ', trim($user['full_name'])), 0, 2)));

// Format joined date
$joined = date("F j, Y", strtotime($user['created_at']));

// Success message from update
$updated_msg = ($_GET['updated'] ?? '') === '1' ? 'Profile updated successfully.' : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($user['full_name']) ?> — NexCore</title>
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
      <div class="user-chip">
        <div class="avatar"><?= htmlspecialchars($initials) ?></div>
        <span><?= htmlspecialchars($user['full_name']) ?></span>
      </div>
      <a href="logout.php" class="btn btn-secondary" style="padding:7px 16px;font-size:13px;">
        Sign Out
      </a>
    </div>
  </header>

  <!-- ── Main Content ── -->
  <main class="main-content">

    <?php if ($updated_msg): ?>
      <div class="alert alert-success" style="margin-bottom:20px;">
        <span class="alert-icon">✓</span>
        <span><?= htmlspecialchars($updated_msg) ?></span>
      </div>
    <?php endif; ?>

    <div class="profile-card">

      <!-- Header -->
      <div class="profile-header">
        <div class="avatar-lg"><?= htmlspecialchars($initials) ?></div>
        <div class="profile-header-info">
          <h2><?= htmlspecialchars($user['full_name']) ?></h2>
          <?php if ($user['job_title']): ?>
            <div class="badge"><?= htmlspecialchars($user['job_title']) ?></div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Body -->
      <div class="profile-body">
        <div class="info-grid">

          <div class="info-item">
            <label>Email Address</label>
            <p><?= htmlspecialchars($user['email']) ?></p>
          </div>

          <div class="info-item">
            <label>Phone Number</label>
            <p><?= $user['phone'] ? htmlspecialchars($user['phone']) : '<span style="color:var(--grey-500);font-weight:400;">—</span>' ?></p>
          </div>

          <div class="info-item">
            <label>Department</label>
            <p><?= $user['department'] ? htmlspecialchars($user['department']) : '<span style="color:var(--grey-500);font-weight:400;">—</span>' ?></p>
          </div>

          <div class="info-item">
            <label>Job Title</label>
            <p><?= $user['job_title'] ? htmlspecialchars($user['job_title']) : '<span style="color:var(--grey-500);font-weight:400;">—</span>' ?></p>
          </div>

          <div class="info-item">
            <label>Member Since</label>
            <p><?= htmlspecialchars($joined) ?></p>
          </div>

          <div class="info-item">
            <label>Account ID</label>
            <p style="font-family:monospace;font-size:13px;color:var(--grey-700);">
              NXC-<?= str_pad($user['id'], 6, '0', STR_PAD_LEFT) ?>
            </p>
          </div>

          <?php if ($user['bio']): ?>
            <div class="divider" style="grid-column:1/-1;margin:4px 0;"></div>
            <div class="info-item full">
              <label>About</label>
              <p style="font-weight:400;line-height:1.65;color:var(--grey-700);">
                <?= nl2br(htmlspecialchars($user['bio'])) ?>
              </p>
            </div>
          <?php endif; ?>

        </div>
      </div>

      <!-- Action Bar -->
      <div class="action-bar">
        <a href="update.php" class="btn btn-primary" style="width:auto;padding:10px 24px;">
          ✎ &nbsp;Edit Profile
        </a>
      </div>

    </div>
  </main>

</div>

</body>
</html>
