<?php
// includes/auth.php - Session & Auth helpers

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function requireAdmin() {
    if (!isLoggedIn() || !isAdmin()) {
        header('Location: dashboard.php');
        exit;
    }
}

function currentUserId() {
    return $_SESSION['user_id'] ?? null;
}

function currentUserName() {
    return $_SESSION['name'] ?? 'Guest';
}
?>
