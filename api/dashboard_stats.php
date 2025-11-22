<?php
require_once '../config/database.php';
require_once '../auth/check_auth.php';

header('Content-Type: application/json');

$type = $_GET['type'] ?? '';

switch ($type) {
    case 'revenue':
        // Get revenue for last 6 months
        $stmt = $pdo->query("
            SELECT 
                DATE_FORMAT(a.created_at, '%Y-%m') as month,
                SUM(al.subtotal) as total
            FROM appointment_layanan al
            JOIN appointment a ON al.appointment_id = a.appointment_id
            WHERE a.created_at >= DATE_SUB(CURRENT_DATE, INTERVAL 6 MONTH)
            GROUP BY DATE_FORMAT(a.created_at, '%Y-%m')
            ORDER BY month ASC
        ");
        $revenue_data = $stmt->fetchAll();

        // Format data for chart
        $months = [];
        $values = [];
        
        // Indonesian month names
        $month_names = [
            '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr',
            '05' => 'Mei', '06' => 'Jun', '07' => 'Jul', '08' => 'Agt',
            '09' => 'Sep', '10' => 'Okt', '11' => 'Nov', '12' => 'Des'
        ];

        foreach ($revenue_data as $data) {
            list($year, $month) = explode('-', $data['month']);
            $months[] = $month_names[$month] . ' ' . $year;
            $values[] = (float)$data['total'];
        }

        echo json_encode([
            'months' => $months,
            'values' => $values
        ]);
        break;

    case 'appointments':
        // Get today's appointments count by status
        $stmt = $pdo->prepare("
            SELECT status, COUNT(*) as count
            FROM appointment
            WHERE DATE(tanggal_appointment) = CURRENT_DATE
            GROUP BY status
        ");
        $stmt->execute();
        $appointments = $stmt->fetchAll();

        echo json_encode($appointments);
        break;

    case 'services':
        // Get top services
        $stmt = $pdo->query("
            SELECT s.nama_layanan, COUNT(*) as count
            FROM appointment_layanan al
            JOIN service s ON al.layanan_id = s.layanan_id
            GROUP BY s.layanan_id
            ORDER BY count DESC
            LIMIT 5
        ");
        $services = $stmt->fetchAll();

        echo json_encode($services);
        break;

    case 'diagnoses':
        // Get top diagnoses
        $stmt = $pdo->query("
            SELECT diagnosa, COUNT(*) as count
            FROM medical_record
            GROUP BY diagnosa
            ORDER BY count DESC
            LIMIT 5
        ");
        $diagnoses = $stmt->fetchAll();

        echo json_encode($diagnoses);
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid type parameter']);
}
?>