<?php
// Start session
session_start();

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    // If not logged in, redirect to login page
    header("Location: index.html");
    exit;
}

// Include database connection
require_once 'db.php';

// Fetch properties from the database
$query = $conn->query("SELECT * FROM Properties ORDER BY id DESC");
$properties = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="css/style.css"> <!-- Optional for custom styles -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.5/font/bootstrap-icons.min.css">

</head>
<body>
    <div class="container mt-5">
        <!-- Header Section -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Admin Dashboard</h1>
            <a href="logout.php" class="btn btn-danger">Logout</a>
        </div>

        <!-- Add Property Button -->
        <div class="text-end mb-3">
            <a href="add_property.php" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Add Property
            </a>
        </div>

        <!-- Property Cards -->
        <div class="row">
            <?php if (!empty($properties)): ?>
                <?php foreach ($properties as $property): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 shadow">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($property['address']); ?></h5>
                                <p class="card-text">
                                    <strong>Bedrooms:</strong> <?php echo htmlspecialchars($property['bedrooms']); ?><br>
                                    <strong>Bathrooms:</strong> <?php echo htmlspecialchars($property['bathrooms']); ?><br>
                                    <strong>Status:</strong> <?php echo htmlspecialchars($property['status']); ?>
                                </p>
                                <p class="text-muted small">Inspection: <?php echo htmlspecialchars($property['inspection_time']); ?></p>
                                <div class="d-flex justify-content-between">
                                    <!-- Edit Icon -->
                                    <a href="edit_property.php?id=<?php echo $property['id']; ?>" class="btn btn-sm btn-success">
                                        <i class="bi bi-pencil-square"></i> Edit
                                    </a>
                                    <!-- Delete Icon -->
                                    <a href="delete_property.php?id=<?php echo $property['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this property?');">
                                        <i class="bi bi-trash"></i> Delete
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-center">No properties found. Click the "Add Property" button to create a new listing.</p>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
