<?php
// Start session
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: index.html");
    exit;
}

require_once 'db.php';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    try {
        $query = $conn->prepare("DELETE FROM Properties WHERE id = :id");
        $query->execute([':id' => $id]);
        header("Location: dashboard.php");
        exit;
    } catch (PDOException $e) {
        die("Error deleting property: " . $e->getMessage());
    }
} else {
    header("Location: dashboard.php");
    exit;
}
?>
