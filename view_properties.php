<?php
require_once 'db.php';

// Fetch all properties
try {
    $query = $conn->prepare("SELECT * FROM Properties WHERE status = 'Available' OR status = 'Deposit Taken'");
    $query->execute();
    $properties = $query->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching properties: " . $e->getMessage());
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
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #ddd;
            padding: 16px 0;
        }
        .property-item:last-child {
            border-bottom: none;
        }
        .property-header {
            font-size: 1.25rem;
            font-weight: bold;
        }
        .property-icons i {
            margin-right: 8px;
        }
        .property-details {
            margin-top: 8px;
        }
        .rent {
            font-size: 1.25rem;
            font-weight: bold;
            color: #2a9d8f;
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
        <?php if (empty($properties)): ?>
            <div class="alert alert-info">No properties available at the moment.</div>
        <?php else: ?>
            <ul class="property-list">
                <?php foreach ($properties as $property): ?>
                    <li class="property-item">
                        <div>
                            <div class="property-header"><?php echo htmlspecialchars($property['address']); ?></div>
                            <div class="property-details mt-2">
                                <p>
                                    <i class="fas fa-bed"></i> <?php echo htmlspecialchars($property['bedrooms']); ?> Bedrooms
                                    <i class="fas fa-bath ms-3"></i> <?php echo htmlspecialchars($property['bathrooms']); ?> Bathrooms
                                    <i class="fas fa-car ms-3"></i> <?php echo htmlspecialchars($property['parking']); ?>
                                </p>
                            </div>
                            <div class="rent">
                                $<?php echo number_format($property['rent_per_week'], 2); ?> / Week
                            </div>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>

            <!-- Share Entire Page -->
            <div class="text-center mt-4">
                <button class="share-btn" onclick="sharePage()">Share This Page</button>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function sharePage() {
            // Define a custom placeholder link
            const customLink = "https://yourdomain.com/properties"; // Replace with your preferred alias or custom link
            
            if (navigator.share) {
                // Use native sharing API if available
                navigator.share({
                    title: 'Property Listings',
                    text: 'Check out all available properties!',
                    url: customLink
                }).catch((error) => console.error('Error sharing:', error));
            } else {
                // Fallback: Copy the custom link to the clipboard
                navigator.clipboard.writeText(customLink).then(() => {
                    alert("Custom link copied to clipboard!");
                }).catch((error) => console.error('Error copying to clipboard:', error));
            }
        }
    </script>
</body>
</html>
