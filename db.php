<?php
// ─── Database Configuration ───────────────────────────────────────────────
// CHANGE these values to match your server setup before deployment.
define('DB_HOST', 'localhost');
define('DB_USER', 'root');          // your MySQL username
define('DB_PASS', '');              // your MySQL password
define('DB_NAME', 'nexcore_db');    // database name

// ─── Establish Connection ─────────────────────────────────────────────────
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    // In production, log this — never expose DB errors to users
    error_log("DB Connection failed: " . $conn->connect_error);
    die(json_encode(['error' => 'Service temporarily unavailable. Please try later.']));
}

$conn->set_charset("utf8mb4");

// ─── Session Security ─────────────────────────────────────────────────────
// Call this at the top of any page that needs session management
function start_secure_session() {
    if (session_status() === PHP_SESSION_NONE) {
        ini_set('session.cookie_httponly', 1);
        ini_set('session.use_only_cookies', 1);
        // ini_set('session.cookie_secure', 1); // Uncomment when on HTTPS
        session_start();
    }
}

// ─── Auth Guard ───────────────────────────────────────────────────────────
// Call on pages that require login
function require_login() {
    start_secure_session();
    if (empty($_SESSION['user_id'])) {
        header("Location: login.php?msg=login_required");
        exit();
    }
}

// ─── CSRF Protection ─────────────────────────────────────────────────────
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>
