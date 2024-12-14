<?php
require_once 'db.php';

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect form data
    $property_name = trim($_POST['property_name']);
    $inspection_slot = trim($_POST['inspection_slot']);  // inspection_slot is now a VARCHAR
    $user_name = trim($_POST['name']);
    $user_phone = trim($_POST['phone']);
    $user_email = trim($_POST['email']);

    try {
        // Insert booking into the database
        $query = $conn->prepare(
            "INSERT INTO InspectionBookings (property_name, inspection_slot, user_name, user_phone, user_email) 
            VALUES (:property_name, :inspection_slot, :user_name, :user_phone, :user_email)"
        );
        $query->execute([
            ':property_name' => $property_name,
            ':inspection_slot' => $inspection_slot,  // store the string as VARCHAR
            ':user_name' => $user_name,
            ':user_phone' => $user_phone,
            ':user_email' => $user_email,
        ]);

        // Send an email notification to the user
        $subject = "Inspection Booking Confirmation";
        $message = "Dear $user_name,\n\nYou have successfully booked an inspection for the property '$property_name' at $inspection_slot.\n\nThank you for your booking.";
        $headers = "From: no-reply@yourwebsite.com";

        mail($user_email, $subject, $message, $headers);

        // Redirect back to the property listings with a success message
        header("Location: view_properties.php?booking=success");
        exit;
    } catch (PDOException $e) {
        // Handle errors and redirect with an error message
        header("Location: view_properties.php?booking=error");
        exit;
    }
} else {
    // Redirect if the request method is not POST
    header("Location: view_properties.php");
    exit;
}
