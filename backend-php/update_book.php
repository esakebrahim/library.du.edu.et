<?php
session_start();
require_once 'database.php'; // Include your database connection file

$message = "";

// Check if the ID is present in the URL
if (isset($_GET['id'])) {
    $book_id = (int)$_GET['id'];

    // Fetch the current details of the book
    $sql = "SELECT title, author, isbn, published_year, edition, price, location, category_id, branch_id FROM books WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $book_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $book = $result->fetch_assoc();
    } else {
        $message = "Book not found.";
    }

    // Close the statement
    $stmt->close();
} else {
    $message = "Invalid request.";
}

// Fetch library branches for the dropdown
$branches = [];
$branch_sql = "SELECT id, name FROM library_branches";
$branch_result = $conn->query($branch_sql);

if ($branch_result->num_rows > 0) {
    while ($row = $branch_result->fetch_assoc()) {
        $branches[] = $row;
    }
}

// Fetch categories for the dropdown
$categories = [];
$category_query = "SELECT id, name FROM categories";
$category_result = $conn->query($category_query);

if ($category_result->num_rows > 0) {
    while ($row = $category_result->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Update logic
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate input
    $title = $conn->real_escape_string(trim($_POST['title']));
    $author = $conn->real_escape_string(trim($_POST['author']));
    $isbn = $conn->real_escape_string(trim($_POST['isbn']));
    $published_year = (int)$_POST['published_year'];
    $edition = (int)$_POST['edition'];
    $price = floatval($_POST['price']);
    $location = $conn->real_escape_string(trim($_POST['location']));
    $branch_id = (int)$_POST['branch_id']; // Get selected branch ID
    $category_id = (int)$_POST['category_id']; // Get selected category ID

    // Validate inputs
    $errors = [];
    if (empty($title) || empty($author) || empty($isbn) || empty($location)) {
        $errors[] = "All fields are required.";
    }
    if (!preg_match('/^(978|979)[0-9]{10}$/', $isbn)) {
        $errors[] = "ISBN must be exactly 13 digits starting with 978 or 979.";
    }
    if ($published_year < 1900 || $published_year > date("Y")) {
        $errors[] = "Published year must be between 1900 and the current year.";
    }
    if ($price <= 0) {
        $errors[] = "Price must be a positive number.";
    }
    if (empty($location)) {
        $errors[] = "Location is required.";
    }
    if ($category_id <= 0) {
        $errors[] = "Category is required.";
    }

    if (empty($errors)) {
        $check_isbn_sql = "SELECT id FROM books WHERE isbn = ? AND (title != ? OR author != ? OR edition != ?)";
        $stmt_check_isbn = $conn->prepare($check_isbn_sql);
        $stmt_check_isbn->bind_param("issi", $isbn, $title, $author, $edition);
        $stmt_check_isbn->execute();
        $stmt_check_isbn->store_result();

        if ($stmt_check_isbn->num_rows > 0) {
            $errors[] = "ISBN is already in use by another book ";
        }

        // Close the statement
        $stmt_check_isbn->close();
    }
    if (empty($errors)) {
        // Update all books that match the same title, author, and edition
        $update_sql = "UPDATE books 
                       SET title = ?, author = ?, isbn = ?, published_year = ?, price = ?, location = ?, category_id = ?,  edition = ?
                       WHERE title = ? AND author = ? AND edition = ?";
    
        if ($stmt = $conn->prepare($update_sql)) {
            $stmt->bind_param("ssiiisiissi", 
                $title, $author, $isbn, $published_year, $price, $location, $category_id,  $edition,
                $old_title, $old_author, $old_edition
            );
            $old_title = $book['title'];
            $old_author = $book['author'];
            $old_edition = $book['edition'];

            // Execute the prepared statement
            if ($stmt->execute()) {
                $message = "Book updated successfully!";

                // Log the action in the librarian_actions_log
                $librarian_id = $_SESSION['librarian_id']; // Assuming the librarian's ID is stored in the session

                $sql_log = "INSERT INTO librarian_actions_log (librarian_id, action, book_id, library_branch_id) 
                            VALUES (?, 'Update', ?, ?)";
                $stmt_log = $conn->prepare($sql_log);
                $stmt_log->bind_param("iii", $librarian_id, $book_id, $branch_id);

                if ($stmt_log->execute()) {
                    // Action successfully logged
                } else {
                    $message = "Error logging the action: " . $stmt_log->error;
                }

                // Close the log statement
                $stmt_log->close();

                $update_branch_sql = "UPDATE books SET branch_id = ? WHERE id = ?";
                if ($stmt_branch = $conn->prepare($update_branch_sql)) {
                    $stmt_branch->bind_param("ii", $branch_id, $book_id);
                    $stmt_branch->execute();
                    $stmt_branch->close();
                }

            } else {
                $message = "Error updating book: " . $stmt->error;
            }

            // Close the statement
            $stmt->close();
        } else {
            $message = "Preparation failed: " . $conn->error;
        }
    } else {
        $message = implode("<br>", $errors); // Show validation errors
    }
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Book</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<div class="container">
    <div class="mb-4">
        <a href="view_books.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <h2>Update Book</h2>
    <?php if (!empty($message)): ?>
        <div class='alert alert-info'><?php echo $message; ?></div>
    <?php endif; ?>

    <?php if (isset($book)): ?>
        <form action="" method="post" id="updateBookForm">
            <div class="form-group">
                <label for="title">Title:</label>
                <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($book['title']); ?>" required>
            </div>
            <div class="form-group">
                <label for="author">Author:</label>
                <input type="text" class="form-control" id="author" name="author" value="<?php echo htmlspecialchars($book['author']); ?>" required>
            </div>
            <div class="form-group">
                <label for="isbn">ISBN:</label>
                <input type="text" class="form-control" id="isbn" name="isbn" value="<?php echo htmlspecialchars($book['isbn']); ?>" required>
            </div>
            <div class="form-group">
                <label for="published_year">Published Year:</label>
                <input type="number" class="form-control" id="published_year" name="published_year" value="<?php echo htmlspecialchars($book['published_year']); ?>" required>
            </div>
            <div class="form-group">
                <label for="edition">Edition:</label>
                <input type="number" class="form-control" id="edition" name="edition" value="<?php echo htmlspecialchars($book['edition']); ?>" required>
            </div>
            <div class="form-group">
                <label for="price">Price:</label>
                <input type="number" step="0.01" class="form-control" id="price" name="price" value="<?php echo htmlspecialchars($book['price']); ?>" required>
            </div>
            <div class="form-group">
                <label for="locationInput">Location:</label>
                <input type="text" id="locationInput" name="location" class="form-control" value="<?php echo htmlspecialchars($book['location']); ?>" required>
            </div>
            <div class="form-group">
                <label for="categorySelect">Category:</label>
                <select class="form-control" id="categorySelect" name="category_id" required>
                    <option value="" disabled>Select a category</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['id']; ?>" <?php echo ($category['id'] == $book['category_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($category['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        
            <button type="submit" class="btn btn-primary">Update Book</button>
        </form>
    <?php endif; ?>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const locationInput = document.getElementById("locationInput");
        const categorySelect = document.getElementById("categorySelect");

        // Mapping of location prefixes to category names
        const categoryLocationMap = {
            "Computer Science, Information & General Work": [0, 99],
            "Philosophy & Psychology": [100, 199],
            "Religion": [200, 299],
            "Social Sciences": [300, 399],
            "Language": [400, 499],
            "Science": [500, 599],
            "Technology": [600, 699],
            "Arts & Recreation": [700, 799],
            "Literature": [800, 899],
            "History & Geography": [900, 999],    
        };

        locationInput.addEventListener("input", function () {
            // Call this function to update edition and published year
            extractDetailsFromLocation();
            
            const firstThreeDigits = locationInput.value.match(/^\d{3}/);
            if (firstThreeDigits) {
                const categoryNumber = parseInt(firstThreeDigits[0], 10);

                for (const [category, range] of Object.entries(categoryLocationMap)) {
                    if (categoryNumber >= range[0] && categoryNumber <= range[1]) {
                        for (let option of categorySelect.options) {
                            if (option.text.trim().toLowerCase() === category.toLowerCase()) {
                                option.selected = true;
                                break;
                            }
                        }
                        break;
                    }
                }
            }
        });

        function extractDetailsFromLocation() {
            let locationValue = locationInput.value.trim();
            // Ensure the regex pattern matches the expected format
            let pattern = /^(\d{3}\.\d{3}) edition(\d+) (\d{4}) C\.\d+$/i;

            let match = locationValue.match(pattern);
            if (match) {
                document.getElementById("edition").value = match[2]; // Edition
                document.getElementById("published_year").value = match[3]; // Published Year
            } else {
                document.getElementById("edition").value = "";
                document.getElementById("published_year").value = "";
            }
        }

        // Form validation
        document.getElementById("updateBookForm").addEventListener("submit", function (event) {
            let errors = [];
            const title = document.getElementById("title").value.trim();
            const author = document.getElementById("author").value.trim();
            const isbn = document.getElementById("isbn").value.trim();
            const publishedYear = parseInt(document.getElementById("published_year").value, 10);
            const edition = parseInt(document.getElementById("edition").value, 10);
            const price = parseFloat(document.getElementById("price").value);
            const location = locationInput.value.trim();
            const category = categorySelect.value;

            const currentYear = new Date().getFullYear();

            if (title === "") errors.push("Title is required.");
            if (author === "") errors.push("Author is required.");
            if (!/^(978|979)[0-9]{10}$/.test(isbn)) {
                errors.push("ISBN must be exactly 13 digits starting with 978 or 979.");
            }
            if (isNaN(publishedYear) || publishedYear < 1900 || publishedYear > currentYear) {
                errors.push(`Published year must be between 1900 and ${currentYear}.`);
            }
            if (isNaN(edition)) errors.push("Edition is required.");
            if (isNaN(price) || price <= 0) errors.push("Price must be a positive number.");
            if (location === "") errors.push("Location is required.");
            if (category === "") errors.push("Category is required.");

            if (errors.length > 0) {
                event.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Errors',
                    html: errors.join('<br>'),
                    confirmButtonColor: '#0069d9'
                });
            }
        });
    });
</script>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>