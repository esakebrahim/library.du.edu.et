<?php
// Include database connection
include_once '../../../backend-php/database.php';

// Get list of all librarians
$librarians_query = "SELECT id, name FROM users WHERE type = 'librarian'";
$librarians_result = mysqli_query($conn, $librarians_query);

// Get list of roles
$roles_query = "SELECT id, role_name FROM librarian_roles";
$roles_result = mysqli_query($conn, $roles_query);

// Get assigned roles
$assigned_roles_query = "
    SELECT users.id AS librarian_id, users.name AS librarian_name, librarian_roles.role_name 
    FROM users 
    JOIN librarian_roles ON users.role_id = librarian_roles.id 
    WHERE users.type = 'librarian'";
$assigned_roles_result = mysqli_query($conn, $assigned_roles_query);

// Handle role assignment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['librarian_id']) && isset($_POST['role_id'])) {
    $librarian_id = $_POST['librarian_id'];
    $role_id = $_POST['role_id'];

    // Prepare the SQL query to assign the role
    $assign_query = "UPDATE users SET role_id = ? WHERE id = ?";
    $stmt = $conn->prepare($assign_query);
    $stmt->bind_param("ii", $role_id, $librarian_id);
    $stmt->execute();
    header("Location: " . $_SERVER['PHP_SELF']); // Refresh page
}

// Handle unassign role request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['unassign_id'])) {
    $unassign_id = $_POST['unassign_id'];

    // Prepare the SQL query to unassign the role
    $unassign_query = "UPDATE users SET role_id = NULL WHERE id = ?";
    $stmt = $conn->prepare($unassign_query);
    
    if ($stmt) {
        $stmt->bind_param("i", $unassign_id); // Bind the ID parameter
        $stmt->execute(); // Execute the statement
        $stmt->close(); // Close the statement
    }

    header("Location: " . $_SERVER['PHP_SELF']); // Redirect to the same page to refresh
    exit; // Ensure no further code is executed
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Assign Roles</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }

        h1 {
            color: #333;
            text-align: center;
        }

        form {
            max-width: 400px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
        }

        select {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
        }

        button {
            width: 100%;
            padding: 10px;
            background-color: #5cb85c;
            border: none;
            border-radius: 4px;
            color: white;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background-color: #4cae4c;
        }

        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }

        th, td {
            padding: 10px;
            border: 1px solid #ccc;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .unassign-button {
            background-color: #d9534f;
        }

        .unassign-button:hover {
            background-color: #c9302c;
        }
    </style>
    <script>
        function confirmUnassign() {
            return confirm("Are you sure you want to unassign this role?");
        }
    </script>
</head>
<body>
    <h1>Assign Roles to Librarians</h1>

    <form action="" method="POST">
        <label for="librarian_id">Select Librarian:</label>
        <select name="librarian_id" id="librarian_id">
            <?php while ($librarian = mysqli_fetch_assoc($librarians_result)) { ?>
                <option value="<?= $librarian['id'] ?>"><?= $librarian['name'] ?></option>
            <?php } ?>
        </select>

        <label for="role_id">Select Role:</label>
        <select name="role_id" id="role_id">
            <?php while ($role = mysqli_fetch_assoc($roles_result)) { ?>
                <option value="<?= $role['id'] ?>"><?= $role['role_name'] ?></option>
            <?php } ?>
        </select>

        <button type="submit">Assign Role</button>
    </form>

    <h2>Assigned Librarians</h2>
    <table>
        <thead>
            <tr>
                <th>Librarian Name</th>
                <th>Assigned Role</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($assigned = mysqli_fetch_assoc($assigned_roles_result)) { ?>
                <tr>
                    <td><?= $assigned['librarian_name'] ?></td>
                    <td><?= $assigned['role_name'] ?></td>
                    <td>
                        <form action="" method="POST" style="display:inline;" onsubmit="return confirmUnassign();">
                            <input type="hidden" name="unassign_id" value="<?= $assigned['librarian_id'] ?>">
                            <button type="submit" class="unassign-button">Unassign</button>
                        </form>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</body>
</html>