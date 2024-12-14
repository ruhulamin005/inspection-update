<?php
// Start session
session_start();

// Include database connection
require_once 'db.php';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    try {
        // Prepare SQL query to fetch admin user
        $query = $conn->prepare("SELECT * FROM Admin WHERE username = :username LIMIT 1");
        $query->bindParam(':username', $username, PDO::PARAM_STR);
        $query->execute();

        $user = $query->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Check if password matches (plain text comparison)
            if ($user['password'] === $password) {
                // Set session variables
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];

                // Redirect to dashboard
                header("Location: dashboard.php");
                exit;
            } else {
                $error = "Invalid username or password.";
            }
        } else {
            $error = "User does not exist.";
        }
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Property Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
</head>
<body>
    <div class="container vh-100 d-flex justify-content-center align-items-center">
        <div class="card shadow p-4" style="width: 100%; max-width: 400px;">
            <h2 class="text-center mb-4">Admin Login</h2>
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger text-center"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form action="login.php" method="POST">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" placeholder="Enter username" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Enter password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Login</button>
            </form>
            <div class="text-center mt-3">
                <small>&copy; 2024 Property Management System</small>
            </div>
        </div>
    </div>
</body>
</html>
