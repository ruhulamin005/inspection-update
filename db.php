<?php
// Database connection settings
$host = '127.0.0.1'; // This will work for both localhost and IP
$dbname = 'u842291920_dbtd';
$username = 'root';
$password = null; // No password required

try {
    // Create a new PDO instance
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);

    // Set PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Optional: Uncomment this line to confirm the connection is successful
    // echo "Connected successfully";
} catch (PDOException $e) {
    // Display error message if connection fails
    die("Connection failed: " . $e->getMessage());
}
?>
