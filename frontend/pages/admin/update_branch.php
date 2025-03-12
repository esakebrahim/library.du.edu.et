<?php
// Include the database connection file
include_once '../../../backend-php/database.php';

// Check if the branch ID is provided
if (isset($_GET['id'])) {
    $branch_id = intval($_GET['id']);

    // Fetch the current branch details
    $query = "SELECT lb.name AS branch_name, c.name AS campus_name 
              FROM library_branches lb 
              JOIN campuses c ON lb.campus_id = c.id 
              WHERE lb.id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $branch_id);
    $stmt->execute();
    $stmt->bind_result($branch_name, $campus_name);
    $stmt->fetch();
    $stmt->close();
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['branch_name'], $_POST['campus'])) {
    $branch_name = $conn->real_escape_string(trim($_POST['branch_name']));
    $campus_name = $conn->real_escape_string(trim($_POST['campus']));

    // First, get the campus ID using campus name
    $campus_query = "SELECT id FROM campuses WHERE name = ?";
    $stmt = $conn->prepare($campus_query);
    $stmt->bind_param("s", $campus_name);
    $stmt->execute();
    $stmt->bind_result($campus_id);
    
    if ($stmt->fetch()) {
        // If campus exists, update the branch
        $stmt->close();
        
        // Update the library branch
        $update_query = "UPDATE library_branches SET name = ?, campus_id = ? WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("sii", $branch_name, $campus_id, $branch_id);

        if ($stmt->execute()) {
            $message = "Branch updated successfully.";
            header("Location: manage_branch.php?message=" . urlencode($message));
            exit();
        } else {
            $message = "Error updating branch: " . $stmt->error;
        }
    } else {
        $message = "The specified campus does not exist.";
    }

    // Close the statement
    $stmt->close();
}

// Fetch all campuses for the dropdown
$campus_query = "SELECT name FROM campuses";
$campuses_result = $conn->query($campus_query);

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Library Branch</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .back-button {
            margin: 20px 0;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="back-button">
        <a href="javascript:history.back()" class="btn btn-primary">
            &larr; Back
        </a>
    </div>
    <div class="row justify-content-center">
        <div class="col-md-8 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5>Update Library Branch</h5>
                </div>
                <div class="card-body">
                    <?php if (isset($message)): ?>
                        <div class="alert alert-info" role="alert">
                            <?php echo htmlspecialchars($message); ?>
                        </div>
                    <?php endif; ?>
                    <form action="" method="post">
                        <div class="form-group">
                            <label for="branch_name">Branch Name:</label>
                            <input type="text" class="form-control" id="branch_name" name="branch_name" value="<?php echo htmlspecialchars($branch_name); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="campus">Campus:</label>
                            <select class="form-control" id="campus" name="campus" required>
                                <option value="" disabled>Select a campus</option>
                                <?php while ($row = $campuses_result->fetch_assoc()): ?>
                                    <option value="<?php echo htmlspecialchars($row['name']); ?>" <?php echo ($row['name'] == $campus_name) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($row['name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-success">Update Branch</button>
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