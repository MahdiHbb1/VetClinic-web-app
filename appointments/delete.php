<?php
session_start();
require_once '../auth/check_auth.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/appointment_functions.php';

// Security headers
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' cdn.jsdelivr.net code.jquery.com; style-src 'self' 'unsafe-inline' cdn.jsdelivr.net cdnjs.cloudflare.com; img-src 'self' data: https:; font-src cdnjs.cloudflare.com");

// Validate user role
if ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Dokter') {
    $_SESSION['error'] = "Anda tidak memiliki akses untuk menghapus janji temu";
    header("Location: index.php");
    exit;
}

// Get appointment ID
$appointment_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$appointment_id) {
    $_SESSION['error'] = "ID Janji Temu tidak valid";
    header("Location: index.php");
    exit;
}

try {
    // Start transaction
    $pdo->beginTransaction();

    // Get appointment details first
    $stmt = $pdo->prepare("
        SELECT 
            a.*,
            p.nama_hewan,
            o.owner_id,
            o.nama_lengkap as owner_name,
            o.no_telepon as owner_phone
        FROM appointment a
        JOIN pet p ON a.pet_id = p.pet_id
        JOIN owner o ON a.owner_id = o.owner_id
        WHERE a.appointment_id = ?
    ");
    $stmt->execute([$appointment_id]);
    $appointment = $stmt->fetch();

    if (!$appointment) {
        throw new Exception("Data janji temu tidak ditemukan");
    }

    // Check if appointment can be deleted
    $appointment_date = strtotime($appointment['tanggal'] . ' ' . $appointment['jam_mulai']);
    $now = time();

    // Only allow deletion of future appointments or by admin
    if ($appointment_date < $now && $_SESSION['role'] !== 'Admin') {
        throw new Exception("Tidak dapat menghapus janji temu yang sudah lewat");
    }

    // Log deletion to appointment_history
    $stmt = $pdo->prepare("
        INSERT INTO appointment_history (
            appointment_id,
            action,
            old_status,
            new_status,
            performed_by,
            performed_at,
            notes
        ) VALUES (
            ?, 'DELETE', ?, 'DELETED', ?, NOW(),
            'Appointment deleted by " . $_SESSION['nama_lengkap'] . "'
        )
    ");
    $stmt->execute([
        $appointment_id,
        $appointment['status'],
        $_SESSION['user_id']
    ]);

    // Create notification for owner
    create_notification(
        $pdo,
        $appointment['owner_id'],
        'appointment_deleted',
        $appointment_id
    );

    // Delete related records first
    $stmt = $pdo->prepare("DELETE FROM appointment_layanan WHERE appointment_id = ?");
    $stmt->execute([$appointment_id]);

    $stmt = $pdo->prepare("DELETE FROM medical_record WHERE appointment_id = ?");
    $stmt->execute([$appointment_id]);

    // Finally, delete the appointment
    $stmt = $pdo->prepare("DELETE FROM appointment WHERE appointment_id = ?");
    $stmt->execute([$appointment_id]);

    // Create system log
    $log_message = sprintf(
        "Appointment #%d for pet '%s' (owner: %s) deleted by %s (%s)",
        $appointment_id,
        $appointment['nama_hewan'],
        $appointment['owner_name'],
        $_SESSION['nama_lengkap'],
        $_SESSION['role']
    );
    
    $stmt = $pdo->prepare("
        INSERT INTO system_log (
            user_id,
            action,
            entity_type,
            entity_id,
            description,
            ip_address,
            user_agent,
            created_at
        ) VALUES (
            ?, 'DELETE', 'appointment', ?, ?, ?, ?, NOW()
        )
    ");
    $stmt->execute([
        $_SESSION['user_id'],
        $appointment_id,
        $log_message,
        get_client_ip(),
        $_SERVER['HTTP_USER_AGENT']
    ]);

    // Send SMS notification if enabled
    if (ENABLE_SMS_NOTIFICATIONS) {
        $message = sprintf(
            "Janji temu untuk %s pada tanggal %s jam %s telah dibatalkan. Hubungi kami untuk informasi lebih lanjut.",
            $appointment['nama_hewan'],
            date('d/m/Y', strtotime($appointment['tanggal'])),
            date('H:i', strtotime($appointment['jam_mulai']))
        );
        
        send_sms($appointment['owner_phone'], $message);
    }

    // Send email notification if enabled
    if (ENABLE_EMAIL_NOTIFICATIONS && !empty($appointment['owner_email'])) {
        $subject = "Pembatalan Janji Temu - VetClinic";
        $email_body = generate_appointment_cancellation_email(
            $appointment['nama_hewan'],
            $appointment['tanggal'],
            $appointment['jam_mulai'],
            $appointment['owner_name']
        );
        
        send_email($appointment['owner_email'], $subject, $email_body);
    }

    // Commit transaction
    $pdo->commit();

    // Set success message
    $_SESSION['success'] = "Janji temu berhasil dihapus";

} catch (Exception $e) {
    // Rollback transaction on error
    $pdo->rollBack();
    
    // Log error
    error_log("Error deleting appointment #$appointment_id: " . $e->getMessage());
    
    // Set error message
    $_SESSION['error'] = "Error: " . $e->getMessage();
}

// Redirect back to index
header("Location: index.php");
exit;