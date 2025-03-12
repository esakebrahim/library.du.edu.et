<?php
session_start();
include 'database.php';

if (!isset($_SESSION['librarian_id'])) {
    header("Location: ../../login.html");
    exit();
}

$librarianName = $_SESSION['librarian_name'];
$Id = $_SESSION['librarian_id'];

// Get notifications count
$sql = "SELECT COUNT(*) AS unread_count FROM notifications WHERE user_id = ? AND status = 'unread'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $Id);
$stmt->execute();
$result = $stmt->get_result();
$notifications_count = $result->fetch_assoc()['unread_count'];
$stmt->close();

// Get pending feedback count
$pendingFeedbackCount = 0;
$sql = "SELECT COUNT(*) AS pending_count FROM feedback WHERE status = 'pending'";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$pendingFeedbackCount = $row['pending_count']; 
$stmt->close();

// Get librarian role
$sql_role = "SELECT role_name FROM librarian_roles r JOIN users u ON r.id = u.role_id WHERE u.id = ?";
$stmt_role = $conn->prepare($sql_role);
$stmt_role->bind_param("i", $Id);
$stmt_role->execute();
$stmt_role->bind_result($role_name);
$stmt_role->fetch();
$stmt_role->close();

// Get librarian's branch
$sql_branch = "SELECT library_branch_id FROM librarian_branches WHERE librarian_id = ?";
$stmt_branch = $conn->prepare($sql_branch);
$stmt_branch->bind_param("i", $Id);
$stmt_branch->execute();
$stmt_branch->store_result();

if ($stmt_branch->num_rows == 0) {
    die("<div class='alert alert-danger'>Access Denied: You are not assigned to a library branch.</div>");
}
$stmt_branch->bind_result($librarian_branch);
$stmt_branch->fetch();
$stmt_branch->close();

// Get books from assigned branch
$sql = "SELECT b.*, c.name as category_name FROM books b 
        LEFT JOIN categories c ON b.category_id = c.id 
        WHERE b.branch_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $librarian_branch);
$stmt->execute();
$result = $stmt->get_result();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Books</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1e40af;
            --secondary: #64748b;
            --success: #059669;
            --danger: #dc2626;
            --warning: #fbbf24;
            --white: #ffffff;
            --gray-50: #f8fafc;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-800: #1e293b;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: linear-gradient(135deg, #6366f1, #818cf8);
            height: 100vh;
            margin: 0;
            overflow: hidden;
        }

        .d-flex {
            position: relative;
            height: 100vh;
            overflow: hidden;
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            background: rgba(30, 41, 59, 0.8);
            backdrop-filter: blur(10px);
            color: var(--white);
            padding: 1.5rem;
            width: 250px;
            overflow-y: auto;
            z-index: 1000;
            scrollbar-width: thin;
            scrollbar-color: rgba(255,255,255,0.2) transparent;
        }

        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: transparent;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background-color: rgba(255,255,255,0.2);
            border-radius: 3px;
        }

        .sidebar h2 {
            font-size: 1.25rem;
            font-weight: 600;
            padding-bottom: 1rem;
            margin-bottom: 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar a {
            color: var(--gray-200);
            text-decoration: none;
            padding: 0.75rem 1rem;
            display: block;
            border-radius: 0.5rem;
            margin-bottom: 0.25rem;
            transition: all 0.2s;
        }

        .sidebar a:hover {
            background: rgba(255,255,255,0.1);
            color: var(--white);
            transform: translateX(4px);
        }

        .sidebar a i {
            width: 1.5rem;
            margin-right: 0.75rem;
        }

        .notification-badge {
            background: var(--danger);
            color: var(--white);
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            float: right;
        }

        .content {
            margin-left: 250px;
            padding: 2rem;
            width: calc(100% - 250px);
            height: 100vh;
            overflow: hidden;
        }

        .card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 1rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            height: calc(100vh - 4rem);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .search-box {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
            margin-bottom: 1.5rem;
            width: 100%;
            transition: all 0.3s ease;
        }

        .search-box:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
            border-color: #6366f1;
        }

        .table-responsive {
            flex: 1;
            overflow-y: auto;
            margin-top: 1rem;
            scrollbar-width: thin;
            scrollbar-color: rgba(99, 102, 241, 0.2) transparent;
        }

        .table-responsive::-webkit-scrollbar {
            width: 6px;
        }

        .table-responsive::-webkit-scrollbar-track {
            background: transparent;
        }

        .table-responsive::-webkit-scrollbar-thumb {
            background-color: rgba(99, 102, 241, 0.2);
            border-radius: 3px;
        }

        table {
            width: 100%;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-radius: 1rem;
            overflow: hidden;
        }

        th {
            background: var(--gray-800);
            color: var(--white);
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            padding: 1rem;
        }

        td {
            padding: 1rem;
            border-bottom: 1px solid var(--gray-200);
            color: var(--gray-800);
        }

        tr:last-child td {
            border-bottom: none;
        }

        .btn {
            border-radius: 0.5rem;
            padding: 0.5rem 1rem;
            font-weight: 500;
            transition: all 0.2s;
        }

        .btn-warning {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            border: none;
            color: white;
        }

        .btn-danger {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            border: none;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .dropdown-toggle::after {
            float: right;
            margin-top: 8px;
        }

        .submenu {
            display: none;
            list-style: none;
            padding-left: 2rem;
        }

        .dropdown.active .submenu {
            display: block;
        }

        .submenu a {
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .sidebar {
                position: relative;
                width: 100%;
                min-height: auto;
            }
            .content {
                margin-left: 0;
                width: 100%;
            }
            .d-flex {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="d-flex">
        <div class="sidebar">
            <h2>Librarian Dashboard</h2>
            <a href="../frontend/pages/librarian/dashboard.php"><i class="fas fa-home"></i> Home</a>

            <?php
            if ($role_name == 'Cataloging') {
                echo '<a href="../frontend/pages/librarian/add_category.php"><i class="fas fa-plus"></i> Add Category</a>';
                echo '<a href="../frontend/pages/librarian/add_book.php"><i class="fas fa-plus"></i> Add Books</a>';
                echo '<a href="view_books.php" class="active"><i class="fas fa-book"></i> Manage Books</a>';
            } elseif ($role_name == 'Circulation') {
                echo '<a href="../frontend/pages/librarian/pending books.php"><i class="fas fa-arrow-right"></i> Issue Book</a>';
                echo '<a href="../frontend/pages/librarian/confirm return.php"><i class="fas fa-arrow-left"></i> Return Book</a>';
                echo '<a href="../frontend/pages/librarian/manage_extensions.php"><i class="fas fa-arrow-right"></i>Manage extension</a>';

                echo '<div class="dropdown">';
                echo '<a href="#" class="dropdown-toggle"><i class="fas fa-money-bill-wave"></i> Payment <i class="fas fa-chevron-down float-right"></i></a>';
                echo '<ul class="submenu">';
                echo '<li><a href="../frontend/pages/librarian/confirm_fine.php"><i class="fas fa-check-circle"></i> Confirm Fine</a></li>';
                echo '<li><a href="../frontend/pages/librarian/enforce_fine.php"><i class="fas fa-gavel"></i> Enforce Fine</a></li>';
                echo '</ul>';
                echo '</div>';
               
            } elseif ($role_name == 'Acquisition') {
                echo '<a href=""><i class="fas fa-book-open"></i> Manage Acquisitions</a>';
            }

            if ($pendingFeedbackCount > 0) {
                echo '<a href="../frontend/pages/librarian/feedback display.php"><i class="fas fa-comments"></i> View Feedback <span class="badge badge-danger float-right">' . $pendingFeedbackCount . '</span></a>';
            }
            ?>
            <a href="../frontend/pages/librarian/search_books.php"><i class="fas fa-search"></i> Search Book</a>

            <a href="../frontend/pages/librarian/notifications.php"><i class="fas fa-bell"></i> Notifications
            <?php if ($notifications_count > 0): ?>
                <span class="notification-badge"><?php echo $notifications_count; ?></span>
            <?php endif; ?>
            </a>
            <a href="../../login.html"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>

        <div class="content">
            <div class="card">
                <h2 class="mb-4">Book Management</h2>

                <input type="text" id="searchInput" class="search-box" onkeyup="searchBooks()" placeholder="Search by title, author, or category...">

                <div class="table-responsive">
                    <table id="booksTable" class="table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Author</th>
                                <th>ISBN</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['title']); ?></td>
                                        <td><?php echo htmlspecialchars($row['author']); ?></td>
                                        <td><?php echo htmlspecialchars($row['isbn']); ?></td>
                                        <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo $row['status'] == 'available' ? 'success' : 'warning'; ?>">
                                                <?php echo ucfirst($row['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="edit_book.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <button class="btn btn-danger btn-sm" onclick="deleteBook(<?php echo $row['id']; ?>)">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4">No books found in this branch.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let dropdowns = document.querySelectorAll(".dropdown");
            dropdowns.forEach(dropdown => {
                let toggle = dropdown.querySelector(".dropdown-toggle");
                toggle.addEventListener("click", function(event) {
                    event.preventDefault();
                    dropdown.classList.toggle("active");
                });
            });
        });

        function searchBooks() {
            let input = document.getElementById("searchInput").value.toLowerCase();
            let table = document.getElementById("booksTable");
            let rows = table.getElementsByTagName("tr");

            for (let i = 1; i < rows.length; i++) {
                let show = false;
                let cells = rows[i].getElementsByTagName("td");
                
                if (cells.length > 0) {
                    let title = cells[0].textContent.toLowerCase();
                    let author = cells[1].textContent.toLowerCase();
                    let category = cells[3].textContent.toLowerCase();
                    
                    if (title.includes(input) || author.includes(input) || category.includes(input)) {
                        show = true;
                    }
                }
                
                rows[i].style.display = show ? "" : "none";
            }
        }

        function deleteBook(bookId) {
            if (confirm('Are you sure you want to delete this book?')) {
                fetch(`delete_book.php?id=${bookId}`, {
                    method: 'GET'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        location.reload();
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting the book.');
                });
            }
        }
    </script>
</body>
</html>