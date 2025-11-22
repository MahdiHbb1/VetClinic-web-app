<?php
/**
 * Validate appointment date and time
 * 
 * @param string $date Date in Y-m-d format
 * @param string $time Time in H:i format
 * @return bool
 */
function validate_appointment_datetime($date, $time) {
    // Convert to DateTime objects
    $appointment_date = new DateTime($date . ' ' . $time);
    $now = new DateTime();
    
    // Check if appointment is in the past
    if ($appointment_date < $now) {
        return false;
    }
    
    // Check if date is too far in the future (3 months)
    $max_date = clone $now;
    $max_date->modify('+3 months');
    if ($appointment_date > $max_date) {
        return false;
    }
    
    // Check if time is within business hours (8:00 - 20:00)
    $hour = (int)$appointment_date->format('H');
    if ($hour < 8 || $hour >= 20) {
        return false;
    }
    
    return true;
}

/**
 * Check if doctor is available at the given time
 * 
 * @param PDO $pdo Database connection
 * @param int $dokter_id Doctor ID
 * @param string $date Date in Y-m-d format
 * @param string $start_time Start time in H:i format
 * @param string $end_time End time in H:i format
 * @param int|null $exclude_appointment_id Appointment ID to exclude from check (for updates)
 * @return bool
 */
function is_doctor_available($pdo, $dokter_id, $date, $start_time, $end_time, $exclude_appointment_id = null) {
    try {
        // Check doctor's schedule first
        $stmt = $pdo->prepare("
            SELECT jadwal_praktek
            FROM veterinarian
            WHERE dokter_id = ? AND status = 'Aktif'
        ");
        $stmt->execute([$dokter_id]);
        $schedule = $stmt->fetchColumn();

        // If no schedule or doctor not active, allow booking (simplified)
        // In production, you'd want to properly validate against doctor's schedule
        
        // Check for overlapping appointments
        $query = "
            SELECT COUNT(*)
            FROM appointment
            WHERE dokter_id = ?
            AND tanggal_appointment = ?
            AND jam_appointment = ?
            AND status NOT IN ('Cancelled', 'No_Show')
        ";
        $params = [
            $dokter_id,
            $date,
            $start_time
        ];

        // Exclude current appointment if updating
        if ($exclude_appointment_id) {
            $query .= " AND appointment_id != ?";
            $params[] = $exclude_appointment_id;
        }

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $overlapping = $stmt->fetchColumn();

        return $overlapping === 0;

    } catch (Exception $e) {
        error_log("Error checking doctor availability: " . $e->getMessage());
        return false;
    }
}

/**
 * Create a notification for a user
 * 
 * @param PDO $pdo Database connection
 * @param int $user_id User ID
 * @param string $type Notification type
 * @param int $reference_id Reference ID (e.g., appointment_id)
 * @return bool
 */
function create_notification($pdo, $user_id, $type, $reference_id) {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO notification (
                user_id,
                type,
                reference_id,
                created_at,
                status
            ) VALUES (
                ?, ?, ?, CURRENT_TIMESTAMP, 'Unread'
            )
        ");
        
        return $stmt->execute([$user_id, $type, $reference_id]);
    } catch (Exception $e) {
        error_log("Error creating notification: " . $e->getMessage());
        return false;
    }
}

/**
 * Get appointment status badge HTML
 * 
 * @param string $status Appointment status
 * @return string HTML for status badge
 */
function get_appointment_status_badge($status) {
    $colors = [
        'Pending' => 'bg-yellow-100 text-yellow-800',
        'Confirmed' => 'bg-green-100 text-green-800',
        'Completed' => 'bg-blue-100 text-blue-800',
        'Cancelled' => 'bg-red-100 text-red-800',
        'No_Show' => 'bg-gray-100 text-gray-800'
    ];
    
    $labels = [
        'Pending' => 'Menunggu',
        'Confirmed' => 'Dikonfirmasi',
        'Completed' => 'Selesai',
        'Cancelled' => 'Dibatalkan',
        'No_Show' => 'Tidak Hadir'
    ];
    
    $color_class = $colors[$status] ?? 'bg-gray-100 text-gray-800';
    $label = $labels[$status] ?? $status;
    
    return sprintf(
        '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full %s">%s</span>',
        $color_class,
        $label
    );
}