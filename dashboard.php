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

try {
    // Fetch properties from the database
    $query = $conn->query("SELECT * FROM Properties ORDER BY id DESC");
    $properties = $query->fetchAll(PDO::FETCH_ASSOC);

    // Fetch inspection times and group them by property_id
    $inspectionQuery = $conn->query("SELECT property_id, inspection_slot FROM InspectionTimes");
    $inspectionSlots = $inspectionQuery->fetchAll(PDO::FETCH_ASSOC);

    // Organize inspection slots by property_id
    $inspectionsByProperty = [];
    foreach ($inspectionSlots as $slot) {
        $propertyId = $slot['property_id'];
        if (!isset($inspectionsByProperty[$propertyId])) {
            $inspectionsByProperty[$propertyId] = [];
        }
        $inspectionsByProperty[$propertyId][] = $slot['inspection_slot'];
    }

    // Fetch all inspection bookings
    $bookingQuery = $conn->query("SELECT * FROM InspectionBookings");
    $bookings = $bookingQuery->fetchAll(PDO::FETCH_ASSOC);

    // Create a mapping from property address to property id
    $propertyAddressToId = [];
    foreach ($properties as $property) {
        $propertyAddressToId[$property['address']] = $property['id'];
    }

    // Organize bookings by property_id
    $bookingsByProperty = [];
    foreach ($bookings as $booking) {
        $propertyName = $booking['property_name'];
        if (isset($propertyAddressToId[$propertyName])) {
            $propertyId = $propertyAddressToId[$propertyName];
            if (!isset($bookingsByProperty[$propertyId])) {
                $bookingsByProperty[$propertyId] = [];
            }
            $bookingsByProperty[$propertyId][] = $booking;
        }
    }
} catch (PDOException $e) {
    // Handle database errors
    echo "Error: " . htmlspecialchars($e->getMessage());
    exit;
}
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

        <!-- Buttons Section -->
        <div class="d-flex justify-content-between mb-3">
            <!-- Add Property Button -->
            <a href="add_property.php" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Add Property
            </a>

            <!-- View Properties Button -->
            <a href="https://system.tengdragon.com.au/insp/view_properties.php" target="_blank" class="btn btn-secondary">
                <i class="bi bi-eye"></i> View Properties
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
                                <p class="text-muted small">
                                    <strong>Inspection Slots:</strong><br>
                                    <?php if (isset($inspectionsByProperty[$property['id']])): ?>
                                        <?php foreach ($inspectionsByProperty[$property['id']] as $slot): ?>
                                            <?php echo htmlspecialchars($slot); ?><br>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        No inspection slots available.
                                    <?php endif; ?>
                                </p>
                                <div class="d-flex justify-content-between mt-3">
                                    <!-- Edit Icon -->
                                    <a href="edit_property.php?id=<?php echo urlencode($property['id']); ?>" class="btn btn-sm btn-success">
                                        <i class="bi bi-pencil-square"></i> Edit
                                    </a>
                                    <!-- Delete Icon -->
                                    <a href="delete_property.php?id=<?php echo urlencode($property['id']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this property?');">
                                        <i class="bi bi-trash"></i> Delete
                                    </a>
                                    <!-- People Like Icon with Booking Count -->
                                    <?php
                                        // Calculate the number of bookings for the current property
                                        $bookingCount = isset($bookingsByProperty[$property['id']]) ? count($bookingsByProperty[$property['id']]) : 0;
                                    ?>
                                    <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#peopleModal-<?php echo htmlspecialchars($property['id']); ?>">
                                        <i class="bi bi-people-fill"></i> <?php echo $bookingCount; ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal for People Interested in Property (Unique per Property) -->
                    <div class="modal fade" id="peopleModal-<?php echo htmlspecialchars($property['id']); ?>" tabindex="-1" aria-labelledby="peopleModalLabel-<?php echo htmlspecialchars($property['id']); ?>" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="peopleModalLabel-<?php echo htmlspecialchars($property['id']); ?>">People Interested in <?php echo htmlspecialchars($property['address']); ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <?php
                                    $currentPropertyId = $property['id'];
                                    if (isset($bookingsByProperty[$currentPropertyId]) && !empty($bookingsByProperty[$currentPropertyId])):
                                    ?>
                                        <ul class="list-group">
                                            <?php foreach ($bookingsByProperty[$currentPropertyId] as $booking): ?>
                                                <li class="list-group-item">
                                                    <strong>Name:</strong> <?php echo htmlspecialchars($booking['user_name']); ?><br>
                                                    <strong>Phone:</strong> <?php echo htmlspecialchars($booking['user_phone']); ?><br>
                                                    <strong>Email:</strong> <?php echo htmlspecialchars($booking['user_email']); ?>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php else: ?>
                                        <p>No bookings found for this property.</p>
                                    <?php endif; ?>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
