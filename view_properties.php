<?php
require_once 'db.php';

// Fetch all available properties
try {
    $propertiesQuery = $conn->prepare("SELECT * FROM Properties WHERE status = 'Available' OR status = 'Deposit Taken'");
    $propertiesQuery->execute();
    $properties = $propertiesQuery->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching properties: " . $e->getMessage());
}

// If there are properties, fetch all inspection times for these properties
$inspectionTimes = [];
if (!empty($properties)) {
    // Extract property IDs
    $propertyIds = array_column($properties, 'id');
    // Prepare placeholders for the IN clause
    $placeholders = implode(',', array_fill(0, count($propertyIds), '?'));
    
    try {
        $inspectionQuery = $conn->prepare("SELECT * FROM InspectionTimes WHERE property_id IN ($placeholders)");
        $inspectionQuery->execute($propertyIds);
        $inspectionResults = $inspectionQuery->fetchAll(PDO::FETCH_ASSOC);
        
        // Map inspection slots to property IDs
        foreach ($inspectionResults as $slot) {
            $inspectionTimes[$slot['property_id']][] = $slot['inspection_slot'];
        }
    } catch (PDOException $e) {
        die("Error fetching inspection times: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property Listings</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .property-list {
            list-style-type: none;
            padding: 0;
        }
        .property-item {
            border: 1px solid #ddd;
            border-radius: 10px;
            margin-bottom: 30px;
            padding: 20px;
            background-color: #ffffff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .property-header {
            font-size: 1.5rem;
            font-weight: bold;
            color: #333333;
        }
        .property-details {
            margin-top: 15px;
            font-size: 1rem;
            color: #555555;
        }
        .property-details i {
            margin-right: 8px;
            color: #007bff;
        }
        .notes-section {
            margin-top: 15px;
            font-style: italic;
            color: #666666;
        }
        .inspection-times {
            margin-top: 15px;
        }
        .inspection-times ul {
            list-style-type: none;
            padding: 0;
        }
        .inspection-times li {
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .inspection-times li i {
            margin-right: 8px;
            color: #17a2b8;
        }
        .btn-book {
            background-color: #28a745;
            color: #fff;
            border: none;
            padding: 5px 12px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .btn-book:hover {
            background-color: #218838;
        }
        .share-btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            text-align: center;
            cursor: pointer;
            margin-top: 20px;
        }
        .share-btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">Available Properties</h1>

        <!-- Booking Success/Error Messages -->
        <?php if (isset($_GET['booking']) && $_GET['booking'] === 'success'): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Your inspection booking has been confirmed! A confirmation email has been sent to you.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php elseif (isset($_GET['booking']) && $_GET['booking'] === 'error'): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                There was an error booking your inspection. Please try again later.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Property Listings -->
        <?php if (empty($properties)): ?>
            <div class="alert alert-info">No properties available at the moment.</div>
        <?php else: ?>
            <ul class="property-list">
                <?php foreach ($properties as $property): ?>
                    <li class="property-item">
                        <div class="property-header"><?php echo htmlspecialchars($property['address']); ?></div>
                        <div class="property-details">
                            <p>
                                <i class="fas fa-bed"></i> <strong>Bedrooms:</strong> <?php echo htmlspecialchars($property['bedrooms']); ?><br>
                                <i class="fas fa-bath"></i> <strong>Bathrooms:</strong> <?php echo htmlspecialchars($property['bathrooms']); ?><br>
                                <i class="fas fa-car"></i> <strong>Parking:</strong> <?php echo htmlspecialchars($property['parking']); ?><br>
                                <i class="fas fa-dollar-sign"></i> <strong>Rent:</strong> $<?php echo number_format($property['rent_per_week'], 2); ?> / Week
                            </p>
                        </div>
                        <?php if (!empty($property['notes'])): ?>
                            <div class="notes-section">
                                <strong>Notes:</strong> <?php echo nl2br(htmlspecialchars($property['notes'])); ?>
                            </div>
                        <?php endif; ?>
                        <div class="inspection-times">
                            <strong>Inspection Times:</strong>
                            <?php 
                            if (isset($inspectionTimes[$property['id']]) && !empty($inspectionTimes[$property['id']])): ?>
                                <ul>
                                    <?php foreach ($inspectionTimes[$property['id']] as $time): ?>
                                        <li>
                                            <span><i class="fas fa-calendar-alt"></i> <?php echo htmlspecialchars($time); ?></span>
                                            <button 
                                                class="btn-book" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#bookingModal" 
                                                data-property="<?php echo htmlspecialchars($property['address']); ?>" 
                                                data-slot="<?php echo htmlspecialchars($time); ?>">
                                                Book
                                            </button>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <p>No inspections scheduled.</p>
                            <?php endif; ?>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>

            <!-- Share Entire Page -->
            <div class="text-center mt-4">
                <button class="share-btn btn btn-primary" onclick="sharePage()">Share This Page</button>
            </div>
        <?php endif; ?>
    </div>

    <!-- Booking Form Modal -->
    <div class="modal fade" id="bookingModal" tabindex="-1" aria-labelledby="bookingModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="book_inspection.php" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="bookingModalLabel">Book Inspection</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Hidden Fields for Property Name and Inspection Slot -->
                        <input type="hidden" name="property_name" id="modal-property-name">
                        <input type="hidden" name="inspection_slot" id="modal-inspection-slot">
                        
                        <!-- Display Selected Property and Slot -->
                        <div class="mb-3">
                            <label class="form-label"><strong>Property:</strong></label>
                            <p id="selected-property"></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><strong>Inspection Slot:</strong></label>
                            <p id="selected-slot"></p>
                        </div>

                        <!-- User Information Fields -->
                        <div class="mb-3">
                            <label for="user-name" class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="user-name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="user-phone" class="form-label">Mobile <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="user-phone" name="phone" required pattern="\d{10,15}" title="Please enter a valid phone number (10-15 digits).">
                        </div>
                        <div class="mb-3">
                            <label for="user-email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="user-email" name="email" required>
                        </div>
                        <p class="text-muted"><small>* Required fields</small></p>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Submit Booking</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies (Popper) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Function to handle "Book Inspection" button clicks
        const bookingModal = document.getElementById('bookingModal');
        bookingModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget; // Button that triggered the modal
            const property = button.getAttribute('data-property');
            const slot = button.getAttribute('data-slot');

            // Update the modal's content
            const modalTitle = bookingModal.querySelector('.modal-title');
            const selectedProperty = bookingModal.querySelector('#selected-property');
            const selectedSlot = bookingModal.querySelector('#selected-slot');
            const hiddenProperty = bookingModal.querySelector('#modal-property-name');
            const hiddenSlot = bookingModal.querySelector('#modal-inspection-slot');

            modalTitle.textContent = 'Book Inspection for ' + property;
            selectedProperty.textContent = property;
            selectedSlot.textContent = slot;
            hiddenProperty.value = property;
            hiddenSlot.value = slot;
        });

        // Share Entire Page Function
        function sharePage() {
            // Define your actual page URL here
            const pageUrl = window.location.href;

            if (navigator.share) {
                // Use native sharing API if available
                navigator.share({
                    title: 'Property Listings',
                    text: 'Check out all available properties!',
                    url: pageUrl
                }).catch((error) => console.error('Error sharing:', error));
            } else {
                // Fallback: Copy the page URL to the clipboard
                navigator.clipboard.writeText(pageUrl).then(() => {
                    alert("Page URL copied to clipboard!");
                }).catch((error) => console.error('Error copying to clipboard:', error));
            }
        }
    </script>
</body>
</html>
