<?php
// Database configuration — XAMPP defaults
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');           // XAMPP default: no password
define('DB_NAME', 'issue_portal');

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    die('<div style="font-family:sans-serif;padding:40px;color:#c00;">
        <h2>Database Connection Failed</h2>
        <p>' . mysqli_connect_error() . '</p>
        <p>Make sure XAMPP MySQL is running and you have imported <code>database.sql</code>.</p>
    </div>');
}

mysqli_set_charset($conn, 'utf8');

// Session start (safe to call multiple times)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Helper: check if logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Helper: check if admin
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Helper: redirect shorthand
function redirect($url) {
    header("Location: $url");
    exit();
}

// Helper: sanitize input
function clean($conn, $data) {
    return mysqli_real_escape_string($conn, htmlspecialchars(trim($data)));
}
?>