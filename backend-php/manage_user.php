<?php
// Include the database connection file
require_once 'database.php'; // Adjust the path if necessary

// Initialize filter variable
$searchTerm = '';
if (isset($_POST['search'])) {
    $searchTerm = $conn->real_escape_string(trim($_POST['search']));
}

// Fetch users from the database
$query = "SELECT * FROM users";
if (!empty($searchTerm)) {
    $query .= " WHERE name LIKE '%$searchTerm%' OR email LIKE '%$searchTerm%'";
}
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Users</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #eef2f7;
        }
        #content {
            padding: 20px;
        }
        #searchBox {
            width: 100%;
            max-width: 500px;
            margin: auto;
            padding: 20px;
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
</head>
<body>

<div id="content" class="container mt-5">
    <a href="javascript:history.back()" class="btn btn-secondary mb-3"><i class="fas fa-arrow-left"></i> Back</a>
    <h2>View All Users</h2>

    <!-- Search form -->
    <div id="searchBox">
        <input type="text" id="searchInput" class="form-control" placeholder="Search by name or email" autocomplete="off">
    </div>

    <!-- User table -->
    <table class="table table-bordered mt-3">
        <thead class="thead-dark">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Type</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="userTableBody">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['type']); ?></td>
                        <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                        <td>
                            <a href="update user.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">Update</a>
                            <form action="remove_user.php" method="POST" style="display:inline;">
                                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to remove this user?');">Remove</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center">No users found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
$(document).ready(function() {
    $('#searchInput').on('keyup', function() {
        var searchTerm = $(this).val().toLowerCase();
        $('#userTableBody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(searchTerm) > -1);
        });
    });
});
</script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>