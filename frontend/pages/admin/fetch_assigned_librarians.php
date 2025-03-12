<?php
include '../../../backend-php/database.php';
$result = $conn->query("
    SELECT u.name AS librarian, lb.name, lb.id AS branch_id, u.id AS librarian_id
    FROM librarian_branches lb_rel
    JOIN users u ON lb_rel.librarian_id = u.id
    JOIN library_branches lb ON lb_rel.library_branch_id = lb.id
");

while ($row = $result->fetch_assoc()) {
    echo "<tr>
        <td>{$row['librarian']}</td>
        <td>{$row['name']}</td>
        <td>
            <button class='btn btn-danger btn-sm' onclick='unassign({$row['librarian_id']})'>Unassign</button>
        </td>
    </tr>";
}

$conn->close();
?>

<script>
function unassign(librarian_id) {
    $.post('unassign_librarian.php', { librarian_id: librarian_id }, function (response) {
        alert(response);
        location.reload();
    });
}
</script>
