<?php
// Database configuration for Docker
define('DB_HOST', 'db');  // nama service MySQL di docker-compose
define('DB_USER', 'vetclinic_user');
define('DB_PASS', 'vetclinic_password');
define('DB_NAME', 'vetclinic');

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>