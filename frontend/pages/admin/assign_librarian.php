<?php
include '../../../backend-php/database.php';

$librarian_id = $_POST['librarian_id'];
$branch_id = $_POST['library_branch_id'];

// Check if the librarian is already assigned to a branch
$check = $conn->prepare("
    SELECT library_branches.name 
    FROM librarian_branches 
    JOIN library_branches ON librarian_branches.library_branch_id = library_branches.id 
    WHERE librarian_branches.librarian_id = ?
");
$check->bind_param("i", $librarian_id);
$check->execute();
$result = $check->get_result();
$row = $result->fetch_assoc();

if ($row) {
    // Librarian is already assigned, display assigned branch
    echo "<div class='alert alert-warning'>Librarian is already assigned to <b>" . htmlspecialchars($row['name']) . "</b>. Cannot reassign.</div>";
} else {
    // Assign librarian to branch if not already assigned
    $stmt = $conn->prepare("INSERT INTO librarian_branches (librarian_id, library_branch_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $librarian_id, $branch_id);
    
    if ($stmt->execute()) {
        echo "<div class='alert alert-success'>Librarian assigned successfully!</div>";
    } else {
        echo "<div class='alert alert-danger'>Error assigning librarian.</div>";
    }
}

$conn->close();
?>
