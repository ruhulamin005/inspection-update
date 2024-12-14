<?php
// get_bookings.php

// Enable error reporting for debugging (disable in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

require_once 'db.php';

if (isset($_GET['property_id']) && is_numeric($_GET['property_id'])) {
    $property_id = intval($_GET['property_id']);

    if ($property_id <= 0) {
        echo json_encode(['error' => 'Invalid property ID']);
        exit;
    }

    try {
        // Prepare and execute the query to fetch bookings for the given property_id
        $stmt = $conn->prepare("SELECT id, inspection_slot, user_name, user_phone, user_email, booking_time FROM InspectionBookings WHERE property_id = ? ORDER BY booking_time DESC");
        $stmt->execute([$property_id]);
        $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($bookings);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database query failed', 'details' => $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Property ID not provided or invalid']);
}
?>
