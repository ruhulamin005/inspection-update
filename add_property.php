<?php
// Start session
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: index.html");
    exit;
}

require_once 'db.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $address = trim($_POST['address']);
    $bedrooms = (int)$_POST['bedrooms'];
    $bathrooms = (int)$_POST['bathrooms'];
    $parking = trim($_POST['parking']); // Parking as text
    $rent_per_week = (float)$_POST['rent_per_week']; // Rent per week as a decimal
    $status = $_POST['status'];
    $inspection_time = trim($_POST['inspection_time']); // Inspection time as text
    $notes = trim($_POST['notes']);

    try {
        // Insert property details into the Properties table
        $query = $conn->prepare(
            "INSERT INTO Properties (address, bedrooms, bathrooms, parking, rent_per_week, inspection_time, status, notes) 
            VALUES (:address, :bedrooms, :bathrooms, :parking, :rent_per_week, :inspection_time, :status, :notes)"
        );
        $query->execute([
            ':address' => $address,
            ':bedrooms' => $bedrooms,
            ':bathrooms' => $bathrooms,
            ':parking' => $parking,
            ':rent_per_week' => $rent_per_week,
            ':inspection_time' => $inspection_time,
            ':status' => $status,
            ':notes' => $notes,
        ]);

        // Redirect to the dashboard
        header("Location: dashboard.php");
        exit;
    } catch (PDOException $e) {
        $error = "Error adding property: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Property</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h1>Add Property</h1>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form action="add_property.php" method="POST">
            <div class="mb-3">
                <label for="address" class="form-label">Address</label>
                <input type="text" class="form-control" id="address" name="address" required>
            </div>
            <div class="mb-3">
                <label for="bedrooms" class="form-label">Bedrooms</label>
                <input type="number" class="form-control" id="bedrooms" name="bedrooms" required>
            </div>
            <div class="mb-3">
                <label for="bathrooms" class="form-label">Bathrooms</label>
                <input type="number" class="form-control" id="bathrooms" name="bathrooms" required>
            </div>
            <div class="mb-3">
                <label for="parking" class="form-label">Parking</label>
                <input type="text" class="form-control" id="parking" name="parking" placeholder="e.g., Garage, Street Parking, None" required>
            </div>
            <div class="mb-3">
                <label for="rent_per_week" class="form-label">Rent Per Week ($)</label>
                <input type="number" step="0.01" class="form-control" id="rent_per_week" name="rent_per_week" placeholder="e.g., 650.50" required>
            </div>
            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-control" id="status" name="status" required>
                    <option value="Available">Available</option>
                    <option value="Deposit Taken">Deposit Taken</option>
                    <option value="Leased Already">Leased Already</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="inspection_time" class="form-label">Inspection Time</label>
                <input type="text" class="form-control" id="inspection_time" name="inspection_time" placeholder="e.g., 17/12/2024 4:30 pm to 5:00 pm">
            </div>
            <div class="mb-3">
                <label for="notes" class="form-label">Notes</label>
                <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Add Property</button>
            <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</body>
</html>
