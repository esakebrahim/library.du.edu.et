<?php
session_start();
require_once '../../../backend-php/database.php';

// Check if the user is a guest
if (!isset($_SESSION['guest_id'])) {
    header("Location: guest_login.php");
    exit();
}

$guest_id = $_SESSION['guest_id'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guest Dashboard</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <h2>Welcome, Guest!</h2>
    <p>You can browse books, journals, and research papers.</p>

    <h3>📚 Available Books</h3>
    <table border="1">
        <tr>
            <th>Title</th>
            <th>Author</th>
            <th>ISBN</th>
            <th>Category</th>
            <th>Status</th>
        </tr>
        <?php
        $sql = "SELECT books.title, books.author, books.isbn, categories.name AS category, books.status 
                FROM books 
                JOIN categories ON books.category_id = categories.id";
        $result = $conn->query($sql);

        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['title']}</td>
                    <td>{$row['author']}</td>
                    <td>{$row['isbn']}</td>
                    <td>{$row['category']}</td>
                    <td>{$row['status']}</td>
                </tr>";
        }
        ?>
    </table>

    <h3>📖 Research Papers & Journals</h3>
    <table border="1">
        <tr>
            <th>Title</th>
            <th>Author</th>
            <th>Published Year</th>
        </tr>
        <?php
        $sql = "SELECT title, author, published_year FROM research_papers";
        $result = $conn->query($sql);

        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['title']}</td>
                    <td>{$row['author']}</td>
                    <td>{$row['published_year']}</td>
                </tr>";
        }
        ?>
    </table>

    <br>
    <a href="logout.php">Logout</a>

</body>
</html>

<?php $conn->close(); ?>
