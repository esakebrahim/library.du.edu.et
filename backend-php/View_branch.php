<?php
// Include the database connection file
require_once 'database.php';

// Fetch all library branches along with their respective campuses
$query = "SELECT lb.id, lb.name AS branch_name, c.name AS campus_name 
          FROM library_branches lb 
          JOIN campuses c ON lb.campus_id = c.id";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Library Branches</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }

        /* Table Styling */
        table {
            margin-top: 20px;
        }
        th {
            background-color: #007bff;
            color: white;
        }

        .back-button {
            margin: 20px 0;
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
        <div class="col-md-10 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5>Library Branches</h5>
                </div>
                <div class="card-body">
                    <?php if ($result->num_rows > 0): ?>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Branch Name</th>
                                    <th>Campus Name</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['id']); ?></td>
                                        <td><?php echo htmlspecialchars($row['branch_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['campus_name']); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="alert alert-warning" role="alert">
                            No branches found.
                        </div>
                    <?php endif; ?>
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

<?php
// Close the database connection
$conn->close();
?>