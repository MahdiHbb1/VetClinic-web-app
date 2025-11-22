<?php
session_start();
require_once '../auth/check_auth.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

// Get pet ID from URL
$pet_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$pet_id) {
    $_SESSION['error'] = "ID Hewan tidak valid";
    header("Location: index.php");
    exit;
}

try {
    // Start transaction
    $pdo->beginTransaction();

    // Get pet data to delete photo
    $stmt = $pdo->prepare("SELECT foto_url FROM pet WHERE pet_id = ?");
    $stmt->execute([$pet_id]);
    $pet = $stmt->fetch();

    // Delete related records first (if any)
    $pdo->prepare("DELETE FROM vaksinasi WHERE pet_id = ?")->execute([$pet_id]);
    $pdo->prepare("DELETE FROM appointment WHERE pet_id = ?")->execute([$pet_id]);

    // Delete pet record
    $stmt = $pdo->prepare("DELETE FROM pet WHERE pet_id = ?");
    $stmt->execute([$pet_id]);

    // Delete photo file if exists
    if ($pet && $pet['foto_url']) {
        $photo_path = __DIR__ . '/../assets/images/uploads/' . $pet['foto_url'];
        if (file_exists($photo_path)) {
            unlink($photo_path);
        }
    }

    // Commit transaction
    $pdo->commit();

    $_SESSION['success'] = "Data hewan berhasil dihapus";

} catch (Exception $e) {
    // Rollback transaction on error
    $pdo->rollBack();
    $_SESSION['error'] = "Error: " . $e->getMessage();
}

header("Location: index.php");
exit;