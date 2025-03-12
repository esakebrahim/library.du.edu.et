<?php
// Include the database connection file
require_once 'database.php'; // Adjust the path if necessary

// Initialize variables
$user = null;

if (isset($_GET['id'])) {
    // Get the user ID from the URL
    $user_id = $conn->real_escape_string($_GET['id']);

    // Fetch the user data from the database
    $query = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
    }
}

// Check if the form has been submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $conn->real_escape_string(trim($_POST['name']));
    $email = $conn->real_escape_string(trim($_POST['email']));
    $password = $_POST['password']; // raw password input

    // Prepare the update query
    $update_query = "UPDATE users SET name = ?, email = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("ssi", $name, $email, $user_id);

    // Execute the update statement
    if ($update_stmt->execute()) {
        // If a password was provided, update it as well
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT); // Hash the password
            $password_update_query = "UPDATE users SET password = ? WHERE id = ?";
            $password_update_stmt = $conn->prepare($password_update_query);
            $password_update_stmt->bind_param("si", $hashed_password, $user_id);
            $password_update_stmt->execute();
            $password_update_stmt->close();
        }

        // Redirect to view_users.php after successful update
        header("Location: view_users.php");
        exit;
    } else {
        echo "Error updating user: " . $update_stmt->error;
    }

    // Close the statement
    $update_stmt->close();
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update User</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>

<div class="container mt-5">
    <h2>Update User</h2>

    <?php if ($user): ?>
    <form action="" method="POST">
        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
        </div>
        <div class="form-group">
            <label for="password">New Password (leave blank to keep current password)</label>
            <input type="password" class="form-control" id="password" name="password" placeholder="Enter new password">
        </div>
        <button type="submit" class="btn btn-primary">Update User</button>
        <a href="view_users.php" class="btn btn-secondary">Cancel</a>
    </form>
    <?php else: ?>
        <div class="alert alert-danger">User not found.</div>
    <?php endif; ?>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>