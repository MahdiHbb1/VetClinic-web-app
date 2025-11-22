<?php
// Production database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'vetclinic_user');
define('DB_PASS', 'use_strong_password_in_production');
define('DB_NAME', 'vetclinic');

// Create PDO connection with security settings
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
            PDO::ATTR_PERSISTENT => false // Disable persistent connections
        ]
    );
    
    // Set additional MySQL session variables
    $pdo->exec("SET SESSION sql_mode = 'STRICT_ALL_TABLES'");
    $pdo->exec("SET SESSION max_execution_time = 30000"); // 30 seconds
    
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("Service temporarily unavailable. Please try again later.");
}

// Function to prevent SQL injection in LIKE clauses
function escapeLikeString($str) {
    return str_replace(['%', '_'], ['\%', '\_'], $str);
}

// Function to safely handle transactions
function executeTransaction($pdo, callable $callback) {
    try {
        $pdo->beginTransaction();
        $result = $callback($pdo);
        $pdo->commit();
        return $result;
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}