<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'database.php'; // Database connection file

// Check if librarian is logged in
if (!isset($_SESSION['librarian_id'])) {
    die("Access denied. Please log in as a librarian.");
}

$librarian_id = $_SESSION['librarian_id'];
$message = ""; // Message for feedback
$categories = []; // Store categories
$assigned_branch_name = ""; // Store librarian's branch

// Fetch the librarian's assigned branch
$sql = "SELECT library_branch_id FROM librarian_branches WHERE librarian_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $librarian_id);
$stmt->execute();
$stmt->bind_result($branch_id);
$stmt->fetch();
$stmt->close();

// Fetch branch name
$branch_query = "SELECT name FROM library_branches WHERE id = ?";
$stmt_branch = $conn->prepare($branch_query);
$stmt_branch->bind_param("i", $branch_id);
$stmt_branch->execute();
$stmt_branch->bind_result($assigned_branch_name);
$stmt_branch->fetch();
$stmt_branch->close();

// Fetch book categories
$category_query = "SELECT id, name FROM categories";
$category_result = $conn->query($category_query);
if ($category_result) {
    while ($row = $category_result->fetch_assoc()) {
        $categories[] = $row;
    }
    $category_result->free();
}


// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['title'], $_POST['author'], $_POST['isbn'], $_POST['category_id'], $_POST['published_year'], $_POST['edition'], $_POST['price'], $_POST['location'])) {

        $title         = $conn->real_escape_string(trim($_POST['title']));
        $author        = $conn->real_escape_string(trim($_POST['author']));
        $isbn          = $conn->real_escape_string(trim($_POST['isbn']));
        $location      = $conn->real_escape_string(trim($_POST['location']));
        $category_id   = (int)$_POST['category_id'];
        $published_year= (int)$_POST['published_year'];
        $edition       = (int)$_POST['edition'];
        $price         = floatval($_POST['price']);
        $parent_book_id = NULL;


        // Check if this book (same title, author, edition) already exists.
        $parent_book_query = "SELECT id, isbn FROM books WHERE title = ? AND author = ? AND edition = ? ORDER BY id ASC LIMIT 1";
        $parent_book_stmt = $conn->prepare($parent_book_query);
        $parent_book_stmt->bind_param("ssi", $title, $author, $edition);
        $parent_book_stmt->execute();
        $parent_book_stmt->bind_result($first_book_id, $existing_isbn);

        if ($parent_book_stmt->fetch()) {
            $parent_book_id = $first_book_id;
        
            // Check if entered ISBN is different
            if ($isbn !== $existing_isbn) {
                echo "<script>
                    var confirmChange = confirm('The ISBN you entered does not match the existing book copy. The correct ISBN is $existing_isbn. Do you want to proceed with this ISBN?');
                    if (!confirmChange) {
                        window.history.back();
                    }
                </script>";
                $isbn = $existing_isbn; // Assign correct ISBN only if the user confirms
            }
        }
         else {
            // If this is a new book, ensure the ISBN is unique across different books
            $isbn_check_query = "SELECT COUNT(*) FROM books WHERE isbn = ? AND (parent_book_id IS NULL OR parent_book_id != ?)";
            $isbn_check_stmt = $conn->prepare($isbn_check_query);
            $isbn_check_stmt->bind_param("si", $isbn, $parent_book_id);
            $isbn_check_stmt->execute();
            $isbn_check_stmt->bind_result($isbn_count);
            $isbn_check_stmt->fetch();
            $isbn_check_stmt->close();
            
            if ($isbn_count > 0) {
                echo "<script>alert('Error: ISBN already exists for a different book.'); window.history.back();</script>";
                exit;
            }
        }
        $parent_book_stmt->close();

        // Extract copy number from location (expected format: "25.231 edition1 2025 C.1")
        preg_match('/C\.(\d+)/i', $location, $copy_matches);
        $copy_number = isset($copy_matches[1]) ? (int)$copy_matches[1] : 1;

        // Determine the next available copy number for this book
        $copy_query = "SELECT MAX(copy_number) FROM books WHERE title = ? AND author = ? AND edition = ?";
        $copy_stmt = $conn->prepare($copy_query);
        $copy_stmt->bind_param("ssi", $title, $author, $edition);
        $copy_stmt->execute();
        $copy_stmt->bind_result($max_copy);
        $copy_stmt->fetch();
        $copy_stmt->close();

        $next_copy_number = $max_copy ? $max_copy + 1 : 1;
        // Replace the copy part in the location with the next available copy number.
        $location = preg_replace('/C\.\d+/i', 'C.' . $next_copy_number, $location);

       

        // Insert new book copy.
        $sql = "INSERT INTO books (title, author, isbn, category_id, branch_id, published_year, edition, price, parent_book_id, status, location, copy_number) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'available', ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssiiiidisi", $title, $author, $isbn, $category_id, $branch_id, $published_year, $edition, $price, $parent_book_id, $location, $next_copy_number);

        if ($stmt->execute()) {
            $book_id = $stmt->insert_id;
            $message = "New copy of $title (Edition $edition, Copy $next_copy_number) added successfully!";

            // Log librarian action
            $action = "Added book copy: $title (Copy $next_copy_number)";
            $sql_log = "INSERT INTO librarian_actions_log (librarian_id, action, book_id, library_branch_id) 
                        VALUES (?, ?, ?, ?)";
            $stmt_log = $conn->prepare($sql_log);
            $stmt_log->bind_param("isii", $librarian_id, $action, $book_id, $branch_id);
            $stmt_log->execute();
            $stmt_log->close();

            // Notify students and teachers
            $user_sql = "SELECT id FROM users WHERE type IN ('student', 'teacher')";
            $user_stmt = $conn->prepare($user_sql);
            $user_stmt->execute();
            $result = $user_stmt->get_result();

            while ($row = $result->fetch_assoc()) {
                $user_id = $row['id'];
                $notif_sql = "INSERT INTO notifications (user_id, message) VALUES (?, ?)";
                $notif_stmt = $conn->prepare($notif_sql);
                $notif_stmt->bind_param("is", $user_id, $message);
                $notif_stmt->execute();
                $notif_stmt->close();
            }
            $user_stmt->close();

        } else {
            $message = "Error adding book: " . $stmt->error;
        }

        $stmt->close();
    } else {
        $message = "Please fill in all fields.";
    }
}

$conn->close();
include '../frontend/pages/librarian/books.php';
?>
