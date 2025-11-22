<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If already logged in, redirect based on role
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'Owner') {
        header("Location: /owners/portal/");
    } else {
        header("Location: /dashboard/");
    }
} else {
    // Show landing page
    header("Location: /landing.php");
}
exit;