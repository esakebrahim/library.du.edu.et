<?php
// Start the session
session_start();

// Include the database connection file
include 'database.php';

// Initialize the message variable
$message = '';

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the form data
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password =$_POST['password']; // Hash the password
    $role = $_POST['role'];

    // Prepare the SQL statement to prevent SQL injection
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, type) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $password, $role);

    // Execute the statement
    if ($stmt->execute()) {
        $_SESSION['message'] = "User added successfully"; // Set success message
        header("Location: add_user.php");
        exit();
    } else {
        $_SESSION['message'] = "Error adding user: " . $stmt->error; // Set error message
        header("Location: add_user.php");
        exit();
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
}

// Check if there is a message in the session and store it in a variable
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']); // Clear message after displaying
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add User</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            margin-top: 50px;
        }
        .card {
            border: 1px solid #007bff;
        }
        .card-header {
            background-color: #007bff;
            color: white;
        }
        .form-group label {
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="container">
    <a href="../frontend/pages/admin/dashboard.php" class="btn btn-secondary mb-3"><i class="fas fa-arrow-left"></i> Back</a>
    <div class="row justify-content-center">
        <!-- User Registration Card -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header text-center">
                    <h5>Register User</h5>
                </div>
                <div class="card-body">

                    <?php if ($message): // Display message if exists ?>
                        <div class="alert alert-info">
                            <?php echo $message; ?>
                        </div>
                    <?php endif; ?>

                    <form action="add_user.php" method="post">
                        <div class="form-group">
                            <label for="name">Name:</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email:</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="password">Password:</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="form-group">
                            <label for="role">Role:</label>
                            <select class="form-control" id="role" name="role" required>
                                <option value="student">Student</option>
                                <option value="teacher">Teacher</option>
                                <option value="librarian">Librarian</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">Add User</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>