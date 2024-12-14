<?php
// Enable error reporting for debugging (disable in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: index.html");
    exit;
}

require_once 'db.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize input data
    $address = filter_var(trim($_POST['address']), FILTER_SANITIZE_STRING);
    $bedrooms = filter_var($_POST['bedrooms'], FILTER_VALIDATE_INT);
    $bathrooms = filter_var($_POST['bathrooms'], FILTER_VALIDATE_INT);
    $parking = filter_var(trim($_POST['parking']), FILTER_SANITIZE_STRING);
    $rent_per_week = filter_var($_POST['rent_per_week'], FILTER_VALIDATE_FLOAT);
    $status = filter_var($_POST['status'], FILTER_SANITIZE_STRING);
    $notes = trim($_POST['notes']);

    // Validate required fields
    if ($address === false || $bedrooms === false || $bathrooms === false || 
        $parking === false || $rent_per_week === false || empty($status)) {
        $error = "Please provide valid inputs for all required fields.";
    } else {
        // Collect inspection slots
        $inspection_slots = $_POST['inspection_slots'] ?? []; // Array of start and end times

        try {
            // Begin a transaction to ensure data integrity
            $conn->beginTransaction();

            // Insert property details into the Properties table
            $query = $conn->prepare(
                "INSERT INTO Properties (address, bedrooms, bathrooms, parking, rent_per_week, status, notes) 
                VALUES (:address, :bedrooms, :bathrooms, :parking, :rent_per_week, :status, :notes)"
            );
            $query->execute([
                ':address' => $address,
                ':bedrooms' => $bedrooms,
                ':bathrooms' => $bathrooms,
                ':parking' => $parking,
                ':rent_per_week' => $rent_per_week,
                ':status' => $status,
                ':notes' => $notes,
            ]);

            // Get the ID of the newly inserted property
            $property_id = $conn->lastInsertId();

            // Prepare the inspection times insert statement
            $inspection_query = $conn->prepare(
                "INSERT INTO InspectionTimes (property_id, inspection_slot) VALUES (:property_id, :inspection_slot)"
            );

            foreach ($inspection_slots as $slot) {
                if (!isset($slot['start']) || !isset($slot['end'])) {
                    continue; // Skip if start or end time is not set
                }

                $start_time = $slot['start'];
                $end_time = $slot['end'];

                if (empty($start_time) || empty($end_time)) {
                    continue; // Skip incomplete slots
                }

                // Create DateTime objects for start and end times
                try {
                    $start_time_obj = new DateTime($start_time);
                    $end_time_obj = new DateTime($end_time);
                } catch (Exception $e) {
                    throw new Exception("Invalid date format in inspection slots.");
                }

                // Ensure that end time is after start time
                if ($end_time_obj <= $start_time_obj) {
                    throw new Exception("End time must be after start time for all inspection slots.");
                }

                // Format start time: "14-12-2024 10:00 pm"
                $start_time_formatted = $start_time_obj->format("d-m-Y h:i a");

                // Format end time: "10:30 pm" (same day as start time)
                $end_time_formatted = $end_time_obj->format("h:i a");

                // Combine into a single string: "14-12-2024 10:00 pm to 10:30 pm"
                $inspection_slot_formatted = "$start_time_formatted to $end_time_formatted";

                // Execute the inspection slot insert
                $inspection_query->execute([
                    ':property_id' => $property_id,
                    ':inspection_slot' => $inspection_slot_formatted,
                ]);
            }

            // Commit the transaction
            $conn->commit();

            // Redirect to the dashboard after successful insertion
            header("Location: dashboard.php");
            exit;
        } catch (PDOException $e) {
            // Rollback the transaction on error
            $conn->rollBack();
            $error = "Database Error: " . $e->getMessage();
        } catch (Exception $e) {
            // Rollback the transaction on general error
            $conn->rollBack();
            $error = "Error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- [HTML Head Content] -->
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
            <!-- [Form Fields] -->
            <!-- Address -->
            <div class="mb-3">
                <label for="address" class="form-label">Address</label>
                <input type="text" class="form-control" id="address" name="address" required value="<?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?>">
            </div>
            <!-- Bedrooms -->
            <div class="mb-3">
                <label for="bedrooms" class="form-label">Bedrooms</label>
                <input type="number" class="form-control" id="bedrooms" name="bedrooms" required min="0" value="<?php echo isset($_POST['bedrooms']) ? htmlspecialchars($_POST['bedrooms']) : '0'; ?>">
            </div>
            <!-- Bathrooms -->
            <div class="mb-3">
                <label for="bathrooms" class="form-label">Bathrooms</label>
                <input type="number" class="form-control" id="bathrooms" name="bathrooms" required min="0" value="<?php echo isset($_POST['bathrooms']) ? htmlspecialchars($_POST['bathrooms']) : '0'; ?>">
            </div>
            <!-- Parking -->
            <div class="mb-3">
                <label for="parking" class="form-label">Parking</label>
                <input type="text" class="form-control" id="parking" name="parking" placeholder="e.g., Garage, Street Parking, None" required value="<?php echo isset($_POST['parking']) ? htmlspecialchars($_POST['parking']) : ''; ?>">
            </div>
            <!-- Rent Per Week -->
            <div class="mb-3">
                <label for="rent_per_week" class="form-label">Rent Per Week ($)</label>
                <input type="number" step="0.01" class="form-control" id="rent_per_week" name="rent_per_week" placeholder="e.g., 650.50" required min="0" value="<?php echo isset($_POST['rent_per_week']) ? htmlspecialchars($_POST['rent_per_week']) : ''; ?>">
            </div>
            <!-- Status -->
            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status" required>
                    <option value="Available" <?php echo (isset($_POST['status']) && $_POST['status'] === 'Available') ? 'selected' : ''; ?>>Available</option>
                    <option value="Deposit Taken" <?php echo (isset($_POST['status']) && $_POST['status'] === 'Deposit Taken') ? 'selected' : ''; ?>>Deposit Taken</option>
                    <option value="Leased Already" <?php echo (isset($_POST['status']) && $_POST['status'] === 'Leased Already') ? 'selected' : ''; ?>>Leased Already</option>
                </select>
            </div>
            <!-- Inspection Slots -->
            <div class="mb-3">
                <label for="inspection_slots" class="form-label">Inspection Slots</label>
                <div id="inspection-slots-container">
                    <div class="row mb-2">
                        <div class="col">
                            <label for="inspection_slots_0_start" class="form-label">Start Time</label>
                            <input type="datetime-local" class="form-control" id="inspection_slots_0_start" name="inspection_slots[0][start]" required>
                        </div>
                        <div class="col">
                            <label for="inspection_slots_0_end" class="form-label">End Time</label>
                            <input type="datetime-local" class="form-control" id="inspection_slots_0_end" name="inspection_slots[0][end]" required>
                        </div>
                    </div>
                </div>
                <button type="button" id="add-inspection-slot" class="btn btn-sm btn-secondary">Add More</button>
            </div>
            <!-- Notes -->
            <div class="mb-3">
                <label for="notes" class="form-label">Notes</label>
                <textarea class="form-control" id="notes" name="notes" rows="3"><?php echo isset($_POST['notes']) ? htmlspecialchars($_POST['notes']) : ''; ?></textarea>
            </div>
            <!-- Submit and Cancel Buttons -->
            <button type="submit" class="btn btn-primary">Add Property</button>
            <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>

    <script>
        // Add dynamic fields for multiple inspection slots
        let slotIndex = 1;
        document.getElementById('add-inspection-slot').addEventListener('click', () => {
            const container = document.getElementById('inspection-slots-container');
            const newRow = document.createElement('div');
            newRow.className = 'row mb-2';
            newRow.innerHTML = `
                <div class="col">
                    <label for="inspection_slots_${slotIndex}_start" class="form-label">Start Time</label>
                    <input type="datetime-local" class="form-control" id="inspection_slots_${slotIndex}_start" name="inspection_slots[${slotIndex}][start]" required>
                </div>
                <div class="col">
                    <label for="inspection_slots_${slotIndex}_end" class="form-label">End Time</label>
                    <input type="datetime-local" class="form-control" id="inspection_slots_${slotIndex}_end" name="inspection_slots[${slotIndex}][end]" required>
                </div>
            `;
            container.appendChild(newRow);
            slotIndex++;
        });
    </script>
</body>
</html>
