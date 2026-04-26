<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

session_start();

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['role']);
}

/**
 * Check if current session is a regular user
 */
function isUser() {
    return isLoggedIn() && $_SESSION['role'] === 'user';
}

/**
 * Check if current session is an admin
 */
function isAdmin() {
    return isLoggedIn() && $_SESSION['role'] === 'admin';
}

/**
 * Check if current session is a super admin
 */
function isSuperAdmin() {
    return isLoggedIn() && $_SESSION['role'] === 'superadmin';
}

/**
 * Require user login - redirect to user login if not authenticated
 */
function requireUser() {
    if (!isUser()) {
        header('Location: /book-request-system/user/login.php');
        exit;
    }
}

/**
 * Require admin login - redirect to admin login if not authenticated
 */
function requireAdmin() {
    if (!isAdmin() && !isSuperAdmin()) {
        header('Location: /book-request-system/admin/login.php');
        exit;
    }
}

/**
 * Require super admin login - redirect to superadmin login if not authenticated
 */
function requireSuperAdmin() {
    if (!isSuperAdmin()) {
        header('Location: /book-request-system/superadmin/login.php');
        exit;
    }
}

/**
 * Sanitize output to prevent XSS
 */
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>
