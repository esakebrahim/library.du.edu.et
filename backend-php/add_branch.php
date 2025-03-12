<?php
// Include the database connection file
require_once 'database.php';

// Check if the form fields are set
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['branch_name'], $_POST['campus'])) {
    // Clean the input data
    $branch_name = $conn->real_escape_string(trim($_POST['branch_name']));
    $campus_name = $conn->real_escape_string(trim($_POST['campus']));

    // First, get the campus ID using campus name
    $campus_query = "SELECT id FROM campuses WHERE name = ?";
    $stmt = $conn->prepare($campus_query);
    
    // Bind campus name as a parameter
    $stmt->bind_param("s", $campus_name);
    
    // Execute and get the campus ID
    $stmt->execute();
    $stmt->bind_result($campus_id);
    
    if ($stmt->fetch()) {
        // If campus exists, insert the new branch
        $stmt->close();
        
        // Prepare the SQL statement to insert the branch
        $sql = "INSERT INTO library_branches (name, campus_id) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);

        // Bind the parameters
        $stmt->bind_param("si", $branch_name, $campus_id);

        // Execute the statement
        if ($stmt->execute()) {
            $message = "New branch '$branch_name' added successfully for campus '$campus_name'.";
        } else {
            $message = "Error adding branch: " . $stmt->error;
        }

    } else {
        $message = "The specified campus does not exist.";
    }

    // Close the statement
    $stmt->close();
} else {
    $message = "Please fill in all fields.";
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Library Branch</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .back-button {
            margin: 20px 0;
        }

        .card {
            border: 1px solid #007bff;
        }
        
        .card-header {
            background-color: #007bff;
            color: white;
        }
        
        .alert {
            margin-top: 20px;
        }
        
        .form-group label {
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="back-button">
        <a href="../frontend/pages/admin/dashboard.php" class="btn btn-primary">
            &larr; Back
        </a>
    </div>
    <div class="row justify-content-center">
        <!-- Add Library Branch Card -->
        <div class="col-md-8 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5>Add Library Branch</h5>
                </div>
                <div class="card-body">
                    <form action="" method="post"> <!-- Action is the same page -->
                        <div class="form-group">
                            <label for="branch_name">Branch Name:</label>
                            <input type="text" class="form-control" id="branch_name" name="branch_name" required>
                        </div>
                        <div class="form-group">
                            <label for="campus">Campus:</label>
                            <select class="form-control" id="campus" name="campus" required>
                                <option value="" disabled selected>Select a campus</option>
                                <option value="Hasedilla">Hasedilla</option>
                                <option value="Main">Main</option>
                                <option value="Semera">Semera</option>
                                <option value="Health Campus">Health Campus</option>
                            </select>
                        </div>
                        <?php echo isset($message) ? "<div class='alert alert-info mt-3'>$message</div>" : "" ?>
                        <button type="submit" class="btn btn-success">Add Branch</button>
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