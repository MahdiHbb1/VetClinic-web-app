<?php
/**
 * Owner Portal Authentication Check
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header('Location: /owners/portal/login.php');
    exit;
}

// Check if user is an owner
if ($_SESSION['role'] !== 'Owner') {
    header('Location: /auth/login.php?error=access_denied');
    exit;
}

// Verify owner record exists
require_once __DIR__ . '/../../config/database.php';

$stmt = $pdo->prepare("SELECT owner_id, nama_lengkap, email FROM owner WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$owner = $stmt->fetch();

if (!$owner) {
    session_destroy();
    header('Location: /owners/portal/login.php?error=invalid_owner');
    exit;
}

// Store owner info in session
$_SESSION['owner_id'] = $owner['owner_id'];
$_SESSION['owner_name'] = $owner['nama_lengkap'];
$_SESSION['owner_email'] = $owner['email'];

// Update last activity (owner table already has last_login column from migration)
$stmt = $pdo->prepare("UPDATE owner SET last_login = NOW() WHERE owner_id = ?");
$stmt->execute([$owner['owner_id']]);
?>
