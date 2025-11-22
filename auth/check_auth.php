<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    if (!headers_sent()) {
        header('Location: /auth/login.php');
        exit();
    } else {
        echo '<script>window.location.href = "/auth/login.php";</script>';
        exit();
    }
}

// Check role-based access
function check_role($required_role) {
    if ($_SESSION['role'] !== $required_role && $_SESSION['role'] !== 'Admin') {
        header('HTTP/1.0 403 Forbidden');
        die('Access denied');
    }
}

// CSRF Token generation
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// CSRF Token validation
function validate_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>